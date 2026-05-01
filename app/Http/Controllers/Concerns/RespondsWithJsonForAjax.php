<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithJsonForAjax
{
    protected function respondSaved(Request $request, string $message, string $routeName, array $routeParameters = []): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route($routeName, $routeParameters),
            ]);
        }

        return redirect()->route($routeName, $routeParameters)->with('success', $message);
    }

    protected function respondDeleted(Request $request, string $message, string $routeName, array $routeParameters = []): JsonResponse|RedirectResponse
    {
        return $this->respondSaved($request, $message, $routeName, $routeParameters);
    }
}
