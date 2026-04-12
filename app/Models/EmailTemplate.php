<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmailTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'subject',
        'editor_type',
        'html_content',
        'design_json',
        'category',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaigns()
    {
        return $this->hasMany(MarketingCampaign::class, 'email_template_id');
    }

    public function automationSteps()
    {
        return $this->hasMany(AutomationStep::class, 'email_template_id');
    }
}
