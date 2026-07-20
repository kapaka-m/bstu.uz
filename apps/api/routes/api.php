<?php

use App\Http\Controllers\Api\AdminCrudController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // =========================================================================
    // 1. AUTH ROUTES
    // =========================================================================
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:auth-register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

    Route::middleware(['auth:sanctum', 'throttle:student-api'])->group(function () {
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });

    // =========================================================================
    // 2. PUBLIC API ROUTES
    // =========================================================================
    Route::middleware('throttle:public-api')->group(function () {
        Route::get('/locales', [PublicApiController::class, 'locales']);
        Route::get('/translations', [PublicApiController::class, 'translations']);
        Route::get('/settings', [PublicApiController::class, 'settings']);
        Route::get('/settings/public', [PublicApiController::class, 'settings']);
        Route::get('/footer-web', [PublicApiController::class, 'footerWeb']);
        Route::get('/administration/settings', [PublicApiController::class, 'administrationSettings']);
        Route::get('/administration', [PublicApiController::class, 'administration']);
        Route::get('/administration/{slug}', [PublicApiController::class, 'administrationProfile']);
        Route::get('/news-events/settings', [PublicApiController::class, 'newsEventSettings']);
        Route::get('/menus', [PublicApiController::class, 'menus']);
        Route::get('/menus/{location}', [PublicApiController::class, 'menu']);
        Route::get('/home', [PublicApiController::class, 'home']);
        Route::get('/pages', [PublicApiController::class, 'pages']);
        Route::get('/pages/{slug}', [PublicApiController::class, 'page']);
        Route::get('/page-blocks/{pageSlug}', [PublicApiController::class, 'pageBlocks']);
        Route::get('/faculties', [PublicApiController::class, 'faculties']);
        Route::get('/faculties/{slug}', [PublicApiController::class, 'faculty']);
        Route::get('/departments', [PublicApiController::class, 'departments']);
        Route::get('/departments/{slug}', [PublicApiController::class, 'department']);
        Route::get('/programs', [PublicApiController::class, 'programs']);
        Route::get('/programs/{slug}', [PublicApiController::class, 'program']);
        Route::get('/courses', [PublicApiController::class, 'courses']);
        Route::get('/news', [PublicApiController::class, 'news']);
        Route::get('/news/{slug}', [PublicApiController::class, 'newsItem']);
        Route::get('/blog/settings', [PublicApiController::class, 'blogSettings']);
        Route::get('/blog', [PublicApiController::class, 'blog']);
        Route::get('/blog/{slug}', [PublicApiController::class, 'blogItem']);
        Route::get('/blog/{slug}/comments', [PublicApiController::class, 'blogComments']);
        Route::post('/blog/{slug}/comments', [PublicApiController::class, 'storeBlogComment'])->middleware(['auth:sanctum', 'throttle:uploads']);
        Route::get('/announcements/settings', [PublicApiController::class, 'announcementSettings']);
        Route::get('/announcements', [PublicApiController::class, 'announcements']);
        Route::get('/announcements/{slug}', [PublicApiController::class, 'announcement']);
        Route::get('/green-campus/stats', [PublicApiController::class, 'greenCampusStats']);
        Route::get('/green-campus/settings', [PublicApiController::class, 'greenCampusSettings']);
        Route::get('/green-campus/articles', [PublicApiController::class, 'greenCampusArticles']);
        Route::get('/green-campus/articles/{slug}', [PublicApiController::class, 'greenCampusArticle']);
        Route::get('/services', [PublicApiController::class, 'services']);
        Route::get('/services/{slug}', [PublicApiController::class, 'service']);
        Route::get('/videos/settings', [PublicApiController::class, 'videoGallerySettings']);
        Route::get('/videos', [PublicApiController::class, 'videos']);
        Route::post('/videos/{slug}/view', [PublicApiController::class, 'videoView'])->middleware('throttle:uploads');
        Route::post('/videos/{slug}/like', [PublicApiController::class, 'videoLike'])->middleware('throttle:uploads');
        Route::get('/videos/{slug}/comments', [PublicApiController::class, 'videoComments']);
        Route::post('/videos/{slug}/comments', [PublicApiController::class, 'storeVideoComment'])->middleware(['auth:sanctum', 'throttle:uploads']);
        Route::get('/staff', [PublicApiController::class, 'staff']);
        Route::get('/staff/{slug}', [PublicApiController::class, 'staffProfile']);
        Route::get('/media/{id}', [PublicApiController::class, 'media']);
        Route::post('/inquiries', [PublicApiController::class, 'storeInquiry']);
        Route::post('/comments', [PublicApiController::class, 'storeComment']);
        Route::post('/newsletter-subscriptions', [PublicApiController::class, 'storeNewsletterSubscription'])->middleware('throttle:uploads');
    });

    // =========================================================================
    // 3. STUDENT PORTAL ROUTES (auth required)
    // =========================================================================
    Route::middleware(['auth:sanctum', 'throttle:student-api'])->group(function () {
        // --- Profile ---
        Route::get('/student/profile', [StudentApiController::class, 'showProfile']);
        Route::put('/student/profile', [StudentApiController::class, 'updateProfile']);

        // --- Applications ---
        Route::post('/applications', [StudentApiController::class, 'createApplication']);
        Route::get('/applications', [StudentApiController::class, 'listApplications']);
        Route::get('/applications/{id}', [StudentApiController::class, 'showApplication']);
        Route::put('/applications/{id}', [StudentApiController::class, 'updateApplication']);
        Route::post('/applications/{id}/submit', [StudentApiController::class, 'submitApplication']);

        // --- Documents ---
        Route::post('/applications/{id}/documents', [StudentApiController::class, 'uploadDocument'])->middleware('throttle:uploads');
        Route::delete('/applications/{id}/documents/{documentId}', [StudentApiController::class, 'deleteDocument']);

        // --- Notifications ---
        Route::get('/student/notifications', [StudentApiController::class, 'notifications']);
        Route::patch('/student/notifications/{notifId}/read', [StudentApiController::class, 'markNotificationRead']);
        Route::post('/student/notifications/read-all', [StudentApiController::class, 'markAllNotificationsRead']);

        // --- Contracts & Payments ---
        Route::get('/student/contracts', [StudentApiController::class, 'contracts']);
        Route::get('/student/payments', [StudentApiController::class, 'payments']);

        // --- Support Tickets ---
        Route::get('/student/support-tickets', [StudentApiController::class, 'supportTickets']);
        Route::post('/student/support-tickets', [StudentApiController::class, 'createSupportTicket']);
        Route::post('/student/support-tickets/{id}/messages', [StudentApiController::class, 'addSupportTicketMessage']);

        // --- Document Requests ---
        Route::get('/student/document-requests', [StudentApiController::class, 'documentRequests']);

        // --- Secure Document Download ---
        Route::get('/student/documents/{id}/download', [StudentApiController::class, 'downloadDocument']);
    });

    // =========================================================================
    // 4. APANEL CRUD ROUTES (auth + apanel role required)
    // =========================================================================
    Route::middleware(['auth:sanctum', 'role:apanel', 'throttle:apanel-api'])->prefix('apanel')->group(function () {
        Route::get('application-documents/{id}/download', [AdminCrudController::class, 'downloadApplicationDocument']);
        Route::get('cms/footer-web', [AdminCrudController::class, 'showFooterWeb']);
        Route::put('cms/footer-web', [AdminCrudController::class, 'updateFooterWeb']);
        Route::get('cms/news-events/settings', [AdminCrudController::class, 'showNewsEventSettings']);
        Route::put('cms/news-events/settings', [AdminCrudController::class, 'updateNewsEventSettings']);
        Route::get('cms/announcements/settings', [AdminCrudController::class, 'showAnnouncementSettings']);
        Route::put('cms/announcements/settings', [AdminCrudController::class, 'updateAnnouncementSettings']);
        Route::get('cms/blog/settings', [AdminCrudController::class, 'showBlogSettings']);
        Route::put('cms/blog/settings', [AdminCrudController::class, 'updateBlogSettings']);
        Route::get('cms/video-bdtu/settings', [AdminCrudController::class, 'showVideoGallerySettings']);
        Route::put('cms/video-bdtu/settings', [AdminCrudController::class, 'updateVideoGallerySettings']);
        Route::get('cms/green-campus/settings', [AdminCrudController::class, 'showGreenCampusSettings']);
        Route::put('cms/green-campus/settings', [AdminCrudController::class, 'updateGreenCampusSettings']);
        Route::get('cms/administration/settings', [AdminCrudController::class, 'showAdministrationSettings']);
        Route::put('cms/administration/settings', [AdminCrudController::class, 'updateAdministrationSettings']);
        Route::get('{resource}', [AdminCrudController::class, 'index']);
        Route::post('{resource}', [AdminCrudController::class, 'store'])->middleware('throttle:uploads');
        Route::get('{resource}/{id}', [AdminCrudController::class, 'show']);
        Route::put('{resource}/{id}', [AdminCrudController::class, 'update']);
        Route::delete('{resource}/{id}', [AdminCrudController::class, 'destroy']);
    });
});
