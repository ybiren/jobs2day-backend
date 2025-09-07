<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostAvailabilityDays extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
