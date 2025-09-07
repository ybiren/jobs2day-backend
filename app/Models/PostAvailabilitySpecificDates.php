<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostAvailabilitySpecificDates extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'availability_date',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
