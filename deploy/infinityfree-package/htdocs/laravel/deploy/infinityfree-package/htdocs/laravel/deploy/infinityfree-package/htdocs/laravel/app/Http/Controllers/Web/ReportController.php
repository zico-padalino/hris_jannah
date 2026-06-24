<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\Branch;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends WebController
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::ReportsView);

        $branchIds = $this->manageableBranchIds($request);
        $month = $request->string('month', now()->format('Y-m'));

        $summary = $this->reportService->attendanceSummary($branchIds, $month);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)->orderBy('name')->get();

        return view('reports.index', compact('summary', 'branches', 'month'));
    }
}
