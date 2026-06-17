<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactConversationMessage extends Model
{
    protected $fillable = [
        'contact_message_id',
        'sender_id',
        'sender_type',
        'message',
    ];

    public function thread()
    {
        return $this->belongsTo(ContactMessage::class, 'contact_message_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
