<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAvailability extends Model
{
    use HasFactory;

    // Specify the table name to match the database table
    protected $table = 'user_availability'; // Correct table name

    protected $fillable = [
        'user_id', 'available_at', 'expected_min_salary',
        'expected_max_salary', 'are_you_mobile', 'salary_type'
    ];

    // Define the inverse relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
