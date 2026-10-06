<?php

namespace Unwahas\ErrorRedirect\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Unwahas\ErrorRedirect\Models\ErrorLog;

class ErrorLogController
{
    public function index(Request $request): JsonResponse
    {
        $default = (int) config('error-redirect.api.per_page', 5);
        $maximum = max(1, (int) config('error-redirect.api.max_per_page', 100));
        $perPage = min(max(1, $request->integer('per_page', $default)), $maximum);

        return response()->json(
            ErrorLog::query()
                ->select(['id', 'app', 'class', 'message', 'file', 'line', 'url', 'method', 'count', 'resolved_at', 'created_at'])
                ->latest('id')
                ->paginate($perPage),
        );
    }

    public function newSince(Request $request): JsonResponse
    {
        return response()->json(
            ErrorLog::query()
                ->where('id', '>', $request->integer('after_id'))
                ->orderBy('id')
                ->limit(20)
                ->get(['id', 'app', 'class', 'message', 'count', 'resolved_at', 'created_at']),
        );
    }

    public function latestId(): JsonResponse
    {
        return response()->json(['id' => (int) ErrorLog::query()->max('id')]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(ErrorLog::query()->findOrFail($id));
    }

    public function resolve(int $id): JsonResponse
    {
        $errorLog = ErrorLog::query()->findOrFail($id);
        $errorLog->resolved_at = $errorLog->resolved_at === null ? now() : null;
        $errorLog->save();

        return response()->json($errorLog->refresh());
    }

    public function destroy(int $id): Response
    {
        ErrorLog::query()->findOrFail($id)->delete();

        return response()->noContent();
    }

    public function notificationCount(): JsonResponse
    {
        $unresolved = ErrorLog::query()->whereNull('resolved_at');
        $latest = (clone $unresolved)->latest('id')->first(['id', 'class', 'app', 'message']);

        return response()->json([
            'count' => (clone $unresolved)->count(),
            'latest' => $latest,
        ]);
    }
}
