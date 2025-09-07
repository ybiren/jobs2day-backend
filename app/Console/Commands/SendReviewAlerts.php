<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\ReviewsNotification;
use App\Models\Notification;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendReviewAlerts extends Command
{
    protected $signature = 'reviews:send-alerts';
    protected $description = 'Send review reminder notifications for entries older than 12 hours and delete them';
    public function __construct(
        private FirebaseNotificationService $firebaseService
    ) {
        parent::__construct();
    }
    public function handle()
    {
        $this->info('Starting review alerts processing...');
        Log::info('Review alerts command started');

        DB::transaction(function () {
            $reviewNotifications = ReviewsNotification::where('created_at', '<=', Carbon::now()->subHours(12))
                ->with([
                    'user.userDevices',
                    'post' => function($query) {
                        $query->select('id', 'job_role', 'user_id', 'created_at')
                            ->with(['user' => function($q) {
                                $q->select('id', 'first_name', 'last_name');
                            }]);
                    }
                ])
                ->get();

            if ($reviewNotifications->isEmpty()) {
                $this->info('No review alerts to send.');
                Log::info('No review alerts to send');
                return;
            }

            $this->info('Found '.$reviewNotifications->count().' review alerts to send');

            foreach ($reviewNotifications as $notification) {
                $user = $notification->user;
                $post = $notification->post;

                if (!$user || !$post) {
                    Log::warning("Missing user or post data", [
                        'notification_id' => $notification->id,
                        'user_id' => $notification->user_id,
                        'post_id' => $notification->post_id
                    ]);
                    continue;
                }

                if ($user->userDevices->isNotEmpty()) {
                    foreach ($user->userDevices as $device) {
                        try {
                            // Improved name display logic
                            $senderName = $post->user->first_name;
                            if (!empty($post->user->last_name)) {
                                $senderName .= ' ' . $post->user->last_name;
                            }
                            $senderName = $senderName ?: 'Unknown';
                            $mypost = Post::find($post->id);

                            $notificationPayload = [
                                'type' => "Review_Alert",
                                'id' => $post->id,
                                'sender_id' => $user->id
//                                'data' => $post->toNotificationPayload() // Use your custom method instead
                            ];

                            $response = $this->firebaseService->sendNotification([
                                'sender_id' => $user->id,
                                'fcm_token' => $device->device_token,
                                'type' => 'Review_Alert',
                                'title' => 'נשמח אם תשאיר פידבק על העבודה שבוצעה',
                                'body' => 'נשמח אם תדרג/י את העבודה שביצעת לאחרונה: '.$post->job_role,
                                'data' => $notificationPayload
                            ]);

                            Notification::create([
                                'user_id' => $user->id,
                                'title' => 'נשמח אם תשאיר פידבק על העבודה שבוצעה',
                                'body' => 'נשמח אם תדרג/י את העבודה שביצעת לאחרונה: '.$post->job_role,
                                'payload' => json_encode($notificationPayload),
                                'status' => 'pending',
                            ]);

                            Log::info("Review alert sent", [
                                'post_id' => $post->id,
                                'user_id' => $user->id,
                                'device_id' => $device->id
                            ]);

                        } catch (\Exception $e) {
                            Log::error("Failed to send review alert", [
                                'user_id' => $user->id,
                                'post_id' => $post->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                } else {
                    Log::warning("No device token found for user ID: {$user->id}", [
                        'post_id' => $post->id
                    ]);
                }

                $notification->delete();
            }

            $this->info('Review alerts processing completed');
            Log::info('Review alerts command completed', [
                'count' => $reviewNotifications->count(),
                'processed' => $reviewNotifications->count() - $reviewNotifications->whereNull('deleted_at')->count()
            ]);
        });
    }
}
