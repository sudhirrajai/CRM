<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InvoiceEmailRecipient extends Model
{
    use HasUuids;

    protected $fillable = [
        'invoice_id',
        'name',
        'email',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
