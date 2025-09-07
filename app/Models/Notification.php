<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // The table associated with the model.
    protected $table = 'notifications';

    // The attributes that are mass assignable.
    protected $fillable = ['user_id', 'title', 'body', 'payload', 'status'];

    // The attributes that should be hidden for arrays.
    protected $hidden = [];

    // The attributes that should be cast.
    protected $casts = [
        'payload' => 'array', // Automatically cast 'payload' to an array when retrieved
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
