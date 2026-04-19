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
        Schema::create('lead_getter_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_getter_task_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('company')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('reviews_count')->nullable();
            $table->string('category')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('status')->default('new'); // new, qualified, disqualified
            $table->timestamp('qualified_at')->nullable();
            $table->foreignUuid('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_getter_results');
    }
};
