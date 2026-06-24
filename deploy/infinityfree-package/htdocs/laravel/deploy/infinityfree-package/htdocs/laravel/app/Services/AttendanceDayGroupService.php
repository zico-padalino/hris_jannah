<?php

namespace App\Services;

use App\Data\AttendanceDayGroup;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\DB;

class AttendanceDayGroupService
{
    public function paginateForRequest(Request $request, ?array $branchIds, int $perPage = 20): LengthAwarePaginator
    {
        $user = $request->user();
        $page = Paginator::resolveCurrentPage();

        $groupQuery = $this->groupKeyQuery($request, $branchIds, $user);

        $total = DB::query()
            ->fromSub(
                (clone $groupQuery)
                    ->select('employee_id', DB::raw('DATE(attended_at) as attendance_date'))
                    ->groupBy('employee_id', DB::raw('DATE(attended_at)')),
                'attendance_day_groups',
            )
            ->count();

        $groupKeys = (clone $groupQuery)
            ->select([
                'employee_id',
                DB::raw('DATE(attended_at) as attendance_date'),
                DB::raw('MAX(attended_at) as latest_at'),
            ])
            ->groupBy('employee_id', DB::raw('DATE(attended_at)'))
            ->orderByDesc('latest_at')
            ->forPage($page, $perPage)
            ->get();

        if ($groupKeys->isEmpty()) {
            return new Paginator([], $total, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $records = Attendance::query()
            ->with(['employee', 'branch', 'branchLocation'])
            ->where(function (Builder $query) use ($groupKeys) {
                foreach ($groupKeys as $key) {
                    $query->orWhere(function (Builder $nested) use ($key) {
                        $nested->where('employee_id', $key->employee_id)
                            ->whereDate('attended_at', $key->attendance_date);
                    });
                }
            })
            ->orderBy('attended_at')
            ->get();

        $groups = $groupKeys->map(function ($key) use ($records) {
            $dayRecords = $records->filter(
                fn (Attendance $record) => $record->employee_id === (int) $key->employee_id
                    && $record->attended_at->format('Y-m-d') === $key->attendance_date,
            );

            return new AttendanceDayGroup(
                $dayRecords->first()->employee,
                Carbon::parse($key->attendance_date)->startOfDay(),
                $dayRecords,
            );
        });

        return new Paginator($groups->values(), $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function groupKeyQuery(Request $request, ?array $branchIds, User $user): Builder
    {
        return Attendance::query()
            ->when($branchIds !== null, fn (Builder $query) => $query->whereIn('branch_id', $branchIds))
            ->when($request->filled('branch_id'), fn (Builder $query) => $query->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('attended_at', $request->string('date')))
            ->when(
                $user->employee && $user->role->value === 'employee',
                fn (Builder $query) => $query->where('employee_id', $user->employee->id),
            );
    }
}
