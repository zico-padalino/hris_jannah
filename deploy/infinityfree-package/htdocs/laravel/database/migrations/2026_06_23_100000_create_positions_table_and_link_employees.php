<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
        });

        if (Schema::hasColumn('employees', 'position')) {
            $names = DB::table('employees')
                ->whereNotNull('position')
                ->where('position', '!=', '')
                ->distinct()
                ->orderBy('position')
                ->pluck('position');

            $usedCodes = [];

            foreach ($names as $name) {
                $baseCode = strtoupper(Str::slug($name, '_'));
                $baseCode = $baseCode !== '' ? substr($baseCode, 0, 20) : 'JABATAN';
                $code = $baseCode;
                $suffix = 1;

                while (in_array($code, $usedCodes, true) || DB::table('positions')->where('code', $code)->exists()) {
                    $suffix++;
                    $code = substr($baseCode, 0, max(1, 20 - strlen((string) $suffix))) . $suffix;
                }

                $usedCodes[] = $code;

                $positionId = DB::table('positions')->insertGetId([
                    'code' => $code,
                    'name' => $name,
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('employees')
                    ->where('position', $name)
                    ->update(['position_id' => $positionId]);
            }

            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('position')->nullable()->after('phone');
        });

        $employees = DB::table('employees')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->select('employees.id', 'positions.name as position_name')
            ->get();

        foreach ($employees as $employee) {
            DB::table('employees')
                ->where('id', $employee->id)
                ->update(['position' => $employee->position_name]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
        });

        Schema::dropIfExists('positions');
    }
};
