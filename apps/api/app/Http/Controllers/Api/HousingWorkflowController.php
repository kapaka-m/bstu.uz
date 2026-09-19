<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\HousingPayment;
use App\Models\HousingRequest;
use App\Models\TranslationKey;
use App\Services\ApplicationWorkflowService;
use App\Services\HousingWorkflowService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class HousingWorkflowController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly HousingWorkflowService $housing) {}

    public function submit(Request $request, int $application)
    {
        $this->ownedApplication($request, $application);
        $data = $request->validate([
            'pinfl' => ['required', 'string', 'regex:/^[0-9]{14}$/D'],
            'month' => ['required', 'date_format:Y-m'],
            'preferred_room_type' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'file' => $this->fileRules(false),
        ]);
        $path = null;
        try {
            $result = DB::transaction(function () use ($request, $application, $data, &$path) {
                $app = $this->ownedApplication($request, $application, true);
                $this->requireEligible($app);
                $housing = HousingRequest::firstOrCreate(['application_id' => $app->id], ['student_profile_id' => $app->student_profile_id]);
                if ($housing->requested && $housing->pinfl && $housing->start_month && $housing->status !== 'REJECTED') {
                    $this->fail('housingAlreadySubmitted');
                }
                $month = $housing->start_month?->format('Y-m') ?? now()->format('Y-m');
                if ($data['month'] !== $month) {
                    $this->fail('housingInvalidMonth');
                }
                $housing->fill([
                    'requested' => true, 'status' => 'UNDER_REVIEW', 'pinfl' => $data['pinfl'],
                    'pinfl_verified_at' => null, 'pinfl_verified_by' => null,
                    'start_month' => $month.'-01', 'preferred_room_type' => $data['preferred_room_type'] ?? null,
                    'notes' => $data['notes'] ?? null, 'reviewer_id' => null, 'reviewed_at' => null,
                ])->save();
                if ($request->hasFile('file')) {
                    $this->storePayment($request, $housing, $month, $path);
                } elseif (! $housing->payments()->whereDate('month', $month.'-01')->whereIn('status', ['APPROVED', 'UPLOADED'])->exists()) {
                    $this->fail('housingReceiptRequired');
                }
                $this->notify($app, 'housing.requestSubmitted');

                return $housing->fresh('payments');
            });
        } catch (Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }

        return $this->successResponse($result, null, 201);
    }

    public function uploadReceipt(Request $request, int $application)
    {
        $this->ownedApplication($request, $application);
        $data = $request->validate(['month' => ['required', 'date_format:Y-m'], 'file' => $this->fileRules()]);
        $path = null;
        try {
            $payment = DB::transaction(function () use ($request, $application, $data, &$path) {
                $app = $this->ownedApplication($request, $application, true);
                $housing = $app->housingRequest;
                if (! $housing?->requested || ! $housing->start_month || in_array($housing->status, ['NOT_REQUIRED', 'REJECTED'], true)) {
                    $this->fail('housingRequestRequired');
                }
                if ($data['month'] < $housing->start_month->format('Y-m') || $data['month'] > now()->format('Y-m')) {
                    $this->fail('housingInvalidMonth');
                }
                if (! $housing->isCompleted() && $data['month'] !== $housing->start_month->format('Y-m')) {
                    $this->fail('housingInvalidMonth');
                }

                $payment = $this->storePayment($request, $housing, $data['month'], $path);
                $this->notify($app, 'interface.housingSaved');

                return $payment;
            });
        } catch (Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }

        return $this->successResponse($payment, null, 201);
    }

    public function reviewPayment(Request $request, int $application, int $payment)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED'])],
            'rejection_reason' => ['required_if:status,REJECTED', 'nullable', 'string', 'max:2000'],
        ]);
        $result = DB::transaction(function () use ($request, $application, $payment, $data) {
            $app = Application::lockForUpdate()->findOrFail($application);
            $receipt = HousingPayment::whereHas('housingRequest', fn ($query) => $query->where('application_id', $app->id))->findOrFail($payment);
            if ($receipt->status !== 'UPLOADED') {
                $this->fail('housingReceiptReviewed');
            }
            if ($data['status'] === 'APPROVED' && (! Storage::disk('local')->exists($receipt->receipt_path)
                || $receipt->currency !== 'USD' || (float) $receipt->amount < HousingWorkflowService::MONTHLY_AMOUNT)) {
                $this->fail('housingReceiptRequired');
            }
            $receipt->update($data + ['reviewer_id' => $request->user()->id, 'reviewed_at' => now()]);
            $this->notify($app, $data['status'] === 'APPROVED' ? 'apanel.workflow.paymentApproved' : 'apanel.workflow.paymentRejected');

            return $receipt;
        });

        return $this->successResponse($result);
    }

    public function review(Request $request, int $application)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED', 'UNDER_REVIEW', 'NOT_REQUIRED', 'COMPLETED'])],
            'pinfl_verified' => ['required_if:status,APPROVED,COMPLETED', 'boolean'],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $result = DB::transaction(function () use ($request, $application, $data) {
            $app = Application::lockForUpdate()->findOrFail($application);
            $housing = $app->housingRequest;
            if (! $housing?->requested) {
                $this->fail('housingRequestRequired');
            }
            if (in_array($data['status'], ['APPROVED', 'COMPLETED'], true)) {
                $this->requireEligible($app);
                if (! ($data['pinfl_verified'] ?? false) || ! preg_match('/^[0-9]{14}$/D', $housing->pinfl ?? '') || ! $this->housing->firstMonthPaid($housing)) {
                    $this->fail('housingApprovalRequirements');
                }
                $housing->pinfl_verified_at = now();
                $housing->pinfl_verified_by = $request->user()->id;
            }
            $housing->fill([
                'status' => $data['status'], 'admin_notes' => $data['admin_notes'] ?? null,
                'reviewer_id' => $request->user()->id, 'reviewed_at' => now(),
            ])->save();
            $this->notify($app, match ($data['status']) {
                'APPROVED', 'COMPLETED' => 'apanel.workflow.housingApproved',
                'REJECTED' => 'apanel.workflow.housingRejected',
                default => 'interface.housingSaved',
            });

            return $housing->fresh('payments');
        });

        return $this->successResponse($result);
    }

    public function studentDownload(Request $request, int $application, int $payment)
    {
        $this->ownedApplication($request, $application);

        return $this->download($application, $payment);
    }

    public function adminDownload(Request $request, int $application, int $payment)
    {
        return $this->download($application, $payment);
    }

    private function download(int $application, int $payment)
    {
        $receipt = HousingPayment::whereHas('housingRequest', fn ($query) => $query->where('application_id', $application))->findOrFail($payment);
        abort_unless(Storage::disk('local')->exists($receipt->receipt_path), 404);

        return Storage::disk('local')->download($receipt->receipt_path, $receipt->receipt_original_name);
    }

    private function storePayment(Request $request, HousingRequest $housing, string $month, ?string &$path): HousingPayment
    {
        $previous = $housing->payments()->whereDate('month', $month.'-01')->first();
        if ($previous && $previous->status !== 'REJECTED') {
            $this->fail('housingDuplicateReceipt');
        }
        $file = $request->file('file');
        $path = $file->storeAs('applications/'.$housing->application_id.'/receipts/housing', Str::uuid().'.'.$file->extension(), 'local');
        if (! $path) {
            $this->fail('housingReceiptRequired');
        }

        return $housing->payments()->create([
            'month' => $month.'-01', 'version' => ($previous?->version ?? 0) + 1,
            'amount' => HousingWorkflowService::MONTHLY_AMOUNT, 'currency' => 'USD', 'status' => 'UPLOADED',
            'receipt_path' => $path, 'receipt_original_name' => $file->getClientOriginalName(),
            'receipt_mime_type' => $file->getMimeType(), 'receipt_size' => $file->getSize(),
        ]);
    }

    private function ownedApplication(Request $request, int $id, bool $lock = false): Application
    {
        $query = Application::whereHas('studentProfile', fn ($profile) => $profile->where('user_id', $request->user()->id));
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($id);
    }

    private function requireEligible(Application $application): void
    {
        if (in_array(false, $this->housing->prerequisites($application), true)) {
            $this->fail('housingPrerequisites');
        }
    }

    private function fileRules(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,heic,heif',
            'mimetypes:application/pdf,image/jpeg,image/png,image/webp,image/heic,image/heif', 'max:10240'];
    }

    private function notify(Application $application, string $message): void
    {
        app(ApplicationWorkflowService::class)->notify($application, 'student.nav.housing', $message, 'housing', '/student/housing');
    }

    private function fail(string $key): never
    {
        $translation = TranslationKey::where('group', 'interface')->where('key', $key)->first();
        $message = $translation?->values()->where('locale', app()->getLocale())->value('value')
            ?? $translation?->values()->where('locale', 'en')->value('value') ?? $key;
        throw ValidationException::withMessages(['housing' => $message]);
    }
}
