<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function __construct(private readonly ActivityLogService $activityLogService) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($response->getStatusCode() >= 400) {
            return;
        }

        try {
            $this->activityLogService->recordFromRequest($request);
        } catch (\Throwable) {
            // Jangan ganggu alur utama jika logging gagal.
        }
    }
}
