<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    protected $table = 'users_bank';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_branch',
        'account_holder_name',
        'account_no'
    ];
}
