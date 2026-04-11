<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CampaignRecipient extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'email',
        'name',
        'status',
        'sent_at',
        'opened_at',
        'open_count',
        'clicked_at',
        'click_count',
        'bounced_at',
        'failed_reason',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'bounced_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function subscriber()
    {
        return $this->belongsTo(MailingListSubscriber::class, 'subscriber_id');
    }

    public function linkClicks()
    {
        return $this->hasMany(CampaignLinkClick::class, 'recipient_id');
    }
}
