<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;
use Unwahas\ErrorRedirect\Models\ErrorLog;

class ErrorLogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_paginated_error_logs_as_json_with_count(): void
    {
        ErrorLog::query()->create([
            'class' => RuntimeException::class,
            'message' => 'External service failed.',
            'file' => '/app/Service.php',
            'line' => 23,
            'trace' => 'trace',
            'deduplication_key' => hash('sha256', 'api-test'),
            'count' => 4,
        ]);

        $this->withHeader('X-Error-Log-Key', 'test-api-key')
            ->getJson(route('error-redirect.api.index'))
            ->assertOk()
            ->assertJsonPath('data.0.message', 'External service failed.')
            ->assertJsonPath('data.0.count', 4)
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
    }

    public function test_api_rejects_requests_without_a_valid_api_key(): void
    {
        $this->getJson(route('error-redirect.api.index'))
            ->assertUnauthorized();
    }

    public function test_latest_id_and_new_logs_are_available_as_json(): void
    {
        $cursor = ErrorLog::query()->create($this->attributes('Already seen.'));
        $newLog = ErrorLog::query()->create($this->attributes('New failure.'));

        $this->withHeader('X-Error-Log-Key', 'test-api-key')
            ->getJson(route('error-redirect.api.latest-id'))
            ->assertOk()
            ->assertExactJson(['id' => $newLog->id]);

        $this->withHeader('X-Error-Log-Key', 'test-api-key')
            ->getJson(route('error-redirect.api.new', ['after_id' => $cursor->id]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $newLog->id)
            ->assertJsonPath('0.message', 'New failure.');
    }

    public function test_api_can_show_resolve_and_delete_a_log(): void
    {
        $log = ErrorLog::query()->create($this->attributes('Failure details.'));
        $headers = ['X-Error-Log-Key' => 'test-api-key'];

        $this->withHeaders($headers)
            ->getJson(route('error-redirect.api.show', ['id' => $log->id]))
            ->assertOk()
            ->assertJsonPath('id', $log->id)
            ->assertJsonPath('message', 'Failure details.');

        $this->withHeaders($headers)
            ->patchJson(route('error-redirect.api.resolve', ['id' => $log->id]))
            ->assertOk()
            ->assertJsonPath('id', $log->id)
            ->assertJsonStructure(['id', 'resolved_at']);

        $this->assertNotNull($log->fresh()->resolved_at);

        $this->withHeaders($headers)
            ->deleteJson(route('error-redirect.api.destroy', ['id' => $log->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing('error_logs', ['id' => $log->id]);
    }

    public function test_api_returns_not_found_for_a_missing_log(): void
    {
        $this->withHeader('X-Error-Log-Key', 'test-api-key')
            ->getJson(route('error-redirect.api.show', ['id' => 999]))
            ->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function attributes(string $message): array
    {
        return [
            'class' => RuntimeException::class,
            'message' => $message,
            'file' => '/app/Service.php',
            'line' => 23,
            'trace' => 'trace',
            'deduplication_key' => hash('sha256', $message),
            'count' => 1,
        ];
    }
}
