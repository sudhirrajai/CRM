<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('automation_id')->constrained('marketing_automations')->cascadeOnDelete();
            $table->string('enrollable_type');
            $table->uuid('enrollable_id');
            $table->foreignUuid('current_step_id')->nullable()->constrained('automation_steps')->nullOnDelete();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['enrollable_type', 'enrollable_id']);
            $table->index(['automation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_enrollments');
    }
};
