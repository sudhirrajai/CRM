<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SecretCategory extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'icon', 'color', 'sort_order', 'created_by'];

    public function secrets()
    {
        return $this->hasMany(Secret::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
