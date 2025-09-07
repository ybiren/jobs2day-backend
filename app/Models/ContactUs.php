<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $table = 'contact_us';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'file',
        'admin_review',
        'status',
        'notification'
    ];

    protected $casts = [
        'status' => 'boolean',
        'notification' => 'boolean',
    ];

    // Relationship to user (if needed)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Type labels (optional)
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            0 => 'General Inquiry',
            1 => 'Support Request',
            2 => 'Feedback',
            3 => 'Other',
            default => 'Unknown',
        };
    }
}
