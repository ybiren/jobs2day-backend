<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseNotification;

class FirebaseNotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotification $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'device_token' => 'required',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $deviceToken = $request->device_token;
        $title = $request->title;
        $body = $request->body;

        $response = $this->firebaseService->sendNotification($deviceToken, $title, $body);

        return response()->json($response);
    }
}
