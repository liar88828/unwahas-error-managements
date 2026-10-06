<?php

namespace Unwahas\ErrorRedirect\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

class ErrorLog
{
    /**
     * Record an exception. Logging errors are intentionally swallowed so this
     * recorder can safely run from the application's exception reporting path.
     *
     * @param array<string, mixed> $context
     */
    public static function record(Throwable $exception, array $context = [], ?bool $deduplicate = null): void
    {
        try {
            $deduplicate ??= (bool) config('error-redirect.deduplicate', true);
            $deduplicationKey = hash('sha256', implode("\0", [
                $exception::class,
                $exception->getFile(),
                (string) $exception->getLine(),
            ]));

            if ($deduplicate) {
                $existing = DB::table(config('error-redirect.table', 'error_logs'))
                    ->where('deduplication_key', $deduplicationKey)
                    ->latest('id')
                    ->first(['id']);

                if ($existing !== null) {
                    DB::table(config('error-redirect.table', 'error_logs'))
                        ->where('id', $existing->id)
                        ->increment('count', 1, ['updated_at' => now()]);

                    return;
                }
            }

            $request = app()->runningInConsole() ? null : request();
            $now = now();

            DB::table(config('error-redirect.table', 'error_logs'))->insert([
                'app' => config('app.name'),
                'class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'deduplication_key' => $deduplicationKey,
                'trace' => $exception->getTraceAsString(),
                'context' => $context === [] ? null : json_encode($context, JSON_INVALID_UTF8_SUBSTITUTE),
                'count' => 1,
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'user_id' => auth()->id(),
                'ip' => $request?->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (Throwable) {
            // Do not cause recursive exception-reporting failures.
        }
    }

    /**
     * Record a message as a RuntimeException.
     *
     * @param array<string, mixed> $context
     */
    public static function message(string $message, array $context = [], ?bool $deduplicate = null): void
    {
        self::record(new \RuntimeException($message), $context, $deduplicate);
    }
}
