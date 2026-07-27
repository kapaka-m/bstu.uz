<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Application;
use App\Models\ApplicationFeePayment;
use App\Models\ApplicationStatusHistory;
use App\Models\Contract;
use App\Models\DocumentRequirement;
use App\Models\Enrollment;
use App\Models\HousingRequest;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Prikaz;
use App\Models\ResidencePermitProcess;
use App\Models\ServiceFeePayment;
use App\Models\StudentVisaProcess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ApplicationWorkflowService
{
    public const DOCUMENT_STATUSES = ['NOT_UPLOADED', 'UPLOADED', 'UNDER_REVIEW', 'APPROVED', 'REJECTED', 'REUPLOAD_REQUIRED'];

    public const EQUIVALENCY_STATUSES = [
        'NOT_REQUIRED',
        'WAITING_DOCUMENTS',
        'UNDER_REVIEW',
        'ADDITIONAL_DOCUMENTS_REQUIRED',
        'RESULT_ISSUED',
        'STUDENT_REVIEW_REQUIRED',
        'STUDENT_ACCEPTED',
        'REVIEW_REQUESTED',
        'APPROVED',
    ];

    public const PAYMENT_STATUSES = ['NOT_REQUIRED', 'REQUIRED', 'UPLOADED', 'UNDER_REVIEW', 'APPROVED', 'REJECTED', 'REUPLOAD_REQUIRED'];

    public const APPLICATION_STATUSES = [
        'PROFILE_CREATED',
        'DOCUMENTS_REQUIRED',
        'DOCUMENTS_UNDER_REVIEW',
        'DOCUMENT_CORRECTION_REQUIRED',
        'ACADEMIC_REVIEW_REQUIRED',
        'ACADEMIC_REVIEW_IN_PROGRESS',
        'EQUIVALENCY_RESULT_ISSUED',
        'STUDENT_EQUIVALENCY_APPROVAL_REQUIRED',
        'EQUIVALENCY_REVIEW_REQUESTED',
        'APPLICATION_FEE_REQUIRED',
        'APPLICATION_PAYMENT_UNDER_REVIEW',
        'APPLICATION_PAYMENT_REJECTED',
        'FINAL_REVIEW',
        'CORRECTION_REQUIRED',
        'APPLICATION_APPROVED',
        'ADMISSION_ISSUED',
        'APPLICATION_REJECTED',
    ];

    public function __construct(
        private readonly AdmissionPdfService $admissionPdf,
        private readonly EnrollmentCertificatePdfService $enrollmentPdf,
        private readonly StudyContractPdfService $contractPdf,
        private readonly PrikazPdfService $prikazPdf,
    ) {}

    public function requiredDocuments(Application $application): array
    {
        $degree = strtolower((string) $application->degree_level);
        $degreeAliases = match ($degree) {
            'phd' => ['phd', 'doctorate'],
            'doctorate' => ['doctorate', 'phd'],
            default => [$degree],
        };
        $studentType = strtolower((string) $application->student_type);

        $requirements = DocumentRequirement::query()
            ->whereNull('application_id')
            ->where('is_active', true)
            ->where(function ($query) use ($degreeAliases) {
                $query->whereNull('degree_level')->orWhereIn('degree_level', $degreeAliases);
            })
            ->where(function ($query) use ($studentType) {
                $query->whereNull('student_type')->orWhere('student_type', $studentType);
            })
            ->where(function ($query) use ($application) {
                $query->whereNull('program_id')->orWhere('program_id', $application->program_id);
            })
            ->orderByRaw('program_id is null')
            ->orderBy('id')
            ->get()
            ->map(fn (DocumentRequirement $requirement) => $this->documentRequirementPayload($requirement))
            ->all();

        $additional = DocumentRequirement::query()
            ->where('application_id', $application->id)
            ->where('is_active', true)
            ->get()
            ->map(fn (DocumentRequirement $requirement) => $this->documentRequirementPayload($requirement))
            ->all();

        return array_values(collect(array_merge($requirements, $additional))
            ->unique(fn ($item) => $item['document_type'])
            ->values()
            ->all());
    }

    public function applicationSnapshot(Application $application): array
    {
        if ($application->admission) {
            $this->ensureContract($application);
        }

        $application->loadMissing([
            'studentProfile.user',
            'program.translations',
            'faculty.translations',
            'department.translations',
            'documents.previousDocument',
            'statusHistories.user',
            'equivalency.courses',
            'applicationFeePayments',
            'admission',
            'contracts.payments',
            'enrollment',
            'prikaz',
            'visaProcess',
            'housingRequest',
            'residencePermitProcess',
            'serviceFeePayments',
        ]);

        $requirements = $this->requiredDocuments($application);
        $documents = $application->documents;
        $documentCards = collect($requirements)->map(function ($requirement) use ($documents) {
            $doc = $documents
                ->where('document_type', $requirement['document_type'])
                ->sortByDesc('current_version')
                ->sortByDesc('created_at')
                ->first();

            return array_merge($requirement, [
                'document' => $doc,
                'status' => $doc?->review_status ?: 'NOT_UPLOADED',
            ]);
        })->values();

        $requiredCards = $documentCards->where('is_required', true);
        $approvedRequired = $requiredCards->filter(fn ($item) => $item['status'] === 'APPROVED')->count();
        $documentsApproved = $requiredCards->count() > 0 && $approvedRequired === $requiredCards->count();
        $equivalencyReady = ! $this->isTransfer($application)
            || in_array($application->equivalency?->status, ['STUDENT_ACCEPTED', 'APPROVED'], true);
        $paymentApproved = $application->applicationFeePayments->contains('status', 'APPROVED');
        $admissionIssued = (bool) $application->admission;
        $studyContractIssued = $application->contracts->contains(fn (Contract $contract) => filled($contract->document_path));
        $contractAdvanceApproved = $application->contracts->flatMap->payments->contains(fn ($payment) => $payment->payment_type === 'contract_advance' && $payment->status === 'APPROVED');
        $enrollmentIssued = (bool) $application->enrollment;
        $prikazIssued = (bool) $application->prikaz;
        $serviceFeeApproved = $application->serviceFeePayments->contains('status', 'APPROVED');
        $telexIssued = in_array($application->visaProcess?->telex_status, ['ISSUED', 'COMPLETED'], true);
        $visaReady = in_array($application->visaProcess?->visa_status, ['APPROVED', 'ISSUED', 'COMPLETED'], true);
        $housingCompleted = ! $application->housingRequest?->requested || in_array($application->housingRequest?->status, ['APPROVED', 'COMPLETED', 'NOT_REQUIRED'], true);
        $residenceCompleted = in_array($application->residencePermitProcess?->status, ['ISSUED', 'COMPLETED'], true);

        $checks = [
            'profile_complete' => $this->profileComplete($application),
            'passport_valid' => optional($application->studentProfile?->passport_expiry_date)->isFuture() || (string) $application->studentProfile?->passport_expiry_date > now()->toDateString(),
            'academic_complete' => filled($application->program_id) && filled($application->faculty_id) && filled($application->degree_level),
            'documents_approved' => $documentsApproved,
            'equivalency_complete' => $equivalencyReady,
            'payment_approved' => $paymentApproved,
            'final_review_approved' => $application->final_review_status === 'APPROVED',
            'admission_issued' => $admissionIssued,
            'study_contract_issued' => $studyContractIssued,
            'contract_advance_paid' => $contractAdvanceApproved,
            'enrollment_issued' => $enrollmentIssued,
            'prikaz_issued' => $prikazIssued,
            'service_fee_paid' => $serviceFeeApproved,
            'telex_issued' => $telexIssued,
            'visa_ready' => $visaReady,
            'housing_completed' => $housingCompleted,
            'residence_completed' => $residenceCompleted,
        ];

        $completion = (int) floor((collect($checks)->filter()->count() / count($checks)) * 100);

        return [
            'application' => $application,
            'requirements' => $documentCards,
            'timeline' => $this->timeline($application, $checks),
            'checks' => $checks,
            'completion_percentage' => $completion,
            'next_action' => $this->nextAction($application, $documentCards, $checks),
        ];
    }

    public function syncApplicationState(Application $application, ?int $actorId = null, ?string $note = null): Application
    {
        $snapshot = $this->applicationSnapshot($application);
        $checks = $snapshot['checks'];
        $required = collect($snapshot['requirements'])->where('is_required', true);
        $hasMissing = $required->contains(fn ($item) => $item['status'] === 'NOT_UPLOADED');
        $hasRejected = $required->contains(fn ($item) => in_array($item['status'], ['REJECTED', 'REUPLOAD_REQUIRED'], true));
        $hasUnreviewed = $required->contains(fn ($item) => in_array($item['status'], ['UPLOADED', 'UNDER_REVIEW'], true));

        $status = strtoupper((string) $application->status);
        if (in_array($status, ['PROFILE_CREATED', ''], true)) {
            $status = 'DOCUMENTS_REQUIRED';
        }

        if ($hasRejected) {
            $status = 'DOCUMENT_CORRECTION_REQUIRED';
        } elseif ($hasUnreviewed) {
            $status = 'DOCUMENTS_UNDER_REVIEW';
        } elseif (! $hasMissing && $checks['documents_approved']) {
            if ($this->isTransfer($application) && ! $checks['equivalency_complete']) {
                $status = $application->equivalency?->status === 'RESULT_ISSUED'
                    ? 'STUDENT_EQUIVALENCY_APPROVAL_REQUIRED'
                    : 'ACADEMIC_REVIEW_REQUIRED';
            } elseif (! $checks['payment_approved']) {
                $status = $application->applicationFeePayments->contains(fn ($p) => in_array($p->status, ['UPLOADED', 'UNDER_REVIEW'], true))
                    ? 'APPLICATION_PAYMENT_UNDER_REVIEW'
                    : 'APPLICATION_FEE_REQUIRED';
            } elseif ($application->final_review_status !== 'APPROVED') {
                $status = 'FINAL_REVIEW';
            } elseif ($application->admission) {
                $status = 'ADMISSION_ISSUED';
            } else {
                $status = 'APPLICATION_APPROVED';
            }
        } elseif ($hasMissing) {
            $status = 'DOCUMENTS_REQUIRED';
        }

        $documentsStatus = 'DOCUMENTS_REQUIRED';
        if ($hasRejected) {
            $documentsStatus = 'REUPLOAD_REQUIRED';
        } elseif ($hasUnreviewed) {
            $documentsStatus = 'UNDER_REVIEW';
        } elseif (! $hasMissing && $checks['documents_approved']) {
            $documentsStatus = 'APPROVED';
        }

        $application->forceFill(['documents_status' => $documentsStatus])->save();

        return $this->transition($application, $status, $actorId, $note ?: 'Workflow state synchronized');
    }

    public function ensureContract(Application $application): Contract
    {
        $amount = (float) ($application->program?->tuition_fee ?: 0);
        $advancePercentage = $this->settings()->int('workflow.contract_advance_percentage', 30);
        $advanceAmount = $amount > 0 ? round($amount * ($advancePercentage / 100), 2) : null;

        $contract = Contract::firstOrCreate(
            ['application_id' => $application->id],
            [
                'contract_number' => $this->nextContractNumber(),
                'amount' => $amount,
                'currency' => $application->program?->currency ?: $this->settings()->text('workflow.currency', 'USD'),
                'advance_percentage' => $advancePercentage,
                'advance_amount' => $advanceAmount,
                'status' => 'issued',
            ]
        );
        $this->contractPdf->ensureDocument($contract);

        return $contract->fresh('payments');
    }

    public function contractAdvanceApproved(Application $application): bool
    {
        $application->loadMissing('contracts.payments');

        return $application->contracts->flatMap->payments->contains(fn (Payment $payment) => $payment->payment_type === 'contract_advance' && $payment->status === 'APPROVED');
    }

    public function issueEnrollment(Application $application, int $actorId): Enrollment
    {
        if (! $application->admission) {
            throw new RuntimeException('Enrollment cannot be issued before admission.');
        }
        if (! $this->contractAdvanceApproved($application)) {
            throw new RuntimeException('Enrollment cannot be issued before the '.$this->settings()->int('workflow.contract_advance_percentage', 30).'% contract payment is approved.');
        }

        return DB::transaction(function () use ($application, $actorId) {
            $existing = Enrollment::where('application_id', $application->id)->first();
            if ($existing) {
                $this->enrollmentPdf->ensureDocument($existing);

                return $existing;
            }

            $enrollment = Enrollment::create([
                'application_id' => $application->id,
                'admission_id' => $application->admission->id,
                'student_profile_id' => $application->student_profile_id,
                'program_id' => $application->program_id,
                'student_number' => $this->nextEnrollmentNumber(),
                'academic_year' => $this->academicYear($application->intended_intake),
                'issue_date' => now()->toDateString(),
                'status' => 'active',
                'issued_by' => $actorId,
                'issued_at' => now(),
            ]);

            $this->enrollmentPdf->generate($enrollment);
            $this->notify($application, 'Enrollment certificate issued', 'Your student enrollment certificate is ready.', 'enrollment', '/student/enrollment');

            return $enrollment;
        });
    }

    public function issuePrikaz(Application $application, int $actorId): Prikaz
    {
        if (! $application->enrollment) {
            throw new RuntimeException('Prikaz cannot be issued before enrollment certificate.');
        }

        return DB::transaction(function () use ($application, $actorId) {
            $existing = Prikaz::where('application_id', $application->id)->first();
            if ($existing) {
                $this->prikazPdf->ensureDocument($existing);

                return $existing;
            }

            $prikaz = Prikaz::create([
                'application_id' => $application->id,
                'enrollment_id' => $application->enrollment->id,
                'student_profile_id' => $application->student_profile_id,
                'program_id' => $application->program_id,
                'prikaz_number' => $this->nextPrikazNumber(),
                'issue_date' => now()->toDateString(),
                'academic_year' => $application->enrollment->academic_year,
                'status' => 'ISSUED',
                'issued_by' => $actorId,
                'issued_at' => now(),
            ]);

            $this->prikazPdf->generate($prikaz);
            $this->notify($application, 'Prikaz issued', 'Your university enrollment order is ready.', 'prikaz', '/student/prikaz');

            return $prikaz;
        });
    }

    public function serviceFeeApproved(Application $application): bool
    {
        $application->loadMissing('serviceFeePayments');

        return $application->serviceFeePayments->contains('status', 'APPROVED');
    }

    public function ensureVisaProcess(Application $application): StudentVisaProcess
    {
        return StudentVisaProcess::firstOrCreate(
            ['application_id' => $application->id],
            ['student_profile_id' => $application->student_profile_id]
        );
    }

    public function ensureHousingRequest(Application $application): HousingRequest
    {
        return HousingRequest::firstOrCreate(
            ['application_id' => $application->id],
            ['student_profile_id' => $application->student_profile_id]
        );
    }

    public function ensureResidencePermitProcess(Application $application): ResidencePermitProcess
    {
        return ResidencePermitProcess::firstOrCreate(
            ['application_id' => $application->id],
            ['student_profile_id' => $application->student_profile_id]
        );
    }

    public function transition(Application $application, string $newStatus, ?int $actorId = null, ?string $note = null): Application
    {
        $newStatus = strtoupper($newStatus);
        $oldStatus = strtoupper((string) $application->status);
        if ($oldStatus === $newStatus) {
            return $application;
        }

        $application->forceFill([
            'status' => strtolower($newStatus),
            'current_step' => $newStatus,
        ])->save();

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'old_status' => strtolower($oldStatus),
            'new_status' => strtolower($newStatus),
            'status' => strtolower($newStatus),
            'comment' => $note,
            'note' => $note,
            'changed_by' => $actorId ?: $application->studentProfile?->user_id,
        ]);

        $this->notify($application, 'Application status updated', 'Your application moved to '.str_replace('_', ' ', $newStatus).'.', 'status', '/student/application');

        return $application->refresh();
    }

    public function issueAdmission(Application $application, int $actorId): Admission
    {
        $snapshot = $this->applicationSnapshot($application);
        $checks = $snapshot['checks'];
        foreach (['profile_complete', 'passport_valid', 'academic_complete', 'documents_approved', 'equivalency_complete', 'payment_approved', 'final_review_approved'] as $check) {
            if (! $checks[$check]) {
                throw new RuntimeException('Admission cannot be issued before all conditions are satisfied.');
            }
        }

        return DB::transaction(function () use ($application, $actorId) {
            $existing = Admission::where('application_id', $application->id)->first();
            if ($existing) {
                $this->ensureContract($application);

                return $existing;
            }

            $admission = Admission::create([
                'application_id' => $application->id,
                'student_profile_id' => $application->student_profile_id,
                'faculty_id' => $application->faculty_id,
                'program_id' => $application->program_id,
                'admission_number' => $this->nextAdmissionNumber(),
                'issue_date' => now()->toDateString(),
                'status' => 'ISSUED',
                'student_type' => $application->student_type,
                'education_type' => $application->study_mode,
                'study_language' => $application->language_of_study,
                'estimated_study_duration' => $application->program?->duration_years ? $application->program->duration_years.' years' : null,
                'issued_by' => $actorId,
                'issued_at' => now(),
            ]);
            $this->admissionPdf->generate($admission);
            $this->ensureContract($application);

            $application->forceFill(['admission_status' => 'ISSUED'])->save();
            $this->transition($application, 'ADMISSION_ISSUED', $actorId, 'Final admission issued.');
            $this->notify($application, 'Admission issued', 'Your final admission has been issued.', 'admission', '/student/admission');

            return $admission;
        });
    }

    public function notify(Application $application, string $title, string $message, string $type, string $actionUrl): void
    {
        $userId = $application->studentProfile?->user_id;
        if (! $userId) {
            return;
        }

        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'related_application_id' => $application->id,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    public function isTransfer(Application $application): bool
    {
        return strtolower((string) $application->student_type) === 'transfer';
    }

    private function profileComplete(Application $application): bool
    {
        $profile = $application->studentProfile;
        return filled($profile?->full_name_english)
            && filled($profile?->birth_date)
            && filled($profile?->passport_number)
            && filled($profile?->passport_expiry_date)
            && filled($profile?->phone)
            && filled($profile?->nationality);
    }

    private function timeline(Application $application, array $checks): array
    {
        $transfer = $this->isTransfer($application);
        $items = [
            ['key' => 'account', 'label' => 'Account Created', 'status' => 'Completed'],
            ['key' => 'documents_required', 'label' => 'Documents Required', 'status' => $checks['documents_approved'] ? 'Completed' : 'Action Required'],
            ['key' => 'documents_review', 'label' => 'Documents Under Review', 'status' => $checks['documents_approved'] ? 'Approved' : 'In Progress'],
        ];
        if ($transfer) {
            $items[] = ['key' => 'academic_review', 'label' => 'Academic Review', 'status' => $checks['equivalency_complete'] ? 'Approved' : 'Under Review'];
        }
        $items[] = ['key' => 'fee', 'label' => 'Application Fee Required', 'status' => $checks['payment_approved'] ? 'Completed' : 'Action Required'];
        $items[] = ['key' => 'payment_review', 'label' => 'Payment Under Review', 'status' => $application->application_fee_status === 'APPROVED' ? 'Approved' : 'Not Started'];
        $items[] = ['key' => 'final_review', 'label' => 'Final Application Review', 'status' => $checks['final_review_approved'] ? 'Approved' : 'Not Started'];
        $items[] = ['key' => 'admission', 'label' => 'Admission Issued', 'status' => $checks['admission_issued'] ? 'Completed' : 'Not Started'];
        $items[] = ['key' => 'study_contract', 'label' => 'Study Contract', 'status' => $checks['study_contract_issued'] ? 'Issued' : ($checks['admission_issued'] ? 'Action Required' : 'Not Started')];
        $items[] = ['key' => 'contract_advance', 'label' => '30% Contract Payment', 'status' => $checks['contract_advance_paid'] ? 'Completed' : ($checks['admission_issued'] ? 'Action Required' : 'Not Started')];
        $items[] = ['key' => 'enrollment', 'label' => 'Enrollment Certificate', 'status' => $checks['enrollment_issued'] ? 'Completed' : 'Not Started'];
        $items[] = ['key' => 'prikaz', 'label' => 'Prikaz', 'status' => $checks['prikaz_issued'] ? 'Issued' : 'Not Started'];
        $items[] = ['key' => 'service_fee', 'label' => 'Service Fee', 'status' => $checks['service_fee_paid'] ? 'Completed' : ($checks['prikaz_issued'] ? 'Action Required' : 'Not Started')];
        $items[] = ['key' => 'telex', 'label' => 'Telex', 'status' => $checks['telex_issued'] ? 'Issued' : 'Not Started'];
        $items[] = ['key' => 'visa', 'label' => 'Visa', 'status' => $checks['visa_ready'] ? 'Ready' : 'Not Started'];
        $items[] = ['key' => 'housing', 'label' => 'Housing', 'status' => $checks['housing_completed'] ? 'Completed' : 'In Progress'];
        $items[] = ['key' => 'residence', 'label' => 'Residence Permit', 'status' => $checks['residence_completed'] ? 'Completed' : 'Not Started'];

        return $items;
    }

    private function nextAction(Application $application, Collection $documentCards, array $checks): string
    {
        $currency = $this->settings()->text('workflow.currency', 'USD');
        $applicationFeeAmount = $this->settings()->float('workflow.application_fee_amount', 50);
        $serviceFeeAmount = $this->settings()->float('workflow.service_fee_amount', 300);
        $advancePercentage = $this->settings()->int('workflow.contract_advance_percentage', 30);

        if (! $checks['documents_approved']) {
            return $documentCards->where('is_required', true)->where('status', 'NOT_UPLOADED')->count() > 0
                ? $this->settings()->text('workflow.next.upload_documents', '')
                : $this->settings()->text('workflow.next.wait_document_review', '');
        }
        if (! $checks['equivalency_complete']) {
            return $this->isTransfer($application)
                ? $this->settings()->text('workflow.next.wait_equivalency', '')
                : $this->settings()->text('workflow.next.equivalency_not_required', '');
        }
        if (! $checks['payment_approved']) {
            return $this->settings()->render('workflow.next.pay_application_fee', [
                'amount' => $this->moneyLabel($applicationFeeAmount),
                'currency' => $currency,
            ]);
        }
        if (! $checks['final_review_approved']) {
            return $this->settings()->text('workflow.next.wait_final_review', '');
        }
        if (! $checks['admission_issued']) {
            return $this->settings()->text('workflow.next.wait_admission', '');
        }
        if (! $checks['study_contract_issued']) {
            return $this->settings()->text('workflow.next.review_contract', '');
        }
        if (! $checks['contract_advance_paid']) {
            return $this->settings()->render('workflow.next.upload_contract_advance', [
                'percentage' => $advancePercentage,
            ]);
        }
        if (! $checks['enrollment_issued']) {
            return $this->settings()->text('workflow.next.wait_enrollment', '');
        }
        if (! $checks['prikaz_issued']) {
            return $this->settings()->text('workflow.next.wait_prikaz', '');
        }
        if (! $checks['service_fee_paid']) {
            return $this->settings()->render('workflow.next.upload_service_fee', [
                'amount' => $this->moneyLabel($serviceFeeAmount),
                'currency' => $currency,
            ]);
        }
        if (! $checks['telex_issued']) {
            return $this->settings()->text('workflow.next.wait_telex', '');
        }
        if (! $checks['visa_ready']) {
            return $this->settings()->text('workflow.next.wait_visa', '');
        }
        if (! $checks['housing_completed']) {
            return $this->settings()->text('workflow.next.wait_housing', '');
        }
        if (! $checks['residence_completed']) {
            return $this->settings()->text('workflow.next.wait_residence', '');
        }
        return $this->settings()->text('workflow.next.completed', '');
    }

    private function documentRequirementPayload(DocumentRequirement $requirement): array
    {
        return [
            'id' => $requirement->id,
            'document_type' => $requirement->document_type,
            'name' => $requirement->name,
            'description' => $requirement->description,
            'is_required' => (bool) $requirement->is_required,
            'deadline' => $requirement->deadline,
            'request_reason' => $requirement->request_reason,
        ];
    }

    private function moneyLabel(float $amount): string
    {
        return fmod($amount, 1.0) === 0.0 ? (string) (int) $amount : number_format($amount, 2);
    }

    private function settings(): CmsSettingService
    {
        return app(CmsSettingService::class);
    }

    private function nextAdmissionNumber(): string
    {
        do {
            $number = 'ADM-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Admission::where('admission_number', $number)->exists());

        return $number;
    }

    private function nextContractNumber(): string
    {
        do {
            $number = 'CTR-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Contract::where('contract_number', $number)->exists());

        return $number;
    }

    private function nextEnrollmentNumber(): string
    {
        do {
            $number = 'ENR-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Enrollment::where('student_number', $number)->exists());

        return $number;
    }

    private function nextPrikazNumber(): string
    {
        do {
            $number = 'PRK-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Prikaz::where('prikaz_number', $number)->exists());

        return $number;
    }

    private function academicYear(?string $intake): string
    {
        if (preg_match('/(fall|autumn)[-_ ]?(20\d{2})/i', (string) $intake, $match)) {
            $year = (int) $match[2];

            return $year.'–'.($year + 1);
        }
        if (preg_match('/spring[-_ ]?(20\d{2})/i', (string) $intake, $match)) {
            $year = (int) $match[1] - 1;

            return $year.'–'.($year + 1);
        }

        $year = (int) now()->format('Y');

        return $year.'–'.($year + 1);
    }

    public function nextPaymentNumber(): string
    {
        do {
            $number = 'FEE-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (ApplicationFeePayment::where('payment_number', $number)->exists());

        return $number;
    }

    public function nextContractPaymentNumber(): string
    {
        do {
            $number = 'ADV-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Payment::where('payment_number', $number)->exists());

        return $number;
    }

    public function nextServiceFeePaymentNumber(): string
    {
        do {
            $number = 'SRV-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (ServiceFeePayment::where('payment_number', $number)->exists());

        return $number;
    }
}
