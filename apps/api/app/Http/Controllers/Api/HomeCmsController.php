<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Locale;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeCmsController extends Controller
{
    use ApiResponse;

    public function publicIndex(Request $request)
    {
        $locale = $this->requestLocale($request);
        $this->ensureDefaults();

        $payload = Cache::remember(
            'public_api:v'.Cache::get('public_content_cache_version', '1').':home-cms:'.$locale,
            now()->addSeconds((int) config('cache.public_api_ttl', 600)),
            fn () => $this->localizedSections($locale, true)
        );

        return $this->successResponse($payload, 'Home CMS content retrieved successfully');
    }

    public function adminShow()
    {
        $this->ensureDefaults();

        return $this->successResponse([
            'locales' => $this->localeCodes(),
            'sections' => HomeSection::with(['translations', 'items.translations'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ], 'Home CMS content retrieved');
    }

    public function adminUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.section_key' => 'required|string|max:100',
            'sections.*.section_type' => 'nullable|string|max:100',
            'sections.*.sort_order' => 'nullable|integer|min:0',
            'sections.*.is_active' => 'boolean',
            'sections.*.settings' => 'nullable|array',
            'sections.*.translations' => 'required|array',
            'sections.*.translations.*.eyebrow' => 'nullable|string|max:255',
            'sections.*.translations.*.title' => 'nullable|string|max:255',
            'sections.*.translations.*.subtitle' => 'nullable|string',
            'sections.*.translations.*.description' => 'nullable|string',
            'sections.*.translations.*.secondary_title' => 'nullable|string|max:255',
            'sections.*.translations.*.secondary_description' => 'nullable|string',
            'sections.*.translations.*.cta_label' => 'nullable|string|max:255',
            'sections.*.translations.*.cta_url' => 'nullable|string|max:255',
            'sections.*.translations.*.image_alt' => 'nullable|string|max:255',
            'sections.*.items' => 'nullable|array',
            'sections.*.items.*.item_key' => 'nullable|string|max:100',
            'sections.*.items.*.icon' => 'nullable|string|max:100',
            'sections.*.items.*.value' => 'nullable|string|max:100',
            'sections.*.items.*.suffix' => 'nullable|string|max:50',
            'sections.*.items.*.url' => 'nullable|string|max:255',
            'sections.*.items.*.sort_order' => 'nullable|integer|min:0',
            'sections.*.items.*.is_active' => 'boolean',
            'sections.*.items.*.settings' => 'nullable|array',
            'sections.*.items.*.translations' => 'required|array',
            'sections.*.items.*.translations.*.title' => 'nullable|string|max:255',
            'sections.*.items.*.translations.*.description' => 'nullable|string',
            'sections.*.items.*.translations.*.label' => 'nullable|string|max:255',
            'sections.*.items.*.translations.*.action_label' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        DB::transaction(function () use ($validator) {
            foreach ($validator->validated()['sections'] as $sectionData) {
                $section = HomeSection::firstOrCreate(['section_key' => $sectionData['section_key']]);
                $section->update([
                    'section_type' => $sectionData['section_type'] ?? $section->section_type ?? 'content',
                    'sort_order' => $sectionData['sort_order'] ?? $section->sort_order ?? 0,
                    'is_active' => $sectionData['is_active'] ?? true,
                    'settings' => $sectionData['settings'] ?? [],
                ]);

                foreach ($sectionData['translations'] as $locale => $fields) {
                    $section->translations()->updateOrCreate(['locale' => $locale], $fields + ['locale' => $locale]);
                }

                $keptItemIds = [];
                foreach ($sectionData['items'] ?? [] as $index => $itemData) {
                    $lookup = [
                        'home_section_id' => $section->id,
                        'item_key' => $itemData['item_key'] ?: 'item_'.$index,
                    ];
                    $item = HomeSectionItem::firstOrCreate($lookup);
                    $item->update([
                        'icon' => $itemData['icon'] ?? null,
                        'value' => $itemData['value'] ?? null,
                        'suffix' => $itemData['suffix'] ?? null,
                        'url' => $itemData['url'] ?? null,
                        'sort_order' => $itemData['sort_order'] ?? $index,
                        'is_active' => $itemData['is_active'] ?? true,
                        'settings' => $itemData['settings'] ?? [],
                    ]);
                    $keptItemIds[] = $item->id;

                    foreach ($itemData['translations'] as $locale => $fields) {
                        $item->translations()->updateOrCreate(['locale' => $locale], $fields + ['locale' => $locale]);
                    }
                }

                if (array_key_exists('items', $sectionData)) {
                    $keptItemIds === []
                        ? $section->items()->delete()
                        : $section->items()->whereNotIn('id', $keptItemIds)->delete();
                }
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());

        return $this->adminShow();
    }

    protected function localizedSections(string $locale, bool $activeOnly): array
    {
        $fallback = $this->fallbackLocale();
        $query = HomeSection::with(['translations', 'items.translations'])
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get()
            ->mapWithKeys(fn (HomeSection $section) => [
                $section->section_key => $this->formatSection($section, $locale, $fallback, $activeOnly),
            ])
            ->all();
    }

    protected function formatSection(HomeSection $section, string $locale, string $fallback, bool $activeOnly): array
    {
        $translation = $this->translation($section->translations, $locale, $fallback);
        $items = $section->items
            ->when($activeOnly, fn ($items) => $items->where('is_active', true))
            ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
            ->map(fn (HomeSectionItem $item) => $this->formatItem($item, $locale, $fallback))
            ->values()
            ->all();

        return [
            'id' => $section->id,
            'section_key' => $section->section_key,
            'section_type' => $section->section_type,
            'sort_order' => $section->sort_order,
            'is_active' => (bool) $section->is_active,
            'settings' => $section->settings ?: [],
            'eyebrow' => $translation->eyebrow ?? '',
            'title' => $translation->title ?? '',
            'subtitle' => $translation->subtitle ?? '',
            'description' => $translation->description ?? '',
            'secondary_title' => $translation->secondary_title ?? '',
            'secondary_description' => $translation->secondary_description ?? '',
            'cta_label' => $translation->cta_label ?? '',
            'cta_url' => $translation->cta_url ?? '',
            'image_alt' => $translation->image_alt ?? '',
            'items' => $items,
        ];
    }

    protected function formatItem(HomeSectionItem $item, string $locale, string $fallback): array
    {
        $translation = $this->translation($item->translations, $locale, $fallback);

        return [
            'id' => $item->id,
            'item_key' => $item->item_key,
            'icon' => $item->icon,
            'value' => $item->value,
            'suffix' => $item->suffix,
            'url' => $item->url,
            'sort_order' => $item->sort_order,
            'is_active' => (bool) $item->is_active,
            'settings' => $item->settings ?: [],
            'title' => $translation->title ?? '',
            'description' => $translation->description ?? '',
            'label' => $translation->label ?? '',
            'action_label' => $translation->action_label ?? '',
        ];
    }

    protected function translation(Collection $translations, string $locale, string $fallback): ?object
    {
        return $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', $fallback)
            ?: $translations->first();
    }

    protected function ensureDefaults(): void
    {
        $locales = $this->localeCodes();

        foreach ($this->defaults() as $sectionData) {
            $section = HomeSection::firstOrCreate(
                ['section_key' => $sectionData['section_key']],
                [
                    'section_type' => $sectionData['section_type'],
                    'sort_order' => $sectionData['sort_order'],
                    'is_active' => true,
                    'settings' => $sectionData['settings'] ?? [],
                ]
            );

            foreach ($locales as $locale) {
                $section->translations()->firstOrCreate(
                    ['locale' => $locale],
                    ($sectionData['translation'] ?? []) + ['locale' => $locale]
                );
            }

            foreach ($sectionData['items'] ?? [] as $index => $itemData) {
                $item = HomeSectionItem::firstOrCreate(
                    ['home_section_id' => $section->id, 'item_key' => $itemData['item_key']],
                    [
                        'icon' => $itemData['icon'] ?? null,
                        'value' => $itemData['value'] ?? null,
                        'suffix' => $itemData['suffix'] ?? null,
                        'url' => $itemData['url'] ?? null,
                        'sort_order' => $itemData['sort_order'] ?? $index,
                        'is_active' => true,
                        'settings' => $itemData['settings'] ?? [],
                    ]
                );

                if (array_key_exists('settings', $itemData)) {
                    $item->update(['settings' => $itemData['settings'] ?? []]);
                }

                foreach ($locales as $locale) {
                    $item->translations()->firstOrCreate(
                        ['locale' => $locale],
                        ($itemData['translation'] ?? []) + ['locale' => $locale]
                    );
                }
            }
        }
    }

    protected function localeCodes(): array
    {
        $codes = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        return $codes ?: ['en', 'uz', 'ru', 'ar'];
    }

    protected function requestLocale(Request $request): string
    {
        $locale = strtolower((string) ($request->query('locale') ?: $request->header('Accept-Language')));
        $locale = trim(explode(',', $locale)[0]);
        if (strlen($locale) > 2 && $locale[2] === '-') {
            $locale = substr($locale, 0, 2);
        }

        return in_array($locale, $this->localeCodes(), true) ? $locale : $this->fallbackLocale();
    }

    protected function fallbackLocale(): string
    {
        return $this->localeCodes()[0] ?? config('app.fallback_locale', 'en');
    }

    protected function defaults(): array
    {
        return [
            [
                'section_key' => 'hero',
                'section_type' => 'hero',
                'sort_order' => 10,
                'settings' => [
                    'cta_url' => '/apply',
                    'video_url' => 'cms/videos/files/graduation-2026.mp4',
                    'image' => 'cms/home/hero/hero-university.jpg',
                ],
                'translation' => [
                    'title' => 'Bukhara State Technical University',
                    'subtitle' => 'Empowering international students with accredited engineering programs, applied research opportunities, and dedicated academic support.',
                    'cta_label' => 'Apply Now',
                    'secondary_title' => 'Watch video',
                    'image_alt' => 'University campus',
                ],
                'items' => [
                    ['item_key' => 'student_count', 'value' => '15,000', 'suffix' => '+', 'sort_order' => 1, 'translation' => ['label' => 'Active students']],
                    ['item_key' => 'accreditation', 'sort_order' => 2, 'translation' => ['title' => 'Accredited', 'label' => 'State programs']],
                ],
            ],
            [
                'section_key' => 'registrar_office',
                'section_type' => 'registrar',
                'sort_order' => 20,
                'settings' => ['image' => 'cms/university-centers/registrar_office.jpg'],
                'translation' => [
                    'eyebrow' => 'Access Registrar Office',
                    'title' => 'Office of the Registrar',
                    'description' => 'Manage your academic records, requests, official transcripts, and enrollment certificates.',
                    'cta_label' => 'Access Registrar Office',
                    'cta_url' => 'https://student.bstu.uz/dashboard/login',
                    'image_alt' => 'Office of the Registrar',
                ],
                'items' => [
                    ['item_key' => 'hemis', 'sort_order' => 1, 'translation' => ['title' => 'HEMIS Student Portal']],
                    ['item_key' => 'distance_learning', 'sort_order' => 2, 'translation' => ['title' => 'Distance Learning Platform']],
                    ['item_key' => 'contract_invoice', 'sort_order' => 3, 'translation' => ['title' => 'Contract & Invoice Portal']],
                    ['item_key' => 'remote_education', 'sort_order' => 4, 'translation' => ['title' => 'Remote Education Management']],
                ],
            ],
            [
                'section_key' => 'alt_features',
                'section_type' => 'feature_list',
                'sort_order' => 30,
                'settings' => ['image' => 'cms/home/feature-list.png'],
                'translation' => ['image_alt' => 'Academic Integrity'],
                'items' => [
                    ['item_key' => 'academic_integrity', 'sort_order' => 1, 'translation' => ['title' => 'Academic Integrity', 'description' => 'Upholding international ethics, transparency in ECTS grading, and zero tolerance for corruption through our Compliance Control systems.']],
                    ['item_key' => 'innovation_leadership', 'sort_order' => 2, 'translation' => ['title' => 'Innovation Leadership', 'description' => 'Fostering student startup talent, supporting national patented technologies, and driving digital engineering solutions.']],
                    ['item_key' => 'global_inclusivity', 'sort_order' => 3, 'translation' => ['title' => 'Global Inclusivity', 'description' => 'Supporting diverse international students, executing dual-degrees, and offering specialized inclusive training for physically challenged youth.']],
                    ['item_key' => 'active_students', 'sort_order' => 4, 'translation' => ['title' => 'Active Students', 'description' => 'Enrolled in undergraduate and graduate courses']],
                    ['item_key' => 'professors', 'sort_order' => 5, 'translation' => ['title' => 'Professors & Instructors', 'description' => 'Experienced researchers and academic educators']],
                    ['item_key' => 'faculties', 'sort_order' => 6, 'translation' => ['title' => 'Faculties', 'description' => 'Engineering, Technology, Service, and Natural Resources']],
                ],
            ],
            [
                'section_key' => 'strategic_goals',
                'section_type' => 'feature_detail',
                'sort_order' => 40,
                'settings' => ['image' => 'cms/about-page/bstu-about-identity.jpg'],
                'translation' => [
                    'eyebrow' => 'Strategic Goals',
                    'title' => 'Mission & Vision',
                    'subtitle' => 'Institutional Merge',
                    'secondary_title' => 'Our Identity',
                    'description' => 'Our university aims to become a major technological base in Uzbekistan, equipping students with deep engineering expertise, research capabilities, and global industrial integration, partnering directly with conglomerates like Uzbekneftegaz JSC.',
                    'cta_label' => 'Explore Campus Life',
                    'cta_url' => '/video-bdtu',
                    'image_alt' => 'Mission & Vision BSTU',
                ],
                'items' => [
                    ['item_key' => 'mission', 'sort_order' => 1, 'translation' => ['title' => 'Our Mission']],
                    ['item_key' => 'vision', 'sort_order' => 2, 'translation' => ['title' => 'Our Vision']],
                    ['item_key' => 'innovation', 'sort_order' => 3, 'translation' => ['title' => 'Innovation Leadership']],
                    ['item_key' => 'inclusivity', 'sort_order' => 4, 'translation' => ['title' => 'Global Inclusivity']],
                ],
            ],
            [
                'section_key' => 'core_values',
                'section_type' => 'cards',
                'sort_order' => 50,
                'translation' => ['eyebrow' => 'Operational Ethics', 'title' => 'Core Values'],
                'items' => [
                    ['item_key' => 'academic_integrity', 'sort_order' => 1, 'settings' => ['image' => 'cms/home/core-values/academic-integrity.png'], 'translation' => ['title' => 'Academic Integrity', 'description' => 'Upholding international ethics, transparency in ECTS grading, and zero tolerance for corruption through our Compliance Control systems.']],
                    ['item_key' => 'innovation_leadership', 'sort_order' => 2, 'settings' => ['image' => 'cms/home/core-values/innovation-leadership.png'], 'translation' => ['title' => 'Innovation Leadership', 'description' => 'Fostering student startup talent, supporting national patented technologies, and driving digital engineering solutions.']],
                    ['item_key' => 'global_inclusivity', 'sort_order' => 3, 'settings' => ['image' => 'cms/home/core-values/global-inclusivity.png'], 'translation' => ['title' => 'Global Inclusivity', 'description' => 'Supporting diverse international students, executing dual-degrees, and offering specialized inclusive training for physically challenged youth.']],
                ],
            ],
            [
                'section_key' => 'identity',
                'section_type' => 'about',
                'sort_order' => 60,
                'settings' => ['image' => 'cms/about-page/bstu-about-identity.jpg'],
                'translation' => [
                    'eyebrow' => 'Institutional Merge',
                    'title' => 'Our Identity',
                    'description' => 'Formed through the integration of the Bukhara Engineering-Technological Institute and the Bukhara Institute of Natural Resources Management (Presidential Resolution No. PP-22), BSTU brings together the region\'s elite scholars, researchers, and campus facilities.',
                    'secondary_description' => 'Our university aims to become a major technological base in Uzbekistan, equipping students with deep engineering expertise, research capabilities, and global industrial integration, partnering directly with conglomerates like Uzbekneftegaz JSC.',
                    'cta_label' => 'About Us',
                    'cta_url' => '/about',
                    'image_alt' => 'Our university aims to become a major technological base in Uzbekistan',
                ],
            ],
            [
                'section_key' => 'stats',
                'section_type' => 'stats',
                'sort_order' => 70,
                'items' => [
                    ['item_key' => 'active_students', 'value' => '18000', 'suffix' => '+', 'sort_order' => 1, 'translation' => ['label' => 'Active Students']],
                    ['item_key' => 'professors', 'value' => '730', 'suffix' => '+', 'sort_order' => 2, 'translation' => ['label' => 'Professors & Instructors']],
                    ['item_key' => 'faculties', 'value' => '4', 'sort_order' => 3, 'translation' => ['label' => 'Faculties']],
                    ['item_key' => 'departments', 'value' => '24', 'sort_order' => 4, 'translation' => ['label' => 'Departments']],
                    ['item_key' => 'research_centers', 'value' => '13', 'sort_order' => 5, 'translation' => ['label' => 'Research Centers & Labs']],
                    ['item_key' => 'technology_parks', 'value' => '2', 'sort_order' => 6, 'translation' => ['label' => 'Technology Parks']],
                    ['item_key' => 'international_students', 'value' => '250', 'suffix' => '+', 'sort_order' => 7, 'translation' => ['label' => 'International Students']],
                    ['item_key' => 'technical_schools', 'value' => '18', 'sort_order' => 8, 'translation' => ['label' => 'Affiliated Technical Schools']],
                ],
            ],
        ];
    }
}
