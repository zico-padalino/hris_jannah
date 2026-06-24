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
        'profile_photo_path',
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

    public function hasProfilePhoto(): bool
    {
        $path = $this->profile_photo_path;

        return is_string($path) && $path !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->hasProfilePhoto()) {
            return null;
        }

        return route('profile.photo', [
            'v' => $this->updated_at?->getTimestamp() ?? 0,
        ], false);
    }

    public function profileInitials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        if ($parts === []) {
            return '?';
        }

        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
    }
}
