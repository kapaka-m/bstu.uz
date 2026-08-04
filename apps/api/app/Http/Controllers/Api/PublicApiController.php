<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\InquiryRequest;
use App\Mail\CmsTemplateMail;
use App\Http\Resources\LocalizedCollection;
use App\Http\Resources\LocalizedResource;
use App\Models\AboutPage;
use App\Models\Announcement;
use App\Models\AnnouncementSetting;
use App\Models\AdministrationProfile;
use App\Models\AdministrationSetting;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\BlogSetting;
use App\Models\Comment;
use App\Models\ContactPage;
use App\Models\Course;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\GreenCampusArticle;
use App\Models\GreenCampusSetting;
use App\Models\GreenCampusStat;
use App\Models\InteractiveServiceSetting;
use App\Models\Inquiry;
use App\Models\Locale;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\NewsEventSetting;
use App\Models\NewsletterSubscription;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Program;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\UniversityCenter;
use App\Models\UniversityCenterSetting;
use App\Models\TranslationKey;
use App\Models\Video;
use App\Models\VideoComment;
use App\Models\VideoGallerySetting;
use App\Models\WebFooter;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PublicApiController extends Controller
{
    use ApiResponse;

    protected ?string $fallbackLocaleCode = null;
    protected array $activeLocaleCodes = [];

    /**
     * Set locale from request query parameter or Accept-Language header.
     */
    protected function getRequestLocale(Request $request): string
    {
        $locale = $request->query('locale') ?: $request->header('Accept-Language');
        $supported = $this->activeLocaleCodes();

        if ($locale) {
            $locale = strtolower(trim(explode(',', $locale)[0]));
            if (strlen($locale) > 2 && $locale[2] === '-') {
                $locale = substr($locale, 0, 2);
            }

            if (in_array($locale, $supported, true)) {
                return $locale;
            }
        }

        return $this->fallbackLocale();
    }

    protected function activeLocaleCodes(): array
    {
        if ($this->activeLocaleCodes === []) {
            $this->activeLocaleCodes = Locale::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('code')
                ->filter()
                ->values()
                ->all();
        }

        return $this->activeLocaleCodes;
    }

    protected function fallbackLocale(): string
    {
        if ($this->fallbackLocaleCode === null) {
            $this->fallbackLocaleCode = $this->activeLocaleCodes()[0]
                ?? config('app.fallback_locale')
                ?? config('app.locale');
        }

        return $this->fallbackLocaleCode;
    }

    protected function directionForLocale(string $locale): string
    {
        return Locale::query()->where('code', $locale)->value('direction') ?: 'ltr';
    }

    protected function localizedData(Request $request, object $model, string $locale): array
    {
        $arr = (new LocalizedResource($model, $locale))->toArray($request);

        return $arr['data'] ?? $arr;
    }

    protected function localizedList(Request $request, mixed $items, string $locale): array
    {
        $arr = (new LocalizedCollection($items, $locale))->toArray($request);

        return $arr['data'] ?? [];
    }

    protected function publicCache(Request $request, string $name, array $varyBy, Closure $callback)
    {
        $version = Cache::get('public_content_cache_version', '1');
        $key = 'public_api:v'.$version.':'.$name.':'.md5(json_encode($varyBy));

        return Cache::remember($key, now()->addSeconds((int) config('cache.public_api_ttl', 600)), $callback);
    }

    protected function withProgramDisplayCodes(array $programs): array
    {
        return array_map(function (array $program) {
            $program['display_code'] = $program['official_code'] ?: ($program['code'] ?? null);

            return $program;
        }, $programs);
    }

    protected function withDepartmentHeadProfile(array $data, Department $department): array
    {
        $staffProfiles = $department->relationLoaded('staffProfiles')
            ? $department->staffProfiles
            : $department->staffProfiles()->where('is_active', true)->with('translations')->orderBy('sort_order')->get();

        $headName = trim((string) $department->head_name);
        $head = $staffProfiles->first(function (StaffProfile $profile) use ($headName) {
            return $headName !== '' && trim((string) $profile->translate('full_name', $this->fallbackLocale())) === $headName;
        }) ?: $staffProfiles->first(function (StaffProfile $profile) use ($department) {
            return $department->email && $profile->email === $department->email;
        }) ?: $staffProfiles->first();

        unset($data['staff_profiles']);
        $data['head_profile_slug'] = $head?->slug;

        return $data;
    }

    protected function withPublicImageUrl(array $data, string $field = 'image'): array
    {
        $value = $data[$field] ?? null;
        if (! is_string($value) || trim($value) === '') {
            $data[$field.'_url'] = null;

            return $data;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            $data[$field.'_url'] = $value;
        } elseif (str_starts_with($value, 'assets/')) {
            $data[$field.'_url'] = '/'.$value;
        } else {
            $data[$field.'_url'] = asset('storage/'.$value);
        }

        return $data;
    }

    protected function formatStaffProfile(Request $request, StaffProfile $profile, string $locale): array
    {
        $data = $this->withPublicImageUrl($this->localizedData($request, $profile, $locale), 'photo');
        $data['image'] = $data['photo_url'] ?? $data['photo'] ?? null;

        return $data;
    }

    protected function localizedStaffList(Request $request, mixed $profiles, string $locale): array
    {
        return collect($profiles)
            ->map(fn (StaffProfile $profile) => $this->formatStaffProfile($request, $profile, $locale))
            ->values()
            ->all();
    }

    public function locales(Request $request)
    {
        $locales = $this->publicCache($request, 'locales', [], function () {
            return Locale::where('is_active', true)->orderBy('sort_order')->get()->toArray();
        });

        return $this->successResponse($locales, 'Locales retrieved successfully');
    }

    public function translations(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $fallbackLocale = $this->fallbackLocale();
        $direction = $this->directionForLocale($locale);

        $dictionary = $this->publicCache($request, 'translations', [$locale, $fallbackLocale], function () use ($locale, $fallbackLocale) {
            $keys = TranslationKey::with(['values' => function ($q) use ($locale, $fallbackLocale) {
                $q->whereIn('locale', array_unique(array_filter([$locale, $fallbackLocale])));
            }])->get()->sortBy(function ($keyModel) {
                return substr_count($keyModel->group.'.'.$keyModel->key, '.');
            });

            $dictionary = [];
            foreach ($keys as $keyModel) {
                $group = $keyModel->group;
                $k = $keyModel->key;

                $valueModel = $keyModel->values->firstWhere('locale', $locale);
                $value = $valueModel ? $valueModel->value : null;

                if (is_null($value) && $fallbackLocale && $locale !== $fallbackLocale) {
                    $fallback = $keyModel->values->where('locale', $fallbackLocale)->first();
                    $value = $fallback ? $fallback->value : '';
                }

                $path = "{$group}.{$k}";
                if (is_array(Arr::get($dictionary, $path))) {
                    continue;
                }
                Arr::set($dictionary, $path, $value ?: '');
            }

            return $dictionary;
        });

        return response()->json([
            'locale' => $locale,
            'direction' => $direction,
            'data' => $dictionary,
        ]);
    }

    public function settings(Request $request)
    {
        $settings = $this->publicCache($request, 'settings', [], function () {
            return Setting::where('is_public', true)->get()->pluck('value', 'key')->toArray();
        });

        return $this->successResponse($settings, 'Public settings retrieved successfully');
    }

    public function footerWeb(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'footer-web', [$locale], function () use ($locale) {
            $footer = WebFooter::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $footer) {
                return null;
            }

            return $this->formatFooterWeb($footer, $locale);
        });

        if (! $payload) {
            return $this->errorResponse('Public footer content not found', 404);
        }

        return $this->successResponse($payload, 'Public footer content retrieved successfully');
    }

    public function aboutPage(Request $request)
    {
        $locale = $this->getRequestLocale($request);

        $payload = $this->publicCache($request, 'about-page', [$locale], function () use ($request, $locale) {
            $page = AboutPage::where('key', 'main')
                ->where('is_published', true)
                ->with('contentEntries.translations')
                ->first();

            if (! $page) {
                return null;
            }

            $data = [
                'key' => $page->key,
                'hero_contact_url' => $page->hero_contact_url,
                'hero_campus_url' => $page->hero_campus_url,
                'identity_image' => $page->identity_image,
                'rector_profile_slug' => $page->rector_profile_slug,
                'is_published' => (bool) $page->is_published,
                'content' => $page->contentForLocale($locale),
            ];

            return $this->withPublicImageUrl($data, 'identity_image');
        });

        if (! $payload) {
            return $this->errorResponse('Public about page content not found', 404);
        }

        return $this->successResponse($payload, 'Public about page content retrieved successfully');
    }

    public function contactPage(Request $request)
    {
        $locale = $this->getRequestLocale($request);

        $payload = $this->publicCache($request, 'contact-page', [$locale], function () use ($locale) {
            $page = ContactPage::where('key', 'main')
                ->where('is_published', true)
                ->with('translations')
                ->first();

            if (! $page) {
                return null;
            }

            $translation = $page->translations->firstWhere('locale', $locale)
                ?: $page->translations->firstWhere('locale', $this->fallbackLocale())
                ?: $page->translations->first();

            return [
                'key' => $page->key,
                'map_embed_url' => $page->map_embed_url,
                'is_published' => (bool) $page->is_published,
                'content' => $translation?->content ?: [],
            ];
        });

        if (! $payload) {
            return $this->errorResponse('Public contact page content not found', 404);
        }

        return $this->successResponse($payload, 'Public contact page content retrieved successfully');
    }

    public function administrationSettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'administration-settings', [$locale], function () use ($locale) {
            $setting = AdministrationSetting::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            $translation = $setting->translations->firstWhere('locale', $locale)
                ?: $setting->translations->firstWhere('locale', $this->fallbackLocale());

            return array_merge([
                'key' => $setting->key,
                'home_limit' => $setting->home_limit,
                'is_active' => $setting->is_active,
            ], $translation?->only([
                'home_tag',
                'home_title',
                'reception_label',
                'phone_label',
                'email_label',
                'telegram_label',
                'rector_bot_label',
                'structure_title',
                'profile_category_label',
                'email_address_label',
                'phone_number_label',
                'office_hours_label',
                'academic_rank_label',
                'biography_label',
                'duties_label',
                'achievements_label',
            ]) ?: []);
        });

        if (! $payload) {
            return $this->errorResponse('Administration settings not found', 404);
        }

        return $this->successResponse($payload, 'Administration settings retrieved successfully');
    }

    public function administration(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $limit = $request->filled('limit') ? max(1, min((int) $request->query('limit'), 50)) : null;

        $items = $this->publicCache($request, 'administration-list', [$locale, $limit], function () use ($request, $locale, $limit) {
            $query = AdministrationProfile::where('is_published', true)
                ->with('translations')
                ->orderBy('sort_order')
                ->orderBy('id');

            if ($limit) {
                $query->limit($limit);
            }

            return $query->get()
                ->map(fn (AdministrationProfile $profile) => $this->formatAdministrationProfile($request, $profile, $locale))
                ->values()
                ->all();
        });

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $items,
        ]);
    }

    public function administrationProfile(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $profile = AdministrationProfile::where('slug', $slug)
            ->where('is_published', true)
            ->with('translations')
            ->first();

        if (! $profile) {
            return $this->errorResponse("Administration profile '{$slug}' not found", 404);
        }

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $this->formatAdministrationProfile($request, $profile, $locale),
        ]);
    }

    protected function formatAdministrationProfile(Request $request, AdministrationProfile $profile, string $locale): array
    {
        $data = $this->withPublicImageUrl($this->localizedData($request, $profile, $locale), 'photo');
        $data['id'] = $profile->id;
        $data['slug'] = $profile->slug;
        $data['path'] = '/profile/'.$profile->slug;
        $data['name'] = $data['full_name'] ?? '';
        $data['title'] = $data['position'] ?? '';
        $data['image'] = $data['photo_url'] ?? $data['photo'] ?? null;
        $data['officeHours'] = $data['office_hours'] ?? null;
        $data['about'] = $data['about'] ?? null;
        $data['telegram'] = $profile->telegram_url;

        return $data;
    }

    public function newsEventSettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'news-event-settings', [$locale], function () use ($locale) {
            $setting = NewsEventSetting::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            $translation = $setting->translations->firstWhere('locale', $locale)
                ?: $setting->translations->firstWhere('locale', $this->fallbackLocale());

            return array_merge([
                'key' => $setting->key,
                'home_limit' => $setting->home_limit,
                'recent_limit' => $setting->recent_limit,
                'home_icon' => $setting->home_icon ?: 'newspaper',
            ], $translation?->only([
                'home_tag',
                'home_title',
                'home_subtitle',
                'view_all_label',
                'read_details_label',
                'search_title',
                'search_placeholder',
                'categories_title',
                'recent_title',
                'all_news_label',
                'news_label',
                'events_label',
                'views_label',
                'loading_label',
                'no_results_label',
                'clear_filters_label',
            ]) ?: []);
        });

        if (! $payload) {
            return $this->errorResponse('News and events settings not found', 404);
        }

        return $this->successResponse($payload, 'News and events settings retrieved successfully');
    }

    public function blogSettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'blog-settings', [$locale], function () use ($locale) {
            $setting = BlogSetting::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            $translation = $setting->translations->firstWhere('locale', $locale)
                ?: $setting->translations->firstWhere('locale', $this->fallbackLocale());

            $categories = Blog::where('is_published', true)
                ->whereNotNull('category')
                ->with('translations')
                ->orderBy('category')
                ->get()
                ->groupBy('category')
                ->map(function ($items, string $category) use ($locale) {
                    $translation = $items->first()->translations->firstWhere('locale', $locale)
                        ?: $items->first()->translations->firstWhere('locale', $this->fallbackLocale());

                    return [
                        'value' => $category,
                        'label' => $translation?->category_label ?: $category,
                    ];
                })
                ->values()
                ->all();

            return array_merge([
                'key' => $setting->key,
                'home_limit' => $setting->home_limit,
                'recent_limit' => $setting->recent_limit,
                'home_icon' => $setting->home_icon ?: 'book-open',
                'tags' => $categories,
            ], $translation?->only([
                'home_tag',
                'home_title',
                'view_all_label',
                'read_more_label',
                'search_title',
                'search_placeholder',
                'categories_title',
                'recent_title',
                'tags_title',
                'all_blog_label',
                'loading_label',
                'no_results_label',
                'clear_filters_label',
                'back_to_blog_label',
                'comments_label',
                'reply_label',
                'form_title',
                'form_name_label',
                'form_email_label',
                'form_comment_label',
                'form_submit_label',
                'comment_login_title',
                'comment_login_text',
                'comment_login_action',
                'signed_in_as_label',
            ]) ?: []);
        });

        if (! $payload) {
            return $this->errorResponse('Blog settings not found', 404);
        }

        return $this->successResponse($payload, 'Blog settings retrieved successfully');
    }

    protected function formatFooterWeb(WebFooter $footer, string $locale): array
    {
        $translation = $footer->translations->firstWhere('locale', $locale)
            ?: $footer->translations->firstWhere('locale', $this->fallbackLocale());

        $usefulLabels = $translation?->useful_link_labels ?: [];
        $facultyLabels = $translation?->faculty_link_labels ?: [];

        return [
            'key' => $footer->key,
            'description' => $translation?->description,
            'logo_alt' => $translation?->logo_alt,
            'admissions_badge' => $translation?->admissions_badge,
            'admissions_heading' => $translation?->admissions_heading,
            'admissions_description' => $translation?->admissions_description,
            'admissions_button_label' => $translation?->admissions_button_label,
            'newsletter_title' => $translation?->newsletter_title,
            'newsletter_description' => $translation?->newsletter_description,
            'newsletter_placeholder' => $translation?->newsletter_placeholder,
            'newsletter_success_message' => $translation?->newsletter_success_message,
            'useful_links_title' => $translation?->useful_links_title,
            'faculties_title' => $translation?->faculties_title,
            'contact_title' => $translation?->contact_title,
            'address_line_1' => $translation?->address_line_1,
            'address_line_2' => $translation?->address_line_2,
            'phone_label' => $translation?->phone_label,
            'email_label' => $translation?->email_label,
            'rights_text' => $translation?->rights_text,
            'useful_links' => collect($footer->useful_links ?: [])->map(function ($link) use ($usefulLabels) {
                $key = $link['key'] ?? '';

                return array_merge($link, [
                    'label' => $usefulLabels[$key] ?? ($link['label'] ?? $key),
                ]);
            })->values()->all(),
            'faculty_links' => collect($footer->faculty_links ?: [])->map(function ($link) use ($facultyLabels) {
                $key = $link['key'] ?? '';

                return array_merge($link, [
                    'label' => $facultyLabels[$key] ?? ($link['label'] ?? $key),
                ]);
            })->values()->all(),
            'social_links' => $footer->social_links ?: [],
            'admissions_apply_url' => $footer->admissions_apply_url,
            'phone' => $footer->phone,
            'email' => $footer->email,
            'copyright_year' => $footer->copyright_year,
        ];
    }

    public function menu(Request $request, string $location)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'menu', [$locale, $location], function () use ($locale, $location) {
            $menu = Menu::where('location', $location)->where('is_active', true)->first();

            if (! $menu) {
                return null;
            }

            $items = MenuItem::where('menu_id', $menu->id)
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->with(['translations', 'children' => function ($q) {
                    $q->where('is_active', true)
                        ->with(['translations', 'children' => function ($childQuery) {
                            $childQuery->where('is_active', true)->with('translations')->orderBy('sort_order');
                        }])
                        ->orderBy('sort_order');
                }])
                ->get();

            return [
                'locale' => $locale,
                'direction' => $this->directionForLocale($locale),
                'data' => $this->formatMenuItems($items, $locale),
            ];
        });

        if (! $payload) {
            return $this->errorResponse("Menu at location '{$location}' not found", 404);
        }

        return response()->json($payload);
    }

    public function menus(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $data = $this->publicCache($request, 'menus', [$locale], function () use ($locale) {
            $menus = Menu::where('is_active', true)->orderBy('location')->get();
            $data = [];

            foreach ($menus as $menu) {
                $items = MenuItem::where('menu_id', $menu->id)
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with(['translations', 'children' => function ($q) {
                        $q->where('is_active', true)
                            ->with(['translations', 'children' => function ($childQuery) {
                                $childQuery->where('is_active', true)->with('translations')->orderBy('sort_order');
                            }])
                            ->orderBy('sort_order');
                    }])
                    ->get();

                $data[$menu->location] = [
                    'id' => $menu->id,
                    'key' => $menu->key,
                    'location' => $menu->location,
                    'items' => $this->formatMenuItems($items, $locale),
                ];
            }

            return $data;
        });

        return $this->successResponse($data, 'Menus retrieved successfully');
    }

    protected function formatMenuItems($items, string $locale): array
    {
        return $items
            ->map(fn (MenuItem $item) => $this->formatMenuItem($item, $locale))
            ->unique(fn (array $item) => implode('|', [
                $item['sort_order'] ?? '',
                $item['route_name'] ?? '',
                $item['label'] ?? '',
            ]))
            ->values()
            ->all();
    }

    protected function formatMenuItem(MenuItem $item, string $locale): array
    {
        $translation = $item->translations->firstWhere('locale', $locale)
            ?: $item->translations->firstWhere('locale', $this->fallbackLocale())
            ?: $item->translations->first();

        return [
            'id' => $item->id,
            'parent_id' => $item->parent_id,
            'route_name' => $item->route_name,
            'url' => $item->url,
            'icon' => $item->icon,
            'sort_order' => $item->sort_order,
            'is_active' => (bool) $item->is_active,
            'label' => $translation?->label ?: '',
            'children' => $item->children
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->map(fn (MenuItem $child) => $this->formatMenuItem($child, $locale))
                ->values()
                ->all(),
        ];
    }

    public function home(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'home', [$locale], function () use ($request, $locale) {
            $page = Page::where('slug', 'home')->where('is_published', true)->with('translations')->first();

            if (! $page) {
                return null;
            }

            $blocks = PageBlock::where('page_id', $page->id)
                ->where('is_active', true)
                ->with('translations')
                ->orderBy('sort_order')
                ->get();

            return [
                'page' => (new LocalizedResource($page, $locale))->toArray($request)['data'],
                'blocks' => (new LocalizedCollection($blocks, $locale))->toArray($request)['data'],
            ];
        });

        if (! $payload) {
            return $this->errorResponse("Page 'home' not found", 404);
        }

        return $this->successResponse($payload, 'Home content retrieved successfully');
    }

    public function pages(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'pages', [$locale], function () use ($request, $locale) {
            $pages = Page::where('is_published', true)->with('translations')->orderBy('sort_order')->get();

            return (new LocalizedCollection($pages, $locale))->toArray($request);
        });

        return response()->json($payload);
    }

    public function page(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'page', [$locale, $slug], function () use ($request, $locale, $slug) {
            $page = Page::where('slug', $slug)->where('is_published', true)->with('translations')->first();

            if (! $page) {
                return null;
            }

            return (new LocalizedResource($page, $locale))->toArray($request);
        });

        if (! $payload) {
            return $this->errorResponse("Page '{$slug}' not found", 404);
        }

        return response()->json($payload);
    }

    public function pageBlocks(Request $request, string $pageSlug)
    {
        $locale = $this->getRequestLocale($request);
        $page = Page::where('slug', $pageSlug)->where('is_published', true)->first();

        if (! $page) {
            return $this->errorResponse("Page '{$pageSlug}' not found", 404);
        }

        $blocks = PageBlock::where('page_id', $page->id)
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('sort_order')
            ->get();

        return new LocalizedCollection($blocks, $locale);
    }

    public function faculties(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'faculties', [$locale], function () use ($request, $locale) {
            $faculties = Faculty::where('is_active', true)->with('translations')->orderBy('sort_order')->get();

            return (new LocalizedCollection($faculties, $locale))->toArray($request);
        });

        return response()->json($payload);
    }

    public function faculty(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'faculty', [$locale, $slug], function () use ($request, $locale, $slug) {
            $faculty = Faculty::where('slug', $slug)
                ->where('is_active', true)
                ->with([
                    'translations',
                    'departments' => fn ($query) => $query->where('is_active', true)->with(['translations', 'staffProfiles.translations'])->orderBy('sort_order'),
                    'programs' => fn ($query) => $query->where('is_active', true)->with('translations')->orderBy('sort_order'),
                    'staffProfiles' => fn ($query) => $query->where('is_active', true)->with('translations')->orderBy('sort_order'),
                ])
                ->first();

            if (! $faculty) {
                return null;
            }

            $data = $this->withPublicImageUrl($this->localizedData($request, $faculty, $locale));
            $data['departments'] = $faculty->departments
                ->map(function (Department $department) use ($request, $locale) {
                    return $this->withDepartmentHeadProfile($this->localizedData($request, $department, $locale), $department);
                })
                ->all();
            $data['programs'] = $this->withProgramDisplayCodes($this->localizedList($request, $faculty->programs, $locale));
            $data['staff'] = $this->localizedStaffList($request, $faculty->staffProfiles, $locale);
            $data['leadership'] = array_values(array_filter($data['staff'], fn ($member) => empty($member['department_id'])));

            return [
                'locale' => $locale,
                'direction' => $this->directionForLocale($locale),
                'data' => $data,
            ];
        });

        if (! $payload) {
            return $this->errorResponse("Faculty '{$slug}' not found", 404);
        }

        return response()->json($payload);
    }

    public function departments(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'departments', [$locale], function () use ($request, $locale) {
            $departments = Department::where('is_active', true)->with(['translations', 'staffProfiles.translations'])->orderBy('sort_order')->get();

            return [
                'data' => $departments
                    ->map(function (Department $department) use ($request, $locale) {
                        return $this->withDepartmentHeadProfile($this->localizedData($request, $department, $locale), $department);
                    })
                    ->all(),
            ];
        });

        return response()->json($payload);
    }

    public function department(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $department = Department::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'translations',
                'faculty.translations',
                'programs' => fn ($query) => $query->where('is_active', true)->with(['translations', 'courses.translations'])->orderBy('sort_order'),
                'staffProfiles' => fn ($query) => $query->where('is_active', true)->with('translations')->orderBy('sort_order'),
            ])
            ->first();

        if (! $department) {
            return $this->errorResponse("Department '{$slug}' not found", 404);
        }

        $data = $this->withPublicImageUrl($this->localizedData($request, $department, $locale));
        $data = $this->withDepartmentHeadProfile($data, $department);
        $data['faculty'] = $department->faculty ? $this->localizedData($request, $department->faculty, $locale) : null;
        $data['programs'] = $this->withProgramDisplayCodes($this->localizedList($request, $department->programs, $locale));
        $data['staff'] = $this->localizedStaffList($request, $department->staffProfiles, $locale);
        $data['courses'] = $department->programs
            ->flatMap(fn ($program) => $program->courses)
            ->unique('id')
            ->values()
            ->map(fn ($course) => $this->localizedData($request, $course, $locale))
            ->all();

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $data,
        ]);
    }

    public function programs(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $programs = Program::where('is_active', true)
            ->with(['translations', 'faculty.translations', 'department.translations'])
            ->orderBy('sort_order')
            ->get()
            ->map(function (Program $program) use ($request, $locale) {
                $data = $this->localizedData($request, $program, $locale);
                $data['display_code'] = $data['official_code'] ?: ($data['code'] ?? null);

                if ($program->faculty) {
                    $faculty = $this->localizedData($request, $program->faculty, $locale);
                    $data['faculty'] = [
                        'id' => $faculty['id'] ?? null,
                        'slug' => $faculty['slug'] ?? null,
                        'name' => $faculty['name'] ?? null,
                        'short_name' => $faculty['short_name'] ?? null,
                    ];
                } else {
                    $data['faculty'] = null;
                }

                if ($program->department) {
                    $department = $this->localizedData($request, $program->department, $locale);
                    $data['department'] = [
                        'id' => $department['id'] ?? null,
                        'slug' => $department['slug'] ?? null,
                        'name' => $department['name'] ?? null,
                        'short_name' => $department['short_name'] ?? null,
                    ];
                } else {
                    $data['department'] = null;
                }

                return $data;
            })
            ->all();

        return $this->successResponse(
            $programs,
            'Programs retrieved successfully'
        );
    }

    public function program(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $program = Program::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'translations',
                'faculty.translations',
                'department.translations',
                'department.staffProfiles' => fn ($query) => $query->where('is_active', true)->with('translations')->orderBy('sort_order'),
                'courses.translations',
            ])
            ->first();

        if (! $program) {
            return $this->errorResponse("Program '{$slug}' not found", 404);
        }

        $data = $this->withPublicImageUrl($this->localizedData($request, $program, $locale));
        $data['display_code'] = $data['official_code'] ?: ($data['code'] ?? null);
        $data['faculty'] = $program->faculty ? $this->localizedData($request, $program->faculty, $locale) : null;
        $data['department'] = $program->department ? $this->withDepartmentHeadProfile($this->localizedData($request, $program->department, $locale), $program->department) : null;
        $data['staff'] = $program->department ? $this->localizedStaffList($request, $program->department->staffProfiles, $locale) : [];
        $data['courses'] = $this->localizedList($request, $program->courses, $locale);

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $data,
        ]);
    }

    public function courses(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $courses = Course::where('is_active', true)->with('translations')->get();

        return new LocalizedCollection($courses, $locale);
    }

    public function news(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));
        $query = News::where('is_published', true)
            ->where('category', '!=', 'blog')
            ->with('translations');

        if ($request->filled('category') && strtolower($request->query('category')) !== 'all') {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('translations', function ($translationQuery) use ($search) {
                $translationQuery
                    ->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('summary', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $news = $query->orderBy('published_at', 'desc')->paginate($perPage);
        $items = $news->getCollection()
            ->map(fn (News $item) => $this->formatNewsItem($request, $item, $locale))
            ->values()
            ->all();

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $items,
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
            ],
        ]);
    }

    public function newsItem(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $item = News::where('slug', $slug)
            ->where('category', '!=', 'blog')
            ->where('is_published', true)
            ->with('translations')
            ->first();

        if (! $item) {
            return $this->errorResponse("News item '{$slug}' not found", 404);
        }

        if ($request->boolean('view')) {
            $item->increment('views_count');
            $item->refresh();
        }

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $this->formatNewsItem($request, $item->fresh('translations'), $locale),
        ]);
    }

    protected function formatNewsItem(Request $request, News $item, string $locale): array
    {
        $data = $this->localizedData($request, $item, $locale);
        $data['slug'] = $item->slug;
        $data['image_url'] = $this->newsImageUrl($item->image);

        return $data;
    }

    protected function newsImageUrl(?string $image): ?string
    {
        if (! is_string($image) || trim($image) === '') {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
            return $image;
        }

        return asset('storage/'.preg_replace('/^public\//', '', $image));
    }

    public function blog(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));
        $query = Blog::where('is_published', true)->with('translations');

        if ($request->filled('category') && strtolower($request->query('category')) !== 'all') {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('translations', function ($translationQuery) use ($search) {
                $translationQuery
                    ->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('summary', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
                    ->orWhere('author', 'LIKE', "%{$search}%");
            });
        }

        $posts = $query->orderBy('published_at', 'desc')->paginate($perPage);
        $items = $posts->getCollection()
            ->map(fn (Blog $item) => $this->formatBlogItem($request, $item, $locale))
            ->values()
            ->all();

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $items,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function blogItem(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $post = Blog::where(function ($query) use ($slug) {
            $query->where('slug', $slug);
            if (ctype_digit($slug)) {
                $query->orWhere('id', (int) $slug);
            }
        })
            ->where('is_published', true)
            ->with('translations')
            ->first();

        if (! $post) {
            return $this->errorResponse("Blog post '{$slug}' not found", 404);
        }

        $post->increment('views_count');
        $post->refresh();

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $this->formatBlogItem($request, $post->fresh('translations'), $locale),
        ]);
    }

    protected function formatBlogItem(Request $request, Blog $item, string $locale): array
    {
        $data = $this->localizedData($request, $item, $locale);
        $translation = $item->translations->firstWhere('locale', $locale)
            ?: $item->translations->firstWhere('locale', $this->fallbackLocale());

        $data['slug'] = $item->slug;
        $data['author'] = $translation?->author ?: $item->author;
        $data['category'] = $item->category;
        $data['category_label'] = $translation?->category_label ?: $item->category;
        $data['image_url'] = $this->newsImageUrl($item->image);
        $data['author_image_url'] = $this->newsImageUrl($item->author_image);
        $data['views_count'] = $item->views_count;
        $data['comments_count'] = $item->comments()->where('is_approved', true)->count();

        return $data;
    }

    public function blogComments(Request $request, string $slug)
    {
        $post = Blog::where(function ($query) use ($slug) {
            $query->where('slug', $slug);
            if (ctype_digit($slug)) {
                $query->orWhere('id', (int) $slug);
            }
        })
            ->where('is_published', true)
            ->first();

        if (! $post) {
            return $this->errorResponse("Blog post '{$slug}' not found", 404);
        }

        $comments = BlogComment::where('blog_id', $post->id)
            ->where('is_approved', true)
            ->orderBy('created_at')
            ->get()
            ->map(fn (BlogComment $comment) => [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'author' => $comment->author_name,
                'email' => $comment->email,
                'text' => $comment->content,
                'date' => $comment->created_at?->toISOString(),
                'avatar' => null,
            ])
            ->values();

        return $this->successResponse($comments, 'Blog comments retrieved successfully');
    }

    public function storeBlogComment(Request $request, string $slug)
    {
        $post = Blog::where(function ($query) use ($slug) {
            $query->where('slug', $slug);
            if (ctype_digit($slug)) {
                $query->orWhere('id', (int) $slug);
            }
        })
            ->where('is_published', true)
            ->first();

        if (! $post) {
            return $this->errorResponse("Blog post '{$slug}' not found", 404);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|integer|exists:blog_comments,id',
        ]);

        $user = $request->user();
        $comment = BlogComment::create(array_merge($validated, [
            'blog_id' => $post->id,
            'author_name' => $user?->name ?: 'User',
            'email' => $user?->email,
            'is_approved' => true,
        ]));

        $post->update(['comments_count' => $post->comments()->where('is_approved', true)->count()]);
        $this->sendBlogReplyEmail($post, $comment);

        return $this->successResponse([
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'author' => $comment->author_name,
            'email' => $comment->email,
            'text' => $comment->content,
            'date' => $comment->created_at?->toISOString(),
            'avatar' => null,
        ], 'Blog comment created successfully', 201);
    }

    public function announcementSettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'announcement-settings', [$locale], function () use ($locale) {
            $setting = AnnouncementSetting::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            $translation = $setting->translations->firstWhere('locale', $locale)
                ?: $setting->translations->firstWhere('locale', $this->fallbackLocale());

            return array_merge([
                'key' => $setting->key,
                'home_limit' => $setting->home_limit,
                'recent_limit' => $setting->recent_limit,
                'important_limit' => $setting->important_limit,
            ], $translation?->only([
                'home_tag',
                'home_title',
                'view_all_label',
                'read_details_label',
                'search_title',
                'search_placeholder',
                'categories_title',
                'recent_title',
                'all_label',
                'views_label',
                'important_label',
                'loading_label',
                'no_results_label',
                'clear_filters_label',
                'share_label',
                'copy_link_label',
                'copied_label',
                'published_by_label',
                'publisher_name',
            ]) ?: []);
        });

        if (! $payload) {
            return $this->errorResponse('Announcement settings not found', 404);
        }

        return $this->successResponse($payload, 'Announcement settings retrieved successfully');
    }

    public function announcements(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        $query = Announcement::where('is_published', true)->with('translations');

        if ($request->boolean('active_only')) {
            $query->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
        }

        if ($request->filled('category') && strtolower($request->query('category')) !== 'all') {
            $query->where('type', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('translations', function ($translationQuery) use ($search) {
                $translationQuery
                    ->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('summary', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
                    ->orWhere('category_label', 'LIKE', "%{$search}%");
            });
        }

        $announcements = $query
            ->orderBy('starts_at', 'desc')
            ->orderBy('priority', 'desc')
            ->paginate($perPage);

        $items = $announcements->getCollection()
            ->map(fn (Announcement $announcement) => $this->formatAnnouncementItem($request, $announcement, $locale))
            ->values()
            ->all();

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $items,
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page' => $announcements->lastPage(),
                'per_page' => $announcements->perPage(),
                'total' => $announcements->total(),
            ],
        ]);
    }

    public function announcement(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $ann = Announcement::where('slug', $slug)->where('is_published', true)->with('translations')->first();

        if (! $ann) {
            return $this->errorResponse("Announcement '{$slug}' not found", 404);
        }

        $ann->increment('views_count');
        $ann->refresh();

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $this->formatAnnouncementItem($request, $ann->fresh('translations'), $locale),
        ]);
    }

    protected function formatAnnouncementItem(Request $request, Announcement $item, string $locale): array
    {
        $data = $this->localizedData($request, $item, $locale);
        $translation = $item->translations->firstWhere('locale', $locale)
            ?: $item->translations->firstWhere('locale', $this->fallbackLocale());

        $data['id'] = $item->id;
        $data['slug'] = $item->slug;
        $data['type'] = $item->type;
        $data['category'] = $item->type;
        $data['category_label'] = $translation?->category_label ?: '';
        $data['image'] = $item->image;
        $data['image_url'] = $this->newsImageUrl($item->image);
        $data['starts_at'] = $item->starts_at?->toDateString();
        $data['date'] = $item->starts_at?->toDateString();
        $data['ends_at'] = $item->ends_at?->toDateString();
        $data['is_published'] = $item->is_published;
        $data['priority'] = $item->priority;
        $data['important'] = $item->priority === 'high';
        $data['views_count'] = $item->views_count;
        $data['views'] = $item->views_count;

        return $data;
    }

    public function services(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $homeOnly = $request->boolean('home');
        $limit = (int) $request->query('limit', 0);

        $payload = $this->publicCache($request, 'interactive-services', [$locale, $homeOnly ? 'home' : 'all', $limit], function () use ($homeOnly, $limit, $locale) {
            $query = Service::where('is_active', true)
                ->with('translations')
                ->orderBy('sort_order');

            if ($homeOnly) {
                $query->where('home_visible', true);
            }

            if ($limit > 0) {
                $query->limit($limit);
            }

            return $query->get()
                ->map(fn (Service $service) => $this->formatInteractiveService($service, $locale))
                ->values()
                ->all();
        });

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $payload,
        ]);
    }

    public function serviceSettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'interactive-service-settings', [$locale], function () use ($locale) {
            $setting = InteractiveServiceSetting::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            return $this->localizedData(request(), $setting, $locale) + [
                'home_limit' => $setting->home_limit,
                'is_active' => $setting->is_active,
            ];
        });

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $payload,
        ]);
    }

    protected function formatInteractiveService(Service $service, string $locale): array
    {
        $data = $this->localizedData(request(), $service, $locale);

        return array_merge($data, [
            'id' => $service->id,
            'slug' => $service->slug,
            'icon' => $service->icon,
            'url' => $service->url,
            'color' => $service->color ?: 'cyan',
            'home_visible' => (bool) $service->home_visible,
            'opens_new_tab' => (bool) $service->opens_new_tab,
            'sort_order' => $service->sort_order,
            'is_active' => (bool) $service->is_active,
        ]);
    }

    public function videos(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'videos', [$locale], function () use ($request, $locale) {
            $videos = Video::where('is_active', true)->with('translations')->orderBy('sort_order')->get();

            return $videos->map(fn (Video $video) => $this->formatVideoItem($request, $video, $locale))->values()->all();
        });

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $payload,
        ]);
    }

    public function videoGallerySettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'video-gallery-settings', [$locale], function () use ($locale) {
            $setting = VideoGallerySetting::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            $translation = $setting->translations->firstWhere('locale', $locale)
                ?: $setting->translations->firstWhere('locale', $this->fallbackLocale());

            return array_merge([
                'key' => $setting->key,
                'home_limit' => $setting->home_limit,
                'subscriber_count' => $setting->subscriber_count,
                'youtube_channel_url' => $setting->youtube_channel_url,
            ], $translation?->only([
                'home_tag',
                'home_title',
                'home_subtitle',
                'view_all_label',
                'recommended_label',
                'videos_label',
                'description_title',
                'show_more_label',
                'show_less_label',
                'like_label',
                'liked_label',
                'share_label',
                'subscribe_label',
                'subscribed_label',
                'subscribers_label',
                'views_label',
                'channel_name',
                'link_copied_label',
                'no_videos_label',
                'comments_label',
                'reply_label',
                'form_title',
                'form_comment_label',
                'form_submit_label',
                'sign_in_title',
                'sign_in_text',
                'sign_in_action',
                'signed_in_as_label',
                'category_label',
                'duration_label',
                'platform_label',
                'local_label',
                'youtube_label',
                'playing_label',
                'verified_channel_label',
                'category_labels',
            ]) ?: []);
        });

        if (! $payload) {
            return $this->errorResponse('Video gallery settings not found', 404);
        }

        return $this->successResponse($payload, 'Video gallery settings retrieved successfully');
    }

    public function videoView(Request $request, string $slug)
    {
        $video = Video::where('slug', $slug)->where('is_active', true)->first();

        if (! $video) {
            return $this->errorResponse("Video '{$slug}' not found", 404);
        }

        $video->increment('views_count');
        $video->refresh();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());

        return $this->successResponse(['views_count' => $video->views_count], 'Video view counted');
    }

    public function videoLike(Request $request, string $slug)
    {
        $video = Video::where('slug', $slug)->where('is_active', true)->first();

        if (! $video) {
            return $this->errorResponse("Video '{$slug}' not found", 404);
        }

        $video->increment('likes_count');
        $video->refresh();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());

        return $this->successResponse(['likes_count' => $video->likes_count], 'Video like counted');
    }

    public function videoComments(Request $request, string $slug)
    {
        $video = Video::where('slug', $slug)->where('is_active', true)->first();

        if (! $video) {
            return $this->errorResponse("Video '{$slug}' not found", 404);
        }

        $comments = VideoComment::where('video_id', $video->id)
            ->where('is_approved', true)
            ->orderBy('created_at')
            ->get()
            ->map(fn (VideoComment $comment) => [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'author' => $comment->author_name,
                'email' => $comment->email,
                'text' => $comment->content,
                'date' => $comment->created_at?->toISOString(),
                'avatar' => null,
            ])
            ->values();

        return $this->successResponse($comments, 'Video comments retrieved successfully');
    }

    public function storeVideoComment(Request $request, string $slug)
    {
        $video = Video::where('slug', $slug)->where('is_active', true)->first();

        if (! $video) {
            return $this->errorResponse("Video '{$slug}' not found", 404);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|integer|exists:video_comments,id',
        ]);

        if (
            ! empty($validated['parent_id'])
            && ! VideoComment::where('id', $validated['parent_id'])->where('video_id', $video->id)->exists()
        ) {
            return $this->errorResponse('Parent comment does not belong to this video', 422);
        }

        $user = $request->user();
        $comment = VideoComment::create(array_merge($validated, [
            'video_id' => $video->id,
            'author_name' => $user?->name ?: 'User',
            'email' => $user?->email,
            'is_approved' => true,
        ]));

        return $this->successResponse([
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'author' => $comment->author_name,
            'email' => $comment->email,
            'text' => $comment->content,
            'date' => $comment->created_at?->toISOString(),
            'avatar' => null,
        ], 'Video comment created successfully', 201);
    }

    protected function formatVideoItem(Request $request, Video $video, string $locale): array
    {
        $data = $this->localizedData($request, $video, $locale);
        $translation = $video->translations->firstWhere('locale', $locale)
            ?: $video->translations->firstWhere('locale', $this->fallbackLocale());
        $thumbnail = $video->thumbnail;
        $url = $video->url;

        if (is_string($thumbnail) && $thumbnail !== '' && ! str_starts_with($thumbnail, 'http') && ! str_starts_with($thumbnail, '/')) {
            $thumbnail = $this->normalizePublicFilePath($thumbnail);
        }
        if (is_string($url) && $url !== '' && ! str_starts_with($url, 'http') && ! str_starts_with($url, '/')) {
            $url = $this->normalizePublicFilePath($url);
        }

        return array_merge($data, [
            'id' => $video->slug,
            'slug' => $video->slug,
            'url' => $url,
            'video_url' => $url,
            'thumbnail' => $thumbnail,
            'poster' => $thumbnail,
            'video_type' => $video->video_type,
            'isLocal' => $video->video_type === 'local',
            'youtubeId' => $video->youtube_id,
            'youtube_id' => $video->youtube_id,
            'duration' => $video->duration,
            'views_count' => $video->views_count,
            'likes_count' => $video->likes_count,
            'published_at' => $video->published_at?->toISOString(),
            'category' => $translation?->category,
        ]);
    }

    public function staff(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $staff = StaffProfile::where('is_active', true)
            ->with(['faculty', 'department'])
            ->with('translations')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $this->localizedStaffList($request, $staff, $locale),
        ]);
    }

    public function staffProfile(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $staff = StaffProfile::where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);

                if (ctype_digit($slug)) {
                    $query->orWhere('id', (int) $slug);
                }
            })
            ->with(['faculty', 'department'])
            ->with('translations')
            ->first();

        if (! $staff) {
            $inactiveMatch = StaffProfile::where('slug', $slug)->first();

            if ($inactiveMatch?->email) {
                $staff = StaffProfile::where('is_active', true)
                    ->where('email', $inactiveMatch->email)
                    ->with(['faculty', 'department'])
                    ->with('translations')
                    ->first();
            }
        }

        if (! $staff) {
            return $this->errorResponse("Staff profile '{$slug}' not found", 404);
        }

        $data = $this->formatStaffProfile($request, $staff, $locale);
        $data['faculty'] = $staff->faculty ? $this->localizedData($request, $staff->faculty, $locale) : null;
        $data['department'] = $staff->department ? $this->localizedData($request, $staff->department, $locale) : null;

        return response()->json([
            'locale' => $locale,
            'direction' => $this->directionForLocale($locale),
            'data' => $data,
        ]);
    }

    public function media(Request $request, int $id)
    {
        $media = Media::find($id);

        if (! $media || ! $media->is_public) {
            return $this->errorResponse('Media file not found', 404);
        }

        $url = asset('storage/'.$media->path);

        return $this->successResponse([
            'id' => $media->id,
            'filename' => $media->filename,
            'title' => $media->title,
            'alt_text' => $media->alt_text,
            'type' => $media->type,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'url' => $url,
        ], 'Media details retrieved');
    }

    public function storeInquiry(InquiryRequest $request)
    {
        $inquiry = Inquiry::create($request->validated());
        $this->sendInquiryReceivedEmail($inquiry, $request);

        return $this->successResponse($inquiry, 'Inquiry submitted successfully', 201);
    }

    public function storeComment(CommentRequest $request)
    {
        $userId = auth('sanctum')->id();

        $data = $request->validated();
        $data['user_id'] = $userId ?: null;

        $comment = Comment::create($data);

        return $this->successResponse($comment, 'Comment posted successfully', 201);
    }

    public function storeNewsletterSubscription(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscription = NewsletterSubscription::updateOrCreate(
            ['email' => strtolower($validated['email'])],
            [
                'locale' => $this->getRequestLocale($request),
                'status' => 'active',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );
        $this->sendNewsletterWelcomeEmail($subscription);

        return $this->successResponse($subscription, 'Newsletter subscription saved successfully', 201);
    }

    protected function sendNewsletterWelcomeEmail(NewsletterSubscription $subscription): void
    {
        try {
            Mail::to($subscription->email)->send(new CmsTemplateMail(
                'newsletter_welcome',
                [
                    'email' => $subscription->email,
                    'cta_url' => rtrim((string) config('app.frontend_url'), '/'),
                ],
                $subscription->locale ?: $this->fallbackLocale(),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    protected function sendInquiryReceivedEmail(Inquiry $inquiry, Request $request): void
    {
        try {
            Mail::to($inquiry->email)->send(new CmsTemplateMail(
                'inquiry_received',
                [
                    'name' => $inquiry->name,
                    'subject' => $inquiry->subject,
                    'email' => $inquiry->email,
                    'cta_url' => rtrim((string) config('app.frontend_url'), '/').'/contact',
                ],
                $this->getRequestLocale($request),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    protected function sendBlogReplyEmail(Blog $post, BlogComment $comment): void
    {
        if (! $comment->parent_id) {
            return;
        }

        $parent = BlogComment::find($comment->parent_id);
        $recipient = trim((string) ($parent?->email ?? ''));

        if ($recipient === '' || $recipient === $comment->email) {
            return;
        }

        $post->loadMissing('translations');
        $translation = $post->translations->firstWhere('locale', $this->fallbackLocale())
            ?: $post->translations->first();
        $title = $translation?->title ?: $post->slug;

        try {
            Mail::to($recipient)->send(new CmsTemplateMail(
                'blog_comment_reply',
                [
                    'name' => $parent->author_name ?: 'Reader',
                    'blog_title' => $title,
                    'reply_author' => $comment->author_name,
                    'reply_excerpt' => Str::limit($comment->content, 160),
                    'cta_url' => rtrim((string) config('app.frontend_url'), '/').'/blog/'.$post->slug,
                ],
                $this->fallbackLocale(),
            ));
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function greenCampusStats(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'green-campus-stats', [$locale], function () use ($locale) {
            $stats = GreenCampusStat::with('translations')->orderBy('sort_order')->get();

            return $stats
                ->map(fn (GreenCampusStat $stat) => $this->formatGreenCampusStat($stat, $locale))
                ->values()
                ->all();
        });

        return $this->successResponse(
            $payload,
            'Green campus stats retrieved successfully'
        );
    }

    protected function formatGreenCampusStat(GreenCampusStat $stat, string $locale): array
    {
        $translation = $stat->translations->where('locale', $locale)->first()
            ?: $stat->translations->where('locale', $this->fallbackLocale())->first();

        return [
            'id' => $stat->id,
            'icon' => $stat->icon,
            'sort_order' => $stat->sort_order,
            'value' => $translation?->value ?: '',
            'label' => $translation?->label ?: '',
            'created_at' => $stat->created_at,
            'updated_at' => $stat->updated_at,
        ];
    }

    public function greenCampusSettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);

        $payload = $this->publicCache($request, 'green-campus-settings', [$locale], function () use ($locale) {
            $setting = GreenCampusSetting::where('key', 'main')
                ->where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            $translation = $setting->translations->where('locale', $locale)->first()
                ?: $setting->translations->where('locale', $this->fallbackLocale())->first();

            if (! $translation) {
                return null;
            }

            $fields = [
                'home_tag',
                'home_title',
                'view_all_label',
                'read_more_label',
                'search_title',
                'search_placeholder',
                'categories_title',
                'recent_title',
                'all_label',
                'no_results_label',
                'callout_title',
                'callout_description',
                'callout_cta_label',
                'callout_email',
                'views_label',
                'gallery_label',
                'related_label',
                'close_viewer_label',
                'previous_image_label',
                'next_image_label',
                'category_labels',
            ];

            $data = [
                'home_limit' => $setting->home_limit,
                'recent_limit' => $setting->recent_limit,
                'is_active' => $setting->is_active,
            ];

            foreach ($fields as $field) {
                $data[$field] = $translation->{$field};
            }

            return $data;
        });

        if (! $payload) {
            return $this->errorResponse('Green campus settings not found', 404);
        }

        return $this->successResponse($payload, 'Green campus settings retrieved successfully');
    }

    public function greenCampusArticles(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'green-campus-articles', [$locale], function () use ($locale) {
            $articles = GreenCampusArticle::with('translations')
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->get();

            return $articles
                ->map(fn (GreenCampusArticle $article) => $this->formatGreenCampusArticle($article, $locale))
                ->values()
                ->all();
        });

        return $this->successResponse(
            $payload,
            'Green campus articles retrieved successfully'
        );
    }

    public function greenCampusArticle(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $article = GreenCampusArticle::where('slug', $slug)
            ->where('is_published', true)
            ->with('translations')
            ->first();

        if (! $article) {
            return $this->errorResponse("Green campus article '{$slug}' not found", 404);
        }

        $article->increment('views');

        return $this->successResponse(
            $this->formatGreenCampusArticle($article->fresh('translations'), $locale),
            'Green campus article retrieved successfully'
        );
    }

    protected function formatGreenCampusArticle(GreenCampusArticle $article, string $locale): array
    {
        $translation = $article->translations->where('locale', $locale)->first()
            ?: $article->translations->where('locale', $this->fallbackLocale())->first();

        return [
            'id' => $article->id,
            'slug' => $article->slug,
            'category' => $article->category,
            'category_label' => $translation?->category ?: $article->category,
            'image' => $this->normalizePublicFilePath($article->image),
            'gallery' => collect($article->gallery ?: [])
                ->map(fn ($path) => $this->normalizePublicFilePath($path))
                ->filter()
                ->values()
                ->all(),
            'views' => $article->views,
            'published_at' => $article->published_at?->toISOString(),
            'is_published' => $article->is_published,
            'sort_order' => $article->sort_order,
            'created_at' => $article->created_at?->toISOString(),
            'updated_at' => $article->updated_at?->toISOString(),
            'title' => $translation?->title ?: $article->slug,
            'excerpt' => $translation?->excerpt ?: '',
            'content' => $translation?->content ?: '',
            'author' => $translation?->author ?: '',
        ];
     }

    public function universityCenters(Request $request)
    {
        $locale = $this->getRequestLocale($request);

        $payload = $this->publicCache($request, 'university_centers', [$locale], function () use ($locale) {
            return UniversityCenter::where('is_active', true)
                ->orderBy('sort_order')
                ->with('translations')
                ->get()
                ->map(fn (UniversityCenter $center) => $this->formatUniversityCenter($center, $locale))
                ->values()
                ->all();
        });

        return $this->successResponse(
            $payload,
            'University centers retrieved successfully'
        );
    }

    public function universityCenter(Request $request, string $slug)
    {
        $locale = $this->getRequestLocale($request);
        $center = UniversityCenter::where('slug', $slug)
            ->where('is_active', true)
            ->with('translations')
            ->first();

        if (! $center) {
            return $this->errorResponse("University center '{$slug}' not found", 404);
        }

        return $this->successResponse(
            $this->formatUniversityCenter($center, $locale),
            'University center retrieved successfully'
        );
    }

    protected function formatUniversityCenter(UniversityCenter $center, string $locale): array
    {
        $translation = $center->translations->where('locale', $locale)->first()
            ?: $center->translations->where('locale', $this->fallbackLocale())->first();
        $englishTranslation = $center->translations->where('locale', 'en')->first();
        $headProfileSlug = Str::slug($englishTranslation?->head ?: $translation?->head ?: '');

        return [
            'id' => $center->id,
            'slug' => $center->slug,
            'headProfileSlug' => $headProfileSlug,
            'head_profile_slug' => $headProfileSlug,
            'image' => $this->publicFileUrl($center->image),
            'email' => $center->email,
            'phone' => $center->phone,
            'sort_order' => $center->sort_order,
            'is_active' => $center->is_active,
            'created_at' => $center->created_at?->toISOString(),
            'updated_at' => $center->updated_at?->toISOString(),
            'name' => $translation?->name ?: $center->slug,
            'head' => $translation?->head ?: '',
            'headTitle' => $translation?->head_title ?: '',
            'officeHours' => $translation?->office_hours ?: '',
            'about' => $translation?->about ?: '',
            'functions' => $translation?->functions ?: [],
            'headDescription' => $translation?->head_description ?: '',
        ];
    }

    public function universityCenterSettings(Request $request)
    {
        $locale = $this->getRequestLocale($request);
        $payload = $this->publicCache($request, 'university_center_settings', [$locale], function () use ($locale) {
            $setting = UniversityCenterSetting::where('is_active', true)
                ->with('translations')
                ->first();

            if (! $setting) {
                return null;
            }

            $translation = $setting->translations->firstWhere('locale', $locale)
                ?: $setting->translations->firstWhere('locale', $this->fallbackLocale());

            return array_merge([
                'id' => $setting->id,
                'is_active' => $setting->is_active,
            ], $translation?->only([
                'sidebar_title',
                'structure_label',
                'about_label',
                'staff_label',
                'default_head_desc',
                'mission_label',
                'support_title',
                'support_desc',
                'contact_btn_label',
                'function_badge_label',
            ]) ?: []);
        });

        return $this->successResponse($payload, 'University center settings retrieved successfully');
    }

    protected function publicFileUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    protected function normalizePublicFilePath(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', trim($path)), '/');

        if (str_starts_with($normalized, 'media/green-campus/')) {
            return 'cms/green-campus/'.substr($normalized, strlen('media/green-campus/'));
        }

        if ($normalized === 'media/graduation-2026.mp4') {
            return 'cms/videos/files/graduation-2026.mp4';
        }

        if ($normalized === 'media/graduation-2026-thumbnail.jpg') {
            return 'cms/videos/thumbnails/graduation-2026-thumbnail.jpg';
        }

        return $path;
    }
}
