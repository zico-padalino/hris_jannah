<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isHr(): bool
    {
        return $this->role === UserRole::Hr;
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === UserRole::BranchAdmin;
    }

    public function canManageBranch(?int $branchId): bool
    {
        if ($this->isSuperAdmin() || $this->isHr()) {
            return true;
        }

        return $this->isBranchAdmin() && $this->branch_id === $branchId;
    }

    public function hasPermission(Permission $permission): bool
    {
        return app(\App\Services\PermissionService::class)->userHas($this, $permission);
    }

    public function canSeeModuleAction(string $moduleKey, string $actionKey): bool
    {
        return app(\App\Services\ModuleActionVisibilityService::class)
            ->userCanSee($this, $moduleKey, $actionKey);
    }
}
