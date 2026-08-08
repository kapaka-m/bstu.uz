<?php

use App\Http\Controllers\Api\AdminCrudController;
use App\Http\Controllers\Api\ApanelApplicationWorkflowController;
use App\Http\Controllers\Api\AuthCmsController;
use App\Http\Controllers\Api\HomeCmsController;
use App\Http\Controllers\Api\InitialApplicationController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\StudentApplicationPortalController;
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
    Route::get('/applications/initial/metadata', [InitialApplicationController::class, 'metadata'])->middleware('throttle:public-api');
    Route::post('/applications/initial', [InitialApplicationController::class, 'store'])->middleware('throttle:auth-register');

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
        Route::get('/auth-cms', [AuthCmsController::class, 'publicIndex']);
        Route::get('/settings', [PublicApiController::class, 'settings']);
        Route::get('/settings/public', [PublicApiController::class, 'settings']);
        Route::get('/footer-web', [PublicApiController::class, 'footerWeb']);
        Route::get('/about-page', [PublicApiController::class, 'aboutPage']);
        Route::get('/contact-page', [PublicApiController::class, 'contactPage']);
        Route::get('/administration/settings', [PublicApiController::class, 'administrationSettings']);
        Route::get('/administration', [PublicApiController::class, 'administration']);
        Route::get('/administration/{slug}', [PublicApiController::class, 'administrationProfile']);
        Route::get('/news-events/settings', [PublicApiController::class, 'newsEventSettings']);
        Route::get('/menus', [PublicApiController::class, 'menus']);
        Route::get('/menus/{location}', [PublicApiController::class, 'menu']);
        Route::get('/home', [PublicApiController::class, 'home']);
        Route::get('/home-sections', [HomeCmsController::class, 'publicIndex']);
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
        Route::get('/blog/departments', [PublicApiController::class, 'blogDepartments']);
        Route::get('/blog/departments/{slug}', [PublicApiController::class, 'blogDepartment']);
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
        Route::get('/university-centers/settings', [PublicApiController::class, 'universityCenterSettings']);
        Route::get('/university-centers', [PublicApiController::class, 'universityCenters']);
        Route::get('/university-centers/{slug}', [PublicApiController::class, 'universityCenter']);
        Route::get('/services/settings', [PublicApiController::class, 'serviceSettings']);
        Route::get('/services', [PublicApiController::class, 'services']);
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
    Route::middleware(['auth:sanctum', 'role:student', 'throttle:student-api'])->group(function () {
        Route::get('/student/application-summary', [StudentApplicationPortalController::class, 'summary']);
        Route::get('/student/academic-information', [StudentApplicationPortalController::class, 'academicInformation']);
        Route::get('/student/documents/checklist', [StudentApplicationPortalController::class, 'documents']);
        Route::get('/student/equivalency', [StudentApplicationPortalController::class, 'equivalency']);
        Route::get('/student/application-fee', [StudentApplicationPortalController::class, 'payment']);
        Route::get('/student/admission', [StudentApplicationPortalController::class, 'admission']);
        Route::get('/student/admission/download', [StudentApplicationPortalController::class, 'downloadAdmission']);
        Route::get('/student/contract-advance', [StudentApplicationPortalController::class, 'contractAdvance']);
        Route::get('/student/contract/download', [StudentApplicationPortalController::class, 'downloadContract']);
        Route::get('/student/enrollment', [StudentApplicationPortalController::class, 'enrollment']);
        Route::get('/student/enrollment/download', [StudentApplicationPortalController::class, 'downloadEnrollment']);
        Route::get('/student/prikaz', [StudentApplicationPortalController::class, 'prikaz']);
        Route::get('/student/prikaz/download', [StudentApplicationPortalController::class, 'downloadPrikaz']);
        Route::get('/student/service-fee', [StudentApplicationPortalController::class, 'serviceFee']);
        Route::get('/student/visa', [StudentApplicationPortalController::class, 'visa']);
        Route::get('/student/housing', [StudentApplicationPortalController::class, 'housing']);
        Route::get('/student/residence', [StudentApplicationPortalController::class, 'residence']);
        Route::post('/applications/{id}/documents/private', [StudentApplicationPortalController::class, 'uploadDocument'])->middleware('throttle:uploads');
        Route::get('/student/private-documents/{id}/download', [StudentApplicationPortalController::class, 'downloadDocument']);
        Route::post('/applications/{id}/equivalency/accept', [StudentApplicationPortalController::class, 'acceptEquivalency']);
        Route::post('/applications/{id}/equivalency/request-review', [StudentApplicationPortalController::class, 'requestEquivalencyReview']);
        Route::post('/applications/{id}/application-fee/receipt', [StudentApplicationPortalController::class, 'uploadPaymentReceipt'])->middleware('throttle:uploads');
        Route::post('/applications/{id}/contract-advance/receipt', [StudentApplicationPortalController::class, 'uploadContractAdvanceReceipt'])->middleware('throttle:uploads');
        Route::post('/applications/{id}/service-fee/receipt', [StudentApplicationPortalController::class, 'uploadServiceFeeReceipt'])->middleware('throttle:uploads');
        Route::post('/applications/{id}/housing/request', [StudentApplicationPortalController::class, 'submitHousingRequest']);

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
        Route::get('applications-workflow', [ApanelApplicationWorkflowController::class, 'index']);
        Route::get('applications-workflow/{application}', [ApanelApplicationWorkflowController::class, 'show']);
        Route::get('applications-workflow/{application}/documents', [ApanelApplicationWorkflowController::class, 'documents']);
        Route::post('applications-workflow/{application}/documents/request', [ApanelApplicationWorkflowController::class, 'requestDocument']);
        Route::post('applications-workflow/{application}/documents/{document}/review', [ApanelApplicationWorkflowController::class, 'reviewDocument']);
        Route::get('applications-workflow/{application}/documents/{document}/download', [ApanelApplicationWorkflowController::class, 'downloadDocument']);
        Route::get('applications-workflow/{application}/equivalency', [ApanelApplicationWorkflowController::class, 'equivalency']);
        Route::put('applications-workflow/{application}/equivalency', [ApanelApplicationWorkflowController::class, 'saveEquivalency']);
        Route::post('applications-workflow/{application}/equivalency/issue', [ApanelApplicationWorkflowController::class, 'issueEquivalency']);
        Route::get('applications-workflow/{application}/payments', [ApanelApplicationWorkflowController::class, 'payment']);
        Route::post('applications-workflow/{application}/payments/{payment}/review', [ApanelApplicationWorkflowController::class, 'reviewPayment']);
        Route::post('applications-workflow/{application}/contract-payments/{payment}/review', [ApanelApplicationWorkflowController::class, 'reviewContractPayment']);
        Route::get('applications-workflow/{application}/final-review', [ApanelApplicationWorkflowController::class, 'finalReview']);
        Route::post('applications-workflow/{application}/final-review/approve', [ApanelApplicationWorkflowController::class, 'approveFinalReview']);
        Route::post('applications-workflow/{application}/final-review/return', [ApanelApplicationWorkflowController::class, 'returnForCorrection']);
        Route::post('applications-workflow/{application}/final-review/reject', [ApanelApplicationWorkflowController::class, 'rejectApplication']);
        Route::get('applications-workflow/{application}/admission', [ApanelApplicationWorkflowController::class, 'admission']);
        Route::post('applications-workflow/{application}/admission/issue', [ApanelApplicationWorkflowController::class, 'issueAdmission']);
        Route::get('applications-workflow/{application}/admission/download', [ApanelApplicationWorkflowController::class, 'downloadAdmission']);
        Route::get('applications-workflow/{application}/contract/download', [ApanelApplicationWorkflowController::class, 'downloadContract']);
        Route::post('applications-workflow/{application}/enrollment/issue', [ApanelApplicationWorkflowController::class, 'issueEnrollment']);
        Route::get('applications-workflow/{application}/enrollment/download', [ApanelApplicationWorkflowController::class, 'downloadEnrollment']);
        Route::post('applications-workflow/{application}/prikaz/issue', [ApanelApplicationWorkflowController::class, 'issuePrikaz']);
        Route::get('applications-workflow/{application}/prikaz/download', [ApanelApplicationWorkflowController::class, 'downloadPrikaz']);
        Route::post('applications-workflow/{application}/service-fees/{payment}/review', [ApanelApplicationWorkflowController::class, 'reviewServiceFee']);
        Route::put('applications-workflow/{application}/visa', [ApanelApplicationWorkflowController::class, 'updateVisa']);
        Route::post('applications-workflow/{application}/housing/review', [ApanelApplicationWorkflowController::class, 'reviewHousing']);
        Route::put('applications-workflow/{application}/residence', [ApanelApplicationWorkflowController::class, 'updateResidence']);

        Route::get('application-documents/{id}/download', [AdminCrudController::class, 'downloadApplicationDocument']);
        Route::post('newsletter/subscriptions/send', [AdminCrudController::class, 'sendNewsletterCampaign']);
        Route::get('cms/footer-web', [AdminCrudController::class, 'showFooterWeb']);
        Route::put('cms/footer-web', [AdminCrudController::class, 'updateFooterWeb']);
        Route::get('cms/about-page', [AdminCrudController::class, 'showAboutPage']);
        Route::put('cms/about-page', [AdminCrudController::class, 'updateAboutPage']);
        Route::put('cms/about-page/settings', [AdminCrudController::class, 'updateAboutPageSettings']);
        Route::post('cms/about-page/entries', [AdminCrudController::class, 'storeAboutPageEntry']);
        Route::put('cms/about-page/entries/{entry}', [AdminCrudController::class, 'updateAboutPageEntry']);
        Route::delete('cms/about-page/entries/{entry}', [AdminCrudController::class, 'deleteAboutPageEntry']);
        Route::post('cms/about-page/entries/reorder', [AdminCrudController::class, 'reorderAboutPageEntries']);
        Route::get('cms/contact-page', [AdminCrudController::class, 'showContactPage']);
        Route::put('cms/contact-page', [AdminCrudController::class, 'updateContactPage']);
        Route::get('cms/home', [HomeCmsController::class, 'adminShow']);
        Route::put('cms/home', [HomeCmsController::class, 'adminUpdate']);
        Route::get('cms/cms-auth', [AuthCmsController::class, 'adminShow']);
        Route::put('cms/cms-auth', [AuthCmsController::class, 'adminUpdate']);
        Route::get('cms/header-navbar', [AdminCrudController::class, 'showHeaderNavbar']);
        Route::put('cms/header-navbar', [AdminCrudController::class, 'updateHeaderNavbar']);
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
        Route::get('cms/interactive-services/settings', [AdminCrudController::class, 'showInteractiveServiceSettings']);
        Route::put('cms/interactive-services/settings', [AdminCrudController::class, 'updateInteractiveServiceSettings']);
        Route::get('cms/university-centers/settings', [AdminCrudController::class, 'showUniversityCenterSettings']);
        Route::put('cms/university-centers/settings', [AdminCrudController::class, 'updateUniversityCenterSettings']);
        Route::get('{resource}', [AdminCrudController::class, 'index']);
        Route::post('{resource}', [AdminCrudController::class, 'store'])->middleware('throttle:uploads');
        Route::get('{resource}/{id}', [AdminCrudController::class, 'show']);
        Route::put('{resource}/{id}', [AdminCrudController::class, 'update']);
        Route::delete('{resource}/{id}', [AdminCrudController::class, 'destroy']);
    });
});
