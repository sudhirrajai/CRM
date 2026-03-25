<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = ['name', 'provider', 'ip_address', 'credentials', 'monthly_cost', 'currency_id', 'renewal_date', 'status'];
    
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'renewal_date' => 'date',
        ];
    }
    
    public function currency() { return $this->belongsTo(Currency::class); }
}
