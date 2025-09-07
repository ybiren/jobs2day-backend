<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'post_id', 'note', 'status', 'document_1', 'document_2'
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Relationship to Transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'type_id');
    }

    // Scopes for filtering applications
    public function scopeRejected($query)
    {
        return $query->where('status', '2');
    }

    public function scopePending($query)
    {
        return $query->where('status', '0');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', '1'); // Fix: Change from '0' to '1'
    }
}
