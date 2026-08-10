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
        Schema::table('lead_getter_tasks', function (Blueprint $table) {
            $table->json('filters')->nullable()->after('api_provider');
        });

        Schema::table('lead_getter_results', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('phone');
            $table->string('whatsapp_url')->nullable()->after('whatsapp_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_getter_tasks', function (Blueprint $table) {
            $table->dropColumn('filters');
        });

        Schema::table('lead_getter_results', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'whatsapp_url']);
        });
    }
};
