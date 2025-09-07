<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_type',
        'type_id',
        'user_id',
        'amount',
        'status',
        'status',
        'response',
        'expdate',
        'cvv',
        'ccno',
        'cred_type',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class, 'type_id');
    }

}

