<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccupationField extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'field_name'];

    // Define the inverse relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
