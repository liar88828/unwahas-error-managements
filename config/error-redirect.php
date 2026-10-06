<?php

return [
    'table' => env('ERROR_REDIRECT_TABLE', 'error_logs'),
    'deduplicate' => env('ERROR_REDIRECT_DEDUPLICATE', true),
    'report_exceptions' => env('ERROR_REDIRECT_REPORT_EXCEPTIONS', false),

    'api' => [
        'enabled' => env('ERROR_REDIRECT_API_ENABLED', true),
        'prefix' => env('ERROR_REDIRECT_API_PREFIX', 'api/error-logs'),
        'middleware' => ['api', 'throttle:60,1', Unwahas\ErrorRedirect\Http\Middleware\VerifyApiKey::class],
        'key' => env('ERROR_REDIRECT_API_KEY'),
        'key_header' => env('ERROR_REDIRECT_API_KEY_HEADER', 'X-Error-Log-Key'),
        'per_page' => 5,
        'max_per_page' => 100,
    ],
];
