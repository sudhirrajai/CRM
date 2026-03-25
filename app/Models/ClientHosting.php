<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientHosting extends Model
{
    protected $fillable = ['client_id', 'server_id', 'domain', 'plan_details', 'price', 'currency_id', 'billing_cycle', 'next_due_date', 'status'];
    
    protected function casts(): array
    {
        return ['next_due_date' => 'date'];
    }
    
    public function client() { return $this->belongsTo(Client::class); }
    public function server() { return $this->belongsTo(Server::class); }
    public function currency() { return $this->belongsTo(Currency::class); }
}
