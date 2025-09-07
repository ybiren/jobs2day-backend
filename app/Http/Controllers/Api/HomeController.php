<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use App\Models\FavoriteJob;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class HomeController extends Controller
{
    public function homeJobs(Request $request)
    {
        $authUser = Auth::user();
        $loggedInUser = User::with(['userAvailability', 'occupationFields'])->find(auth()->id());

        $latitude = $loggedInUser->latitude;
        $longitude = $loggedInUser->longitude;

        $occupationFields = $loggedInUser->occupationFields->pluck('field_name')->toArray();
        $fieldOrder = implode(',', array_map(fn($field) => DB::getPdo()->quote($field), $occupationFields));

        // Base query
        $query = Post::with(['availabilitySpecificDates', 'availabilityDays', 'user.favoriteJobs'])
            ->where('status', '1')
            ->where('user_id', '!=', Auth::id())
            ->whereDoesntHave('jobApplications', function ($q) {
                $q->where('user_id', auth()->id());
            });

        // Add distance calculation if latitude and longitude are available
        if ($latitude && $longitude) {
            Log::info("Received Latitude: $latitude Longitude: $longitude");
            $query->selectRaw('*');
        } else {
            $query->selectRaw('*');
        }

        // Apply ordering
        $query
            // HIGHEST PRIORITY: Transport for non-mobile users
            ->when($loggedInUser->userAvailability && $loggedInUser->userAvailability->are_you_mobile === 'no', function($query) {
                $query->orderByRaw("CASE WHEN transport IN ('public_transport', 'company_transport') THEN 0 ELSE 1 END ASC");
            });


        // Final sorting - different order based on whether we have coordinates
        if ($latitude && $longitude) {
            $query->orderBy('distance', 'ASC');
        }
        $query->orderBy('id', 'DESC');

        $jobs = $query->paginate(1000);

        foreach ($jobs as $job) {
            $job->availability_dates = $job->availabilitySpecificDates->pluck('availability_date')->toArray();
            unset($job->availabilitySpecificDates);

            $availabilityDays = [];
            if ($job->availability == 0 && $job->availabilityDays) {
                foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                    if ($job->availabilityDays->$day == "1") {
                        $availabilityDays[] = $day;
                    }
                }
            }
            $job->availability_days = $availabilityDays;
            unset($job->availabilityDays);

            $job->is_favorite = FavoriteJob::where('user_id', auth()->id())
                ->where('post_id', $job->id)
                ->exists() ? 1 : 0;

            unset($job->user->favoriteJobs);
        }

        // Fetch myJobsList (jobs where user is accepted)
        $myJobsList = Post::with(['availabilitySpecificDates', 'availabilityDays', 'user'])
            ->whereHas('jobApplications', function ($q) {
                $q->where('user_id', auth()->id())->where('status', '1');
            })
            ->whereDoesntHave('reviews', function ($q) {
                $q->where('user_id', auth()->id())->where('type', '0');
            })
            ->orderBy('id', 'desc')
            ->where('status', '0')
            ->get();

        foreach ($myJobsList as $job) {
            $job->availability_dates = $job->availabilitySpecificDates->pluck('availability_date')->toArray();
            unset($job->availabilitySpecificDates);

            $availabilityDays = [];
            if ($job->availability == 0 && $job->availabilityDays) {
                foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                    if ($job->availabilityDays->$day == "1") {
                        $availabilityDays[] = $day;
                    }
                }
            }
            $job->availability_days = $availabilityDays;
            unset($job->availabilityDays);
        }

        // Get unread notifications count
        $notificationsCount = Notification::where('user_id', auth()->id())
            ->whereJsonDoesntContain('payload->type', 'Message_sent')
            ->where('status', 'pending')
            ->count();

        Log::info("Jobs Retrieved: " . $jobs->count());

        return response()->json([
            'status' => 200,
            'unread_notifications_count' => $notificationsCount,
            'message' => 'Job details retrieved successfully',
            'jobs' => $jobs,
            'my_jobs_list' => $myJobsList
        ]);
    }
    public function filteredJobsKeywords(Request $request)
    {
        // Get user latitude and longitude
        $loggedInUser = User::find(auth()->id());

        $latitude = $loggedInUser->latitude;
        $longitude = $loggedInUser->longitude;

        // Get the search keyword from request
        $keyword = $request->input('keyword');

        // Validate latitude and longitude
        if (!$latitude || !$longitude) {
            return response()->json([
                'status' => 400,
                'message' => 'Latitude and Longitude are required.',
                'data' => []
            ], 400);
        }

        Log::info("Received Latitude: $latitude Longitude: $longitude");

        // Start query to fetch jobs
        $query = Post::selectRaw(
            '*')
            ->where('status', '1') // Only active jobs
            ->where('user_id', '!=', Auth::id())
            ->with(['user.favoriteJobs', 'availabilityDays', 'availabilitySpecificDates'])
            ->whereDoesntHave('jobApplications', function ($q) {
                $q->where('user_id', auth()->id());
            });

        // Apply keyword filter if provided
        if ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('job_role', 'like', '%' . $keyword . '%')
                    ->orWhere('field', 'like', '%' . $keyword . '%')
                    ->orWhere('subdomain', 'like', '%' . $keyword . '%')
                    ->orWhere('fixed_salary', 'like', '%' . $keyword . '%');
            });
        }

        // Order by distance (nearest first)
        $jobs = $query->orderBy('distance', 'asc')->orderBy('id', 'desc')->paginate(1000);

        // Modify each job to include `is_favorite`, `availability_dates`, and `availability_days`
        $jobs->getCollection()->transform(function ($job) {
            // Add `is_favorite` field to the job
            $job->is_favorite = FavoriteJob::where('user_id', auth()->id())
                ->where('post_id', $job->id)
                ->exists() ? 1 : 0;

            // Handle availability dates and days
            if ($job->availability == 0) {
                // Convert availability days to a list format
                $availabilityDays = [];
                if ($job->availabilityDays) {
                    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                        if ($job->availabilityDays->$day == "1") {
                            $availabilityDays[] = $day;
                        }
                    }
                }
                $job->availability_days = $availabilityDays;
                $job->availability_dates = [];
            } elseif ($job->availability == 1) {
                $job->availability_dates = $job->availabilitySpecificDates ? $job->availabilitySpecificDates->pluck('availability_date') : [];
                $job->availability_days = null;
            } else {
                $job->availability_dates = [];
                $job->availability_days = null;
            }

            // Clean up relationships
            unset($job->user->favoriteJobs);
            unset($job->availabilitySpecificDates);
            unset($job->availabilityDays);
            return $job;
        });

        // Return success response with job details
        return response()->json([
            'status' => 200,
            'message' => 'Filtered job details retrieved successfully',
            'data' => $jobs
        ]);
    }



    public function filteredJobs(Request $request)
    {
        $query = Post::with(['user', 'availabilitySpecificDates', 'availabilityDays', 'user.favoriteJobs'])
            ->where('user_id', '!=', Auth::id())
            ->where('status', '1')
            ->whereDoesntHave('jobApplications', function ($q) {
                $q->where('user_id', auth()->id());
            });

        // Filter by work_type if provided
        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        // Filter by is_remote if provided
        if ($request->filled('is_remote')) {
            $query->where('is_remote', $request->is_remote);
        }

        // Filter by salary range if provided
        if ($request->filled('min_salary') && $request->filled('max_salary')) {
            $query->whereBetween('fixed_salary', [$request->min_salary, $request->max_salary]);
        }

        // Filter by field if provided
        if ($request->filled('field')) {
            $query->where('field', $request->field);
        }

        // Filter by subdomain if provided
        if ($request->filled('subdomain')) {
            $query->where('subdomain', $request->subdomain);
        }

        // Filter by location
        $latitude = $request->filled('current_latitude') && $request->filled('current_longitude')
            ? $request->current_latitude
            : ($request->filled('custom_latitude') && $request->filled('custom_longitude')
                ? $request->custom_latitude
                : null);
        $longitude = $request->filled('current_longitude') ? $request->current_longitude : $request->custom_longitude;

        if ($latitude !== null && $longitude !== null) {
            $query->selectRaw("*")->orderBy('distance')->orderBy('id', 'desc');
        }

        // Filter by specific dates if provided
        if ($request->filled('specific_dates')) {
            $dates = $request->specific_dates;
            $query->whereHas('availabilitySpecificDates', function ($q) use ($dates) {
                $q->whereIn('availability_date', $dates);
            });
        }

        // Paginate the results
        $posts = $query->paginate(1000);

        // Transform the posts
        $posts->getCollection()->transform(function ($post) {
            // Add is_favorite field
            $post->is_favorite = FavoriteJob::where('user_id', auth()->id())
                ->where('post_id', $post->id)
                ->exists() ? 1 : 0;

            // Handle availability fields
            if ($post->availability == 0) {
                // Convert availability days to a list format
                $availabilityDays = [];
                if ($post->availabilityDays) {
                    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                        if ($post->availabilityDays->$day == "1") {
                            $availabilityDays[] = $day;
                        }
                    }
                }
                $post->availability_days = $availabilityDays;
                $post->availability_dates = [];
            } elseif ($post->availability == 1) {
                $post->availability_dates = $post->availabilitySpecificDates ? $post->availabilitySpecificDates->pluck('availability_date') : [];
                $post->availability_days = null;
            } else {
                $post->availability_dates = [];
                $post->availability_days = null;
            }

            // Clean up relationships
            unset($post->user->favoriteJobs);
            unset($post->availabilitySpecificDates);
            unset($post->availabilityDays);

            return $post;
        });

        return response()->json([
            'status' => 200,
            'message' => 'Filtered job posts retrieved successfully',
            'data' => $posts
        ]);
    }

    public function switchUserType()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
                'data' => (object)[],
            ], 404);
        }

        $switchConditions = [
            'person' => [
                'can_switch' => $user->is_onboarding_person == '1',
                'new_type' => 'business',
                'success_message' => 'User switched to Business',
                'pending_message' => 'Your Business OnBoarding is Pending'
            ],
            'business' => [
                'can_switch' => $user->is_onboarding_business == '1',
                'new_type' => 'person',
                'success_message' => 'User switched to Person',
                'pending_message' => 'Your Person OnBoarding is Pending'
            ]
        ];

        $currentType = $user->type;

        if (!isset($switchConditions[$currentType])) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid user type',
                'data' => (object)[]
            ], 400);
        }

        if ($switchConditions[$currentType]['can_switch']) {
            $user->update(['type' => $switchConditions[$currentType]['new_type']]);
            $message = $switchConditions[$currentType]['success_message'];
        } else {
            $message = $switchConditions[$currentType]['pending_message'];
        }

        return response()->json([
            'status' => 200,
            'message' => $message,
            'data' => $user
        ]);
    }

    public function contactReport(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:0,1,2,3',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'file' => 'nullable|file|mimes:pdf,docx,doc,jpg,jpeg,png|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('contact_us_files');
            }

            // Create contact record
            $contact = ContactUs::create([
                'user_id' => auth()->id(),
                'type' => $request->type,
                'title' => $request->title,
                'description' => $request->description,
                'file' => $filePath,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contact form submitted successfully',
                'data' => $contact
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit contact form',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateToken(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'device_token' => 'nullable|string',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validated->errors()->first(),
                'data' => [],
            ], 422);
        }

        $user = auth()->user();

        if ($request->filled('device_token')) {
            $user->userDevices()->updateOrCreate(
                ['user_id' => $user->id],
                ['device_token' => $request->get('device_token')]
            );
        }

        return response()->json([
            'status' => 200,
            'message' => 'Device token updated successfully',
            'data' => [],
        ]);
    }

}
