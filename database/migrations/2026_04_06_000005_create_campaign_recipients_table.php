<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->foreignUuid('subscriber_id')->nullable()->constrained('mailing_list_subscribers')->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('status', ['queued', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'])->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->timestamp('clicked_at')->nullable();
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('bounced_at')->nullable();
            $table->string('failed_reason')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};
