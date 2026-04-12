<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MailingList extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'is_dynamic',
        'filter_rules',
    ];

    protected function casts(): array
    {
        return [
            'is_dynamic' => 'boolean',
            'filter_rules' => 'array',
        ];
    }

    public function subscribers()
    {
        return $this->hasMany(MailingListSubscriber::class, 'mailing_list_id');
    }

    public function activeSubscribers()
    {
        return $this->subscribers()->where('status', 'subscribed');
    }
}
