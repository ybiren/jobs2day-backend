<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',  // Add new fields here
        'last_name',
        'auth_id',
        'auth_type',
        'country',
        'city',
        'gender',
        'dob',
        'profile_image',
        'otp',
        'description',
        'email_verified_at',
        'phone',
        'is_onboarding_person',
        'is_onboarding_business',
        'type',
        'switched_type',
        'avg_rating' => 0,
        'rating_count',

        'latitude',
        'longitude',
        'coordinates',

        'is_deleted',
        'is_notifiable',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_notifiable' => 'boolean',
        ];
    }

    public function userDevices()
    {
        return $this->hasMany(UserDevice::class);
    }
    public function occupationFields()
    {
        return $this->hasMany(OccupationField::class);
    }

    // Define the relationship with the UserAvailability model
    public function userAvailability()
    {
        return $this->hasOne(UserAvailability::class);
    }

    // Define the relationship with the CompanyDetail model
    public function companyDetails()
    {
        return $this->hasOne(CompanyDetail::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function favoriteJobs()
    {
        return $this->hasMany(FavoriteJob::class);
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }
    public function is_reviews_done()
    {
        // Check if the user has any reviews given or received
        return $this->reviewsReceived()->exists() || $this->reviewsReceived()->exists();
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function bankDetails()
    {
        return $this->hasOne(UserBank::class);
    }

}
