<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'tech_stack',
        'budget',
        'priority',
        'max_file_size',
        'vmcore_profit',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'vmcore_profit' => 'float',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function discussions()
    {
        return $this->hasMany(ProjectDiscussion::class)->with(['user', 'attachments', 'parent.user'])->oldest();
    }

    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('assigned_at');
    }
}
