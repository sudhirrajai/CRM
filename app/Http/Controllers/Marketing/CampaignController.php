<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\EmailTemplate;
use App\Models\MailingList;
use App\Models\MailingListSubscriber;
use App\Models\CampaignRecipient;
use App\Models\CampaignLinkClick;
use App\Jobs\DispatchCampaignJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketingCampaign::with('creator', 'template')
            ->withCount('recipients')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->get()->map(function ($campaign) {
            $campaign->sent_count = $campaign->recipients()->whereIn('status', ['sent', 'delivered', 'opened', 'clicked'])->count();
            $campaign->opened_count = $campaign->recipients()->whereIn('status', ['opened', 'clicked'])->count();
            $campaign->clicked_count = $campaign->recipients()->where('status', 'clicked')->count();
            $campaign->bounced_count = $campaign->recipients()->where('status', 'bounced')->count();
            return $campaign;
        });

        return Inertia::render('Marketing/Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Marketing/Campaigns/Create', [
            'templates' => EmailTemplate::select('id', 'name', 'subject', 'editor_type', 'category')->latest()->get(),
            'mailingLists' => MailingList::withCount(['subscribers as active_count' => fn($q) => $q->where('status', 'subscribed')])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'email_template_id' => 'nullable|exists:email_templates,id',
            'html_content' => 'nullable|string',
            'mailing_list_ids' => 'required|array|min:1',
            'mailing_list_ids.*' => 'exists:mailing_lists,id',
        ]);

        // If template is selected, use its content
        if (!empty($validated['email_template_id']) && empty($validated['html_content'])) {
            $template = EmailTemplate::find($validated['email_template_id']);
            $validated['html_content'] = $template->html_content;
        }

        $campaign = MarketingCampaign::create([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'email_template_id' => $validated['email_template_id'] ?? null,
            'html_content' => $validated['html_content'] ?? '',
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Populate recipients from selected mailing lists
        $recipientEmails = [];
        foreach ($validated['mailing_list_ids'] as $listId) {
            $subscribers = MailingListSubscriber::where('mailing_list_id', $listId)
                ->where('status', 'subscribed')
                ->get();

            foreach ($subscribers as $subscriber) {
                if (in_array($subscriber->email, $recipientEmails)) continue;
                $recipientEmails[] = $subscriber->email;

                CampaignRecipient::create([
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'name' => $subscriber->name,
                    'status' => 'queued',
                ]);
            }
        }

        $campaign->update(['total_recipients' => count($recipientEmails)]);

        return redirect()->route('marketing.campaigns.index')
            ->with('success', "Campaign created with " . count($recipientEmails) . " recipients.");
    }

    public function show($id)
    {
        return $this->analytics($id);
    }

    public function edit($id)
    {
        $campaign = MarketingCampaign::with('template')->findOrFail($id);

        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('marketing.campaigns.analytics', $id)
                ->with('error', 'Cannot edit a campaign that has already been sent.');
        }

        return Inertia::render('Marketing/Campaigns/Create', [
            'campaign' => $campaign,
            'templates' => EmailTemplate::select('id', 'name', 'subject', 'editor_type', 'category')->latest()->get(),
            'mailingLists' => MailingList::withCount(['subscribers as active_count' => fn($q) => $q->where('status', 'subscribed')])->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return redirect()->back()->with('error', 'Cannot edit a campaign that has already been sent.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'email_template_id' => 'nullable|exists:email_templates,id',
            'html_content' => 'nullable|string',
        ]);

        $campaign->update($validated);

        return redirect()->route('marketing.campaigns.index')
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);
        $campaign->delete();

        return redirect()->route('marketing.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    /**
     * Send campaign immediately.
     */
    public function sendNow($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return redirect()->back()->with('error', 'Campaign cannot be sent in its current state.');
        }

        $campaign->update(['status' => 'sending']);

        DispatchCampaignJob::dispatch($campaign);

        return redirect()->route('marketing.campaigns.analytics', $id)
            ->with('success', 'Campaign is now being sent!');
    }

    /**
     * Schedule campaign for later.
     */
    public function schedule(Request $request, $id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $validated['scheduled_at'],
        ]);

        DispatchCampaignJob::dispatch($campaign)->delay($campaign->scheduled_at);

        return redirect()->route('marketing.campaigns.index')
            ->with('success', 'Campaign scheduled successfully.');
    }

    /**
     * Pause a sending campaign.
     */
    public function pause($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        if ($campaign->status !== 'sending') {
            return redirect()->back()->with('error', 'Can only pause campaigns that are currently sending.');
        }

        $campaign->update(['status' => 'paused']);

        return redirect()->back()->with('success', 'Campaign paused.');
    }

    /**
     * Campaign analytics page.
     */
    public function analytics($id)
    {
        $campaign = MarketingCampaign::with('creator', 'template')->findOrFail($id);

        $recipients = CampaignRecipient::where('campaign_id', $id)
            ->latest('updated_at')
            ->get();

        $stats = [
            'total' => $campaign->total_recipients,
            'sent' => $recipients->whereIn('status', ['sent', 'delivered', 'opened', 'clicked'])->count(),
            'delivered' => $recipients->whereIn('status', ['delivered', 'opened', 'clicked'])->count(),
            'opened' => $recipients->whereIn('status', ['opened', 'clicked'])->count(),
            'clicked' => $recipients->where('status', 'clicked')->count(),
            'bounced' => $recipients->where('status', 'bounced')->count(),
            'failed' => $recipients->where('status', 'failed')->count(),
        ];

        $stats['open_rate'] = $stats['sent'] > 0 ? round(($stats['opened'] / $stats['sent']) * 100, 1) : 0;
        $stats['click_rate'] = $stats['sent'] > 0 ? round(($stats['clicked'] / $stats['sent']) * 100, 1) : 0;
        $stats['bounce_rate'] = $stats['sent'] > 0 ? round(($stats['bounced'] / $stats['sent']) * 100, 1) : 0;

        // Top clicked links
        $topLinks = CampaignLinkClick::where('campaign_id', $id)
            ->selectRaw('original_url, COUNT(*) as click_count')
            ->groupBy('original_url')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();

        // Opens over time (hourly buckets for last 7 days)
        $opensOverTime = CampaignRecipient::where('campaign_id', $id)
            ->whereNotNull('opened_at')
            ->selectRaw("DATE(opened_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $clicksOverTime = CampaignLinkClick::where('campaign_id', $id)
            ->selectRaw("DATE(clicked_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Marketing/Campaigns/Analytics', [
            'campaign' => $campaign,
            'recipients' => $recipients,
            'stats' => $stats,
            'topLinks' => $topLinks,
            'opensOverTime' => $opensOverTime,
            'clicksOverTime' => $clicksOverTime,
        ]);
    }
}
