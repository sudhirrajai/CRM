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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
             $table->string('name');
            $table->string('provider');
            $table->string('ip_address')->nullable();
            $table->text('credentials')->nullable();
            $table->decimal('monthly_cost', 10, 2)->default(0);
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->date('renewal_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
