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
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('invoice_file_path')->nullable()->after('amount');
            $table->string('recurring_frequency')->nullable()->after('is_recurring');
            $table->date('next_due_date')->nullable()->after('recurring_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['invoice_file_path', 'recurring_frequency', 'next_due_date']);
        });
    }
};
