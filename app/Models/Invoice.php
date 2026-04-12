<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id',
        'project_id',
        'invoice_number',
        'currency_id',
        'sub_total',
        'tax',
        'total_amount',
        'status',
        'issue_date',
        'due_date',
        'notes',
        'last_reminder_type',
        'payment_mode',
        'payment_reference',
        'payment_note',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
