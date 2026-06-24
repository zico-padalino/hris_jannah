<?php

namespace App\Http\Middleware;

use App\Enums\SidebarNavItem;
use App\Services\ModuleMaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleMaintenance
{
    /** @var list<string> */
    private const EXCLUDED_ROUTES = [
        'login',
        'logout',
        'locale.update',
    ];

    public function __construct(private ModuleMaintenanceService $maintenance) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName === null || $this->isExcludedRoute($routeName)) {
            return $next($request);
        }

        if (str_starts_with($routeName, 'settings.')) {
            return $next($request);
        }

        if (! $this->maintenance->isRouteInMaintenance($routeName)) {
            return $next($request);
        }

        $module = SidebarNavItem::fromRouteName($routeName);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->maintenance->message()
                    ?? __('pages.maintenance.default_message', [
                        'module' => $module !== null ? __($module->navLabelKey()) : __('pages.maintenance.unknown_module'),
                    ]),
            ], 503);
        }

        return response()->view('maintenance.module', [
            'module' => $module,
            'message' => $this->maintenance->message(),
        ], 503);
    }

    private function isExcludedRoute(string $routeName): bool
    {
        return in_array($routeName, self::EXCLUDED_ROUTES, true);
    }
}
