<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeFace;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;

class FaceRecognitionService
{
    public function matchEmployee(Collection $faces, array $descriptor): array
    {
        $threshold = (float) (SystemSetting::getValue('face_match_threshold', config('attendance.face_match_threshold', 0.6)));
        $bestScore = 0.0;
        $bestFace = null;

        foreach ($faces as $face) {
            $score = $this->compareDescriptors($face->face_descriptor, $descriptor);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestFace = $face;
            }
        }

        return [
            'matched' => $bestScore >= $threshold,
            'score' => round($bestScore, 4),
            'face' => $bestFace,
            'threshold' => $threshold,
        ];
    }

    public function findEmployeeByFace(array $descriptor, ?int $branchId = null): ?array
    {
        $query = EmployeeFace::query()
            ->with('employee')
            ->whereHas('employee', function ($query) use ($branchId) {
                $query->where('is_active', true);

                if ($branchId !== null) {
                    $query->where('branch_id', $branchId);
                }
            });

        $faces = $query->get();

        if ($faces->isEmpty()) {
            return null;
        }

        $result = $this->matchEmployee($faces, $descriptor);

        if (! $result['matched'] || ! $result['face'] instanceof EmployeeFace) {
            return null;
        }

        /** @var Employee $employee */
        $employee = $result['face']->employee;

        return [
            'employee' => $employee,
            'score' => $result['score'],
            'face' => $result['face'],
        ];
    }

    public function compareDescriptors(array $stored, array $incoming): float
    {
        if (count($stored) !== count($incoming) || count($stored) === 0) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($stored); $i++) {
            $a = (float) $stored[$i];
            $b = (float) $incoming[$i];
            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        $similarity = $dotProduct / (sqrt($normA) * sqrt($normB));

        return max(0.0, min(1.0, ($similarity + 1) / 2));
    }
}
