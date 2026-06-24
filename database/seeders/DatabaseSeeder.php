<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeFace;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $branchSerang = Branch::query()->create([
            'code' => 'RS-SRG',
            'name' => 'RS Cabang Serang',
            'address' => 'Jl. Raya Serang KM 3',
            'phone' => '0254-123456',
            'city' => 'Serang',
            'is_active' => true,
        ]);

        $branchTangerang = Branch::query()->create([
            'code' => 'RS-TNG',
            'name' => 'RS Cabang Tangerang',
            'address' => 'Jl. Sudirman No. 10',
            'phone' => '021-7654321',
            'city' => 'Tangerang',
            'is_active' => true,
        ]);

        $branchSerang->locations()->createMany([
            [
                'name' => 'Gerbang Utama Serang',
                'latitude' => -6.1188370,
                'longitude' => 106.1536790,
                'radius_meters' => 150,
                'is_active' => true,
            ],
            [
                'name' => 'Lobby IGD Serang',
                'latitude' => -6.1192000,
                'longitude' => 106.1541000,
                'radius_meters' => 100,
                'is_active' => true,
            ],
        ]);

        $branchTangerang->locations()->create([
            'name' => 'Pintu Masuk Tangerang',
            'latitude' => -6.1784840,
            'longitude' => 106.6317690,
            'radius_meters' => 120,
            'is_active' => true,
        ]);

        $deptSerang = Department::query()->create([
            'branch_id' => $branchSerang->id,
            'code' => 'IGD',
            'name' => 'Instalasi Gawat Darurat',
            'is_active' => true,
        ]);

        Department::query()->create([
            'branch_id' => $branchTangerang->id,
            'code' => 'FAR',
            'name' => 'Farmasi',
            'is_active' => true,
        ]);

        $positionDoctor = Position::query()->create([
            'code' => 'DR_UMUM',
            'name' => 'Dokter Umum',
            'description' => 'Dokter umum / dokter jaga',
            'is_active' => true,
        ]);

        Position::query()->create([
            'code' => 'PERAWAT',
            'name' => 'Perawat',
            'description' => 'Perawat pelaksana',
            'is_active' => true,
        ]);

        Position::query()->create([
            'code' => 'ADM',
            'name' => 'Staf Administrasi',
            'description' => 'Administrasi umum rumah sakit',
            'is_active' => true,
        ]);

        User::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin@rs.local',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        User::query()->create([
            'name' => 'HRD Pusat',
            'email' => 'hrd@rs.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Hr,
            'is_active' => true,
        ]);

        $branchAdmin = User::query()->create([
            'name' => 'Admin Serang',
            'email' => 'admin.serang@rs.local',
            'password' => Hash::make('password'),
            'role' => UserRole::BranchAdmin,
            'branch_id' => $branchSerang->id,
            'is_active' => true,
        ]);

        $employeeUser = User::query()->create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi@rs.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'branch_id' => $branchSerang->id,
            'is_active' => true,
        ]);

        $employee = Employee::query()->create([
            'user_id' => $employeeUser->id,
            'branch_id' => $branchSerang->id,
            'department_id' => $deptSerang->id,
            'employee_number' => 'EMP-001',
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi@rs.local',
            'phone' => '081234567890',
            'position_id' => $positionDoctor->id,
            'employment_status' => 'permanent',
            'base_salary' => 15000000,
            'join_date' => '2020-01-15',
            'is_active' => true,
        ]);

        EmployeeFace::query()->create([
            'employee_id' => $employee->id,
            'photo_path' => 'faces/demo/employee-001.jpg',
            'face_descriptor' => $this->demoDescriptor(seed: 1),
            'is_primary' => true,
            'enrolled_at' => now(),
        ]);

        $shiftPagi = \App\Models\Shift::query()->create([
            'branch_id' => $branchSerang->id,
            'code' => 'PAGI',
            'name' => 'Shift Pagi IGD',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'late_tolerance_minutes' => 15,
            'is_active' => true,
        ]);

        $employee->update(['shift_id' => $shiftPagi->id]);

        \App\Models\Holiday::query()->create([
            'branch_id' => null,
            'name' => 'Hari Kemerdekaan RI',
            'date' => '2026-08-17',
            'is_active' => true,
        ]);

        \App\Models\SystemSetting::setValue('face_match_threshold', '0.6', 'Ambang kecocokan wajah');
        \App\Models\SystemSetting::setValue('location_buffer_meters', '0', 'Buffer radius lokasi');
        \App\Models\SystemSetting::setValue('payroll_deduction_invalid', '50000', 'Potongan absensi terlambat/invalid');

        unset($branchAdmin);
    }

    private function demoDescriptor(int $seed): array
    {
        $descriptor = [];

        for ($i = 0; $i < 128; $i++) {
            $descriptor[] = sin(($seed * 100) + $i) * 0.5;
        }

        return $descriptor;
    }
}
