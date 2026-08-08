<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationEquivalency;
use App\Models\ApplicationFeePayment;
use App\Models\DocumentRequirement;
use App\Models\Payment;
use App\Models\ServiceFeePayment;
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
use Illuminate\Validation\Rule;

class ApanelApplicationWorkflowController extends Controller
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

    public function index(Request $request)
    {
        $query = Application::with(['studentProfile.user', 'program.translations', 'faculty.translations', 'documents', 'equivalency', 'applicationFeePayments', 'admission'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhereHas('studentProfile', fn ($p) => $p->where('passport_number', 'like', "%{$search}%")->orWhere('full_name_english', 'like', "%{$search}%"))
                    ->orWhereHas('studentProfile.user', fn ($u) => $u->where('email', 'like', "%{$search}%"));
            });
        }

        foreach (['status', 'degree_level', 'student_type', 'documents_status', 'application_fee_status', 'admission_status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->integer('program_id'));
        }
        if ($request->filled('nationality')) {
            $query->whereHas('studentProfile', fn ($p) => $p->where('nationality', $request->input('nationality')));
        }

        return $this->successResponse($query->paginate((int) $request->input('per_page', 15)), 'Applications retrieved');
    }

    public function show(Request $request, int $application)
    {
        $app = $this->findApplication($application);

        return $this->successResponse($this->workflow->applicationSnapshot($app), 'Application workflow retrieved');
    }

    public function documents(Request $request, int $application)
    {
        $app = $this->findApplication($application);

        return $this->successResponse($this->workflow->applicationSnapshot($app), 'Application documents retrieved');
    }

    public function reviewDocument(Request $request, int $application, int $document)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED', 'REUPLOAD_REQUIRED', 'UNDER_REVIEW'])],
            'rejection_reason' => ['nullable', 'required_if:status,REJECTED,REUPLOAD_REQUIRED', 'string', 'max:2000'],
            'internal_admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $app = $this->findApplication($application);
        $doc = ApplicationDocument::where('id', $document)->where('application_id', $app->id)->firstOrFail();

        $doc->update([
            'review_status' => $validated['status'],
            'status' => strtolower($validated['status']),
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'internal_admin_notes' => $validated['internal_admin_notes'] ?? null,
        ]);

        $this->workflow->notify($app, 'Document reviewed', $doc->document_name.' is now '.$validated['status'].'.', 'document', '/student/documents');
        $this->workflow->syncApplicationState($app, Auth::id(), 'Document '.$doc->document_type.' reviewed as '.$validated['status']);

        return $this->successResponse($doc->fresh(), 'Document reviewed');
    }

    public function requestDocument(Request $request, int $application)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_required' => ['boolean'],
            'deadline' => ['nullable', 'date'],
            'request_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $app = $this->findApplication($application);
        $requirement = DocumentRequirement::updateOrCreate(
            ['application_id' => $app->id, 'document_type' => $validated['document_type']],
            array_merge($validated, ['requested_by' => Auth::id(), 'is_active' => true])
        );

        $this->workflow->notify($app, 'Additional document requested', $requirement->name.' is requested by the university.', 'document', '/student/documents');
        $this->workflow->syncApplicationState($app, Auth::id(), 'Additional document requested: '.$requirement->document_type);

        return $this->successResponse($requirement, 'Additional document requested', 201);
    }

    public function downloadDocument(Request $request, int $application, int $document)
    {
        $app = $this->findApplication($application);
        $doc = ApplicationDocument::where('id', $document)->where('application_id', $app->id)->firstOrFail();
        if (! Storage::disk($doc->storage_disk ?: 'local')->exists($doc->file_path)) {
            return $this->errorResponse('File not found', 404);
        }

        return $this->downloadFromDisk($doc->storage_disk ?: 'local', $doc->file_path, $doc->original_name ?: basename($doc->file_path));
    }

    public function equivalency(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        if (! $this->workflow->isTransfer($app)) {
            return $this->errorResponse('Equivalency is not required for new students.', 404);
        }
        $equivalency = ApplicationEquivalency::firstOrCreate(
            ['application_id' => $app->id],
            ['status' => 'UNDER_REVIEW', 'reviewer_id' => Auth::id()]
        );
        $app->forceFill(['equivalency_status' => $equivalency->status])->save();

        return $this->successResponse($equivalency->load('courses'), 'Equivalency retrieved');
    }

    public function saveEquivalency(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        if (! $this->workflow->isTransfer($app)) {
            return $this->errorResponse('Equivalency is not required for new students.', 422);
        }

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(ApplicationWorkflowService::EQUIVALENCY_STATUSES)],
            'previous_university' => ['nullable', 'string', 'max:255'],
            'previous_country' => ['nullable', 'string', 'max:120'],
            'previous_program' => ['nullable', 'string', 'max:255'],
            'previous_study_language' => ['nullable', 'string', 'max:120'],
            'previous_education_type' => ['nullable', 'string', 'max:120'],
            'completed_years' => ['nullable', 'integer', 'min:0'],
            'completed_semesters' => ['nullable', 'integer', 'min:0'],
            'completed_credits' => ['nullable', 'numeric', 'min:0'],
            'accepted_credits' => ['nullable', 'numeric', 'min:0'],
            'rejected_credits' => ['nullable', 'numeric', 'min:0'],
            'proposed_entry_year' => ['nullable', 'string', 'max:50'],
            'proposed_entry_semester' => ['nullable', 'string', 'max:50'],
            'estimated_remaining_duration' => ['nullable', 'string', 'max:120'],
            'general_academic_notes' => ['nullable', 'string'],
            'courses' => ['array'],
            'courses.*.previous_course_name' => ['required_with:courses', 'string', 'max:255'],
            'courses.*.previous_course_code' => ['nullable', 'string', 'max:120'],
            'courses.*.previous_credits' => ['nullable', 'numeric'],
            'courses.*.matched_university_course' => ['nullable', 'string', 'max:255'],
            'courses.*.matched_course_code' => ['nullable', 'string', 'max:120'],
            'courses.*.accepted_credits' => ['nullable', 'numeric'],
            'courses.*.course_status' => ['nullable', Rule::in(['ACCEPTED', 'PARTIALLY_ACCEPTED', 'NOT_ACCEPTED', 'MUST_BE_STUDIED', 'REQUIRES_EXAM', 'REQUIRES_DOCUMENT'])],
            'courses.*.required_action' => ['nullable', 'string', 'max:255'],
            'courses.*.notes' => ['nullable', 'string'],
        ]);

        $equivalency = DB::transaction(function () use ($app, $validated) {
            $courses = $validated['courses'] ?? null;
            unset($validated['courses']);
            $equivalency = ApplicationEquivalency::updateOrCreate(
                ['application_id' => $app->id],
                array_merge($validated, ['reviewer_id' => Auth::id(), 'status' => $validated['status'] ?? 'UNDER_REVIEW'])
            );
            if (is_array($courses)) {
                $equivalency->courses()->delete();
                foreach ($courses as $course) {
                    $equivalency->courses()->create([
                        'previous_course_name' => $course['previous_course_name'],
                        'previous_course_code' => $course['previous_course_code'] ?? null,
                        'previous_credits' => $course['previous_credits'] ?? null,
                        'matched_university_course' => $course['matched_university_course'] ?? null,
                        'matched_course_code' => $course['matched_course_code'] ?? null,
                        'accepted_credits' => $course['accepted_credits'] ?? null,
                        'course_status' => $course['course_status'] ?? 'MUST_BE_STUDIED',
                        'required_action' => $course['required_action'] ?? null,
                        'notes' => $course['notes'] ?? null,
                    ]);
                }
            }

            return $equivalency->load('courses');
        });
        $app->forceFill(['equivalency_status' => $equivalency->status])->save();
        $this->workflow->syncApplicationState($app, Auth::id(), 'Academic equivalency updated');

        return $this->successResponse($equivalency, 'Equivalency saved');
    }

    public function issueEquivalency(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        $equivalency = ApplicationEquivalency::where('application_id', $app->id)->firstOrFail();
        $equivalency->update(['status' => 'RESULT_ISSUED', 'result_issued_at' => now(), 'reviewer_id' => Auth::id()]);
        $app->forceFill(['equivalency_status' => 'RESULT_ISSUED'])->save();
        $this->workflow->transition($app, 'STUDENT_EQUIVALENCY_APPROVAL_REQUIRED', Auth::id(), 'Equivalency result issued');
        $this->workflow->notify($app, 'Equivalency result issued', 'Please review and accept the academic equivalency result.', 'equivalency', '/student/equivalency');

        return $this->successResponse($equivalency->fresh('courses'), 'Equivalency result issued');
    }

    public function payment(Request $request, int $application)
    {
        $app = $this->findApplication($application);

        return $this->successResponse($app->load('applicationFeePayments'), 'Application fee payments retrieved');
    }

    public function reviewPayment(Request $request, int $application, int $payment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED', 'REUPLOAD_REQUIRED', 'UNDER_REVIEW'])],
            'rejection_reason' => ['nullable', 'required_if:status,REJECTED,REUPLOAD_REQUIRED', 'string', 'max:2000'],
            'internal_admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $app = $this->findApplication($application);
        $fee = ApplicationFeePayment::where('id', $payment)->where('application_id', $app->id)->firstOrFail();
        $fee->update([
            'status' => $validated['status'],
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'internal_admin_notes' => $validated['internal_admin_notes'] ?? null,
        ]);
        $app->forceFill(['application_fee_status' => $validated['status']])->save();
        $this->workflow->notify($app, 'Payment reviewed', 'Your '.$this->settings->float('workflow.application_fee_amount', 50).' '.$this->settings->text('workflow.currency', 'USD').' fee receipt is '.$validated['status'].'.', 'payment', '/student/payments');
        $this->workflow->syncApplicationState($app, Auth::id(), 'Application fee payment reviewed as '.$validated['status']);

        return $this->successResponse($fee->fresh(), 'Payment reviewed');
    }

    public function reviewContractPayment(Request $request, int $application, int $payment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED', 'REUPLOAD_REQUIRED', 'UNDER_REVIEW'])],
            'rejection_reason' => ['nullable', 'required_if:status,REJECTED,REUPLOAD_REQUIRED', 'string', 'max:2000'],
        ]);
        $app = $this->findApplication($application);
        $contractIds = $app->contracts()->pluck('id');
        $contractPayment = Payment::where('id', $payment)->whereIn('contract_id', $contractIds)->firstOrFail();
        $contractPayment->update([
            'status' => $validated['status'],
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);
        $advancePercentage = $this->settings->int('workflow.contract_advance_percentage', 30);
        $this->workflow->notify($app, $advancePercentage.'% contract payment reviewed', 'Your '.$advancePercentage.'% contract payment receipt is '.$validated['status'].'.', 'payment', '/student/payments');

        return $this->successResponse($contractPayment->fresh(), 'Contract payment reviewed');
    }

    public function finalReview(Request $request, int $application)
    {
        $app = $this->findApplication($application);

        return $this->successResponse($this->workflow->applicationSnapshot($app), 'Final review checklist retrieved');
    }

    public function approveFinalReview(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        $snapshot = $this->workflow->applicationSnapshot($app);
        foreach (['profile_complete', 'passport_valid', 'academic_complete', 'documents_approved', 'equivalency_complete', 'payment_approved'] as $check) {
            if (! $snapshot['checks'][$check]) {
                return $this->errorResponse('Final review cannot be approved while '.$check.' is incomplete.', 422);
            }
        }
        $app->forceFill(['final_review_status' => 'APPROVED', 'final_reviewed_by' => Auth::id(), 'final_reviewed_at' => now()])->save();
        $this->workflow->transition($app, 'APPLICATION_APPROVED', Auth::id(), 'Final review approved');

        return $this->successResponse($app->fresh(), 'Application approved');
    }

    public function returnForCorrection(Request $request, int $application)
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:3000']]);
        $app = $this->findApplication($application);
        $app->forceFill(['final_review_status' => 'RETURNED', 'correction_reason' => $validated['reason']])->save();
        $this->workflow->transition($app, 'CORRECTION_REQUIRED', Auth::id(), 'Returned for correction: '.$validated['reason']);
        $this->workflow->notify($app, 'Correction required', $validated['reason'], 'correction', '/student/application');

        return $this->successResponse($app->fresh(), 'Application returned for correction');
    }

    public function rejectApplication(Request $request, int $application)
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:3000']]);
        $app = $this->findApplication($application);
        $app->forceFill(['final_review_status' => 'REJECTED', 'rejection_reason' => $validated['reason']])->save();
        $this->workflow->transition($app, 'APPLICATION_REJECTED', Auth::id(), 'Application rejected: '.$validated['reason']);
        $this->workflow->notify($app, 'Application rejected', $validated['reason'], 'rejection', '/student/application');

        return $this->successResponse($app->fresh(), 'Application rejected');
    }

    public function admission(Request $request, int $application)
    {
        $app = $this->findApplication($application);

        return $this->successResponse($this->workflow->applicationSnapshot($app), 'Admission data retrieved');
    }

    public function issueAdmission(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        try {
            $admission = $this->workflow->issueAdmission($app, Auth::id());
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        return $this->successResponse($admission, 'Admission issued');
    }

    public function downloadAdmission(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        if (! $app->admission) {
            return $this->errorResponse('Admission is not issued yet.', 404);
        }

        $path = $this->admissionPdf->ensureDocument($app->admission);
        $filename = 'admission-'.$app->admission->admission_number.'.pdf';

        return $this->downloadFromDisk('local', $path, $filename);
    }

    public function downloadContract(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        if (! $app->admission) {
            return $this->errorResponse('Study contract is not issued yet.', 404);
        }

        $contract = $this->workflow->ensureContract($app);
        $path = $this->contractPdf->ensureDocument($contract);

        return $this->downloadFromDisk('local', $path, 'study-contract-'.$contract->contract_number.'.pdf');
    }

    public function issueEnrollment(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        try {
            $enrollment = $this->workflow->issueEnrollment($app, Auth::id());
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        return $this->successResponse($enrollment, 'Enrollment certificate issued');
    }

    public function downloadEnrollment(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        if (! $app->enrollment) {
            return $this->errorResponse('Enrollment certificate is not issued yet.', 404);
        }

        $path = $this->enrollmentPdf->ensureDocument($app->enrollment);
        $filename = 'enrollment-'.$app->enrollment->student_number.'.pdf';

        return $this->downloadFromDisk('local', $path, $filename);
    }

    public function issuePrikaz(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        try {
            $prikaz = $this->workflow->issuePrikaz($app, Auth::id());
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        return $this->successResponse($prikaz, 'Prikaz issued');
    }

    public function downloadPrikaz(Request $request, int $application)
    {
        $app = $this->findApplication($application);
        if (! $app->prikaz) {
            return $this->errorResponse('Prikaz is not issued yet.', 404);
        }

        $path = $this->prikazPdf->ensureDocument($app->prikaz);

        return $this->downloadFromDisk('local', $path, 'prikaz-'.$app->prikaz->prikaz_number.'.pdf');
    }

    public function reviewServiceFee(Request $request, int $application, int $payment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED', 'REUPLOAD_REQUIRED', 'UNDER_REVIEW'])],
            'rejection_reason' => ['nullable', 'required_if:status,REJECTED,REUPLOAD_REQUIRED', 'string', 'max:2000'],
        ]);
        $app = $this->findApplication($application);
        $fee = ServiceFeePayment::where('id', $payment)->where('application_id', $app->id)->firstOrFail();
        $fee->update([
            'status' => $validated['status'],
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);
        $this->workflow->notify($app, 'Service fee reviewed', 'Your '.$this->settings->float('workflow.service_fee_amount', 300).' '.$this->settings->text('workflow.currency', 'USD').' service fee receipt is '.$validated['status'].'.', 'payment', '/student/service-fee');

        return $this->successResponse($fee->fresh(), 'Service fee reviewed');
    }

    public function updateVisa(Request $request, int $application)
    {
        $validated = $request->validate([
            'telex_number' => ['nullable', 'string', 'max:120'],
            'telex_status' => ['nullable', Rule::in(['NOT_STARTED', 'IN_PROGRESS', 'ISSUED', 'COMPLETED'])],
            'visa_status' => ['nullable', Rule::in(['NOT_STARTED', 'IN_PROGRESS', 'APPROVED', 'ISSUED', 'COMPLETED', 'REJECTED'])],
            'visa_notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $app = $this->findApplication($application);
        $process = $this->workflow->ensureVisaProcess($app);
        $process->update(array_merge($validated, [
            'reviewer_id' => Auth::id(),
            'telex_issued_at' => in_array(($validated['telex_status'] ?? $process->telex_status), ['ISSUED', 'COMPLETED'], true) ? now() : $process->telex_issued_at,
            'visa_updated_at' => now(),
        ]));
        $this->workflow->notify($app, 'Visa and telex updated', 'Your telex and visa process has been updated.', 'visa', '/student/visa');

        return $this->successResponse($process->fresh(), 'Visa process updated');
    }

    public function reviewHousing(Request $request, int $application)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED', 'UNDER_REVIEW', 'NOT_REQUIRED', 'COMPLETED'])],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $app = $this->findApplication($application);
        $housing = $this->workflow->ensureHousingRequest($app);
        $housing->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        $this->workflow->notify($app, 'Housing request updated', 'Your housing request is '.$validated['status'].'.', 'housing', '/student/housing');

        return $this->successResponse($housing->fresh(), 'Housing request reviewed');
    }

    public function updateResidence(Request $request, int $application)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['NOT_STARTED', 'IN_PROGRESS', 'ISSUED', 'COMPLETED', 'REJECTED'])],
            'notes' => ['nullable', 'string', 'max:3000'],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
            'expires_at' => ['nullable', 'date'],
        ]);
        $app = $this->findApplication($application);
        $residence = $this->workflow->ensureResidencePermitProcess($app);
        $residence->update(array_merge($validated, [
            'reviewer_id' => Auth::id(),
            'issued_at' => in_array($validated['status'], ['ISSUED', 'COMPLETED'], true) ? now() : $residence->issued_at,
        ]));
        $this->workflow->notify($app, 'Residence permit updated', 'Your residence permit process is '.$validated['status'].'.', 'residence', '/student/residence');

        return $this->successResponse($residence->fresh(), 'Residence permit updated');
    }

    private function findApplication(int $id): Application
    {
        return Application::with(['studentProfile.user', 'program.translations', 'faculty.translations', 'department.translations', 'equivalency', 'admission', 'contracts.payments', 'enrollment', 'prikaz', 'visaProcess', 'housingRequest', 'residencePermitProcess', 'serviceFeePayments'])->findOrFail($id);
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
