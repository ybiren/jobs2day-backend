<?php


namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class JobApplicationController extends Controller
{

    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    // Apply for a job
    public function apply(Request $request, $postId)
    {
        // Validate the input
        $validated = Validator::make($request->all(), [
            'note' => 'nullable|string|max:200',
            'document_1' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
            'document_2' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validated->errors()->first(),
                'data' => (object)[],
            ], 422);
        }

        // Check if the post exists
        $post = Post::find($postId);

        if (!$post) {
            return response()->json([
                'status' => 404,
                'message' => 'לא נמצא משרה מתאימה',
                'data' => (object)[],
            ], 404);
        }
        // Ensure the job is finished before reviewing
        if ($post->status != 1) {
            return response()->json([
                'status' => 400,
                'message' => 'Job Vacancy is already Closed',
                'data' => (object)[],
            ], 400);
        }

        // Check if the logged-in user is a business, and prevent them from applying
        $user = auth()->user();
        if ($user->type === 'business') {
            return response()->json([
                'status' => 403,
                'message' => 'Businesses cannot apply for jobs.',
                'data' => (object)[],
            ], 403);
        }

        // Check if the user has already applied for this job
        $existingApplication = JobApplication::where('user_id', auth()->id())
            ->where('post_id', $postId)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'status' => 409,
                'message' => 'You have already applied for this job',
                'data' => (object)[],
            ], 409);
        }

        // Initialize file paths
        $document1Path = null;
        $document2Path = null;

        // Handle document_1 upload
        if ($request->hasFile('document_1')) {
            $document1Path = $this->uploadFiles($request->file('document_1'));
        }

        // Handle document_2 upload
        if ($request->hasFile('document_2')) {
            $document2Path = $this->uploadFiles($request->file('document_2'));
        }

        // Create the job application with the uploaded file paths
        $application = JobApplication::create([
            'user_id' => auth()->id(),
            'post_id' => $postId,
            'note' => $request->note,
            'status' => '0', // Assuming '0' means pending or awaiting review
            'document_1' => $document1Path,
            'document_2' => $document2Path,
        ]);

        if ($application) {
            try {
                // Increment the total application requests
                $post->increment('total_application_requests');

                // Fetching the notification receiver's device token and the sender's info
                $notificationReceiver = User::with('userDevices')->find($post->user_id);
                $notificationSender = User::find(auth()->id());

                // Ensure that receiver and sender exist and receiver has a device token
                if (!$notificationReceiver || $notificationReceiver->userDevices->isEmpty()) {
                    Log::warning("No device token found for user ID: {$post->user_id} while sending notification");
                    return;
                }

                // Get the receiver's device token
                $receiverDeviceToken = $notificationReceiver->userDevices->first()->device_token;

                // Log the device token info
                Log::info("Sending notification to device with token: {$receiverDeviceToken}");
                // Prepare the payload for the database
                $payload = [
                    'type' => 'Application_Submitted',
                    'id' => $application->id,
                    'sender_id' => $notificationSender->id,
                ];

                // Prepare notification data
                $data = [
                    'sender_id' => $post->user_id,
                    'fcm_token' => $receiverDeviceToken,
                    'type' => 'Application_Submitted',
                    'title' => 'התקבלה בקשה למשרה',
                    'body' => $notificationSender->first_name . ' רוצה לבצע את המשרה שלך',
                    'data' => $payload,
                ];

                // Call the Firebase service to send notification
                $response = $this->firebaseService->sendNotification($data);

                // Save notification in the database
                Notification::create([
                    'user_id' => $post->user_id,
                    'title' => 'התקבלה בקשה למשרה',
                    'body' => $notificationSender->first_name . ' רוצה לבצע את המשרה שלך',
                    'payload' => json_encode($payload),
                    'status' => 'pending',
                ]);

                // Log the response from Firebase
                Log::info("Firebase notification sent for post ID: {$post->id}", ['response' => $response]);

            } catch (\Exception $e) {
                // Log any error that occurs while sending the notification
                Log::error("Error sending notification for post ID: {$post->id}", ['error' => $e->getMessage()]);
            }
        }


        return response()->json([
            'status' => 200,
            'message' => 'Job application submitted successfully',
            'data' => $application,
        ], 200);
    }

    public function jobRequestAction(Request $request, $jobId)
    {
        try {
            // Validate only 'type'
            $request->validate([
                'type' => 'required|integer',
            ]);

            // Find the job application
            $job = JobApplication::find($jobId);
            if (!$job) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Job application not found',
                    'data' => (object)[],
                ], 404);
            }
            if ($job->post->remaining_positions == 0) {
                return response()->json([
                    'status' => 200,
                    'message' => 'All Positions Already Filled',
                    'data' => (object)[],
                ], 200);
            }
            $jobUser = $job->user_id;

            // Check if the authenticated user is the owner of the job post
            if (auth()->id() !== $job->post->user_id) {
                Log::info("furqan auth user id " . auth()->id());
                Log::info("furqan auth post user id " . $job->post->user_id);
                return response()->json([
                    'status' => 403,
                    'message' => 'You are not authorized to perform this action',
                    'data' => (object)[],
                ], 403);
            }

            // Handle application approval (only when type == 1)
            if ($request->type == 1) {
                try {
                    // Validate transaction fields
                    $validatedData = $request->validate([
                        'payment_type' => 'required|string',
                        'type_id' => 'required|integer',
                        'amount' => 'required|numeric|min:0',
                        'status' => 'required|string|in:pending,success,failed',
                        'response' => 'nullable|string',
                        'expdate' => 'required|string|size:4', // MMYY format
                        'cvv' => 'required|string|size:3', // 3-digit CVV
                        'ccno' => 'required|string|size:16', // 16-digit card number
                        'cred_type' => 'required|string|in:credit_card,debit_card',
                    ]);

                    // Assign authenticated user ID
                    $validatedData['user_id'] = auth()->id();

                    // Save transaction
                    $transaction = Transaction::create($validatedData);

                        $data = JobApplication::with(['post', 'user'])->find($jobId);
                        $receiverMail = $data->user->email;

                    try {
                        Log::info("Attempting to send email to: " . $receiverMail);

                        Mail::send('emails.jobApplicationApproved', ['data' => $data], function ($message) use ($receiverMail) {
                            $message->to($receiverMail)
                                ->subject('מזל טוב! בקשתך למשרה אושרה!');
                        });
                        Log::info("Email successfully sent to: " . $receiverMail);
                    } catch (\Exception $e) {
                        Log::error("Error sending approval email", [
                            'error' => $e->getMessage(),
                            'receiver' => $receiverMail
                        ]);
                    }


                } catch (\Exception $e) {
                    Log::error("Error processing Payment", ['error' => $e->getMessage()]);

                    return response()->json([
                        'status' => 500,
                        'message' => 'Error processing Payment',
                        'data' => (object)[],
                    ], 500);
                }

                if ($job->post->remaining_positions == 1) {
                    $job->post->status = '2'; // Close the job
                    $job->post->save();
                }

                if ($job->post->remaining_positions > 0) {
                    $job->post->decrement('remaining_positions');
                } else {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Job positions are filled',
                        'data' => (object)[],
                    ], 404);
                }
            }

            // Update job status
            $job->status = $request->type;
            $job->save();

            // Prepare response message
            $actionType = $request->type == 1 ? 'Approved' : 'Rejected';
            $returnMsg = "Job application $actionType successfully";

            // Send notification
            $this->sendNotificationjobRequestAction($job, $request->type, $jobUser);

            return response()->json([
                'status' => 200,
                'message' => $returnMsg,
                'data' => $job,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error processing job application action", ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'message' => 'Error processing job application action',
                'data' => (object)[],
            ], 500);
        }
    }

    /**
     * Send notification for job request action.
     */
    private function sendNotificationjobRequestAction($job, $type, $jobUser)
    {
        try {
            $post = Post::find($job->post_id);
            $notificationReceiver = User::with('userDevices')->find($jobUser);
            $notificationSender = User::find(auth()->id());

            if (!$notificationReceiver || $notificationReceiver->userDevices->isEmpty()) {
                Log::warning("No device token found for user ID: {$job->post->user_id}");
                return;
            }

            $receiverDeviceToken = $notificationReceiver->userDevices->first()->device_token;
            $action = $type == 1 ? 'מאושר' : 'נדחה';
            $action2 = $type == 1 ? 'Approved' : 'Rejected';

            Log::info("Sending notification to device with token: {$receiverDeviceToken}");

            $data = [
                'sender_id' => $notificationReceiver->id,
                'fcm_token' => $receiverDeviceToken,
                'type' => "Application_$action2",
                'title' => " בקשת עבודה $action",
                'body' => $notificationSender->first_name . " $action בקשת העבודה שלך ",
                'data' =>  [
                    'type' => "Application_$action",
                    'id' => $post->id,
                    'sender_id' => $notificationSender->id,
                ],
            ];

            $response = $this->firebaseService->sendNotification($data);

            Notification::create([
                'user_id' => $notificationReceiver->id,
                'title' => " בקשת עבודה $action",
                'body' => $notificationSender->first_name . " $action בקשת העבודה שלך ",
                'payload' => json_encode([
                    'type' => "Application_$action2",
                    'id' => $post->id,
                    'sender_id' => $notificationSender->id,
                ]),
                'status' => 'pending',
            ]);

            Log::info("Firebase notification sent for post ID: {$post->id}", ['response' => $response]);

        } catch (\Exception $e) {
            Log::error("Error sending notification for post ID: {$job->post_id}", ['error' => $e->getMessage()]);
        }
    }

    public function jobCandidateProfile($jobId)
    {
        try {
            // Get all job applications for the authenticated user
            $applications = JobApplication::with('user')->with('post')
                ->where('user_id', auth()->id())
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Job application retrieved successfully',
                'data' => $applications,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching job application',
                'data' => (object)[],
            ], 500);
        }

    }


    // Get all applications for the authenticated user
    public function userWorks()
    {
        try {
            $now = Carbon::now('Asia/Jerusalem');

            $expiredPosts = Post::where('status', '1')->whereHas('availabilitySpecificDates', function ($query) use ($now) {
                $query->whereDate('availability_date', '<=', $now->toDateString());
            })
                ->get()
                ->filter(function ($post) use ($now) {
                    $availability = $post->availabilitySpecificDates->first();
                    $date = $availability->availability_date;
                    $start = Carbon::parse("$date {$post->start_time}");
                    $end = Carbon::parse("$date {$post->end_time}");

                    if ($end->lessThan($start)) {
                        $end->addDay();
                    }

                    Log::info("Post ID {$post->id} - Start: {$start}, End: {$end}, Now: {$now}");

                    return $now->greaterThanOrEqualTo($end);
                });

            if ($expiredPosts->isNotEmpty()) {
                $ids = $expiredPosts->pluck('id')->toArray();
                Log::info("Furqan - Expiring posts on {$now->toDateString()}");
                Log::info("Furqan - Post IDs being expired: " . implode(', ', $ids));
                Post::whereIn('id', $ids)->update(['status' => '0']);
                Log::info("Furqan - Post statuses updated to 0");
            } else {
                Log::info("Furqan - No posts found to expire");
            }


            // Get job applications with status '0', '1', or '2' for the authenticated user
            $applications = JobApplication::with(['post.user', 'post.availabilityDays', 'post.availabilitySpecificDates']) // Include availabilitySpecificDates
            ->where('user_id', auth()->id())
                ->whereIn('status', ['0', '1', '2'])
                ->whereHas('post', function ($query) { // Ensure related post has status '1' or '2'
                    $query->whereIn('status', ['1', '2']);
                })
                ->orderby('id', 'desc')
                ->get();

            // Transform the data to match the desired structure
            $transformedApplications = $applications->map(function ($application) {
                $post = $application->post;

                if ($post) { // Ensure post exists
                    // Convert availability days to a list format
                    $availabilityDays = [];
                    $availabilityDates = [];

                    if ($post->availability == 0 && $post->availabilityDays) {
                        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                            if ($post->availabilityDays->$day == "1") {
                                $availabilityDays[] = ucfirst($day); // Capitalize days
                            }
                        }
                    } elseif ($post->availability == 1 && $post->availabilitySpecificDates) {
                        $availabilityDates = $post->availabilitySpecificDates->pluck('availability_date')->toArray();
                    }

                    $post->availability_days = $availabilityDays;
                    $post->availability_dates = $availabilityDates;

                    unset($post->availabilityDays, $post->availabilitySpecificDates);
                }

                return $post;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Current job applications retrieved successfully',
                'data' => $transformedApplications,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching current job applications',
                'data' => (object)[],
            ], 500);
        }
    }

    public function userWorkHistory()
    {
        try {
            // Fetch the job applications for the authenticated user
            $applications = JobApplication::with([
                'post.user',
                'post.reviews' => function ($query) {
                    $query->where('user_id', auth()->id()); // Filter reviews by the authenticated user
                },
                'post.availabilityDays',
                'post.availabilitySpecificDates' // Include specific availability dates
            ])
                ->where('user_id', auth()->id())
                ->where('status', '1')
                ->whereHas('post', function ($query) {
                    $query->where('status', '0');
                })
                ->orderBy('id', 'desc')
                ->get();

            // Transform the data to match the desired structure
            $transformedApplications = $applications->map(function ($application) {
                $post = $application->post;

                if ($post) { // Ensure post exists
                    // Convert availability days to a list format
                    $availabilityDays = [];
                    if ($post->availability == 0 && $post->availabilityDays) {
                        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                            if ($post->availabilityDays->$day == "1") {
                                $availabilityDays[] = ucfirst($day);
                            }
                        }
                    }

                    // Get availability specific dates
                    $availabilityDates = [];
                    if ($post->availability == 1 && $post->availabilitySpecificDates) {
                        $availabilityDates = $post->availabilitySpecificDates->pluck('availability_date')->toArray();
                    }

                    $post->availability_days = $availabilityDays;
                    $post->availability_dates = $availabilityDates;

                    unset($post->availabilityDays);
                    unset($post->availabilitySpecificDates);
                }

                return $post;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Job application history retrieved successfully',
                'data' => $transformedApplications,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching job application history',
                'data' => (object)[],
            ], 500);
        }
    }


// API for active business job posts (status == 1)
    public function businessJobPosts()
    {
        try {
            $now = Carbon::now('Asia/Jerusalem');
            Log::info('furqan dev: '.auth()->id());

            $expiredPosts = Post::where('status', '1')->whereHas('availabilitySpecificDates', function ($query) use ($now) {
                $query->whereDate('availability_date', '<=', $now->toDateString());
            })
                ->get()
                ->filter(function ($post) use ($now) {
                    $availability = $post->availabilitySpecificDates->first();
                    $date = $availability->availability_date;
                    $start = Carbon::parse("$date {$post->start_time}");
                    $end = Carbon::parse("$date {$post->end_time}");

                    if ($end->lessThan($start)) {
                        $end->addDay();
                    }

                    Log::info("Post ID {$post->id} - Start: {$start}, End: {$end}, Now: {$now}");

                    return $now->greaterThanOrEqualTo($end);
                });

            if ($expiredPosts->isNotEmpty()) {
                $ids = $expiredPosts->pluck('id')->toArray();
                Log::info("Furqan - Expiring posts on {$now->toDateString()}");
                Log::info("Furqan - Post IDs being expired: " . implode(', ', $ids));
                Post::whereIn('id', $ids)->update(['status' => '0']);
                Log::info("Furqan - Post statuses updated to 0");
            } else {
                Log::info("Furqan - No posts found to expire");
            }


            $posts = Post::where('user_id', auth()->id())
                ->whereIn('status', ['1', '2'])
                ->orderBy('id', 'desc')
                ->get();

            $processedPosts = $posts->map(function ($post) {
                // Convert availability days to a list format
                $availabilityDays = [];
                if ($post->availability == 0 && $post->availabilityDays) {
                    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                        if ($post->availabilityDays->$day == "1") {
                            $availabilityDays[] = $day;
                        }
                    }
                }
                $postUser = User::find($post->user_id);

                return [
                    'id' => $post->id,
                    'job_role' => $post->job_role,
                    'field' => $post->field,
                    'subdomain' => $post->subdomain,
                    'fixed_salary' => $post->fixed_salary,
                    'availability' => $post->availability,
                    'latitude' => $post->latitude,
                    'longitude' => $post->longitude,
                    'coordinates' => $post->coordinates,
                    'min_offered_salary' => $post->min_offered_salary,
                    'max_offered_salary' => $post->max_offered_salary,
                    'transport' => $post->transport,
                    'job_description' => $post->job_description,
                    'document' => $post->document,
                    'status' => $post->status,
                    'start_time' => $post->start_time,
                    'end_time' => $post->end_time,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                    'is_remote' => $post->is_remote,
                    'work_type' => $post->work_type,
                    'total_positions' => $post->total_positions,
                    'total_requests' => $post->total_requests,
                    'total_accepted_requests' => $post->total_accepted_requests,
                    'total_remaining_requests' => $post->total_remaining_requests,
                    'last_transaction_date' => $post->transaction_date,
                    'availability_days' => $availabilityDays, // Now in list format
                    'availability_dates' => $post->availabilitySpecificDates->pluck('availability_date')->toArray(),
                    'user' => $postUser,
                ];
            });

            // Get unread notifications count
            $notificationsCount = Notification::where('user_id', auth()->id())
                ->whereJsonDoesntContain('payload->type', 'Message_sent')
                ->where('status', 'pending')
                ->count();

            return response()->json([
                'status' => 200,
                'unread_notifications_count' => $notificationsCount,
                'message' => 'Business job posts retrieved successfully',
                'data' => $processedPosts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching business job posts',
                'data' => (object)[],
            ], 500);
        }
    }

// API for business job posts history (status == 0 or 1)
    public function businessJobPostsHistory()
    {
        try {
            $posts = Post::where('user_id', auth()->id())
                ->where('status', '0')
                ->orderby('updated_at', 'desc')
                ->get();

            $processedPosts = $posts->map(function ($post) {
                $availabilityDays = [];
                if ($post->availability == 0 && $post->availabilityDays) {
                    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                        if ($post->availabilityDays->$day == "1") {
                            $availabilityDays[] = $day;
                        }
                    }
                }

                $postUser = User::find($post->user_id);

                return [
                    'id' => $post->id,
                    'job_role' => $post->job_role,
                    'field' => $post->field,
                    'subdomain' => $post->subdomain,
                    'fixed_salary' => $post->fixed_salary,
                    'availability' => $post->availability,
                    'latitude' => $post->latitude,
                    'longitude' => $post->longitude,
                    'coordinates' => $post->coordinates,
                    'min_offered_salary' => $post->min_offered_salary,
                    'max_offered_salary' => $post->max_offered_salary,
                    'transport' => $post->transport,
                    'job_description' => $post->job_description,
                    'document' => $post->document,
                    'status' => $post->status,
                    'start_time' => $post->start_time,
                    'end_time' => $post->end_time,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                    'is_remote' => $post->is_remote,
                    'work_type' => $post->work_type,
                    'total_positions' => $post->total_positions,
                    'total_requests' => $post->total_requests,
                    'total_accepted_requests' => $post->total_accepted_requests,
                    'total_remaining_requests' => $post->total_remaining_requests,
                    'last_transaction_date' => $post->transaction_date,
                    'availability_days' => $availabilityDays,
                    'availability_dates' => $post->availabilitySpecificDates->pluck('availability_date')->toArray(),
                    'user' => $postUser,
                ];
            });

            $pendingReviews = Post::where('user_id', auth()->id())->where('status', '0')
                ->whereHas('jobApplications', function ($q) {
                    $q->where('status', '1');
                })
                ->get(['id', 'job_role'])
                ->filter(function ($post) {
                    $appliedUsers = JobApplication::where('post_id', $post->id)
                        ->where('status', '1')
                        ->pluck('user_id');

                    $reviewedUsers = Review::where('post_id', $post->id)
                        ->where('type', '1')
                        ->whereIn('reviewed_user_id', $appliedUsers)
                        ->pluck('reviewed_user_id');

                    return $appliedUsers->diff($reviewedUsers)->isNotEmpty();
                })
                ->values();

            return response()->json([
                'status' => 200,
                'message' => 'Business job posts history retrieved successfully',
                'data' => $processedPosts,
                'pending_reviews' => $pendingReviews,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching business job posts history',
                'data' => (object)[],
            ], 500);
        }
    }

    public function businessJobCandidates($jobId)
    {
        try {
            $post = Post::where('id', $jobId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$post) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Job post not found or access denied',
                    'data' => []
                ], 404);
            }

            $applications = JobApplication::with('user')
                ->where('post_id', $jobId)
                ->get();

            $applications = $applications->map(function ($application) {
                $userId = $application->user->id;

                $isReviewDone = \App\Models\Review::where('reviewed_user_id', $userId)
                    ->where('post_id', $application->post_id)
                    ->exists() ? 1 : 0;

                $reviews = \App\Models\Review::with('user')->where('reviewed_user_id', $userId)->get();

                $application->is_reviewed_done = $isReviewDone;
                $application->reviews = $reviews;

                return $application;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Job application retrieved successfully',
                'data' => $applications->toArray(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching job candidates',
                'data' => []
            ], 500);
        }
    }

    public function businessJobApplication($applicationId)
    {
        try {
            $application = JobApplication::with('user')
                ->where('id', $applicationId)
                ->first();

            if (!$application) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Job application not found',
                    'data' => []
                ], 404);
            }


            // Convert application to array and add previous reviews
            $data = $application->toArray();

            return response()->json([
                'status' => 200,
                'message' => 'Job application retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching job application',
                'data' => []
            ], 500);
        }
    }



    public function uploadFiles(UploadedFile $file, $path = 'images/jobApplication/documents')
    {
        // Set path for storing the uploaded files
        $path = public_path($path);

        // Create directory if it does not exist
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Generate a unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        // Move file to the designated directory
        $file->move($path, $filename);

        // Return the relative path to access the file
        return 'images/jobApplication/documents/' . $filename;
    }


}
