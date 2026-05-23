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
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropMorphs('subscribable', 'push_subscriptions_subscribable_morph_idx');
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->uuidMorphs('subscribable', 'push_subscriptions_subscribable_morph_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropMorphs('subscribable', 'push_subscriptions_subscribable_morph_idx');
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->morphs('subscribable', 'push_subscriptions_subscribable_morph_idx');
        });
    }
};
