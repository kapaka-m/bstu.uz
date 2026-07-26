<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Application;
use App\Models\ApplicationFeePayment;
use App\Models\ApplicationStatusHistory;
use App\Models\DocumentRequirement;
use App\Models\Notification;
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

    public function requiredDocuments(Application $application): array
    {
        $degree = strtolower((string) $application->degree_level);
        $isTransfer = $this->isTransfer($application);
        $requirements = [
            ['passport', 'Passport Copy', 'Main passport information page.', true],
            ['photo', 'Personal Photo', 'Recent personal photo.', true],
            ['secondary_certificate', 'Secondary School Certificate', 'Completed secondary education certificate.', true],
        ];

        if ($degree === 'bachelor') {
            $requirements[] = ['secondary_transcript', 'Secondary School Transcript', 'Secondary grades transcript if separate from certificate.', true];
        }

        if (in_array($degree, ['master', 'phd', 'doctorate'], true)) {
            $requirements[] = ['bachelor_degree', 'Bachelor Degree / Diploma', 'Certified bachelor diploma.', true];
            $requirements[] = ['bachelor_transcript', 'Bachelor Transcript', 'Bachelor degree transcript.', true];
        }

        if (in_array($degree, ['phd', 'doctorate'], true)) {
            $requirements[] = ['master_degree', 'Master Degree / Diploma', 'Certified master diploma.', true];
            $requirements[] = ['master_transcript', 'Master Transcript', 'Master degree transcript.', true];
        }

        if ($isTransfer) {
            $requirements[] = ['university_transcript', 'University Transcript', 'Transcript from previous university.', true];
            $requirements[] = ['proof_of_enrollment', 'Proof of Enrollment', 'Student status certificate from previous university.', true];
            $requirements[] = ['course_descriptions', 'Course Descriptions / Syllabus', 'Course descriptions for academic equivalency.', false];
        }

        $additional = DocumentRequirement::query()
            ->where('application_id', $application->id)
            ->where('is_active', true)
            ->get()
            ->map(fn (DocumentRequirement $requirement) => [
                $requirement->document_type,
                $requirement->name,
                $requirement->description,
                (bool) $requirement->is_required,
                $requirement->id,
                $requirement->deadline,
                $requirement->request_reason,
            ])
            ->all();

        return array_values(collect(array_merge($requirements, $additional))
            ->unique(fn ($item) => $item[0])
            ->map(fn ($item) => [
                'id' => $item[4] ?? null,
                'document_type' => $item[0],
                'name' => $item[1],
                'description' => $item[2] ?? null,
                'is_required' => (bool) ($item[3] ?? true),
                'deadline' => $item[5] ?? null,
                'request_reason' => $item[6] ?? null,
            ])
            ->values()
            ->all());
    }

    public function applicationSnapshot(Application $application): array
    {
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

        $checks = [
            'profile_complete' => $this->profileComplete($application),
            'passport_valid' => optional($application->studentProfile?->passport_expiry_date)->isFuture() || (string) $application->studentProfile?->passport_expiry_date > now()->toDateString(),
            'academic_complete' => filled($application->program_id) && filled($application->faculty_id) && filled($application->degree_level),
            'documents_approved' => $documentsApproved,
            'equivalency_complete' => $equivalencyReady,
            'payment_approved' => $paymentApproved,
            'final_review_approved' => $application->final_review_status === 'APPROVED',
            'admission_issued' => $admissionIssued,
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

        return $this->transition($application, $status, $actorId, $note ?: 'Workflow state synchronized');
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
            ['key' => 'documents_review', 'label' => 'Documents Under Review', 'status' => $application->documents_status === 'APPROVED' ? 'Approved' : 'In Progress'],
        ];
        if ($transfer) {
            $items[] = ['key' => 'academic_review', 'label' => 'Academic Review', 'status' => $checks['equivalency_complete'] ? 'Approved' : 'Under Review'];
        }
        $items[] = ['key' => 'fee', 'label' => 'Application Fee Required', 'status' => $checks['payment_approved'] ? 'Completed' : 'Action Required'];
        $items[] = ['key' => 'payment_review', 'label' => 'Payment Under Review', 'status' => $application->application_fee_status === 'APPROVED' ? 'Approved' : 'Not Started'];
        $items[] = ['key' => 'final_review', 'label' => 'Final Application Review', 'status' => $checks['final_review_approved'] ? 'Approved' : 'Not Started'];
        $items[] = ['key' => 'admission', 'label' => 'Admission Issued', 'status' => $checks['admission_issued'] ? 'Completed' : 'Not Started'];

        return $items;
    }

    private function nextAction(Application $application, $documentCards, array $checks): string
    {
        if (! $checks['documents_approved']) {
            return $documentCards->where('is_required', true)->where('status', 'NOT_UPLOADED')->count() > 0
                ? 'Upload all required documents.'
                : 'Wait for document review or correct rejected documents.';
        }
        if (! $checks['equivalency_complete']) {
            return $this->isTransfer($application) ? 'Wait for academic equivalency result and accept it.' : 'Academic equivalency is not required.';
        }
        if (! $checks['payment_approved']) {
            return 'Pay the 50 USD application and admission fee and upload the receipt.';
        }
        if (! $checks['final_review_approved']) {
            return 'Wait for final university review.';
        }
        if (! $checks['admission_issued']) {
            return 'Wait for admission issuance.';
        }
        return 'Admission issued.';
    }

    private function nextAdmissionNumber(): string
    {
        do {
            $number = 'ADM-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Admission::where('admission_number', $number)->exists());

        return $number;
    }

    public function nextPaymentNumber(): string
    {
        do {
            $number = 'FEE-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (ApplicationFeePayment::where('payment_number', $number)->exists());

        return $number;
    }
}
