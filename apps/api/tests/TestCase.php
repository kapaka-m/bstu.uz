<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $isEphemeralSqlite = $connection === 'sqlite' && $database === ':memory:';

        if (! $isEphemeralSqlite) {
            $this->fail('Automated tests must use the configured sqlite :memory: database, not application data.');
        }

        if (! Schema::hasTable('locales')) {
            Schema::create('locales', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });

            DB::table('locales')->insert([
                'code' => 'en',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
