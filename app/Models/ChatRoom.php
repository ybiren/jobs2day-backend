<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = ['name','name_slug', 'user_id', 'receiver_id','created_by','disabled'];

    protected $appends = ['other_user'];

    /**
     * Get the owner of the chat room.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users in the chat room.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_room_users')
            ->withPivot('is_blocked')
            ->withTimestamps();
    }

    /**
     * Messages in the chat room.
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class,'chat_room_id')->with(['sender','receiver']);
    }

    public function unreadChatMessages()
    {
        return $this->hasMany(ChatMessage::class,'chat_room_id')
            ->where('is_read', 0)
            ->where('receiver_id', auth()->id());
    }
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class,'chat_room_id')->latest()->limit(1)
            ->with(['sender', 'receiver']);
    }
    public function chatListLastMessage()
    {
        return $this->hasOne(ChatMessage::class,'chat_room_id')->latest()->limit(1);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getOtherUserAttribute()
    {
        return $this->user_id == auth()->id() ? $this->receiver : $this->sender;
    }


}
