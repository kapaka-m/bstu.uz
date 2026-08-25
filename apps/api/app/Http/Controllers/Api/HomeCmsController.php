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

        $payload = Cache::remember(
            'public_api:v'.Cache::get('public_content_cache_version', '1').':home-cms:'.$locale,
            now()->addSeconds((int) config('cache.public_api_ttl', 600)),
            fn () => $this->localizedSections($locale, true)
        );

        return $this->successResponse($payload, 'Home CMS content retrieved successfully');
    }

    public function adminShow()
    {
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

    protected function localeCodes(): array
    {
        $codes = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->map(fn ($code) => strtolower(str_replace('_', '-', trim((string) $code))))
            ->filter()
            ->values()
            ->all();

        return $codes ?: array_values(array_filter([
            config('app.fallback_locale'),
            config('app.locale'),
        ]));
    }

    protected function requestLocale(Request $request): string
    {
        $locale = $this->normalizeRequestedLocale((string) (
            $request->query('locale')
            ?: $request->header('X-Locale')
            ?: $request->header('Accept-Language')
        ));

        if (in_array($locale, $this->localeCodes(), true)) {
            return $locale;
        }

        $primary = explode('-', $locale)[0] ?? '';

        return $primary && in_array($primary, $this->localeCodes(), true)
            ? $primary
            : $this->fallbackLocale();
    }

    protected function normalizeRequestedLocale(string $locale): string
    {
        return strtolower(trim(explode(',', str_replace('_', '-', $locale))[0]));
    }

    protected function fallbackLocale(): string
    {
        return $this->localeCodes()[0] ?? config('app.fallback_locale', 'en');
    }

}
