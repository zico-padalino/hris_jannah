<?php

namespace App\Services;

use App\Models\User;

class ProfileFaceService
{
    public function __construct(
        private readonly AttendanceMethodSettingsService $attendanceMethods,
    ) {}

    public function canEnrollFace(?User $user): bool
    {
        if ($user === null || $user->employee === null) {
            return false;
        }

        return $this->attendanceMethods->photoEnabled();
    }

    public function needsEnrollment(?User $user): bool
    {
        if (! $this->canEnrollFace($user)) {
            return false;
        }

        return $user->employee->faces()->count() === 0;
    }

    public function registeredFaceCount(?User $user): int
    {
        if ($user?->employee === null) {
            return 0;
        }

        return $user->employee->faces()->count();
    }
}
