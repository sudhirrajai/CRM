<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\CampaignRecipient;
use App\Models\CampaignLinkClick;
use App\Models\MailingListSubscriber;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Track email open via 1x1 transparent pixel.
     * Public route, no auth.
     */
    public function trackOpen($recipientId)
    {
        $recipient = CampaignRecipient::find($recipientId);

        if ($recipient) {
            $recipient->increment('open_count');

            if (!$recipient->opened_at) {
                $recipient->update([
                    'opened_at' => now(),
                    'status' => 'opened',
                ]);
            }
        }

        // Return 1x1 transparent GIF
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Content-Length' => strlen($pixel),
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Track link click and redirect to original URL.
     * Public route, no auth.
     */
    public function trackClick(Request $request, $recipientId, $linkHash)
    {
        $recipient = CampaignRecipient::find($recipientId);

        if ($recipient) {
            // Decode the original URL from the hash
            $originalUrl = base64_decode($linkHash);

            if ($originalUrl && filter_var($originalUrl, FILTER_VALIDATE_URL)) {
                // Log the click
                CampaignLinkClick::create([
                    'campaign_id' => $recipient->campaign_id,
                    'recipient_id' => $recipient->id,
                    'original_url' => $originalUrl,
                    'clicked_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Update recipient status
                $recipient->increment('click_count');
                if (!$recipient->clicked_at) {
                    $recipient->update([
                        'clicked_at' => now(),
                        'status' => 'clicked',
                    ]);
                }

                return redirect($originalUrl);
            }
        }

        // Fallback
        return redirect('/');
    }

    /**
     * Unsubscribe a subscriber.
     * Public route, no auth.
     */
    public function unsubscribe($subscriberId, $token)
    {
        $subscriber = MailingListSubscriber::find($subscriberId);

        if ($subscriber && hash_equals(hash('sha256', $subscriber->email . config('app.key')), $token)) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);

            return response()->view('marketing.unsubscribed', [
                'email' => $subscriber->email,
            ]);
        }

        return response('Invalid unsubscribe link.', 404);
    }
}
