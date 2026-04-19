<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_getter_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_getter_group_id')->constrained()->cascadeOnDelete();
            $table->string('query');
            $table->string('location');
            $table->string('api_provider')->default('serpapi');
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->integer('total_results')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_getter_tasks');
    }
};
