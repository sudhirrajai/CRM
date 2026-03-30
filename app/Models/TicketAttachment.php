<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TicketAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'ticket_message_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    public function message()
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }
}
