<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Notification;
use App\Models\Post;
use App\Models\PostAvailabilityDays;
use App\Models\PostAvailabilitySpecificDates;
use App\Models\ReviewsNotification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class PostController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function store(Request $request)
    {
        Log::info($request);
        try {
            // Validate input fields
            $validated = Validator::make($request->all(), [
                'job_role' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'coordinates' => 'nullable|string',
                'field' => 'required|string',
                'subdomain' => 'nullable|string',
                'fixed_salary' => 'required|numeric',
                'transport' => 'nullable|in:self_transport,public_transport,company_transport',
                'job_description' => 'required|string',

                'start_time' => 'nullable',
                'end_time' => 'nullable',

                'total_positions' => 'nullable|numeric',

                'is_remote' => 'nullable|in:0,1',
                'work_type' => 'nullable|in:0,1',

                'availability' => 'nullable|in:0,1',
                'availability_dates' => 'required_if:availability,1|array',
                'availability_dates.*' => 'date',

                'document' => 'nullable|file|mimes:pdf,docx,doc,jpg,jpeg,png|max:10240', // Max 10MB
            ]);

            // Return the first validation error if exists
            if ($validated->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validated->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            // Start transaction
            DB::beginTransaction();

            // Get authenticated user
            $user = auth()->user();

            // Upload document if available
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $this->uploadImage($request->file('document'), 'images/post');
            }

            // Create post
            $post = Post::create([
                'user_id' => $user->id,
                'user_type' => 'business',
                'job_role' => $request->job_role,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'coordinates' => $request->coordinates,
                'field' => $request->field,
                'subdomain' => $request->subdomain,
                'fixed_salary' => $request->fixed_salary,
                'total_positions' => $request->total_positions,
                'remaining_positions' => $request->total_positions,
                'transport' => $request->transport,
                'job_description' => $request->job_description,
                'availability' => $request->availability,
                'is_remote' => $request->is_remote,
                'work_type' => $request->work_type,
                'document' => $documentPath,
                'status' => '1',
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            if ($post) {
                // Save availability days or specific dates
                if ($request->availability == 0) {
                    // Update or create `post_availability_days`
                    $availabilityDays = $request->only(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
                    PostAvailabilityDays::updateOrCreate(
                        ['post_id' => $post->id],
                        $availabilityDays
                    );
                } elseif ($request->availability == 1) {
                    // Remove existing specific dates and save new ones
                    PostAvailabilitySpecificDates::where('post_id', $post->id)->delete();

                    if ($request->has('availability_dates') && is_array($request->availability_dates)) {
                        foreach ($request->availability_dates as $date) {
                            PostAvailabilitySpecificDates::create([
                                'post_id' => $post->id,
                                'availability_date' => $date,
                            ]);
                        }
                    }
                }
            }

            // Load the post with relationships
            $createdPost = Post::with(['availabilityDays', 'availabilitySpecificDates'])->find($post->id);

            // Format availability data similar to show method
            if ($createdPost->availability == 0) {
                // Convert availability days model to a simple list format
                $days = [];
                $dayMapping = [
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday',
                ];

                if ($createdPost->availabilityDays) {
                    foreach ($dayMapping as $key => $day) {
                        if ($createdPost->availabilityDays->$key == "1") {
                            $days[] = $day;
                        }
                    }
                }

                // Assign only the list of available days
                $createdPost->availability_days = $days;
                $createdPost->availability_dates = []; // Empty availability_dates
            } elseif ($createdPost->availability == 1) {
                // If availability is 1, show the specific availability dates
                $createdPost->availability_dates = $createdPost->availabilitySpecificDates
                    ? $createdPost->availabilitySpecificDates->pluck('availability_date')->toArray()
                    : [];
                $createdPost->availability_days = []; // Empty availability_days
            }

            // Prepare the response data
            $responseData = [
                'id' => $createdPost->id,
                'job_role' => $createdPost->job_role,
                'field' => $createdPost->field,
                'subdomain' => $createdPost->subdomain,
                'fixed_salary' => $createdPost->fixed_salary,
                'availability' => $createdPost->availability,
                'latitude' => $createdPost->latitude,
                'longitude' => $createdPost->longitude,
                'coordinates' => $createdPost->coordinates,
                'min_offered_salary' => $createdPost->min_offered_salary,
                'max_offered_salary' => $createdPost->max_offered_salary,
                'transport' => $createdPost->transport,
                'job_description' => $createdPost->job_description,
                'document' => $createdPost->document,
                'status' => $createdPost->status,
                'start_time' => $createdPost->start_time,
                'end_time' => $createdPost->end_time,
                'created_at' => $createdPost->created_at,
                'updated_at' => $createdPost->updated_at,
                'is_remote' => $createdPost->is_remote,
                'work_type' => $createdPost->work_type,
                'total_positions' => $createdPost->total_positions,
                'total_accepted_requests' => $createdPost->total_accepted_requests,
                'total_remaining_requests' => $createdPost->total_remaining_requests,
                'last_transaction_date' => $createdPost->transaction_date,
                'availability_days' => $createdPost->availability_days,
                'availability_dates' => $createdPost->availability_dates,
                'user' => null
            ];

            // Commit transaction
            DB::commit();

            $this->SendNotificationForPostedJob($createdPost->id);

            return response()->json([
                'status' => 200,
                'message' => 'Post created successfully',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating post: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Error creating post',
                'data' => (object)[],
            ], 500);
        }
    }
    public function update(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'post_id' => 'required|integer|exists:posts,id',
                'job_role' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'coordinates' => 'nullable|string',
                'field' => 'required|string',
                'subdomain' => 'nullable|string',
                'fixed_salary' => 'required|numeric',
                'transport' => 'nullable|in:self_transport,public_transport,company_transport',
                'job_description' => 'required|string',
                'start_time' => 'nullable',
                'end_time' => 'nullable',
                'total_positions' => 'nullable|numeric',
                'is_remote' => 'nullable|in:0,1',
                'work_type' => 'nullable|in:0,1',
                'availability' => 'required|in:0,1',
                'availability_dates' => 'required_if:availability,1|array',
                'availability_dates.*' => 'date',
                'monday' => 'nullable|boolean',
                'tuesday' => 'nullable|boolean',
                'wednesday' => 'nullable|boolean',
                'thursday' => 'nullable|boolean',
                'friday' => 'nullable|boolean',
                'saturday' => 'nullable|boolean',
                'sunday' => 'nullable|boolean',
                'document' => 'nullable|file|mimes:pdf,docx,doc,jpg,jpeg,png|max:10240',
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => $validated->errors()->first(),
                    'data' => (object)[],
                ], 422);
            }

            DB::beginTransaction();

            $user = auth()->user();

            $post = Post::where('id', $request->post_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$post) {
                return response()->json([
                    'status' => 404,
                    'message' => 'לא נמצאה משרה או שלא ניתן  לצפות בה כעת',
                    'data' => (object)[],
                ], 404);
            }
            $postPreviostotal_positions = $post->total_positions;

            if ($request->hasFile('document')) {
                $documentPath = $this->uploadImage($request->file('document'), 'images/post');
                $post->document = $documentPath;
            }

            $post->job_role = $request->job_role;
            $post->latitude = $request->latitude;
            $post->longitude = $request->longitude;
            $post->coordinates = $request->coordinates;
            $post->field = $request->field;
            $post->subdomain = $request->subdomain;
            $post->fixed_salary = $request->fixed_salary;
            $post->total_positions = $request->total_positions;
            $post->transport = $request->transport;
            $post->job_description = $request->job_description;
            $post->availability = $request->availability;
            $post->is_remote = $request->is_remote;
            $post->work_type = $request->work_type;
            $post->start_time = $request->start_time;
            $post->end_time = $request->end_time;
            Log::info(' Request:', ['positions' => (int)$request->total_positions]);
            Log::info(' Post:', ['positions' => (int)$postPreviostotal_positions]);

            // Get count of already hired applicants
            $totalApplicantHired = JobApplication::where('status', '1')
                ->where('post_id', $post->id)
                ->count();

// Case 1: User is trying to decrease positions below already hired count
            if ((int)$request->total_positions < $totalApplicantHired) {
                return response()->json([
                    'status' => 400,
                    'message' => 'אי אפשר לצמצם משרות מתחת ל-' . $totalApplicantHired . ' (מספר עובדים שכבר הועסקו במשרה זו)',                    'data' => (object)[],
                ], 400);
            }

// Case 2: User is increasing total positions
            if ((int)$request->total_positions > (int)$postPreviostotal_positions) {
                $post->status = '1'; // Open the position if increasing
                $difference = (int)$request->total_positions - (int)$postPreviostotal_positions;
                $post->remaining_positions += $difference;
            }
// Case 3: User is decreasing total positions (but still above hired count)
            elseif ((int)$request->total_positions < (int)$postPreviostotal_positions) {
                $post->status = '1';
                $difference = (int)$postPreviostotal_positions - (int)$request->total_positions;

                // Calculate correct remaining positions (total - hired)
                $post->remaining_positions = (int)$request->total_positions - $totalApplicantHired;
            }

// Save the changes
            $post->total_positions = (int)$request->total_positions;
            $post->save();


            $post->save();

            if ($request->availability == 1) {
                PostAvailabilitySpecificDates::where('post_id', $post->id)->delete();

                foreach ($request->availability_dates as $date) {
                    PostAvailabilitySpecificDates::create([
                        'post_id' => $post->id,
                        'availability_date' => $date,
                    ]);
                }

                PostAvailabilityDays::where('post_id', $post->id)->delete();
            } else {
                PostAvailabilitySpecificDates::where('post_id', $post->id)->delete();

                PostAvailabilityDays::updateOrCreate(
                    ['post_id' => $post->id],
                    [
                        'monday' => $request->monday ?? 0,
                        'tuesday' => $request->tuesday ?? 0,
                        'wednesday' => $request->wednesday ?? 0,
                        'thursday' => $request->thursday ?? 0,
                        'friday' => $request->friday ?? 0,
                        'saturday' => $request->saturday ?? 0,
                        'sunday' => $request->sunday ?? 0,
                    ]
                );
            }

            // Load the post with all relationships
            $updatedPost = Post::with(['availabilityDays', 'availabilitySpecificDates'])->find($post->id);

            // Format availability data similar to store method
            if ($updatedPost->availability == 0) {
                // Convert availability days model to a simple list format
                $days = [];
                $dayMapping = [
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday',
                ];

                if ($updatedPost->availabilityDays) {
                    foreach ($dayMapping as $key => $day) {
                        if ($updatedPost->availabilityDays->$key == "1") {
                            $days[] = $day;
                        }
                    }
                }

                // Assign only the list of available days
                $updatedPost->availability_days = $days;
                $updatedPost->availability_dates = []; // Empty availability_dates
            } elseif ($updatedPost->availability == 1) {
                // If availability is 1, show the specific availability dates
                $updatedPost->availability_dates = $updatedPost->availabilitySpecificDates
                    ? $updatedPost->availabilitySpecificDates->pluck('availability_date')->toArray()
                    : [];
                $updatedPost->availability_days = []; // Empty availability_days
            }

            // Prepare the response data
            $responsePost = [
                'id' => $updatedPost->id,
                'job_role' => $updatedPost->job_role,
                'field' => $updatedPost->field,
                'subdomain' => $updatedPost->subdomain,
                'fixed_salary' => $updatedPost->fixed_salary,
                'availability' => $updatedPost->availability,
                'latitude' => $updatedPost->latitude,
                'longitude' => $updatedPost->longitude,
                'coordinates' => $updatedPost->coordinates,
                'min_offered_salary' => $updatedPost->min_offered_salary,
                'max_offered_salary' => $updatedPost->max_offered_salary,
                'transport' => $updatedPost->transport,
                'job_description' => $updatedPost->job_description,
                'document' => $updatedPost->document,
                'status' => $updatedPost->status,
                'start_time' => $updatedPost->start_time,
                'end_time' => $updatedPost->end_time,
                'created_at' => $updatedPost->created_at,
                'updated_at' => $updatedPost->updated_at,
                'is_remote' => $updatedPost->is_remote,
                'work_type' => $updatedPost->work_type,
                'total_positions' => $updatedPost->total_positions,
                'total_accepted_requests' => $updatedPost->total_accepted_requests,
                'total_remaining_requests' => $updatedPost->total_remaining_requests,
                'last_transaction_date' => $updatedPost->transaction_date,
                'availability_days' => $updatedPost->availability_days,
                'availability_dates' => $updatedPost->availability_dates,
                'user' => null
            ];

            DB::commit();


            return response()->json([
                'status' => 200,
                'message' => 'Post updated successfully',
                'data' => $responsePost,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating post: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Error updating post',
                'data' => (object)[],
            ], 500);
        }
    }

    public function jobEnd($postId)
    {
        try {
            $user = auth()->user();

            $post = Post::where('id', $postId)
                ->where('user_id', $user->id)
                ->first();

            if (!$post) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Job not found',
                    'data' => (object)[],
                ], 404);
            }

            if ($post->status == '0') {
                return response()->json([
                    'status' => 200,
                    'message' => 'Job is already finished',
                    'data' => (object)[],
                ], 200);
            }

            $post->update(['status' => '0']);

            // Get all candidate user IDs
            $jobsAllCandidates = JobApplication::where('post_id', $post->id)
                ->where('status', '1')
                ->pluck('user_id');

            // Fetch all users with their devices in one query
            $notifiableUsers = User::with('userDevices')->whereIn('id', $jobsAllCandidates)->get();

//            adding bussiness in notification of review
            ReviewsNotification::create([
                'user_id' => $post->user_id,
                'post_id' => $post->id
            ]);

            foreach ($notifiableUsers as $notifiableUser) {

                ReviewsNotification::create([
                    'user_id' => $notifiableUser->id,
                    'post_id' => $post->id
                ]);

                if ($notifiableUser->userDevices->isEmpty()) {
                    Log::warning("No device token found for user ID: {$notifiableUser->id}");
                    continue;
                }

                foreach ($notifiableUser->userDevices as $device) {
                    $receiverDeviceToken = $device->device_token;
                    Log::info("Sending notification to device with token: {$receiverDeviceToken}");

                    $data = [
                        'sender_id' => $notifiableUser->id,
                        'fcm_token' => $receiverDeviceToken,
                        'type' => 'Job_Finished',
                        'title' => 'המשרה הסתיימה בהצלחה',
                        'body' => 'מזל טוב! ' . $post->name . ' הסתיימה.',
                        'data' => [
                            'type' => "Job_Finished",
                            'id' => $post->id,
                            'sender_id' => $post->user_id,
                        ],
                    ];

                    $response = $this->firebaseService->sendNotification($data);

                    // Save notification in the database
                    Notification::create([
                        'user_id' => $notifiableUser->id,
                        'title' => 'המשרה הסתיימה בהצלחה',
                        'body' => 'מזל טוב! ' . $post->name . ' הסתיימה.',
                        'payload' => json_encode([
                            'type' => "Job_Finished",
                            'id' => $post->id,
                            'sender_id' => $post->user_id,
                        ]),
                        'status' => 'pending',
                    ]);
                    Log::info("Firebase notification sent for post ID: {$post->id}", ['response' => $response]);
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job is finished',
                'data' => (object)[],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error ending job: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Error ending job',
                'data' => (object)[],
            ], 500);
        }
    }

    public function jobDelete($postId)
    {
        try {
            // Find the post
            $post = Post::find($postId);

            if (!$post || $post->user_id !== auth()->id()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'לא נמצאה משרה או שלא ניתן  לצפות בה כעת',
                    'data' => (object)[]
                ], 404);
            }

            // Use transaction for atomicity
            DB::beginTransaction();

            // Delete related job applications and their transactions
            $post->jobApplications()->each(function ($application) {
                $application->transactions()->delete(); // Delete transactions related to job applications
                $application->delete(); // Delete job applications
            });

            // Delete related reviews
            $post->reviews()->delete();

            // Delete related favorite jobs
            $post->favoriteJobs()->delete();

            // Delete the post
            $post->delete();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'הפוסט נמחק בהצלחה', //post deleted succesfully message
                'data' => (object)[]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while deleting the post',
                'error' => $e->getMessage(),
                'data' => (object)[]
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            // Find the job post by ID and load required relationships
            $post = Post::with(['availabilityDays', 'availabilitySpecificDates', 'user.favoriteJobs'])
                ->find($id);

            // If post not found, return error
            if (!$post) {
                return response()->json([
                    'status' => 404,
                    'message' => 'לא נמצא משרה מתאימה',
                    'data' => (object)[], // Empty data object
                ], 404);
            }

            // Add availability data to the response
            if ($post->availability == 0) {
                // Convert availability days model to a simple list format
                $days = [];
                $dayMapping = [
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday',
                ];

                if ($post->availabilityDays) {
                    foreach ($dayMapping as $key => $day) {
                        if ($post->availabilityDays->$key == "1") {
                            $days[] = $day;
                        }
                    }
                }

                // Assign only the list of available days
                $post->availability_days = $days;
                $post->availability_dates = []; // Empty availability_dates

                // Remove availabilityDays model from response to prevent full object from showing
                unset($post->availabilityDays);
            } elseif ($post->availability == 1) {
                // If availability is 1, show the specific availability dates
                $post->availability_dates = $post->availabilitySpecificDates
                    ? $post->availabilitySpecificDates->pluck('availability_date')->toArray()
                    : [];
                $post->availability_days = []; // Empty availability_days
            }


            // Determine if the post is marked as favorite by the user
            $post->is_favorite = $post->user->favoriteJobs->contains('post_id', $post->id) ? 1 : 0;

            // Remove unnecessary relationships from the response
            unset($post->availabilitySpecificDates);
            unset($post->user->favoriteJobs);

            // Return success response with the post data
            return response()->json([
                'status' => 200,
                'message' => 'Job details retrieved successfully',
                'data' => $post,
            ], 200);

        } catch (\Exception $e) {
            // Catch any exception and return error message
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching job details',
                'data' => (object)[], // Empty data object
            ], 500);
        }
    }


    // Reuse the provided image upload function for documents
    public function uploadImage(UploadedFile $image, $path = 'images/post')
    {
        $path = public_path($path);

        // Create the directory if it doesn't exist
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Generate a unique filename
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        // Move the image to the specified directory
        $image->move($path, $filename);

        // Return the relative path to access the image
        return 'images/post/' . $filename;
    }

    private function SendNotificationForPostedJob($createdPostId) {
         try {
             Log::info("start SendNotificationForPostedJob");
             $ids = DB::table('users')
             ->where('type', 'person')
             ->where('is_deleted', '0')  // or 0 if it’s an integer column
             ->pluck('id');

             foreach ($ids as $id) {
               Log::info("SendNotificationForPostedJob person_id={$id}, post_id={$createdPostId}");
               // Fetching the notification receiver's device token and the sender's info
               $notificationReceiver = User::with('userDevices')->find($id);
               $notificationSender = User::find(auth()->id());

               // Ensure that receiver and sender exist and receiver has a device token
               if (!$notificationReceiver || $notificationReceiver->userDevices->isEmpty()) {
                    Log::warning("No device token found for user ID: {$id} while sending notification");
                    continue;
               }

               // Get the receiver's device token
               $receiverDeviceToken = $notificationReceiver->userDevices->first()->device_token;
               // Log the device token info
               Log::info("Sending notification to device with token: {$receiverDeviceToken}");
               // Prepare the payload for the database
               $payload = [
                   'type' => 'JobAdded',
                   'id' => $createdPostId,
                   'sender_id' => $notificationSender->id,
               ];

               // Prepare notification data
               $data = [
                 'sender_id' => $notificationSender->id,
                 'fcm_token' => $receiverDeviceToken,
                 'type' => 'JobAdded',
                 'title' => 'הועלתה משרה חדשה',
                 'body' => $notificationSender->first_name . ' העלה משרה חדשה',
                 'data' => $payload,
               ];

               // Call the Firebase service to send notification
               $response = $this->firebaseService->sendNotification($data);

               // Save notification in the database
               Notification::create([
                 'user_id' => $notificationSender->id,
                 'title' => 'הועלתה משרה חדשה',
                 'body' => $notificationSender->first_name . ' העלה משרה חדשה',
                 'payload' => json_encode($payload),
                 'status' => 'pending',
               ]);

               // Log the response from Firebase
               Log::info("Firebase notification sent for post ID: {$createdPostId}", ['response' => $response]);

             }

           } catch (\Exception $e) {
                    // Log any error that occurs while sending the notification
                    Log::error("Error sending notification for post ID: {$createdPostId}", ['error' => $e->getMessage()]);
           }
        }



}

