<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireOldJobApplications extends Command
{
    protected $firebaseService;

    protected $signature = 'jobapplications:expire';
    protected $description = 'Automatically expire job applications older than 7 days with status 0';

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    public function handle()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // Fetch all expired applications
        $expiredApplications = JobApplication::where('status', '0')
            ->where('created_at', '<', $sevenDaysAgo)
            ->get(); // Get the actual records instead of updating directly

        $count = 0;
        foreach ($expiredApplications as $job) {
            $job->update(['status' => '2']); // Update status to "Rejected"
            $this->sendNotificationjobRequestAction($job, 'Rejected');
            $count++;
        }

        Log::info("Expired Job Applications: " . $count);
        $this->info("Successfully updated $count job applications.");
    }

    private function sendNotificationjobRequestAction($job, $type)
    {
        try {
            $post = Post::find($job->post_id);
            $notificationReceiver = User::with('userDevices')->find($job->user_id);

            if (!$notificationReceiver || $notificationReceiver->userDevices->isEmpty()) {
                Log::warning("No device token found for user ID: {$job->user_id}");
                return;
            }

            $receiverDeviceToken = $notificationReceiver->userDevices->first()->device_token;

            Log::info("Sending notification to device with token: {$receiverDeviceToken}");

            $data = [
                'sender_id' => null, // No auth()->id() in console, use null or system ID
                'fcm_token' => $receiverDeviceToken,
                'type' => "Application_$type",
                'title' => " הגשת מועמדות למשרה $type",
                'body' => " בקשת העבודה שלך $type בוצע אוטומטית . ",
                'data' => [
                    'type' => "Application_$type",
                    'id' => $post->id ?? null,
                ],
            ];

            $response = $this->firebaseService->sendNotification($data);

            Notification::create([
                'user_id' => $notificationReceiver->id,
                'title' => " בקשת עבודה  $type",
                'body' => " בקשת העבודה שלך $type בוצע אוטומטית. ",
                'payload' => json_encode([
                    'type' => "Application_$type",
                    'id' => $post->id ?? null,
                ]),
                'status' => 'pending',
            ]);

            Log::info("Firebase notification sent for post ID: " . ($post->id ?? 'N/A'), ['response' => $response]);

        } catch (\Exception $e) {
            Log::error("Error sending notification for job application ID: {$job->id}", ['error' => $e->getMessage()]);
        }
    }
}
