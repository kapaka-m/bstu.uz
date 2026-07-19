<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\AuditLog;
use App\Models\Blog;
use App\Models\BlogSetting;
use App\Models\Comment;
use App\Models\Contract;
use App\Models\Course;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\GreenCampusArticle;
use App\Models\GreenCampusStat;
use App\Models\Inquiry;
use App\Models\Locale;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\NewsEventSetting;
use App\Models\NewsletterSubscription;
use App\Models\Notification;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Program;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\SupportTicket;
use App\Models\TranslationKey;
use App\Models\TranslationValue;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoGallerySetting;
use App\Models\VideoTranslation;
use App\Models\WebFooter;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AdminCrudController extends Controller
{
    use ApiResponse;

    protected function currentUserId(): int
    {
        return (int) Auth::id();
    }

    /**
     * Map of whitelisted resource slugs to their corresponding model classes.
     */
    protected array $whitelist = [
        'locales' => Locale::class,
        'translation-keys' => TranslationKey::class,
        'translation-values' => TranslationValue::class,
        'settings' => Setting::class,
        'menus' => Menu::class,
        'menu-items' => MenuItem::class,
        'pages' => Page::class,
        'page-blocks' => PageBlock::class,
        'faculties' => Faculty::class,
        'departments' => Department::class,
        'programs' => Program::class,
        'courses' => Course::class,
        'news' => News::class,
        'blogs' => Blog::class,
        'newsletter-subscriptions' => NewsletterSubscription::class,
        'announcements' => Announcement::class,
        'staff' => StaffProfile::class,
        'services' => Service::class,
        'videos' => Video::class,
        'media' => Media::class,
        'users' => User::class,
        'roles' => Role::class,
        'permissions' => Permission::class,
        'students' => StudentProfile::class,
        'applications' => Application::class,
        'application-documents' => ApplicationDocument::class,
        'contracts' => Contract::class,
        'payments' => Payment::class,
        'inquiries' => Inquiry::class,
        'support-tickets' => SupportTicket::class,
        'comments' => Comment::class,
        'notifications' => Notification::class,
        'audit-logs' => AuditLog::class,
        'green-campus-stats' => GreenCampusStat::class,
        'green-campus-articles' => GreenCampusArticle::class,
        'application-status-histories' => ApplicationStatusHistory::class,
    ];

    /**
     * Get model class from whitelisted resource slug.
     */
    protected function getModel(string $resource)
    {
        if (! isset($this->whitelist[$resource])) {
            abort(404, "Resource '{$resource}' not found or unauthorized.");
        }

        return $this->whitelist[$resource];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $resource)
    {
        $modelClass = $this->getModel($resource);
        $query = $modelClass::query();

        // 1. Searching
        if ($request->filled('search')) {
            $search = $request->query('search');
            $tableName = (new $modelClass)->getTable();
            $columns = Schema::getColumnListing($tableName);

            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $index => $column) {
                    // Skip JSON/binary or ID fields for basic string search to prevent DB errors
                    if (in_array($column, ['id', 'settings_json', 'old_values', 'new_values'])) {
                        continue;
                    }
                    if ($index === 0) {
                        $q->where($column, 'LIKE', "%{$search}%");
                    } else {
                        $q->orWhere($column, 'LIKE', "%{$search}%");
                    }
                }
            });
        }

        // 2. Filtering
        if ($request->filled('filter') && is_array($request->query('filter'))) {
            foreach ($request->query('filter') as $field => $value) {
                if (Schema::hasColumn((new $modelClass)->getTable(), $field)) {
                    $query->where($field, $value);
                }
            }
        }

        // Status filter shorthand
        if ($request->filled('status')) {
            if (Schema::hasColumn((new $modelClass)->getTable(), 'status')) {
                $query->where('status', $request->query('status'));
            }
        }

        if ($resource === 'applications') {
            foreach (['program_id', 'faculty_id'] as $field) {
                if ($request->filled($field) && Schema::hasColumn('applications', $field)) {
                    $query->where($field, $request->query($field));
                }
            }

            if ($request->filled('nationality')) {
                $query->whereHas('studentProfile', function ($profileQuery) use ($request) {
                    $profileQuery->where('nationality', $request->query('nationality'));
                });
            }
        }

        if ($resource === 'news' && $request->boolean('news_events_only')) {
            $query->where('category', '!=', 'blog');
        }

        // 3. Sorting
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (Schema::hasColumn((new $modelClass)->getTable(), $sortBy)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        // 4. Pagination
        $perPage = $this->paginationSize($request);

        // For applications, eager-load student and program for readable display
        if ($resource === 'applications') {
            $query->with(['studentProfile.user', 'program.translations', 'faculty.translations', 'department.translations']);
        }

        if (in_array($resource, ['news', 'blogs', 'videos'], true)) {
            $query->with('translations');
        }

        $results = $query->paginate($perPage);

        return $this->successResponse($results, "{$resource} list retrieved successfully");
    }

    /**
     * Display the specified resource.
     * For applications, eager-load all related student, document, and billing data.
     */
    public function show(Request $request, string $resource, int $id)
    {
        $modelClass = $this->getModel($resource);

        if ($resource === 'applications') {
            $record = $modelClass::with([
                'studentProfile.user',
                'studentProfile.guardians',
                'studentProfile.educationBackgrounds',
                'program.translations',
                'faculty.translations',
                'department.translations',
                'documents',
                'statusHistories',
                'contracts.payments',
            ])->find($id);
        } else {
            $record = $modelClass::with(method_exists($modelClass, 'translations') ? 'translations' : [])->find($id);
        }

        if (! $record) {
            return $this->errorResponse('Record not found', 404);
        }

        return $this->successResponse($record, "{$resource} item retrieved");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $resource)
    {
        $modelClass = $this->getModel($resource);
        $rules = $this->getValidationRules($resource);

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        if ($resource === 'videos') {
            $contentErrors = $this->validateUniqueVideoContent($request->input('translations', []));
            if (! empty($contentErrors)) {
                return $this->errorResponse('Validation error', 422, $contentErrors);
            }
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            // Handle Media uploads
            if ($resource === 'media' && $request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('media', 'public');

                $record = $modelClass::create([
                    'disk' => 'public',
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'title' => $request->title,
                    'alt_text' => $request->alt_text,
                    'type' => $request->type ?: explode('/', $file->getMimeType())[0],
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'is_public' => $request->boolean('is_public', true),
                    'alt_key' => $request->alt_key,
                ]);
            } else {
                // Extract translations if present
                $translations = $request->input('translations', []);
                unset($validated['translations']);

                $validated = $this->prepareValidatedData($resource, $validated);
                $record = $modelClass::create($validated);
                $this->handleWorkflowCreated($resource, $record);

                // Save translations
                if (method_exists($record, 'translations') && ! empty($translations)) {
                    foreach ($translations as $locale => $fields) {
                        $record->translations()->create(array_merge([
                            'locale' => $locale,
                        ], $fields));
                    }
                }
            }

            // Log Action
            $this->logAction('create', $modelClass, $record->id, null, $record->toArray());

            DB::commit();
            $this->refreshPublicContentCacheVersion($resource);

            return $this->successResponse($record, "{$resource} created successfully", 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $resource, int $id)
    {
        $modelClass = $this->getModel($resource);
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->errorResponse('Record not found', 404);
        }

        $rules = $this->getValidationRules($resource, $id);
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        if ($resource === 'videos') {
            $contentErrors = $this->validateUniqueVideoContent($request->input('translations', []), $record->id);
            if (! empty($contentErrors)) {
                return $this->errorResponse('Validation error', 422, $contentErrors);
            }
        }

        $validated = $validator->validated();
        $oldValues = $record->toArray();

        DB::beginTransaction();
        try {
            $translations = $request->input('translations', []);
            unset($validated['translations']);

            $validated = $this->prepareValidatedData($resource, $validated, $record);
            $record->update($validated);
            $this->handleWorkflowSideEffects($resource, $record, $oldValues, $validated, $request);

            // Update translations
            if (method_exists($record, 'translations') && ! empty($translations)) {
                foreach ($translations as $locale => $fields) {
                    $record->translations()->updateOrCreate(
                        ['locale' => $locale],
                        $fields
                    );
                }
            }

            // Log Action
            $this->logAction('update', $modelClass, $record->id, $oldValues, $record->fresh()->toArray());

            DB::commit();
            $this->refreshPublicContentCacheVersion($resource);

            return $this->successResponse($record->fresh(), "{$resource} updated successfully");
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e);
        }
    }

    protected function handleWorkflowSideEffects(string $resource, Model $record, array $oldValues, array $validated, Request $request): void
    {
        if ($resource === 'applications') {
            $oldStatus = $oldValues['status'] ?? null;
            $newStatus = $record->status;

            if ($oldStatus !== $newStatus) {
                $note = $request->input('note') ?: $request->input('comment') ?: "Application status changed from {$oldStatus} to {$newStatus}";
                ApplicationStatusHistory::create([
                    'application_id' => $record->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'status' => $newStatus,
                    'comment' => $note,
                    'note' => $note,
                    'changed_by' => $this->currentUserId(),
                ]);

                $record->loadMissing('studentProfile.user');
                if ($record->studentProfile?->user) {
                    Notification::create([
                        'user_id' => $record->studentProfile->user->id,
                        'title' => 'notification.statusChanged.title',
                        'message' => 'notification.statusChanged.message',
                        'is_read' => false,
                    ]);
                }
            }
        }

        if ($resource === 'application-documents') {
            $oldStatus = $oldValues['status'] ?? 'pending';
            $newStatus = $record->status ?? 'pending';

            if ($oldStatus !== $newStatus) {
                $record->loadMissing('application.studentProfile.user');
                $application = $record->application;
                $user = $application?->studentProfile?->user;
                $documentLabel = $record->document_type ?: $record->document_name;

                if ($newStatus === 'requested' && $application && $application->status !== 'missing_documents') {
                    $previous = $application->status;
                    $application->update(['status' => 'missing_documents']);
                    ApplicationStatusHistory::create([
                        'application_id' => $application->id,
                        'old_status' => $previous,
                        'new_status' => 'missing_documents',
                        'status' => 'missing_documents',
                        'comment' => $record->note ?: "Missing {$documentLabel} requested",
                        'note' => $record->note,
                        'changed_by' => $this->currentUserId(),
                    ]);
                }

                if ($user) {
                    $messages = [
                        'approved' => 'notification.documentApproved.message',
                        'rejected' => 'notification.documentRejected.message',
                        'requested' => 'notification.documentRequested.message',
                    ];

                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'notification.documentReview.title',
                        'message' => $messages[$newStatus] ?? "Your {$documentLabel} document status changed to {$newStatus}.",
                        'is_read' => false,
                    ]);
                }
            }
        }

        if (in_array($resource, ['contracts', 'payments'], true)) {
            $oldStatus = $oldValues['status'] ?? null;
            $newStatus = $record->status ?? null;

            if ($oldStatus !== $newStatus) {
                $this->notifyBillingStatus($resource, $record, $newStatus);
            }
        }
    }

    protected function validateUniqueVideoContent(array $translations, ?int $ignoreVideoId = null): array
    {
        $errors = [];
        $seenTitles = [];
        $seenDescriptions = [];

        foreach ($translations as $locale => $fields) {
            $title = $this->normalizedVideoText($fields['title'] ?? '');
            $description = $this->normalizedVideoText($fields['description'] ?? '');

            if ($title !== '') {
                $titleKey = $locale.'|'.$title;
                if (isset($seenTitles[$titleKey])) {
                    $errors["translations.{$locale}.title"][] = 'This video title is duplicated in the submitted translations.';
                }
                $seenTitles[$titleKey] = true;

                $query = VideoTranslation::where('locale', $locale)->whereRaw('LOWER(TRIM(title)) = ?', [$title]);
                if ($ignoreVideoId) {
                    $query->where('video_id', '!=', $ignoreVideoId);
                }
                if ($query->exists()) {
                    $errors["translations.{$locale}.title"][] = 'This video title already exists for this locale.';
                }
            }

            if ($description !== '') {
                $descriptionKey = $locale.'|'.$description;
                if (isset($seenDescriptions[$descriptionKey])) {
                    $errors["translations.{$locale}.description"][] = 'This video description is duplicated in the submitted translations.';
                }
                $seenDescriptions[$descriptionKey] = true;

                $query = VideoTranslation::where('locale', $locale)->whereRaw('LOWER(TRIM(description)) = ?', [$description]);
                if ($ignoreVideoId) {
                    $query->where('video_id', '!=', $ignoreVideoId);
                }
                if ($query->exists()) {
                    $errors["translations.{$locale}.description"][] = 'This video description already exists for this locale.';
                }
            }
        }

        return $errors;
    }

    protected function normalizedVideoText(?string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $value)));
    }

    protected function handleWorkflowCreated(string $resource, Model $record): void
    {
        if (in_array($resource, ['contracts', 'payments'], true)) {
            $this->notifyBillingStatus($resource, $record, $record->status ?? 'pending');
        }
    }

    protected function notifyBillingStatus(string $resource, Model $record, ?string $status): void
    {
        if ($resource === 'contracts') {
            $record->loadMissing('application.studentProfile.user');
            $user = $record->application?->studentProfile?->user;
            $title = 'notification.contractUpdate.title';
            $message = 'notification.contractUpdate.message';
        } else {
            $record->loadMissing('contract.application.studentProfile.user');
            $user = $record->contract?->application?->studentProfile?->user;
            $title = 'notification.paymentUpdate.title';
            $message = 'notification.paymentUpdate.message';
        }

        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $resource, int $id)
    {
        $modelClass = $this->getModel($resource);
        $record = $modelClass::find($id);

        if (! $record) {
            return $this->errorResponse('Record not found', 404);
        }

        $oldValues = $record->toArray();

        DB::beginTransaction();
        try {
            // Delete file from storage if it is media
            if ($resource === 'media' && ! empty($record->path)) {
                Storage::disk('public')->delete($record->path);
            }

            $record->delete();

            // Log Action
            $this->logAction('delete', $modelClass, $id, $oldValues, null);

            DB::commit();
            $this->refreshPublicContentCacheVersion($resource);

            return $this->successResponse(null, "{$resource} deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e);
        }
    }

    public function downloadApplicationDocument(Request $request, int $id)
    {
        $document = ApplicationDocument::find($id);

        if (! $document) {
            return $this->errorResponse('Document not found', 404);
        }

        $relativePath = ltrim(str_replace('\\', '/', $document->file_path), '/');

        if (str_contains($relativePath, '..')) {
            return $this->errorResponse('Invalid file path', 400);
        }

        $absolutePath = storage_path('app/public/'.$relativePath);

        if (! is_file($absolutePath)) {
            return $this->errorResponse('File not found in storage', 404);
        }

        return response()->download($absolutePath, $document->original_name ?: basename($document->file_path));
    }

    public function showFooterWeb(Request $request)
    {
        $footer = WebFooter::with('translations')->firstOrCreate(
            ['key' => 'main'],
            [
                'useful_links' => [],
                'faculty_links' => [],
                'social_links' => [],
                'phone' => null,
                'email' => null,
                'copyright_year' => null,
                'is_active' => true,
            ]
        );

        return $this->successResponse($footer, 'Footer web CMS content retrieved');
    }

    public function updateFooterWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'useful_links' => 'nullable|array',
            'useful_links.*.key' => 'required_with:useful_links|string',
            'useful_links.*.url' => 'required_with:useful_links|string',
            'faculty_links' => 'nullable|array',
            'faculty_links.*.key' => 'required_with:faculty_links|string',
            'faculty_links.*.url' => 'required_with:faculty_links|string',
            'social_links' => 'nullable|array',
            'social_links.*.key' => 'required_with:social_links|string',
            'social_links.*.label' => 'required_with:social_links|string',
            'social_links.*.url' => 'required_with:social_links|string',
            'admissions_apply_url' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'copyright_year' => 'nullable|integer|min:2000|max:2100',
            'is_active' => 'boolean',
            'translations' => 'required|array',
            'translations.*.logo_alt' => 'nullable|string',
            'translations.*.description' => 'nullable|string',
            'translations.*.admissions_badge' => 'nullable|string',
            'translations.*.admissions_heading' => 'nullable|string',
            'translations.*.admissions_description' => 'nullable|string',
            'translations.*.admissions_button_label' => 'nullable|string',
            'translations.*.newsletter_title' => 'nullable|string',
            'translations.*.newsletter_description' => 'nullable|string',
            'translations.*.newsletter_placeholder' => 'nullable|string',
            'translations.*.newsletter_success_message' => 'nullable|string',
            'translations.*.useful_links_title' => 'nullable|string',
            'translations.*.faculties_title' => 'nullable|string',
            'translations.*.contact_title' => 'nullable|string',
            'translations.*.address_line_1' => 'nullable|string',
            'translations.*.address_line_2' => 'nullable|string',
            'translations.*.phone_label' => 'nullable|string',
            'translations.*.email_label' => 'nullable|string',
            'translations.*.rights_text' => 'nullable|string',
            'translations.*.useful_link_labels' => 'nullable|array',
            'translations.*.faculty_link_labels' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $footer = WebFooter::with('translations')->firstOrCreate(['key' => 'main']);
            $oldValues = $footer->toArray();

            $footer->update([
                'useful_links' => array_values($validated['useful_links'] ?? []),
                'faculty_links' => array_values($validated['faculty_links'] ?? []),
                'social_links' => array_values($validated['social_links'] ?? []),
                'admissions_apply_url' => $validated['admissions_apply_url'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'copyright_year' => $validated['copyright_year'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            foreach ($validated['translations'] as $locale => $fields) {
                $footer->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $fields
                );
            }

            $this->logAction('update', WebFooter::class, $footer->id, $oldValues, $footer->fresh('translations')->toArray());
            Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
            DB::commit();

            return $this->successResponse($footer->fresh('translations'), 'Footer web CMS content updated');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e);
        }
    }

    public function showNewsEventSettings(Request $request)
    {
        $setting = NewsEventSetting::with('translations')->firstOrCreate(
            ['key' => 'main'],
            ['home_limit' => 4, 'recent_limit' => 5, 'is_active' => true]
        );

        return $this->successResponse($setting, 'News and events settings retrieved');
    }

    public function updateNewsEventSettings(Request $request)
    {
        $rules = [
            'home_limit' => 'required|integer|min:1|max:12',
            'recent_limit' => 'required|integer|min:1|max:12',
            'is_active' => 'boolean',
            'translations' => 'required|array',
            'translations.*.home_tag' => 'nullable|string|max:255',
            'translations.*.home_title' => 'nullable|string|max:255',
            'translations.*.home_subtitle' => 'nullable|string',
            'translations.*.view_all_label' => 'nullable|string|max:255',
            'translations.*.read_details_label' => 'nullable|string|max:255',
            'translations.*.search_title' => 'nullable|string|max:255',
            'translations.*.search_placeholder' => 'nullable|string|max:255',
            'translations.*.categories_title' => 'nullable|string|max:255',
            'translations.*.recent_title' => 'nullable|string|max:255',
            'translations.*.all_news_label' => 'nullable|string|max:255',
            'translations.*.news_label' => 'nullable|string|max:255',
            'translations.*.events_label' => 'nullable|string|max:255',
            'translations.*.views_label' => 'nullable|string|max:255',
            'translations.*.loading_label' => 'nullable|string|max:255',
            'translations.*.no_results_label' => 'nullable|string|max:255',
            'translations.*.clear_filters_label' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $setting = NewsEventSetting::with('translations')->firstOrCreate(['key' => 'main']);
            $oldValues = $setting->toArray();

            $setting->update([
                'home_limit' => $validated['home_limit'],
                'recent_limit' => $validated['recent_limit'],
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            foreach ($validated['translations'] as $locale => $fields) {
                $setting->translations()->updateOrCreate(['locale' => $locale], $fields);
            }

            $this->logAction('update', NewsEventSetting::class, $setting->id, $oldValues, $setting->fresh('translations')->toArray());
            Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
            DB::commit();

            return $this->successResponse($setting->fresh('translations'), 'News and events settings updated');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e);
        }
    }

    public function showBlogSettings(Request $request)
    {
        $setting = BlogSetting::with('translations')->firstOrCreate(
            ['key' => 'main'],
            ['home_limit' => 3, 'recent_limit' => 5, 'tags' => [], 'is_active' => true]
        );

        return $this->successResponse($setting, 'Blog settings retrieved');
    }

    public function updateBlogSettings(Request $request)
    {
        $rules = [
            'home_limit' => 'required|integer|min:1|max:12',
            'recent_limit' => 'required|integer|min:1|max:12',
            'tags' => 'nullable|array',
            'is_active' => 'boolean',
            'translations' => 'required|array',
            'translations.*.home_tag' => 'nullable|string|max:255',
            'translations.*.home_title' => 'nullable|string|max:255',
            'translations.*.view_all_label' => 'nullable|string|max:255',
            'translations.*.read_more_label' => 'nullable|string|max:255',
            'translations.*.search_title' => 'nullable|string|max:255',
            'translations.*.search_placeholder' => 'nullable|string|max:255',
            'translations.*.categories_title' => 'nullable|string|max:255',
            'translations.*.recent_title' => 'nullable|string|max:255',
            'translations.*.tags_title' => 'nullable|string|max:255',
            'translations.*.all_blog_label' => 'nullable|string|max:255',
            'translations.*.loading_label' => 'nullable|string|max:255',
            'translations.*.no_results_label' => 'nullable|string|max:255',
            'translations.*.clear_filters_label' => 'nullable|string|max:255',
            'translations.*.back_to_blog_label' => 'nullable|string|max:255',
            'translations.*.comments_label' => 'nullable|string|max:255',
            'translations.*.reply_label' => 'nullable|string|max:255',
            'translations.*.form_title' => 'nullable|string|max:255',
            'translations.*.form_name_label' => 'nullable|string|max:255',
            'translations.*.form_email_label' => 'nullable|string|max:255',
            'translations.*.form_comment_label' => 'nullable|string|max:255',
            'translations.*.form_submit_label' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $setting = BlogSetting::with('translations')->firstOrCreate(['key' => 'main']);
            $oldValues = $setting->toArray();

            $setting->update([
                'home_limit' => $validated['home_limit'],
                'recent_limit' => $validated['recent_limit'],
                'tags' => array_values($validated['tags'] ?? []),
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            foreach ($validated['translations'] as $locale => $fields) {
                $setting->translations()->updateOrCreate(['locale' => $locale], $fields);
            }

            $this->logAction('update', BlogSetting::class, $setting->id, $oldValues, $setting->fresh('translations')->toArray());
            Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
            DB::commit();

            return $this->successResponse($setting->fresh('translations'), 'Blog settings updated');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e);
        }
    }

    public function showVideoGallerySettings(Request $request)
    {
        $setting = VideoGallerySetting::with('translations')->firstOrCreate(
            ['key' => 'main'],
            [
                'home_limit' => 4,
                'subscriber_count' => 0,
                'youtube_channel_url' => null,
                'is_active' => true,
            ]
        );

        return $this->successResponse($setting, 'Video gallery settings retrieved');
    }

    public function updateVideoGallerySettings(Request $request)
    {
        $rules = [
            'home_limit' => 'required|integer|min:1|max:12',
            'subscriber_count' => 'required|integer|min:0',
            'youtube_channel_url' => 'nullable|url',
            'is_active' => 'boolean',
            'translations' => 'required|array',
            'translations.*.home_tag' => 'nullable|string|max:255',
            'translations.*.home_title' => 'nullable|string|max:255',
            'translations.*.home_subtitle' => 'nullable|string',
            'translations.*.view_all_label' => 'nullable|string|max:255',
            'translations.*.recommended_label' => 'nullable|string|max:255',
            'translations.*.videos_label' => 'nullable|string|max:255',
            'translations.*.description_title' => 'nullable|string|max:255',
            'translations.*.show_more_label' => 'nullable|string|max:255',
            'translations.*.show_less_label' => 'nullable|string|max:255',
            'translations.*.like_label' => 'nullable|string|max:255',
            'translations.*.liked_label' => 'nullable|string|max:255',
            'translations.*.share_label' => 'nullable|string|max:255',
            'translations.*.subscribe_label' => 'nullable|string|max:255',
            'translations.*.subscribed_label' => 'nullable|string|max:255',
            'translations.*.subscribers_label' => 'nullable|string|max:255',
            'translations.*.views_label' => 'nullable|string|max:255',
            'translations.*.channel_name' => 'nullable|string|max:255',
            'translations.*.link_copied_label' => 'nullable|string|max:255',
            'translations.*.no_videos_label' => 'nullable|string|max:255',
            'translations.*.comments_label' => 'nullable|string|max:255',
            'translations.*.reply_label' => 'nullable|string|max:255',
            'translations.*.form_title' => 'nullable|string|max:255',
            'translations.*.form_comment_label' => 'nullable|string|max:255',
            'translations.*.form_submit_label' => 'nullable|string|max:255',
            'translations.*.sign_in_title' => 'nullable|string|max:255',
            'translations.*.sign_in_text' => 'nullable|string|max:255',
            'translations.*.sign_in_action' => 'nullable|string|max:255',
            'translations.*.signed_in_as_label' => 'nullable|string|max:255',
            'translations.*.category_label' => 'nullable|string|max:255',
            'translations.*.duration_label' => 'nullable|string|max:255',
            'translations.*.platform_label' => 'nullable|string|max:255',
            'translations.*.local_label' => 'nullable|string|max:255',
            'translations.*.youtube_label' => 'nullable|string|max:255',
            'translations.*.playing_label' => 'nullable|string|max:255',
            'translations.*.verified_channel_label' => 'nullable|string|max:255',
            'translations.*.category_labels' => 'nullable|array',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $setting = VideoGallerySetting::with('translations')->firstOrCreate(['key' => 'main']);
            $oldValues = $setting->toArray();

            $setting->update([
                'home_limit' => $validated['home_limit'],
                'subscriber_count' => $validated['subscriber_count'],
                'youtube_channel_url' => $validated['youtube_channel_url'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            foreach ($validated['translations'] as $locale => $fields) {
                $setting->translations()->updateOrCreate(['locale' => $locale], $fields);
            }

            $this->logAction('update', VideoGallerySetting::class, $setting->id, $oldValues, $setting->fresh('translations')->toArray());
            Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
            DB::commit();

            return $this->successResponse($setting->fresh('translations'), 'Video gallery settings updated');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e);
        }
    }

    /**
     * Create audit log entry.
     */
    protected function logAction(string $action, string $modelType, int $modelId, ?array $oldValues = null, ?array $newValues = null)
    {
        if ($modelType === AuditLog::class) {
            return;
        }

        AuditLog::create([
            'user_id' => $this->currentUserId(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function paginationSize(Request $request): int
    {
        return max(1, min((int) $request->query('per_page', 15), 100));
    }

    protected function refreshPublicContentCacheVersion(string $resource): void
    {
        if (! in_array($resource, $this->publicContentResources(), true)) {
            return;
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    protected function publicContentResources(): array
    {
        return [
            'locales',
            'translation-keys',
            'translation-values',
            'settings',
            'menus',
            'menu-items',
            'pages',
            'page-blocks',
            'faculties',
            'departments',
            'programs',
            'courses',
            'news',
            'blogs',
            'announcements',
            'staff',
            'services',
            'videos',
            'media',
            'green-campus-stats',
            'green-campus-articles',
        ];
    }

    protected function prepareValidatedData(string $resource, array $validated, ?Model $record = null): array
    {
        if ($resource === 'users') {
            if (array_key_exists('password', $validated)) {
                if ($validated['password'] === null || $validated['password'] === '') {
                    unset($validated['password']);
                } else {
                    $validated['password'] = Hash::make($validated['password']);
                }
            }
        }

        return $validated;
    }

    protected function transactionErrorResponse(Throwable $e)
    {
        report($e);

        $message = config('app.debug')
            ? 'Transaction failed: '.$e->getMessage()
            : 'Transaction failed.';

        return $this->errorResponse($message, 500);
    }

    /**
     * Dynamic validation rules dictionary.
     */
    protected function getValidationRules(string $resource, ?int $id = null): array
    {
        switch ($resource) {
            case 'locales':
                return [
                    'code' => 'required|string|unique:locales,code,'.$id,
                    'name' => 'required|string',
                    'native_name' => 'required|string',
                    'direction' => 'required|in:ltr,rtl',
                    'is_active' => 'boolean',
                    'sort_order' => 'integer',
                ];
            case 'translation-keys':
                return [
                    'group' => 'required|string',
                    'key' => 'required|string',
                    'description' => 'nullable|string',
                    'is_system' => 'boolean',
                ];
            case 'translation-values':
                return [
                    'translation_key_id' => 'required|integer|exists:translation_keys,id',
                    'locale' => 'required|string|max:5',
                    'value' => 'required|string',
                ];
            case 'settings':
                return [
                    'key' => 'required|string|unique:settings,key,'.$id,
                    'value' => 'nullable|string',
                    'type' => 'string',
                    'group' => 'string',
                    'is_public' => 'boolean',
                ];
            case 'menus':
                return [
                    'key' => 'required|string|unique:menus,key,'.$id,
                    'location' => 'required|string',
                    'is_active' => 'boolean',
                ];
            case 'menu-items':
                return [
                    'menu_id' => 'required|integer|exists:menus,id',
                    'parent_id' => 'nullable|integer|exists:menu_items,id',
                    'route_name' => 'nullable|string',
                    'url' => 'nullable|string',
                    'icon' => 'nullable|string',
                    'sort_order' => 'integer',
                    'is_active' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'pages':
                return [
                    'slug' => 'required|string|unique:pages,slug,'.$id,
                    'template' => 'string',
                    'is_published' => 'boolean',
                    'sort_order' => 'integer',
                    'translations' => 'required|array',
                ];
            case 'page-blocks':
                return [
                    'page_id' => 'required|integer|exists:pages,id',
                    'block_key' => 'required|string',
                    'type' => 'required|string',
                    'sort_order' => 'integer',
                    'settings_json' => 'nullable|array',
                    'is_active' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'faculties':
                return [
                    'slug' => 'required|string|unique:faculties,slug,'.$id,
                    'code' => 'nullable|string|unique:faculties,code,'.$id,
                    'image' => 'nullable|string',
                    'icon' => 'nullable|string',
                    'head_name' => 'nullable|string',
                    'email' => 'nullable|email',
                    'phone' => 'nullable|string',
                    'reception_time' => 'nullable|string',
                    'source_url' => 'nullable|url',
                    'sort_order' => 'integer',
                    'is_active' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'departments':
                return [
                    'faculty_id' => 'required|integer|exists:faculties,id',
                    'slug' => 'required|string|unique:departments,slug,'.$id,
                    'code' => 'nullable|string|unique:departments,code,'.$id,
                    'image' => 'nullable|string',
                    'icon' => 'nullable|string',
                    'sort_order' => 'integer',
                    'is_active' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'programs':
                return [
                    'faculty_id' => 'required|integer|exists:faculties,id',
                    'department_id' => 'required|integer|exists:departments,id',
                    'slug' => 'required|string|unique:programs,slug,'.$id,
                    'code' => 'nullable|string|unique:programs,code,'.$id,
                    'official_code' => 'nullable|string',
                    'track' => 'nullable|string',
                    'degree' => 'required|string',
                    'duration_years' => 'required|numeric',
                    'study_mode' => 'required|string',
                    'language_of_study' => 'required|string',
                    'tuition_fee' => 'required|numeric',
                    'currency' => 'string|max:3',
                    'image' => 'nullable|string',
                    'is_active' => 'boolean',
                    'sort_order' => 'integer',
                    'translations' => 'required|array',
                ];
            case 'courses':
                return [
                    'code' => 'required|string|unique:courses,code,'.$id,
                    'credits' => 'required|integer',
                    'semester' => 'required|integer',
                    'is_active' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'news':
                return [
                    'slug' => 'required|string|unique:news,slug,'.$id,
                    'image' => 'nullable|string',
                    'category' => 'required|string',
                    'published_at' => 'nullable|date',
                    'is_published' => 'boolean',
                    'views_count' => 'integer',
                    'translations' => 'required|array',
                ];
            case 'blogs':
                return [
                    'slug' => 'required|string|unique:blogs,slug,'.$id,
                    'image' => 'nullable|string',
                    'author' => 'nullable|string|max:255',
                    'author_image' => 'nullable|string',
                    'category' => 'required|string|max:255',
                    'published_at' => 'nullable|date',
                    'is_published' => 'boolean',
                    'views_count' => 'integer',
                    'translations' => 'required|array',
                    'translations.*.title' => 'required|string|max:255',
                    'translations.*.author' => 'nullable|string|max:255',
                    'translations.*.category_label' => 'nullable|string|max:255',
                    'translations.*.summary' => 'nullable|string',
                    'translations.*.content' => 'nullable|string',
                    'translations.*.meta_title' => 'nullable|string|max:255',
                    'translations.*.meta_description' => 'nullable|string',
                ];
            case 'newsletter-subscriptions':
                return [
                    'email' => 'required|email|unique:newsletter_subscriptions,email,'.$id,
                    'locale' => 'nullable|string|max:5',
                    'status' => 'required|string|in:active,unsubscribed',
                    'subscribed_at' => 'nullable|date',
                    'unsubscribed_at' => 'nullable|date',
                    'ip_address' => 'nullable|string|max:45',
                    'user_agent' => 'nullable|string',
                ];
            case 'announcements':
                return [
                    'slug' => 'required|string|unique:announcements,slug,'.$id,
                    'type' => 'required|string',
                    'priority' => 'string',
                    'image' => 'nullable|string',
                    'starts_at' => 'nullable|date',
                    'ends_at' => 'nullable|date',
                    'is_published' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'staff':
                return [
                    'slug' => 'nullable|string|unique:staff_profiles,slug,'.$id,
                    'department_id' => 'nullable|integer|exists:departments,id',
                    'faculty_id' => 'nullable|integer|exists:faculties,id',
                    'photo' => 'nullable|string',
                    'email' => 'nullable|email',
                    'phone' => 'nullable|string',
                    'sort_order' => 'integer',
                    'is_active' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'services':
                return [
                    'slug' => 'required|string|unique:services,slug,'.$id,
                    'icon' => 'nullable|string',
                    'image' => 'nullable|string',
                    'sort_order' => 'integer',
                    'is_active' => 'boolean',
                    'translations' => 'required|array',
                ];
            case 'videos':
                return [
                    'slug' => 'required|string|unique:videos,slug,'.$id,
                    'url' => 'required|string',
                    'thumbnail' => 'nullable|string',
                    'video_type' => 'nullable|string|in:youtube,local',
                    'youtube_id' => 'nullable|string|max:255',
                    'duration' => 'nullable|string|max:50',
                    'views_count' => 'nullable|integer|min:0',
                    'likes_count' => 'nullable|integer|min:0',
                    'published_at' => 'nullable|date',
                    'is_active' => 'boolean',
                    'sort_order' => 'integer',
                    'translations' => 'required|array',
                    'translations.*.title' => 'required|string|max:255',
                    'translations.*.category' => 'nullable|string|max:255',
                    'translations.*.description' => 'nullable|string',
                ];
            case 'media':
                return [
                    'file' => ($id ? 'nullable' : 'required').'|file|mimes:pdf,jpg,jpeg,png,webp,mp4,avi,mov|mimetypes:application/pdf,image/jpeg,image/png,image/webp,video/mp4,video/x-msvideo,video/quicktime|max:51200',
                    'title' => 'nullable|string',
                    'alt_text' => 'nullable|string',
                    'type' => 'nullable|string|in:image,document,video',
                    'is_public' => 'boolean',
                    'alt_key' => 'nullable|string',
                ];
            case 'users':
                return [
                    'name' => 'required|string',
                    'email' => 'required|email|unique:users,email,'.$id,
                    'password' => $id ? 'nullable|string|min:6' : 'required|string|min:6',
                ];
            case 'roles':
                return [
                    'name' => 'required|string',
                    'slug' => 'required|string|unique:roles,slug,'.$id,
                    'description' => 'nullable|string',
                ];
            case 'permissions':
                return [
                    'name' => 'required|string',
                    'slug' => 'required|string|unique:permissions,slug,'.$id,
                    'description' => 'nullable|string',
                ];
            case 'students':
                return [
                    'user_id' => 'required|integer|exists:users,id',
                    'phone' => 'required|string',
                    'gender' => 'required|string',
                    'birth_date' => 'required|date',
                    'passport_number' => 'required|string',
                    'passport_expiry_date' => 'nullable|date',
                    'nationality' => 'required|string',
                    'address' => 'required|string',
                ];
            case 'applications':
                return [
                    'student_profile_id' => 'required|integer|exists:student_profiles,id',
                    'program_id' => 'required|integer|exists:programs,id',
                    'faculty_id' => 'nullable|integer|exists:faculties,id',
                    'department_id' => 'nullable|integer|exists:departments,id',
                    'degree_level' => 'nullable|string|in:bachelor,master,phd',
                    'language_of_study' => 'nullable|string|max:30',
                    'study_mode' => 'nullable|string|max:50',
                    'status' => 'required|string|in:draft,submitted,under_review,missing_documents,accepted,rejected,contract_pending,payment_pending,enrolled,active_student,graduated',
                    'note' => 'nullable|string',
                    'comment' => 'nullable|string',
                ];
            case 'application-documents':
                return [
                    'application_id' => 'required|integer|exists:applications,id',
                    'document_name' => 'required|string',
                    'document_type' => 'nullable|string|in:passport,photo,education_certificate,transcript,medical_certificate,language_certificate,payment_receipt,other',
                    'file_path' => 'required|string',
                    'original_name' => 'nullable|string',
                    'mime_type' => 'nullable|string',
                    'size' => 'nullable|integer',
                    'status' => 'nullable|string|in:pending,approved,rejected,requested',
                    'note' => 'nullable|string',
                ];
            case 'contracts':
                return [
                    'application_id' => 'required|integer|exists:applications,id',
                    'contract_number' => 'required|string|unique:contracts,contract_number,'.$id,
                    'amount' => 'required|numeric',
                    'status' => 'required|string|in:pending,active,signed,cancelled',
                ];
            case 'payments':
                return [
                    'contract_id' => 'required|integer|exists:contracts,id',
                    'payment_number' => 'required|string|unique:payments,payment_number,'.$id,
                    'amount' => 'required|numeric',
                    'payment_date' => 'required|date',
                    'status' => 'required|string|in:pending,paid,partially_paid,rejected,cancelled',
                ];
            case 'inquiries':
                return [
                    'name' => 'required|string',
                    'email' => 'required|email',
                    'subject' => 'required|string',
                    'message' => 'required|string',
                    'status' => 'string',
                ];
            case 'support-tickets':
                return [
                    'user_id' => 'required|integer|exists:users,id',
                    'subject' => 'required|string',
                    'status' => 'string',
                    'priority' => 'string',
                ];
            case 'comments':
                return [
                    'user_id' => 'nullable|integer|exists:users,id',
                    'commentable_type' => 'required|string',
                    'commentable_id' => 'required|integer',
                    'content' => 'required|string',
                ];
            case 'notifications':
                return [
                    'user_id' => 'required|integer|exists:users,id',
                    'title' => 'required|string',
                    'message' => 'required|string',
                    'is_read' => 'boolean',
                ];
            case 'application-status-histories':
                return [
                    'application_id' => 'required|integer|exists:applications,id',
                    'old_status' => 'nullable|string',
                    'new_status' => 'nullable|string',
                    'status' => 'required|string|in:draft,submitted,under_review,missing_documents,accepted,rejected,contract_pending,payment_pending,enrolled,active_student,graduated',
                    'comment' => 'nullable|string',
                    'note' => 'nullable|string',
                    'changed_by' => 'required|integer|exists:users,id',
                ];
            case 'green-campus-stats':
                return [
                    'icon' => 'nullable|string',
                    'sort_order' => 'integer',
                    'translations' => 'required|array',
                ];
            case 'green-campus-articles':
                return [
                    'slug' => 'required|string|unique:green_campus_articles,slug,'.$id,
                    'image' => 'nullable|string',
                    'gallery' => 'nullable|array',
                    'views' => 'integer',
                    'translations' => 'required|array',
                ];
            default:
                return [];
        }
    }
}
