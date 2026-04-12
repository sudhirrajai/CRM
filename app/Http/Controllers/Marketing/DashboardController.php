<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\MarketingAutomation;
use App\Models\CampaignRecipient;
use App\Models\CampaignLinkClick;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Overall stats
        $totalCampaigns = MarketingCampaign::count();
        $sentCampaigns = MarketingCampaign::where('status', 'sent')->count();
        $totalRecipients = CampaignRecipient::count();
        $totalSent = CampaignRecipient::whereIn('status', ['sent', 'delivered', 'opened', 'clicked'])->count();
        $totalOpened = CampaignRecipient::whereIn('status', ['opened', 'clicked'])->count();
        $totalClicked = CampaignRecipient::where('status', 'clicked')->count();
        $totalBounced = CampaignRecipient::where('status', 'bounced')->count();

        $overallOpenRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $overallClickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;
        $overallBounceRate = $totalSent > 0 ? round(($totalBounced / $totalSent) * 100, 1) : 0;

        // Active automations
        $activeAutomations = MarketingAutomation::where('status', 'active')->count();
        $totalAutomations = MarketingAutomation::count();

        // Recent campaigns with stats
        $recentCampaigns = MarketingCampaign::with('creator')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($campaign) {
                $sent = $campaign->recipients()->whereIn('status', ['sent', 'delivered', 'opened', 'clicked'])->count();
                $opened = $campaign->recipients()->whereIn('status', ['opened', 'clicked'])->count();
                $clicked = $campaign->recipients()->where('status', 'clicked')->count();
                $campaign->sent_count = $sent;
                $campaign->opened_count = $opened;
                $campaign->clicked_count = $clicked;
                $campaign->open_rate = $sent > 0 ? round(($opened / $sent) * 100, 1) : 0;
                $campaign->click_rate = $sent > 0 ? round(($clicked / $sent) * 100, 1) : 0;
                return $campaign;
            });

        // Sends over time (last 30 days)
        $sendsOverTime = CampaignRecipient::where('created_at', '>=', now()->subDays(30))
            ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked'])
            ->selectRaw("DATE(sent_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $opensOverTime = CampaignRecipient::where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('opened_at')
            ->selectRaw("DATE(opened_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Marketing/Dashboard', [
            'stats' => [
                'total_campaigns' => $totalCampaigns,
                'sent_campaigns' => $sentCampaigns,
                'total_sent' => $totalSent,
                'total_opened' => $totalOpened,
                'total_clicked' => $totalClicked,
                'total_bounced' => $totalBounced,
                'open_rate' => $overallOpenRate,
                'click_rate' => $overallClickRate,
                'bounce_rate' => $overallBounceRate,
                'active_automations' => $activeAutomations,
                'total_automations' => $totalAutomations,
            ],
            'recentCampaigns' => $recentCampaigns,
            'sendsOverTime' => $sendsOverTime,
            'opensOverTime' => $opensOverTime,
        ]);
    }
}
