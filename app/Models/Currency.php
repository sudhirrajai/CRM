<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Currency extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'code', 'symbol', 'symbol_position', 'decimal_places'];
}
