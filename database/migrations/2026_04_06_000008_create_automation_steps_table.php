<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('automation_id')->constrained('marketing_automations')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->enum('action_type', ['send_email', 'wait', 'condition'])->default('send_email');
            $table->foreignUuid('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->unsignedInteger('wait_duration')->nullable()->comment('Wait time in hours');
            $table->json('condition_rules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_steps');
    }
};
