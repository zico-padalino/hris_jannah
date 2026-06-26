<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Services\AppBrandingService;
use App\Services\LeaveBadgeService;
use App\Services\PayrollSlipBadgeService;
use App\Services\SidebarService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            $request = request();
            $host = $request->header('X-Forwarded-Host') ?: $request->getHost();

            if ($request->header('X-Forwarded-Proto') === 'https' || str_ends_with($host, '.trycloudflare.com')) {
                URL::forceScheme('https');
            }

            if (str_ends_with($host, '.trycloudflare.com')) {
                URL::forceRootUrl('https://'.$host);
            }
        }

        Blade::if('perm', function (string $permission) {
            $user = auth()->user();

            if ($user === null) {
                return false;
            }

            foreach (explode('|', $permission) as $item) {
                if ($user->hasPermission(Permission::from(trim($item)))) {
                    return true;
                }
            }

            return false;
        });

        Blade::if('moduleAction', function (string $moduleKey, string $actionKey) {
            $user = auth()->user();

            return $user !== null && $user->canSeeModuleAction($moduleKey, $actionKey);
        });

        View::composer('*', function ($view) {
            $view->with('appBranding', app(AppBrandingService::class));
        });

        View::composer(['layouts.app', 'partials.sidebar-nav', 'partials.mobile-nav', 'partials.header-notifications'], function ($view) {
            $user = auth()->user();
            $sidebarService = app(SidebarService::class);

            $view->with('sidebar', $sidebarService);

            if ($user === null) {
                return;
            }

            $badgeService = app(LeaveBadgeService::class);
            $payrollBadgeService = app(PayrollSlipBadgeService::class);

            $view->with([
                'pendingLeaveApprovalCount' => $badgeService->pendingApprovalCount($user),
                'pendingLeaveApprovalBreakdown' => $badgeService->pendingApprovalBreakdown($user),
                'pendingOwnLeaveCount' => $badgeService->pendingOwnCount($user),
                'pendingPayrollSignatureCount' => $payrollBadgeService->pendingApprovalCount($user),
            ]);
        });
    }
}
