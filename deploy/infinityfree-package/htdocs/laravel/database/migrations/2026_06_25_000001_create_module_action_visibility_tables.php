<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 64)->unique();
            $table->string('label', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('module_actions', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 64);
            $table->string('action_key', 64);
            $table->string('label', 120);
            $table->string('icon_type', 32)->default('extra');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['module_key', 'action_key']);
            $table->foreign('module_key')
                ->references('module_key')
                ->on('action_modules')
                ->cascadeOnDelete();
        });

        Schema::create('role_module_action_visibility', function (Blueprint $table) {
            $table->string('role', 32);
            $table->foreignId('module_action_id')->constrained('module_actions')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['role', 'module_action_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_module_action_visibility');
        Schema::dropIfExists('module_actions');
        Schema::dropIfExists('action_modules');
    }
};
