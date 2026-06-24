<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveProofController extends WebController
{
    public function show(Request $request, LeaveRequest $leave): StreamedResponse
    {
        $user = $request->user();
        $isOwner = $user->employee !== null && $leave->employee_id === $user->employee->id;

        if ($isOwner) {
            if (! $user->hasPermission(Permission::LeaveRequest)
                && ! $user->hasPermission(Permission::LeaveViewOwn)) {
                abort(403);
            }
        } elseif ($user->hasPermission(Permission::LeaveApprove)) {
            $this->authorizeBranchAccess($request, $leave->branch_id);
        } else {
            abort(403);
        }

        if (! $leave->hasProof()) {
            abort(404, 'Bukti tidak ditemukan.');
        }

        $disk = Storage::disk('public');

        return $disk->response(
            $leave->proof_path,
            basename($leave->proof_path),
            ['Content-Disposition' => 'inline']
        );
    }
}
