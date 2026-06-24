<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403, 'Anda tidak memiliki akses ke fitur ini.');
        }

        $allowed = collect($permissions)
            ->flatMap(fn (string $group) => explode('|', $group))
            ->map(fn (string $permission) => Permission::from(trim($permission)));

        if ($allowed->contains(fn (Permission $permission) => $user->hasPermission($permission))) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke fitur ini.');
    }
}
