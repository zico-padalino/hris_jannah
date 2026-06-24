<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function myPayroll(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if ($employee === null) {
            return response()->json(['message' => 'Akun ini bukan pegawai.'], 403);
        }

        $items = $employee->payrollItems()
            ->with('payrollPeriod.branch')
            ->latest('id')
            ->paginate(15);

        return response()->json($items);
    }
}
