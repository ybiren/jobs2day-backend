<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatChannelMessage extends Model
{
    use HasFactory;

    protected $fillable = ['chat_channel_id', 'sender_id', 'receiver_id', 'message', 'file', 'is_seen'];

    public function chatChannel()
    {
        return $this->belongsTo(ChatChannel::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
