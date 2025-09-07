<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SendNotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    public function sendNotification(Request $request)
    {
        $response = $this->firebaseService->sendNotification($request);
        return response()->json($response);

    }

    public function getNotification(Request $request)
    {
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1'
        ]);

        $limit = $validated['limit'] ?? 1000; // Default limit

        // Fetch paginated notifications first
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        // Filter out 'Message_sent' notifications
        $filteredNotifications = $notifications->getCollection()->reject(function ($notification) {
            return optional(json_decode($notification->payload))->type === 'Message_sent';
        });

        // Update status of fetched notifications
        Notification::whereIn('id', $filteredNotifications->pluck('id'))->update(['status' => 'sent']);

        // Attach sender info to notifications
        $filteredNotifications->transform(function ($notification) {
            $payload = json_decode($notification->payload, true);
            if (isset($payload['sender_id'])) {
                $sender = User::find($payload['sender_id']);
                if ($sender) {
                    $notification->user = [
                        'id' => $sender->id,
                        'first_name' => $sender->first_name,
                        'last_name' => $sender->last_name,
                        'profile_image' => $sender->profile_image
                    ];
                }
            }
            return $notification;
        });

        return response()->json([
            'status' => true,
            'message' => 'Notifications List',
            'data' => [
                'current_page' => $notifications->currentPage(),
                'total_notifications' => $filteredNotifications->count(),
                'per_page' => $notifications->perPage(),
                'next_page_url' => $notifications->nextPageUrl(),
                'prev_page_url' => $notifications->previousPageUrl(),
                'notifications' => $filteredNotifications->values(),
            ]
        ]);
    }

}
