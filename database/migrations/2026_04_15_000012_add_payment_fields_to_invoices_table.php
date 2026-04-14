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
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'payment_mode')) {
                $table->string('payment_mode')->nullable()->after('last_reminder_type');
            }

            if (!Schema::hasColumn('invoices', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_mode');
            }

            if (!Schema::hasColumn('invoices', 'payment_note')) {
                $table->text('payment_note')->nullable()->after('payment_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('invoices', 'payment_note')) {
                $columnsToDrop[] = 'payment_note';
            }

            if (Schema::hasColumn('invoices', 'payment_reference')) {
                $columnsToDrop[] = 'payment_reference';
            }

            if (Schema::hasColumn('invoices', 'payment_mode')) {
                $columnsToDrop[] = 'payment_mode';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
