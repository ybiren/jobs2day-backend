<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatRoomUsers extends Pivot
{
    use HasFactory;

    protected $table = 'chat_room_users';

    protected $fillable = ['user_id', 'chat_room_id', 'is_blocked'];

    /**
     * Get the user associated with this chat room.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chat room associated with this user.
     */
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }
}
