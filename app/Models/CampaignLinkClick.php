<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CampaignLinkClick extends Model
{
    use HasUuids;

    protected $fillable = [
        'campaign_id',
        'recipient_id',
        'original_url',
        'clicked_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function recipient()
    {
        return $this->belongsTo(CampaignRecipient::class, 'recipient_id');
    }
}
