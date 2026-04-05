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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreignUuid('project_id')->nullable()->references('id')->on('projects')->onDelete('set null');
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignUuid('assigned_to')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->string('subject');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in-progress', 'closed', 'pending'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
