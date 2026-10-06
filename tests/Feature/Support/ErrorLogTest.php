<?php

namespace Tests\Feature\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;
use Unwahas\ErrorRedirect\Models\ErrorLog as ErrorLogModel;
use Unwahas\ErrorRedirect\Support\ErrorLog;
use Illuminate\Support\Facades\Route;

class ErrorLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_exception_is_saved_with_context(): void
    {
        ErrorLog::record(new RuntimeException('Database unavailable'), [
            'request_id' => 'request-123',
        ]);

        $errorLog = ErrorLogModel::query()->firstOrFail();

        $this->assertModelExists($errorLog);
        $this->assertSame(RuntimeException::class, $errorLog->class);
        $this->assertSame('Database unavailable', $errorLog->message);
        $this->assertSame(1, $errorLog->count);
        $this->assertSame(['request_id' => 'request-123'], $errorLog->context);
    }

    public function test_repeated_exception_increments_count_on_the_existing_log(): void
    {
        $exception = new RuntimeException('Database unavailable');

        ErrorLog::record($exception);
        ErrorLog::record($exception);

        $this->assertDatabaseCount('error_logs', 1);
        $this->assertSame(2, ErrorLogModel::query()->firstOrFail()->count);
    }

    public function test_http_exception_is_logged_by_the_exception_handler(): void
    {
        Route::get('/boom', function (): never {
            throw new RuntimeException('test error');
        });

        $this->get('/boom')->assertInternalServerError();
        $this->get('/boom')->assertInternalServerError();

        $this->assertDatabaseHas('error_logs', [
            'class' => RuntimeException::class,
            'message' => 'test error',
            'count' => 2,
        ]);
    }
}
