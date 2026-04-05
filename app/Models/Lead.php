<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'lead_pipeline_stage_id',
        'client_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'company',
        'value',
        'currency_id',
        'source',
        'priority',
        'assigned_to',
        'expected_close_date',
        'description',
        'position',
        'won_at',
        'lost_at',
        'lost_reason',
        'converted_client_id',
        'auto_convert',
    ];

    protected function casts(): array
    {
        return [
            'expected_close_date' => 'date:Y-m-d',
            'value' => 'decimal:2',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
            'auto_convert' => 'boolean',
        ];
    }

    public function stage()
    {
        return $this->belongsTo(LeadPipelineStage::class, 'lead_pipeline_stage_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }
}
