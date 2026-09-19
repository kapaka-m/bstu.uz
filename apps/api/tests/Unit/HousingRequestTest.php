<?php

namespace Tests\Unit;

use App\Models\HousingRequest;
use PHPUnit\Framework\TestCase;

class HousingRequestTest extends TestCase
{
    public function test_unrequested_housing_is_never_completed(): void
    {
        foreach (['NOT_STARTED', 'NOT_REQUIRED', 'APPROVED', 'COMPLETED'] as $status) {
            $this->assertFalse((new HousingRequest(['requested' => false, 'status' => $status]))->isCompleted());
        }
    }

    public function test_requested_housing_requires_a_successful_review(): void
    {
        foreach (['NOT_STARTED', 'NOT_REQUIRED', 'UNDER_REVIEW', 'REJECTED'] as $status) {
            $this->assertFalse((new HousingRequest(['requested' => true, 'status' => $status]))->isCompleted());
        }
        foreach (['APPROVED', 'COMPLETED'] as $status) {
            $this->assertTrue((new HousingRequest(['requested' => true, 'status' => $status]))->isCompleted());
        }
    }
}
