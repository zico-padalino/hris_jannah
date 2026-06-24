<?php

namespace App\Services;

use App\Models\BranchLocation;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;

class GeofenceService
{
    public function findMatchingLocation(
        float $latitude,
        float $longitude,
        Collection $locations
    ): ?array {
        $buffer = (int) SystemSetting::getValue('location_buffer_meters', config('attendance.location_buffer_meters', 0));

        foreach ($locations as $location) {
            if (! $location->is_active) {
                continue;
            }

            $distance = $this->calculateDistanceMeters(
                $latitude,
                $longitude,
                (float) $location->latitude,
                (float) $location->longitude
            );

            if ($distance <= ($location->radius_meters + $buffer)) {
                return [
                    'location' => $location,
                    'distance_meters' => (int) round($distance),
                ];
            }
        }

        return null;
    }

    public function calculateDistanceMeters(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    public function getActiveLocationsForBranch(int $branchId): Collection
    {
        return BranchLocation::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();
    }
}
