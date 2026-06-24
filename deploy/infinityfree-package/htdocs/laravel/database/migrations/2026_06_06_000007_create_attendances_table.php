<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->timestamp('attended_at');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('photo_path')->nullable();
            $table->decimal('face_match_score', 5, 4)->nullable();
            $table->boolean('face_verified')->default(false);
            $table->boolean('location_verified')->default(false);
            $table->unsignedInteger('distance_meters')->nullable();
            $table->string('status')->default('valid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'attended_at']);
            $table->index(['branch_id', 'attended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
