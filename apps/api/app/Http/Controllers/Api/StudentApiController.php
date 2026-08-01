<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationDocumentRequest;
use App\Http\Requests\ApplicationRequest;
use App\Http\Requests\StudentProfileRequest;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\Contract;
use App\Models\DocumentRequest;
use App\Models\EducationBackground;
use App\Models\Guardian;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\SupportTicket;
use App\Services\ApplicationWorkflowService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class StudentApiController extends Controller
{
    use ApiResponse;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function currentUserId(): int
    {
        return (int) Auth::id();
    }

    protected function getStudentProfile()
    {
        return StudentProfile::where('user_id', $this->currentUserId())->first();
    }

    protected function createStatusHistory(Application $application, ?string $oldStatus, string $newStatus, string $note): void
    {
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'status' => $newStatus,
            'comment' => $note,
            'note' => $note,
            'changed_by' => $this->currentUserId(),
        ]);
    }

    protected function notifyStudent(int $userId, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    protected function storagePath(string $relativePath, string $disk = 'local'): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if (str_contains($relativePath, '..')) {
            abort(400, 'Invalid file path');
        }

        $root = config("filesystems.disks.{$disk}.root");

        return rtrim((string) $root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    // ─── Profile ──────────────────────────────────────────────────────────────

    public function showProfile(Request $request)
    {
        $profile = StudentProfile::with(['guardians', 'educationBackgrounds'])
            ->where('user_id', $this->currentUserId())
            ->first();

        if (! $profile) {
            return $this->successResponse(null, 'Student profile not found, please create one');
        }

        return $this->successResponse($profile, 'Student profile retrieved');
    }

    public function updateProfile(StudentProfileRequest $request)
    {
        $userId = $this->currentUserId();
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $profile = StudentProfile::updateOrCreate(
                ['user_id' => $userId],
                [
                    'full_name_english' => $validated['full_name_english'],
                    'phone' => $validated['phone'],
                    'gender' => $validated['gender'],
                    'birth_date' => $validated['birth_date'],
                    'passport_number' => $validated['passport_number'],
                    'passport_expiry_date' => $validated['passport_expiry_date'],
                    'nationality' => $validated['nationality'],
                    'address' => $validated['address'],
                ]
            );

            if (! empty($validated['guardian_name'])) {
                Guardian::updateOrCreate(
                    ['student_profile_id' => $profile->id],
                    [
                        'name' => $validated['guardian_name'],
                        'relation' => $validated['guardian_relation'] ?? '',
                        'phone' => $validated['guardian_phone'] ?? '',
                        'email' => $validated['guardian_email'] ?? null,
                    ]
                );
            }

            if (! empty($validated['education_institution_name'])) {
                EducationBackground::updateOrCreate(
                    ['student_profile_id' => $profile->id],
                    [
                        'institution_name' => $validated['education_institution_name'],
                        'degree_obtained' => $validated['education_degree_obtained'] ?? '',
                        'gpa' => $validated['education_gpa'] ?? '',
                        'graduation_year' => $validated['education_graduation_year'] ?? date('Y'),
                    ]
                );
            }

            DB::commit();

            $profile->load(['guardians', 'educationBackgrounds']);

            return $this->successResponse($profile, 'Student profile updated successfully');
        } catch (Throwable $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e, 'Failed to update student profile.');
        }
    }

    // ─── Applications ─────────────────────────────────────────────────────────

    public function createApplication(ApplicationRequest $request)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->errorResponse('Please create your student profile first.', 400);
        }

        $app = Application::create([
            'student_profile_id' => $profile->id,
            'program_id' => $request->program_id,
            'faculty_id' => $request->faculty_id,
            'department_id' => $request->department_id,
            'degree_level' => $request->degree_level,
            'language_of_study' => $request->language_of_study,
            'study_mode' => $request->study_mode,
            'status' => 'draft',
        ]);

        $this->createStatusHistory($app, null, 'draft', 'Application draft created');
        $this->notifyStudent($this->currentUserId(), 'notification.applicationCreated.title', 'notification.applicationCreated.message');

        return $this->successResponse($app, 'Application created successfully', 201);
    }

    public function listApplications(Request $request)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->successResponse([], 'No applications found');
        }

        $apps = Application::with(['program.translations', 'faculty.translations', 'department.translations', 'documents', 'contracts'])
            ->where('student_profile_id', $profile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($apps, 'Applications list retrieved');
    }

    public function showApplication(Request $request, int $id)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->errorResponse('Profile not found', 404);
        }

        $app = Application::with([
            'program.translations',
            'faculty.translations',
            'department.translations',
            'documents',
            'statusHistories',
            'contracts.payments',
        ])
            ->where('id', $id)
            ->where('student_profile_id', $profile->id)
            ->first();

        if (! $app) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }

        return $this->successResponse($app, 'Application details retrieved');
    }

    public function updateApplication(ApplicationRequest $request, int $id)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->errorResponse('Profile not found', 404);
        }

        $app = Application::where('id', $id)
            ->where('student_profile_id', $profile->id)
            ->first();

        if (! $app) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }

        if ($app->status !== 'draft') {
            return $this->errorResponse('You can only update applications in draft status', 400);
        }

        $app->update([
            'program_id' => $request->program_id,
            'faculty_id' => $request->faculty_id,
            'department_id' => $request->department_id,
            'degree_level' => $request->degree_level,
            'language_of_study' => $request->language_of_study,
            'study_mode' => $request->study_mode,
        ]);

        return $this->successResponse($app->fresh(['program.translations', 'faculty.translations', 'department.translations', 'documents']), 'Application updated successfully');
    }

    public function submitApplication(Request $request, int $id)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->errorResponse('Profile not found', 404);
        }

        $app = Application::where('id', $id)
            ->where('student_profile_id', $profile->id)
            ->first();

        if (! $app) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }

        if ($app->status !== 'draft') {
            return $this->errorResponse('Application has already been submitted', 400);
        }

        $requiredDocuments = collect($this->workflow()->requiredDocuments($app))
            ->where('is_required', true)
            ->pluck('document_type')
            ->values()
            ->all();
        $uploadedDocuments = ApplicationDocument::where('application_id', $app->id)
            ->pluck('document_type')
            ->filter()
            ->all();
        $missingDocuments = array_values(array_diff($requiredDocuments, $uploadedDocuments));

        if (! empty($missingDocuments)) {
            return $this->errorResponse('Missing required documents: '.implode(', ', $missingDocuments), 422, [
                'missing_documents' => $missingDocuments,
            ]);
        }

        $oldStatus = $app->status;
        $app->update(['status' => 'submitted']);

        $this->createStatusHistory($app, $oldStatus, 'submitted', 'Application submitted for review by student');
        $this->notifyStudent($this->currentUserId(), 'notification.applicationSubmitted.title', 'notification.applicationSubmitted.message');

        return $this->successResponse($app, 'Application submitted successfully');
    }

    // ─── Documents ────────────────────────────────────────────────────────────

    public function uploadDocument(ApplicationDocumentRequest $request, int $id)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->errorResponse('Profile not found', 404);
        }

        $app = Application::where('id', $id)
            ->where('student_profile_id', $profile->id)
            ->first();

        if (! $app) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }

        if (! $request->hasFile('file')) {
            return $this->errorResponse('No file provided', 400);
        }

        $file = $request->file('file');
        $documentType = $request->document_type;
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$extension;
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize();
        $path = $file->storeAs('applications/'.$app->id.'/documents', $filename, 'local');

        $doc = ApplicationDocument::create([
            'application_id' => $app->id,
            'document_name' => $request->document_name ?: $documentType,
            'document_type' => $documentType,
            'file_path' => $path,
            'storage_disk' => 'local',
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'status' => 'pending',
            'note' => $request->note,
        ]);

        $this->notifyStudent($this->currentUserId(), 'notification.documentUploaded.title', 'notification.documentUploaded.message');

        return $this->successResponse($doc, 'Document uploaded successfully', 201);
    }

    public function deleteDocument(Request $request, int $id, int $documentId)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->errorResponse('Profile not found', 404);
        }

        $app = Application::where('id', $id)
            ->where('student_profile_id', $profile->id)
            ->first();

        if (! $app) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }

        $doc = ApplicationDocument::where('id', $documentId)
            ->where('application_id', $app->id)
            ->first();

        if (! $doc) {
            return $this->errorResponse('Document not found', 404);
        }

        if ($app->status !== 'draft') {
            return $this->errorResponse('Documents can only be deleted while the application is still a draft', 400);
        }

        if ($doc->file_path) {
            $absolutePath = $this->storagePath($doc->file_path, $doc->storage_disk ?: 'local');

            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }

        $doc->delete();

        return $this->successResponse(null, 'Document deleted successfully');
    }

    /**
     * Secure download for student's own document (auth-gated).
     */
    public function downloadDocument(Request $request, int $id)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->errorResponse('Profile not found', 404);
        }

        $doc = ApplicationDocument::where('id', $id)
            ->whereHas('application', function ($query) use ($profile) {
                $query->where('student_profile_id', $profile->id);
            })
            ->first();

        if (! $doc) {
            return $this->errorResponse('Document not found or unauthorized', 404);
        }

        $disk = $doc->storage_disk ?: 'local';
        $absolutePath = $this->storagePath($doc->file_path, $disk);

        if (! Storage::disk($disk)->exists($doc->file_path) || ! is_file($absolutePath)) {
            return $this->errorResponse('File not found in storage', 404);
        }

        return response()->download($absolutePath, $doc->original_name ?: basename($doc->file_path));
    }

    // ─── Notifications ────────────────────────────────────────────────────────

    public function notifications(Request $request)
    {
        $notifications = Notification::where('user_id', $this->currentUserId())
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($notifications, 'Notifications list retrieved');
    }

    public function markNotificationRead(Request $request, int $notifId)
    {
        $notif = Notification::where('id', $notifId)
            ->where('user_id', $this->currentUserId())
            ->first();

        if (! $notif) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notif->update(['is_read' => true, 'read_at' => now()]);

        return $this->successResponse($notif, 'Notification marked as read');
    }

    public function markAllNotificationsRead(Request $request)
    {
        Notification::where('user_id', $this->currentUserId())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return $this->successResponse(null, 'All notifications marked as read');
    }

    // ─── Contracts & Payments ─────────────────────────────────────────────────

    public function contracts(Request $request)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->successResponse([], 'No contracts found');
        }

        $appIds = Application::where('student_profile_id', $profile->id)->pluck('id');
        $contracts = Contract::whereIn('application_id', $appIds)
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($contracts, 'Contracts list retrieved');
    }

    public function payments(Request $request)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->successResponse([], 'No payments found');
        }

        $appIds = Application::where('student_profile_id', $profile->id)->pluck('id');
        $contractIds = Contract::whereIn('application_id', $appIds)->pluck('id');
        $payments = Payment::whereIn('contract_id', $contractIds)
            ->with('contract')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($payments, 'Payments list retrieved');
    }

    // ─── Document Requests ────────────────────────────────────────────────────

    public function documentRequests(Request $request)
    {
        $profile = $this->getStudentProfile();

        if (! $profile) {
            return $this->successResponse([], 'No document requests found');
        }

        $reqs = DocumentRequest::where('student_profile_id', $profile->id)->get();

        return $this->successResponse($reqs, 'Document requests list retrieved');
    }

    // ─── Support Tickets ──────────────────────────────────────────────────────

    public function supportTickets(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $this->currentUserId())
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($tickets, 'Support tickets list retrieved');
    }

    public function createSupportTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'in:low,normal,high',
        ]);

        DB::beginTransaction();
        try {
            $ticket = SupportTicket::create([
                'user_id' => $this->currentUserId(),
                'subject' => $validated['subject'],
                'status' => 'open',
                'priority' => $validated['priority'] ?? 'normal',
            ]);

            DB::table('support_ticket_messages')->insert([
                'support_ticket_id' => $ticket->id,
                'user_id' => $this->currentUserId(),
                'message' => $validated['message'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return $this->successResponse($ticket, 'Support ticket created successfully', 201);
        } catch (Throwable $e) {
            DB::rollBack();

            return $this->transactionErrorResponse($e, 'Failed to create support ticket.');
        }
    }

    protected function transactionErrorResponse(Throwable $e, string $message)
    {
        report($e);

        if (config('app.debug')) {
            $message .= ' '.$e->getMessage();
        }

        return $this->errorResponse($message, 500);
    }

    public function addSupportTicketMessage(Request $request, int $id)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::where('id', $id)
            ->where('user_id', $this->currentUserId())
            ->first();

        if (! $ticket) {
            return $this->errorResponse('Support ticket not found or unauthorized', 404);
        }

        DB::table('support_ticket_messages')->insert([
            'support_ticket_id' => $ticket->id,
            'user_id' => $this->currentUserId(),
            'message' => $validated['message'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(null, 'Message added successfully', 201);
    }

    private function workflow(): ApplicationWorkflowService
    {
        return app(ApplicationWorkflowService::class);
    }
}
