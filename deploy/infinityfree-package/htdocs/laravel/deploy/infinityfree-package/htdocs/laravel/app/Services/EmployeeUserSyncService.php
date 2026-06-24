<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Str;

class EmployeeUserSyncService
{
    public function __construct(
        private readonly UserDefaultPasswordService $defaultPasswordService,
    ) {}

    public function syncFromUser(User $user): ?Employee
    {
        if ($user->role !== UserRole::Employee) {
            if ($user->employee) {
                $user->employee->update(['user_id' => null]);
            }

            return null;
        }

        if ($user->branch_id === null) {
            return null;
        }

        $attributes = [
            'branch_id' => $user->branch_id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
        ];

        $employee = $user->employee;

        if ($employee) {
            $employee->update($attributes);

            if ($employee->user_id !== $user->id) {
                $employee->update(['user_id' => $user->id]);
            }

            return $employee->fresh();
        }

        $employee = Employee::query()->create([
            ...$attributes,
            'user_id' => $user->id,
            'employee_number' => $this->generateEmployeeNumber(),
            'fingerprint_pin' => $this->generateFingerprintPin(),
            'employment_status' => 'permanent',
            'join_date' => now()->toDateString(),
        ]);

        return $employee;
    }

    public function syncFromEmployee(Employee $employee): ?User
    {
        $email = $this->resolveEmployeeEmail($employee);

        if ($employee->email !== $email) {
            $employee->update(['email' => $email]);
            $employee->refresh();
        }

        $attributes = [
            'name' => $employee->name,
            'email' => $email,
            'role' => UserRole::Employee,
            'branch_id' => $employee->branch_id,
            'is_active' => $employee->is_active,
        ];

        $user = $employee->user;

        if ($user) {
            $user->update($attributes);

            return $user->fresh();
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser) {
            $existingUser->update($attributes);
            $employee->update(['user_id' => $existingUser->id]);

            return $existingUser->fresh();
        }

        $user = User::query()->create([
            ...$attributes,
            'password' => bcrypt($this->defaultPasswordService->resolve($employee)),
        ]);

        $employee->update(['user_id' => $user->id]);

        return $user;
    }

    public function deleteLinkedUser(Employee $employee): void
    {
        $user = $employee->user;

        if ($user === null) {
            return;
        }

        $employee->update(['user_id' => null]);
        $user->delete();
    }

    public function deleteLinkedEmployee(User $user): void
    {
        $employee = $user->employee;

        if ($employee === null) {
            return;
        }

        $employee->delete();
    }

    private function resolveEmployeeEmail(Employee $employee): string
    {
        $email = trim((string) $employee->email);

        if ($email !== '') {
            return $email;
        }

        $base = Str::slug($employee->employee_number, '.');
        $base = $base !== '' ? $base : 'pegawai'.$employee->id;
        $domain = 'pegawai.local';
        $candidate = strtolower($base).'@'.$domain;
        $sequence = 1;

        while (
            User::query()->where('email', $candidate)
                ->where('id', '!=', $employee->user_id ?? 0)
                ->exists()
        ) {
            $sequence++;
            $candidate = strtolower($base).'.'.$sequence.'@'.$domain;
        }

        return $candidate;
    }

    public function applyDefaultPassword(User $user, ?string $manualPassword = null): void
    {
        $employee = $user->employee;

        if ($employee === null && $user->role === UserRole::Employee) {
            $employee = $this->syncFromUser($user->fresh());
        }

        $plain = $this->defaultPasswordService->resolve($employee, $manualPassword);

        $user->update([
            'password' => bcrypt($plain),
        ]);
    }

    private function generateFingerprintPin(): string
    {
        $sequence = (int) Employee::query()->max('id') + 1;

        do {
            $pin = (string) $sequence;
            $sequence++;
        } while (Employee::query()->where('fingerprint_pin', $pin)->exists());

        return $pin;
    }

    private function generateEmployeeNumber(): string
    {
        $sequence = 1;

        do {
            $number = 'EMP-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Employee::query()->where('employee_number', $number)->exists());

        return $number;
    }
}
