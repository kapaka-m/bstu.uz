<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    public function test_student_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/student/profile')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_apanel_resource_requires_authentication(): void
    {
        $this->getJson('/api/v1/apanel/faculties')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_login_requires_valid_payload(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation error',
            ]);
    }
}
