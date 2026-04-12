<?php

namespace App\Mail;

use App\Models\MarketingCampaign;
use App\Models\CampaignRecipient;
use App\Models\MailingListSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MarketingCampaign $campaign,
        public CampaignRecipient $recipient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function build()
    {
        $html = $this->campaign->html_content ?? '';

        // Replace merge tags
        $html = str_replace('{{name}}', $this->recipient->name ?? 'there', $html);
        $html = str_replace('{{email}}', $this->recipient->email, $html);

        // Inject tracking pixel before </body>
        $trackingPixelUrl = route('marketing.track.open', $this->recipient->id);
        $trackingPixel = '<img src="' . $trackingPixelUrl . '" width="1" height="1" alt="" style="display:none;" />';

        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $trackingPixel . '</body>', $html);
        } else {
            $html .= $trackingPixel;
        }

        // Wrap links with tracking URLs
        $html = $this->wrapLinksWithTracking($html);

        // Add unsubscribe footer
        if ($this->recipient->subscriber_id) {
            $subscriber = MailingListSubscriber::find($this->recipient->subscriber_id);
            if ($subscriber) {
                $token = hash('sha256', $subscriber->email . config('app.key'));
                $unsubscribeUrl = route('marketing.unsubscribe', [$subscriber->id, $token]);
                $unsubscribeFooter = '<div style="text-align:center;padding:20px;font-size:12px;color:#999;"><a href="' . $unsubscribeUrl . '" style="color:#999;">Unsubscribe</a></div>';

                if (stripos($html, '</body>') !== false) {
                    $html = str_ireplace('</body>', $unsubscribeFooter . '</body>', $html);
                } else {
                    $html .= $unsubscribeFooter;
                }
            }
        }

        return $this->html($html);
    }

    /**
     * Replace all <a href="..."> links with tracked redirect links.
     */
    private function wrapLinksWithTracking(string $html): string
    {
        return preg_replace_callback(
            '/<a\s+([^>]*?)href=["\']([^"\']+)["\']/i',
            function ($matches) {
                $originalUrl = $matches[2];

                // Don't wrap certain links
                if (str_contains($originalUrl, 'unsubscribe') ||
                    str_contains($originalUrl, 'track') ||
                    str_starts_with($originalUrl, 'mailto:') ||
                    str_starts_with($originalUrl, 'tel:') ||
                    str_starts_with($originalUrl, '#')) {
                    return $matches[0];
                }

                $linkHash = base64_encode($originalUrl);
                $trackingUrl = route('marketing.track.click', [$this->recipient->id, $linkHash]);

                return '<a ' . $matches[1] . 'href="' . $trackingUrl . '"';
            },
            $html
        );
    }
}
