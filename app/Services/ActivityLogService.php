<?php

namespace App\Services;

use App\Enums\ActivityLogAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityLogService
{
    /** @var list<string> */
    private const SKIP_ROUTE_PREFIXES = [
        'activity-logs.',
        'locale.',
        'branding.',
    ];

    /** @var list<string> */
    private const SKIP_ROUTE_NAMES = [
        'logout',
        'login',
    ];

    public function record(
        ?User $user,
        ActivityLogAction $action,
        ?string $module = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $subjectLabel = null,
        ?string $description = null,
        ?array $properties = null,
        ?Request $request = null,
    ): ActivityLog {
        $request ??= request();

        return ActivityLog::query()->create([
            'user_id' => $user?->id,
            'branch_id' => $user?->branch_id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->role?->value,
            'action' => $action,
            'module' => $module,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
            'description' => $description,
            'properties' => $this->sanitizeProperties($properties),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? Str::limit($request->userAgent(), 512, '') : null,
        ]);
    }

    public function recordLogin(User $user, Request $request): ActivityLog
    {
        return $this->record(
            user: $user,
            action: ActivityLogAction::Login,
            module: __('pages.activity_logs.module_auth'),
            description: __('pages.activity_logs.desc_login', ['email' => $user->email]),
            properties: ['route' => 'login'],
            request: $request,
        );
    }

    public function recordLogout(User $user, Request $request): ActivityLog
    {
        return $this->record(
            user: $user,
            action: ActivityLogAction::Logout,
            module: __('pages.activity_logs.module_auth'),
            description: __('pages.activity_logs.desc_logout', ['email' => $user->email]),
            properties: ['route' => 'logout'],
            request: $request,
        );
    }

    public function recordLoginFailed(string $email, Request $request): ActivityLog
    {
        return $this->record(
            user: null,
            action: ActivityLogAction::LoginFailed,
            module: __('pages.activity_logs.module_auth'),
            subjectLabel: $email,
            description: __('pages.activity_logs.desc_login_failed', ['email' => $email]),
            properties: ['email' => $email, 'route' => 'login'],
            request: $request,
        );
    }

    public function recordFromRequest(Request $request): ?ActivityLog
    {
        $user = $request->user();

        if ($user === null || $this->shouldSkipRequest($request)) {
            return null;
        }

        $route = $request->route();
        $routeName = $route?->getName();
        $action = $this->resolveAction($request, $routeName);
        $module = $this->resolveModule($routeName);
        [$subjectType, $subjectId, $subjectLabel] = $this->resolveSubject($route);
        $description = $this->resolveDescription($action, $module, $subjectLabel, $routeName);

        return $this->record(
            user: $user,
            action: $action,
            module: $module,
            subjectType: $subjectType,
            subjectId: $subjectId,
            subjectLabel: $subjectLabel,
            description: $description,
            properties: [
                'route' => $routeName,
                'method' => $request->method(),
                'path' => '/'.ltrim($request->path(), '/'),
            ],
            request: $request,
        );
    }

    private function shouldSkipRequest(Request $request): bool
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        $routeName = $request->route()?->getName();

        if ($routeName === null) {
            return false;
        }

        if (in_array($routeName, self::SKIP_ROUTE_NAMES, true)) {
            return true;
        }

        foreach (self::SKIP_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function resolveAction(Request $request, ?string $routeName): ActivityLogAction
    {
        if ($routeName !== null) {
            if (str_contains($routeName, '.destroy') || str_ends_with($routeName, 'destroy')) {
                return ActivityLogAction::Delete;
            }

            if (
                str_contains($routeName, '.update')
                || str_contains($routeName, '.approve')
                || str_contains($routeName, '.reject')
                || str_contains($routeName, '.finalize')
                || str_contains($routeName, '.regenerate')
                || str_contains($routeName, '.sync-')
                || str_contains($routeName, '.pull-logs')
                || str_contains($routeName, '.bulk')
                || str_contains($routeName, '.status.')
            ) {
                return ActivityLogAction::Update;
            }
        }

        return match ($request->method()) {
            'DELETE' => ActivityLogAction::Delete,
            'PUT', 'PATCH' => ActivityLogAction::Update,
            default => ActivityLogAction::Create,
        };
    }

    private function resolveModule(?string $routeName): ?string
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        $segment = explode('.', $routeName)[0] ?? null;

        if ($segment === null || $segment === '') {
            return null;
        }

        return match ($segment) {
            'attendance' => __('pages.activity_logs.module_attendance'),
            'attendances' => __('pages.activity_logs.module_attendance'),
            'users' => __('pages.activity_logs.module_users'),
            'roles' => __('pages.activity_logs.module_roles'),
            'settings' => __('pages.activity_logs.module_settings'),
            'branches', 'branch-locations' => __('pages.activity_logs.module_branches'),
            'departments' => __('pages.activity_logs.module_departments'),
            'positions' => __('pages.activity_logs.module_positions'),
            'employees' => __('pages.activity_logs.module_employees'),
            'faces' => __('pages.activity_logs.module_faces'),
            'shifts', 'employee-shifts' => __('pages.activity_logs.module_shifts'),
            'holidays' => __('pages.activity_logs.module_holidays'),
            'leaves' => __('pages.activity_logs.module_leaves'),
            'leave-approvals' => __('pages.activity_logs.module_leave_approvals'),
            'payrolls', 'potongan' => __('pages.activity_logs.module_payroll'),
            'announcements', 'pengumuman' => __('pages.activity_logs.module_announcements'),
            'fingerprint-devices' => __('pages.activity_logs.module_fingerprint'),
            'profile' => __('pages.activity_logs.module_profile'),
            default => Str::headline(str_replace('-', ' ', $segment)),
        };
    }

    /** @return array{0: ?string, 1: ?int, 2: ?string} */
    private function resolveSubject($route): array
    {
        if ($route === null) {
            return [null, null, null];
        }

        foreach ($route->parameters() as $parameter) {
            if (! $parameter instanceof Model) {
                continue;
            }

            return [
                class_basename($parameter),
                (int) $parameter->getKey(),
                $this->resolveModelLabel($parameter),
            ];
        }

        return [null, null, null];
    }

    private function resolveModelLabel(Model $model): string
    {
        foreach (['name', 'title', 'label', 'email', 'serial_number', 'code'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }

    private function resolveDescription(
        ActivityLogAction $action,
        ?string $module,
        ?string $subjectLabel,
        ?string $routeName,
    ): string {
        $target = $subjectLabel ?: ($module ?: __('pages.activity_logs.module_system'));

        return match ($action) {
            ActivityLogAction::Create => __('pages.activity_logs.desc_create', ['target' => $target]),
            ActivityLogAction::Update => __('pages.activity_logs.desc_update', ['target' => $target]),
            ActivityLogAction::Delete => __('pages.activity_logs.desc_delete', ['target' => $target]),
            default => $routeName ?: $target,
        };
    }

    /** @param array<string, mixed>|null $properties */
    private function sanitizeProperties(?array $properties): ?array
    {
        if ($properties === null) {
            return null;
        }

        $blocked = ['password', 'password_confirmation', 'token', '_token', 'face_descriptor', 'photo'];

        foreach ($blocked as $key) {
            unset($properties[$key]);
        }

        return $properties === [] ? null : $properties;
    }
}
