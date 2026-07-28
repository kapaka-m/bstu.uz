<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthRouteTest extends TestCase
{
    public function test_root_health_route_returns_successful_response(): void
    {
        $this->get('/')->assertStatus(200);
    }
}
