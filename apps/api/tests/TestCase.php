<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @method \Illuminate\Testing\TestResponse get(string $uri, array $headers = [])
 * @method \Illuminate\Testing\TestResponse post(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse put(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse patch(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse delete(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse getJson(string $uri, array $headers = [], int $options = 0)
 * @method \Illuminate\Testing\TestResponse postJson(string $uri, array $data = [], array $headers = [], int $options = 0)
 * @method \Illuminate\Testing\TestResponse putJson(string $uri, array $data = [], array $headers = [], int $options = 0)
 * @method \Illuminate\Testing\TestResponse patchJson(string $uri, array $data = [], array $headers = [], int $options = 0)
 * @method \Illuminate\Testing\TestResponse deleteJson(string $uri, array $data = [], array $headers = [], int $options = 0)
 */
abstract class TestCase extends BaseTestCase
{
    //
}
