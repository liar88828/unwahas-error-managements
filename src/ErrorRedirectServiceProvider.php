<?php

namespace Unwahas\ErrorRedirect;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Throwable;
use Unwahas\ErrorRedirect\Http\Middleware\VerifyApiKey;
use Unwahas\ErrorRedirect\Support\ErrorLog;

class ErrorRedirectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/error-redirect.php', 'error-redirect');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/error-redirect.php' => config_path('error-redirect.php'),
        ], 'error-redirect-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if (config('error-redirect.api.enabled', true)) {
            $this->loadRoutes();
        }

        if (config('error-redirect.report_exceptions', false)) {
            $handler = $this->app->make(ExceptionHandler::class);

            if (method_exists($handler, 'reportable')) {
                $handler->reportable(fn (Throwable $exception) => ErrorLog::record($exception));
            }
        }
    }

    private function loadRoutes(): void
    {
        Route::middleware(config('error-redirect.api.middleware', ['api', 'throttle:60,1', VerifyApiKey::class]))
            ->prefix(config('error-redirect.api.prefix', 'api/error-logs'))
            ->as('error-redirect.api.')
            ->group(__DIR__.'/../routes/api.php');
    }
}
