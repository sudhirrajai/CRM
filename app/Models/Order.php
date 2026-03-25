<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id',
        'order_number',
        'total_amount',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
