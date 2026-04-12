<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MailingListSubscriber extends Model
{
    use HasUuids;

    protected $fillable = [
        'mailing_list_id',
        'subscribable_type',
        'subscribable_id',
        'email',
        'name',
        'status',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function mailingList()
    {
        return $this->belongsTo(MailingList::class, 'mailing_list_id');
    }

    public function subscribable()
    {
        return $this->morphTo();
    }

    public function campaignRecipients()
    {
        return $this->hasMany(CampaignRecipient::class, 'subscriber_id');
    }
}
