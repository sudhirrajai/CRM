<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ClientHosting extends Model
{
    use HasUuids;

    protected $fillable = ['client_id', 'server_id', 'project_id', 'domain', 'plan_details', 'price', 'currency_id', 'billing_cycle', 'next_due_date', 'status'];
    
    protected function casts(): array
    {
        return ['next_due_date' => 'date'];
    }
    
    public function client() { return $this->belongsTo(Client::class); }
    public function server() { return $this->belongsTo(Server::class); }
    public function currency() { return $this->belongsTo(Currency::class); }
    public function project() { return $this->belongsTo(Project::class); }
}
