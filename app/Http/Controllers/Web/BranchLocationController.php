<?php

namespace App\Http\Controllers\Web;

use App\Models\Branch;
use App\Models\BranchLocation;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchLocationController extends WebController
{
    public function create(Request $request, Branch $branch): \Illuminate\View\View
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $branch->load('locations');
        $locationBuffer = (int) SystemSetting::getValue(
            'location_buffer_meters',
            config('attendance.location_buffer_meters')
        );

        return view('branches.locations.create', compact('branch', 'locationBuffer'));
    }

    public function store(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $branch->locations()->create($data);

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', 'Lokasi absensi berhasil ditambahkan.');
    }

    public function update(Request $request, BranchLocation $branchLocation): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $branchLocation->branch_id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', false);

        $branchLocation->update($data);

        return back()->with('success', 'Lokasi absensi berhasil diperbarui.');
    }

    public function destroy(Request $request, BranchLocation $branchLocation): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $branchLocation->branch_id);

        $branch = $branchLocation->branch_id;
        $branchLocation->delete();

        return redirect()->route('branches.show', $branch)->with('success', 'Lokasi absensi berhasil dihapus.');
    }
}
