<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ChangeRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'amount',
        'status',
        'invoice_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
