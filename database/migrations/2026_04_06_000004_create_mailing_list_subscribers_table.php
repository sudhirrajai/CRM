<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailing_list_subscribers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mailing_list_id')->constrained('mailing_lists')->cascadeOnDelete();
            $table->string('subscribable_type');
            $table->uuid('subscribable_id');
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('status', ['subscribed', 'unsubscribed', 'bounced'])->default('subscribed');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->index(['subscribable_type', 'subscribable_id']);
            $table->unique(['mailing_list_id', 'subscribable_type', 'subscribable_id'], 'mls_unique_subscriber');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailing_list_subscribers');
    }
};
