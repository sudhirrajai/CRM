<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Models\CampaignRecipient;
use App\Models\Setting;
use App\Mail\CampaignEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    public function __construct(
        public MarketingCampaign $campaign,
        public CampaignRecipient $recipient
    ) {}

    public function handle(): void
    {
        // Check campaign is still sending
        $this->campaign->refresh();
        if ($this->campaign->status === 'paused' || $this->campaign->status === 'cancelled') {
            return;
        }

        try {
            // Configure SMTP from admin settings
            $this->configureSmtp();

            // Send the email
            Mail::to($this->recipient->email)
                ->send(new CampaignEmail($this->campaign, $this->recipient));

            // Update recipient status
            $this->recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Check if all recipients are sent
            $this->checkCampaignCompletion();

        } catch (\Exception $e) {
            Log::error("Failed to send campaign email to {$this->recipient->email}: {$e->getMessage()}");

            $this->recipient->update([
                'status' => 'failed',
                'failed_reason' => substr($e->getMessage(), 0, 255),
            ]);

            // Don't retry if it's a permanent failure
            if ($this->attempts() >= $this->tries) {
                $this->checkCampaignCompletion();
            } else {
                throw $e; // Let the queue retry
            }
        }
    }

    private function configureSmtp(): void
    {
        $host = Setting::getValue('smtp_host');
        if ($host) {
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => Setting::getValue('smtp_port', 587),
                'mail.mailers.smtp.username' => Setting::getValue('smtp_username'),
                'mail.mailers.smtp.password' => Setting::getValue('smtp_password'),
                'mail.mailers.smtp.encryption' => Setting::getValue('smtp_encryption', 'tls'),
                'mail.from.address' => Setting::getValue('smtp_from_address'),
                'mail.from.name' => Setting::getValue('smtp_from_name'),
            ]);
        }
    }

    private function checkCampaignCompletion(): void
    {
        $remaining = CampaignRecipient::where('campaign_id', $this->campaign->id)
            ->where('status', 'queued')
            ->count();

        if ($remaining === 0) {
            $this->campaign->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }
    }
}
