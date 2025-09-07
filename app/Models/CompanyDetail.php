<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDetail extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'company_name', 'registration_no', 'field', 'details', 'company_email'];

    // Define the inverse relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
