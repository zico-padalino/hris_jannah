<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fingerprint_devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->string('name')->nullable();
            $table->string('model')->default('X100-C');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fingerprint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_device_id')->constrained()->cascadeOnDelete();
            $table->string('device_pin');
            $table->timestamp('punched_at');
            $table->unsignedTinyInteger('punch_status')->default(0);
            $table->unsignedTinyInteger('verify_mode')->nullable();
            $table->string('raw_line');
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->string('process_status')->default('pending');
            $table->text('process_message')->nullable();
            $table->timestamps();

            $table->unique(
                ['fingerprint_device_id', 'device_pin', 'punched_at', 'punch_status'],
                'fingerprint_logs_dedup'
            );
        });

        Schema::create('fingerprint_device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fingerprint_device_id')->constrained()->cascadeOnDelete();
            $table->text('command');
            $table->string('status')->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('fingerprint_pin', 20)->nullable()->after('employee_number');
            $table->unique(['branch_id', 'fingerprint_pin'], 'employees_branch_fingerprint_pin_unique');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('source')->default('face')->after('type');
            $table->foreignId('fingerprint_device_id')->nullable()->after('branch_location_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fingerprint_device_id');
            $table->dropColumn('source');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_branch_fingerprint_pin_unique');
            $table->dropColumn('fingerprint_pin');
        });

        Schema::dropIfExists('fingerprint_device_commands');
        Schema::dropIfExists('fingerprint_logs');
        Schema::dropIfExists('fingerprint_devices');
    }
};
