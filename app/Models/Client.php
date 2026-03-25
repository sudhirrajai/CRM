<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'address',
        'currency_id',
        'status',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function hostings()
    {
        return $this->hasMany(ClientHosting::class);
    }
}
