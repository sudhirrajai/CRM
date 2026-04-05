<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Expense extends Model
{
    use HasUuids;

    protected $fillable = [
        'category_id',
        'name',
        'amount',
        'payment_reference',
        'payment_mode',
        'payment_mode_details',
        'invoice_file_path',
        'date',
        'is_recurring',
        'recurring_frequency',
        'next_due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'next_due_date' => 'date',
            'amount' => 'decimal:2',
            'is_recurring' => 'boolean',
            'payment_mode_details' => 'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
