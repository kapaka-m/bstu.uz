<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationFeePayment;
use App\Models\Payment;
use App\Models\ServiceFeePayment;
use App\Models\Notification;
use App\Models\StudentProfile;
use App\Services\AdmissionPdfService;
use App\Services\ApplicationWorkflowService;
use App\Services\CmsSettingService;
use App\Services\EnrollmentCertificatePdfService;
use App\Services\PrikazPdfService;
use App\Services\StudyContractPdfService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentApplicationPortalController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ApplicationWorkflowService $workflow,
        private readonly AdmissionPdfService $admissionPdf,
        private readonly EnrollmentCertificatePdfService $enrollmentPdf,
        private readonly StudyContractPdfService $contractPdf,
        private readonly PrikazPdfService $prikazPdf,
        private readonly CmsSettingService $settings,
    ) {}

    public function summary(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->successResponse(null, 'No active application found');
        }

        $this->workflow->syncApplicationState($application, Auth::id(), 'Student portal opened');

        return $this->successResponse($this->workflow->applicationSnapshot($application->refresh()), 'Application summary retrieved');
    }

    public function profile(Request $request)
    {
        $profile = $this->studentProfile();
        if (! $profile) {
            return $this->errorResponse('Student profile not found', 404);
        }

        $profile->load('user', 'educationBackgrounds');
        return $this->successResponse($profile, 'Student profile retrieved');
    }

    public function academicInformation(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }
        $application->load(['program.translations', 'faculty.translations', 'department.translations']);

        return $this->successResponse($application, 'Academic information retrieved');
    }

    public function documents(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }

        return $this->successResponse($this->workflow->applicationSnapshot($application)['requirements'], 'Document checklist retrieved');
    }

    public function uploadDocument(Request $request, int $applicationId)
    {
        $application = $this->ownedApplication($applicationId);
        if (! $application) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:120'],
            'document_name' => ['nullable', 'string', 'max:255'],
            'student_notes' => ['nullable', 'string', 'max:1000'],
            'file' => $this->uploadFileRules(),
        ]);

        $requirements = collect($this->workflow->requiredDocuments($application));
        $requirement = $requirements->firstWhere('document_type', $validated['document_type']);
        if (! $requirement) {
            return $this->errorResponse('This document type is not required for the current application.', 422);
        }

        $current = ApplicationDocument::where('application_id', $application->id)
            ->where('document_type', $validated['document_type'])
            ->latest('current_version')
            ->first();
        if ($current && $current->review_status === 'APPROVED') {
            return $this->errorResponse('Approved documents cannot be replaced unless administration reopens them.', 422);
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs('private/application-documents/'.$application->id, $filename, 'local');

        $document = ApplicationDocument::create([
            'application_id' => $application->id,
            'student_profile_id' => $application->student_profile_id,
            'document_name' => $validated['document_name'] ?? $requirement['name'],
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'storage_disk' => 'local',
            'stored_filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'current_version' => $current ? $current->current_version + 1 : 1,
            'status' => 'uploaded',
            'review_status' => 'UPLOADED',
            'student_notes' => $validated['student_notes'] ?? null,
            'previous_document_id' => $current?->id,
            'is_required' => (bool) $requirement['is_required'],
        ]);

        $application->forceFill(['documents_status' => 'UNDER_REVIEW'])->save();
        $this->workflow->notify($application, 'Document uploaded', $document->document_name.' was uploaded and is waiting for review.', 'document', '/student/documents');
        $this->workflow->syncApplicationState($application, Auth::id(), 'Student uploaded '.$document->document_type);

        return $this->successResponse($document, 'Document uploaded successfully', 201);
    }

    public function downloadDocument(Request $request, int $documentId)
    {
        $profile = $this->studentProfile();
        $document = ApplicationDocument::where('id', $documentId)
            ->where('student_profile_id', $profile?->id)
            ->first();

        if (! $document || ! Storage::disk($document->storage_disk ?: 'local')->exists($document->file_path)) {
            return $this->errorResponse('Document not found or unauthorized', 404);
        }

        return $this->downloadFromDisk($document->storage_disk ?: 'local', $document->file_path, $document->original_name ?: basename($document->file_path));
    }

    public function equivalency(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application || ! $this->workflow->isTransfer($application)) {
            return $this->errorResponse('Academic equivalency is available only for transfer students.', 404);
        }

        $application->load('equivalency.courses');
        return $this->successResponse($application->equivalency, 'Equivalency retrieved');
    }

    public function acceptEquivalency(Request $request, int $applicationId)
    {
        $application = $this->ownedApplication($applicationId);
        if (! $application || ! $application->equivalency) {
            return $this->errorResponse('Equivalency result not found.', 404);
        }
        if (! in_array($application->equivalency->status, ['RESULT_ISSUED', 'STUDENT_REVIEW_REQUIRED'], true)) {
            return $this->errorResponse('Equivalency result is not waiting for student approval.', 422);
        }

        $application->equivalency->update(['status' => 'STUDENT_ACCEPTED', 'student_responded_at' => now()]);
        $application->forceFill(['equivalency_status' => 'STUDENT_ACCEPTED'])->save();
        $this->workflow->syncApplicationState($application, Auth::id(), 'Student accepted equivalency result');

        return $this->successResponse($application->equivalency->fresh('courses'), 'Equivalency accepted');
    }

    public function requestEquivalencyReview(Request $request, int $applicationId)
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $application = $this->ownedApplication($applicationId);
        if (! $application || ! $application->equivalency) {
            return $this->errorResponse('Equivalency result not found.', 404);
        }

        $application->equivalency->update([
            'status' => 'REVIEW_REQUESTED',
            'student_review_reason' => $validated['reason'],
            'student_responded_at' => now(),
        ]);
        $application->forceFill(['equivalency_status' => 'REVIEW_REQUESTED'])->save();
        $this->workflow->transition($application, 'EQUIVALENCY_REVIEW_REQUESTED', Auth::id(), 'Student requested equivalency review');

        return $this->successResponse($application->equivalency->fresh('courses'), 'Review request submitted');
    }

    public function payment(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }
        $application->load('applicationFeePayments');

        return $this->successResponse([
            'amount' => 50,
            'currency' => 'USD',
            'status' => $application->application_fee_status,
            'payments' => $application->applicationFeePayments,
            'can_upload' => $this->canUploadFeeReceipt($application),
        ], 'Application fee status retrieved');
    }

    public function uploadPaymentReceipt(Request $request, int $applicationId)
    {
        $application = $this->ownedApplication($applicationId);
        if (! $application) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }
        if (! $this->canUploadFeeReceipt($application)) {
            return $this->errorResponse('Application fee is not open yet.', 422);
        }

        $validated = $request->validate([
            'file' => $this->uploadFileRules(),
        ]);

        $file = $validated['file'];
        $path = $file->storeAs('private/application-fees/'.$application->id, Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), 'local');
        $payment = ApplicationFeePayment::create([
            'application_id' => $application->id,
            'payment_number' => $this->workflow->nextPaymentNumber(),
            'amount' => $this->settings->float('workflow.application_fee_amount', 50),
            'currency' => $this->settings->text('workflow.currency', 'USD'),
            'status' => 'UPLOADED',
            'receipt_path' => $path,
            'receipt_original_name' => $file->getClientOriginalName(),
            'receipt_mime_type' => $file->getMimeType(),
            'receipt_size' => $file->getSize(),
            'paid_at' => now(),
        ]);
        $application->forceFill(['application_fee_status' => 'UNDER_REVIEW'])->save();
        $this->workflow->syncApplicationState($application, Auth::id(), 'Application fee receipt uploaded');

        return $this->successResponse($payment, 'Receipt uploaded successfully', 201);
    }

    public function admission(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }

        $snapshot = $this->workflow->applicationSnapshot($application);
        return $this->successResponse([
            'admission' => $application->admission,
            'checks' => $snapshot['checks'],
            'application' => $application,
        ], 'Admission status retrieved');
    }

    public function downloadAdmission(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application || ! $application->admission) {
            return $this->errorResponse('Admission is not issued yet.', 404);
        }

        $path = $this->admissionPdf->ensureDocument($application->admission);
        $filename = 'admission-'.$application->admission->admission_number.'.pdf';

        return $this->downloadFromDisk('local', $path, $filename);
    }

    public function contractAdvance(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }

        $contract = $application->admission ? $this->workflow->ensureContract($application) : null;
        $contract?->load('payments');

        return $this->successResponse([
            'contract' => $contract,
            'can_upload' => (bool) $application->admission && ! $this->workflow->contractAdvanceApproved($application),
            'requires_admission' => ! $application->admission,
            'required_percentage' => $this->settings->int('workflow.contract_advance_percentage', 30),
        ], 'Contract advance payment status retrieved');
    }

    public function uploadContractAdvanceReceipt(Request $request, int $applicationId)
    {
        $application = $this->ownedApplication($applicationId);
        if (! $application) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }
        if (! $application->admission) {
            return $this->errorResponse('Admission must be issued before contract payment.', 422);
        }
        if ($this->workflow->contractAdvanceApproved($application)) {
            return $this->errorResponse('The '.$this->settings->int('workflow.contract_advance_percentage', 30).'% contract payment is already approved.', 422);
        }

        $validated = $request->validate([
            'file' => $this->uploadFileRules(),
        ]);

        $contract = $this->workflow->ensureContract($application);
        $file = $validated['file'];
        $path = $file->storeAs('private/contract-payments/'.$application->id, Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), 'local');

        $payment = Payment::create([
            'contract_id' => $contract->id,
            'payment_number' => $this->workflow->nextContractPaymentNumber(),
            'payment_type' => 'contract_advance',
            'amount' => $contract->advance_amount ?: 0,
            'currency' => $contract->currency ?: $this->settings->text('workflow.currency', 'USD'),
            'payment_date' => now(),
            'status' => 'UPLOADED',
            'receipt_path' => $path,
            'receipt_original_name' => $file->getClientOriginalName(),
            'receipt_mime_type' => $file->getMimeType(),
            'receipt_size' => $file->getSize(),
        ]);
        $advancePercentage = $this->settings->int('workflow.contract_advance_percentage', 30);
        $this->workflow->notify($application, $advancePercentage.'% contract receipt uploaded', 'Your '.$advancePercentage.'% contract payment receipt is waiting for review.', 'payment', '/student/payments');

        return $this->successResponse($payment, 'Contract advance receipt uploaded successfully', 201);
    }

    public function downloadContract(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application || ! $application->admission) {
            return $this->errorResponse('Study contract is not issued yet.', 404);
        }

        $contract = $this->workflow->ensureContract($application);
        $path = $this->contractPdf->ensureDocument($contract);

        return $this->downloadFromDisk('local', $path, 'study-contract-'.$contract->contract_number.'.pdf');
    }

    public function enrollment(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }

        $snapshot = $this->workflow->applicationSnapshot($application);

        return $this->successResponse([
            'enrollment' => $application->enrollment,
            'checks' => $snapshot['checks'],
            'application' => $application,
        ], 'Enrollment status retrieved');
    }

    public function downloadEnrollment(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application || ! $application->enrollment) {
            return $this->errorResponse('Enrollment certificate is not issued yet.', 404);
        }

        $path = $this->enrollmentPdf->ensureDocument($application->enrollment);
        $filename = 'enrollment-'.$application->enrollment->student_number.'.pdf';

        return $this->downloadFromDisk('local', $path, $filename);
    }

    public function prikaz(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }
        $snapshot = $this->workflow->applicationSnapshot($application);

        return $this->successResponse([
            'prikaz' => $application->prikaz,
            'checks' => $snapshot['checks'],
            'application' => $application,
        ], 'Prikaz status retrieved');
    }

    public function downloadPrikaz(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application || ! $application->prikaz) {
            return $this->errorResponse('Prikaz is not issued yet.', 404);
        }

        $path = $this->prikazPdf->ensureDocument($application->prikaz);

        return $this->downloadFromDisk('local', $path, 'prikaz-'.$application->prikaz->prikaz_number.'.pdf');
    }

    public function serviceFee(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }
        $application->load('serviceFeePayments');

        return $this->successResponse([
            'amount' => $this->settings->float('workflow.service_fee_amount', 300),
            'currency' => $this->settings->text('workflow.currency', 'USD'),
            'payments' => $application->serviceFeePayments,
            'can_upload' => (bool) $application->prikaz && ! $this->workflow->serviceFeeApproved($application),
        ], 'Service fee status retrieved');
    }

    public function uploadServiceFeeReceipt(Request $request, int $applicationId)
    {
        $application = $this->ownedApplication($applicationId);
        if (! $application) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }
        if (! $application->prikaz) {
            return $this->errorResponse('Service fee opens after prikaz is issued.', 422);
        }
        if ($this->workflow->serviceFeeApproved($application)) {
            return $this->errorResponse('The service fee is already approved.', 422);
        }

        $validated = $request->validate(['file' => $this->uploadFileRules()]);
        $file = $validated['file'];
        $path = $file->storeAs('private/service-fees/'.$application->id, Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), 'local');

        $payment = ServiceFeePayment::create([
            'application_id' => $application->id,
            'payment_number' => $this->workflow->nextServiceFeePaymentNumber(),
            'amount' => $this->settings->float('workflow.service_fee_amount', 300),
            'currency' => $this->settings->text('workflow.currency', 'USD'),
            'status' => 'UPLOADED',
            'receipt_path' => $path,
            'receipt_original_name' => $file->getClientOriginalName(),
            'receipt_mime_type' => $file->getMimeType(),
            'receipt_size' => $file->getSize(),
            'paid_at' => now(),
        ]);
        $this->workflow->notify($application, 'Service fee receipt uploaded', 'Your '.$this->settings->float('workflow.service_fee_amount', 300).' '.$this->settings->text('workflow.currency', 'USD').' service fee receipt is waiting for review.', 'payment', '/student/service-fee');

        return $this->successResponse($payment, 'Service fee receipt uploaded successfully', 201);
    }

    public function visa(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }

        return $this->successResponse($this->workflow->ensureVisaProcess($application), 'Visa and telex status retrieved');
    }

    public function housing(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }

        return $this->successResponse($this->workflow->ensureHousingRequest($application), 'Housing request retrieved');
    }

    public function submitHousingRequest(Request $request, int $applicationId)
    {
        $application = $this->ownedApplication($applicationId);
        if (! $application) {
            return $this->errorResponse('Application not found or unauthorized', 404);
        }
        if (! $application->enrollment) {
            return $this->errorResponse('Housing request opens after enrollment is issued.', 422);
        }

        $validated = $request->validate([
            'preferred_room_type' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $housing = $this->workflow->ensureHousingRequest($application);
        $housing->update(array_merge($validated, [
            'requested' => true,
            'status' => 'UNDER_REVIEW',
        ]));
        $this->workflow->notify($application, 'Housing request submitted', 'Your housing request is waiting for review.', 'housing', '/student/housing');

        return $this->successResponse($housing->fresh(), 'Housing request submitted');
    }

    public function residence(Request $request)
    {
        $application = $this->currentApplication();
        if (! $application) {
            return $this->errorResponse('Application not found', 404);
        }

        return $this->successResponse($this->workflow->ensureResidencePermitProcess($application), 'Residence permit status retrieved');
    }

    private function canUploadFeeReceipt(Application $application): bool
    {
        $snapshot = $this->workflow->applicationSnapshot($application);
        return $snapshot['checks']['documents_approved']
            && $snapshot['checks']['equivalency_complete']
            && ! $snapshot['checks']['payment_approved'];
    }

    private function studentProfile(): ?StudentProfile
    {
        return StudentProfile::where('user_id', Auth::id())->first();
    }

    private function currentApplication(): ?Application
    {
        $profile = $this->studentProfile();
        if (! $profile) {
            return null;
        }

        return Application::where('student_profile_id', $profile->id)
            ->latest()
            ->first();
    }

    private function uploadFileRules(): array
    {
        return [
            'required',
            'file',
            'mimes:pdf,jpg,jpeg,png,webp,heic,heif',
            'mimetypes:application/pdf,image/jpeg,image/png,image/webp,image/heic,image/heif',
            'max:10240',
        ];
    }

    private function ownedApplication(int $id): ?Application
    {
        $profile = $this->studentProfile();
        if (! $profile) {
            return null;
        }

        return Application::where('id', $id)->where('student_profile_id', $profile->id)->first();
    }

    private function downloadFromDisk(string $disk, string $path, string $filename)
    {
        $root = config("filesystems.disks.{$disk}.root");
        if (! is_string($root) || $root === '') {
            return $this->errorResponse('File download is not supported for this storage disk.', 422);
        }

        $rootPath = realpath($root);
        $filePath = realpath($root.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));

        if (! $rootPath || ! $filePath || ! str_starts_with($filePath, $rootPath.DIRECTORY_SEPARATOR)) {
            return $this->errorResponse('File not found', 404);
        }

        return response()->download($filePath, $filename);
    }
}
