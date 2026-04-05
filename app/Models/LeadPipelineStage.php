<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeadPipelineStage extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'color',
        'position',
        'is_default',
        'is_won',
        'is_lost',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'lead_pipeline_stage_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
