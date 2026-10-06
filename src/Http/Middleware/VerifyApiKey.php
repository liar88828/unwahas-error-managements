<?php

namespace Unwahas\ErrorRedirect\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('error-redirect.api.key');
        $header = config('error-redirect.api.key_header', 'X-Error-Log-Key');
        $provided = $request->header($header);

        if (! is_string($expected) || $expected === '' || ! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Invalid or unconfigured API key.'], 401);
        }

        return $next($request);
    }
}
