<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('leave_request_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('leave_requests')
                ->nullOnDelete();

            $table->unique(['leave_request_id', 'attended_at'], 'attendances_leave_request_day_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_leave_request_day_unique');
            $table->dropConstrainedForeignId('leave_request_id');
        });
    }
};
