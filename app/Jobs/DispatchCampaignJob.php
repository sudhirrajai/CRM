<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Models\CampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public MarketingCampaign $campaign
    ) {}

    public function handle(): void
    {
        // Check if campaign is still in a sendable state
        $this->campaign->refresh();

        if (!in_array($this->campaign->status, ['sending', 'scheduled'])) {
            Log::info("Campaign {$this->campaign->id} is not in a sendable state. Skipping.");
            return;
        }

        $this->campaign->update(['status' => 'sending']);

        // Get all queued recipients and dispatch individual send jobs
        $recipients = CampaignRecipient::where('campaign_id', $this->campaign->id)
            ->where('status', 'queued')
            ->get();

        foreach ($recipients->chunk(50) as $chunk) {
            foreach ($chunk as $recipient) {
                SendCampaignEmailJob::dispatch($this->campaign, $recipient);
            }
        }

        Log::info("Dispatched {$recipients->count()} emails for campaign: {$this->campaign->name}");
    }
}
