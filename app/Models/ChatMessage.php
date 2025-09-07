<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    const TEXT = 1;
    const ATTACHMENT = 2;
    const PICTURE = 3;
    const VIDEO = 4;
    const VOICE = 5;

    protected $fillable = ['chat_room_id', 'sender_id', 'receiver_id', 'message', 'is_read',
        'file_name', 'file_path', 'file_size', 'type'];

    /**
     * Get the chat room where the message was sent.
     */
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
