<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatChannel extends Model
{
    use HasFactory;

    protected $fillable = ['channel', 'is_blocked', 'blocked_by_id'];

    public function messages()
    {
        return $this->hasMany(ChatChannelMessage::class);
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by_id');
    }

    public static function getChannel($user1, $user2)
    {
        // Ensure lower user_id comes first to maintain consistency
        $sortedUsers = [$user1, $user2];
        sort($sortedUsers);
        return implode('-', $sortedUsers);
    }
}
