<?php

namespace App\Services;

use App\Models\Application;
use App\Models\HousingRequest;

class HousingWorkflowService
{
    public const MONTHLY_AMOUNT = 40;

    public function prerequisites(Application $application): array
    {
        $application->loadMissing(['admission', 'enrollment', 'prikaz', 'visaProcess', 'contracts.payments']);

        return [
            'admission' => in_array(strtoupper($application->admission?->status ?? ''), ['ISSUED', 'APPROVED'], true),
            'enrollment' => in_array(strtoupper($application->enrollment?->status ?? ''), ['ACTIVE', 'ISSUED'], true),
            'prikaz' => in_array(strtoupper($application->prikaz?->status ?? ''), ['ISSUED', 'COMPLETED'], true),
            'visa' => in_array($application->visaProcess?->visa_status, ['APPROVED', 'ISSUED', 'COMPLETED'], true),
            'contract_payment' => $application->contracts->contains(function ($contract) {
                $amount = (int) round((float) $contract->amount * 100);
                if ($amount <= 0) {
                    return false;
                }

                // One verified advance receipt, not a sum of potentially duplicate uploads.
                return $contract->payments->contains(fn ($payment) => $payment->payment_type === 'contract_advance'
                    && $payment->status === 'APPROVED' && filled($payment->receipt_path)
                    && $payment->currency === $contract->currency
                    && (int) round((float) $payment->amount * 100) >= (int) round($amount * 0.30));
            }),
        ];
    }

    public function firstMonthPaid(?HousingRequest $housing): bool
    {
        if (! $housing?->start_month) {
            return false;
        }
        $housing->loadMissing('payments');

        return $housing->payments->contains(fn ($payment) => $payment->month->isSameMonth($housing->start_month)
            && $payment->status === 'APPROVED' && $payment->currency === 'USD'
            && (float) $payment->amount >= self::MONTHLY_AMOUNT && filled($payment->receipt_path));
    }

    public function snapshot(Application $application): array
    {
        $requirements = $this->prerequisites($application);
        $eligible = ! in_array(false, $requirements, true);
        $application->loadMissing('housingRequest.payments');
        $housing = $application->housingRequest;
        $pinflValid = (bool) preg_match('/^[0-9]{14}$/D', $housing?->pinfl ?? '');
        $paid = $this->firstMonthPaid($housing);
        $completed = $eligible && ($housing?->isCompleted() ?? false)
            && $pinflValid && $housing->pinfl_verified_at && $paid;
        $due = [];
        if ($housing?->requested && $housing->start_month && ! in_array($housing->status, ['NOT_REQUIRED', 'REJECTED'], true)) {
            $month = $housing->start_month->copy()->startOfMonth();
            $lastMonth = $housing->isCompleted() ? now()->startOfMonth() : $month->copy();
            while ($month->lte($lastMonth)) {
                $settled = $housing->payments->contains(fn ($payment) => $payment->month->isSameMonth($month)
                    && $payment->status === 'APPROVED' && $payment->currency === 'USD'
                    && (float) $payment->amount >= self::MONTHLY_AMOUNT && filled($payment->receipt_path));
                if (! $settled) {
                    $due[] = $month->format('Y-m');
                }
                $month->addMonth();
            }
        }

        return [
            'requirements' => $requirements,
            'status' => $completed ? 'COMPLETED' : (($housing?->isCompleted() ?? false) ? 'UNDER_REVIEW' : ($housing?->status ?? 'NOT_REQUESTED')),
            'eligible' => $eligible,
            'completed' => (bool) $completed,
            'pinfl_verified' => $pinflValid && (bool) $housing?->pinfl_verified_at,
            'first_month_paid' => $paid,
            'monthly_amount' => self::MONTHLY_AMOUNT,
            'currency' => 'USD',
            'current_month' => now()->format('Y-m'),
            'due_months' => $due,
        ];
    }
}
