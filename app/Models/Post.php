<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'job_role',
        'place',
        'latitude',
        'longitude',
        'coordinates',
        'field',
        'subdomain',
        'fixed_salary',
        'availability',
        'min_offered_salary',
        'max_offered_salary',
        'transport',
        'job_description',
        'document',
        'status',

        'total_positions',
        'remaining_positions',
        'total_application_requests',

        'is_remote',
        'work_type',

        'start_time', //hours
        'end_time',
    ];
    protected $appends = ['is_applied', 'is_candidated_reviewed'];

    // Define the inverse relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function availabilityDays()
    {
        return $this->hasOne(PostAvailabilityDays::class, 'post_id');
    }

    public function availabilitySpecificDates()
    {
        return $this->hasMany(PostAvailabilitySpecificDates::class, 'post_id');
    }
    public function favoriteJobs()
    {
        return $this->hasMany(FavoriteJob::class);
    }

    public function getTotalAcceptedRequestsAttribute()
    {
        return $this->jobApplications()->where('status', '1')->count();
    }
    public function getTotalRequestsAttribute()
    {
        return $this->jobApplications()->count();
    }

    public function getTotalRemainingRequestsAttribute()
    {
        return $this->jobApplications()->where('status', '0')->count();
    }
    public function getTransactionDateAttribute()
    {
        return $this->jobApplications()->where('status', '1')
            ->orderBy('updated_at', 'desc')->value('updated_at');
    }

    public function getIsAppliedAttribute()
    {
        // Get the authenticated user
        $user = auth()->user();

        // If no authenticated user, return 0 (not applied)
        if (!$user) {
            return 0;
        }

        // Rest of your existing code...
        $jobApplication = JobApplication::where('user_id', $user->id)
            ->where('post_id', $this->id)
            ->first();

        if ($jobApplication) {
            $status = (int) $jobApplication->status;
            return match ($status) {
                0 => 1, // Pending
                1 => 2, // Approved
                2 => 3, // Rejected
                default => 0,
            };
        }

        return 0;
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'post_id');
    }
    // In Post.php (Model)
    public function getIsCandidatedReviewedAttribute()
    {
        // Get the authenticated user
        $user = auth()->user();

        // If no authenticated user, return 0
        if (!$user) {
            return 0;
        }

        $reviewExists = $this->reviews()
            ->where('user_id', $user->id)
            ->count();

        return $reviewExists > 0 ? 1 : 0;
    }
    public function getSafeAttributes()
    {
        return [
            'id' => $this->id,
            'job_role' => $this->job_role, // Changed from 'name' to match your field
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toIso8601String(),
            'user_name' => $this->user->name ?? null
        ];
    }

    public function toNotificationPayload()
    {
        $payload = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'job_role' => $this->job_role,
            'place' => $this->place,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'field' => $this->field,
            'subdomain' => $this->subdomain,
            'fixed_salary' => $this->fixed_salary,
            'min_offered_salary' => $this->min_offered_salary,
            'max_offered_salary' => $this->max_offered_salary,
            'transport' => $this->transport,
            'job_description' => $this->job_description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Add any other fields you need
        ];

        if ($this->user) {
            $payload['user'] = [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                // Add any other user fields you need
            ];
        }

        return $payload;
    }}
