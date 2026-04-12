<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MarketingCampaign extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'email_template_id',
        'subject',
        'html_content',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }

    public function linkClicks()
    {
        return $this->hasMany(CampaignLinkClick::class, 'campaign_id');
    }

    // Analytics helpers
    public function getSentCountAttribute()
    {
        return $this->recipients()->whereIn('status', ['sent', 'delivered', 'opened', 'clicked'])->count();
    }

    public function getOpenedCountAttribute()
    {
        return $this->recipients()->whereIn('status', ['opened', 'clicked'])->count();
    }

    public function getClickedCountAttribute()
    {
        return $this->recipients()->where('status', 'clicked')->count();
    }

    public function getBouncedCountAttribute()
    {
        return $this->recipients()->where('status', 'bounced')->count();
    }

    public function getOpenRateAttribute()
    {
        $sent = $this->sent_count;
        return $sent > 0 ? round(($this->opened_count / $sent) * 100, 1) : 0;
    }

    public function getClickRateAttribute()
    {
        $sent = $this->sent_count;
        return $sent > 0 ? round(($this->clicked_count / $sent) * 100, 1) : 0;
    }
}
