# Laravel Error Redirect

A Laravel package for recording application exceptions and custom error messages, deduplicating repeat exceptions, and exposing a protected JSON API to browse, resolve, and delete logs.

## Requirements

- PHP 8.1+
- Laravel 10, 11, 12, or 13

## Installation

Install the package with Composer (`composer require unwahas/error-redirect`). Laravel package discovery registers `Unwahas\ErrorRedirect\ErrorRedirectServiceProvider` automatically. Publish its configuration if you want to customize it:

```sh
php artisan vendor:publish --tag=error-redirect-config
php artisan migrate
```

The package registers its migration automatically. Configure the API key in `.env` before making requests:

```env
ERROR_REDIRECT_API_KEY=replace-with-a-long-random-secret
ERROR_REDIRECT_REPORT_EXCEPTIONS=true
```

The routes are enabled by default at `/api/error-logs`. Every route requires the configured key in the `X-Error-Log-Key` header. Set `ERROR_REDIRECT_API_ENABLED=false` to disable the routes, or configure the route prefix, middleware, key header, and storage table in `config/error-redirect.php`.

## Recording errors

```php
use Unwahas\ErrorRedirect\Support\ErrorLog;

try {
    // Application operation...
} catch (Throwable $exception) {
    ErrorLog::record($exception, ['order_id' => $orderId]);
}

ErrorLog::message('Payment provider timed out', ['order_id' => $orderId]);
```

`record()` deduplicates by exception class, file, and line by default, incrementing the existing row's `count`. Override this for a single call with the third argument:

```php
ErrorLog::record($exception, ['source' => 'webhook'], deduplicate: false);
```

Set `ERROR_REDIRECT_REPORT_EXCEPTIONS=true` to automatically record exceptions passed through Laravel's exception reporter. This is disabled by default to avoid changing existing reporting behavior unexpectedly.

Generate a secure code for `ERROR_REDIRECT_API_KEY` with the package's Artisan command:

```sh
php artisan error-redirect:generate-code
```

The command prints a 64-character code by default. Set a custom length between 16 and 128 characters with `--length`, then copy the output into your `.env` file as `ERROR_REDIRECT_API_KEY`.

The Eloquent model is `Unwahas\ErrorRedirect\Models\ErrorLog`.

## API endpoints

All endpoints use the configured route prefix and require the API key header.

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/` | Paginated logs; optional `per_page` (capped by configuration) |
| GET | `/new?after_id={id}` | Up to 20 newer logs |
| GET | `/latest-id` | Latest log ID |
| GET | `/notifications/count` | Unresolved count and newest unresolved log |
| GET | `/{id}` | A log and its stored context/trace |
| PATCH | `/{id}/resolve` | Toggle the resolved state |
| DELETE | `/{id}` | Delete a log |

When using the default prefix `/api/error-logs`, for example, send `X-Error-Log-Key: <configured key>` with requests. API endpoints return JSON; deletion returns HTTP 204.

## Local development

This repository is itself a Composer package. Run `composer test` to execute the Testbench feature suite, or `composer validate` to validate its package metadata. To use it from a Laravel application during development, add it as a Composer path repository and require `unwahas/error-redirect`.
