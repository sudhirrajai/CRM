<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeadGetterGroup extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function tasks()
    {
        return $this->hasMany(LeadGetterTask::class)->latest();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasManyThrough(LeadGetterResult::class, LeadGetterTask::class);
    }
}
