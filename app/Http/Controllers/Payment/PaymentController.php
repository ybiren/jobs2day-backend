<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;


class PaymentController extends Controller
{

    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    private const TRANZILA_URL = 'https://api.tranzila.com/v1/transaction/credit_card/create';
//    private const TRANZILA_URL = 'https://api.tranzila.com/v1/pr/create';
    private const TRANZILA_SUPPLIER = 'fxpjob2day';
    private const TRANZILA_TERMINAL = 'fxpjob2day';
    private const TRANZILA_SUCCESS_URL = 'https://your-domain.com/payment/success';
    private const TRANZILA_FAILURE_URL = 'https://your-domain.com/payment/failure';
    private const TRANZILA_PUBLIC_KEY = 'mTGsJzMDC0ErzmOe9ENdw7iYe1y8eVZHb90BglI58vGN9TjwnDNBOB7tSw0tmKfpZcQgHmIKtsP';
    private const TRANZILA_PRIVATE_KEY = 'v4oM8oWd27';



    public function myCardDetails()
    {
        $lastTransaction = Transaction::where('user_id', auth()->id())->orderBy('id', 'desc')->first();

        if (!$lastTransaction) {
            return response()->json([
                'status' => 404,
                'message' => 'No previous card information found',
                'data' => (object)[],
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Previous Card Information',
            'data' => $lastTransaction,
        ], 200);
    }

    public function Jobpayment(Request $request)
    {
        try {
            Log::info('Payment process started.');

            $timestamp = time();
            Log::info("Generated Timestamp: $timestamp");

            $nonce = bin2hex(random_bytes(16));
            Log::info("Generated Nonce: $nonce");

            $appKey = self::TRANZILA_PUBLIC_KEY;
            $secret = self::TRANZILA_PRIVATE_KEY;

            $hmacKey = $secret . $timestamp . $nonce;
            $accessKey = hash_hmac('sha256', $appKey, $hmacKey);
            Log::info("Generated Access Key: $accessKey");

            $client = User::find(auth()->id());
            $orderId = 'Job2Day-'.$client->id.'-'.$timestamp;

            $clientId = (string)$client->id; // Ensure it's a string
            $clientId = strlen($clientId) < 5 ? str_pad($clientId, 5, '0', STR_PAD_LEFT) : $clientId;

            if (!preg_match('/^[0-9A-Za-z]{5,9}$/', $clientId)) {
                $clientId = 'client_' . bin2hex(random_bytes(4)); // Generate a valid ID
            }

            $clientPhoneNumber = $client->phone ?? '000000000'; // Default if empty
            if (!preg_match('/^[0-9]{3,15}$/', $clientPhoneNumber)) {
                $clientPhoneNumber = '000000000'; // Fallback if invalid
            }

            $payload = [
                "terminal_name" => self::TRANZILA_TERMINAL,
                "txn_currency_code" => "ILS",
                "txn_type" => "debit",
                "expire_month" => (int)$request->input('expire_month'),
                "expire_year" => (int)$request->input('expire_year'),
                "cvv" => $request->input('cvv'),
                "card_holder_id" => str_replace('-', '', $client->id.'000'),                 "card_number" => $request->input('card_number'),
                "payment_plan" => (int)$request->input('payment_plan', 1),
                "installments_number" => (int)$request->input('installments_number', 1),
                "first_installment_amount" => (float)$request->input('amount'),
                "other_installments_amount" => (float)$request->input('amount'),
                "client" => [
                    "name" => $client->name ?? 'Job2Day Client',
                    "contact_person" => $client->phone ?? $client->email,
                    "id" => $clientId,
                    "email" => $client->email ?? 'emailnotavailable@job2day.com',
                    "phone_number" => $clientPhoneNumber,
                ],
                "user_defined_fields" => [["name" => "order_id", "value" => $orderId]],
                "remarks" => "Payment for order #" . $orderId,
                "response_language" => "english"
            ];

            Log::info('Payload prepared successfully.', ['payload' => $payload]);

            $headers = [
                'User-Agent' => 'Mozilla/5.0',
                'X-Tranzila-Api-Access-Token' => $accessKey,
                'X-Tranzila-Api-App-Key' => $appKey,
                'X-Tranzila-Api-Nonce' => $nonce,
                'X-Tranzila-Api-Request-Time' => $timestamp,
                'Content-Type' => 'application/json',
            ];

            Log::info('Request headers prepared.', ['headers' => $headers]);

            Log::info('Sending request to Tranzila...');
            $response = Http::withHeaders($headers)->post(self::TRANZILA_URL, $payload);
            $responseData = $response->json();

            Log::info('Tranzila Response:', [
                'status' => $response->status(),
                'body' => $responseData
            ]);

            if (isset($responseData['error_code']) && $responseData['error_code'] == 0) {
                DB::beginTransaction();

                \App\Models\Transaction::create([
                    'payment_type' => 'job-Payment',
                    'type_id' => $request->input('type_id'),
                    'user_id' => auth()->id(),
                    'amount' => $request->input('amount'),
                    'status' => 'success',
                    'response' => json_encode($responseData),
                    'expdate' => $request->input('expire_month') . '/' . $request->input('expire_year'),
                    'cvv' => $request->input('cvv'),
                    'ccno' => substr($request->input('card_number'), -4),
                    'cred_type' => 'debit'
                ]);
                $job = JobApplication::find($request->input('type_id'));

                $jobApplicationUser = JobApplication::with(['post', 'user.companyDetails'])->find($job->id);
                if ($jobApplicationUser){
                $receiverMail = $jobApplicationUser->user->email;
                try {
                    Log::info("Attempting to send email to: " . $receiverMail);
                    Log::info("emaildata: ", $jobApplicationUser->toArray());

                    Mail::send('emails.jobApplicationApproved', ['data' => $jobApplicationUser], function ($message) use ($receiverMail) {
                        $message->to($receiverMail)
                            ->subject('!מזל טוב! בקשת העבודה שלך אושרה');
                    });
                    Log::info("Email successfully sent to: " . $receiverMail);
                } catch (\Exception $e) {
                    Log::error("Error sending approval email", [
                        'error' => $e->getMessage(),
                        'receiver' => $receiverMail
                    ]);
                }
                }

                if ($job) {
                    if ($job->post->remaining_positions == 0) {
                        return response()->json([
                            'status' => 404,
                            'message' => 'המשרות אוישו במלואן',
                            'data' => (object)[],
                        ], 404);
                    }
                        if ($job->post->remaining_positions == 1) {
                        $job->post->status = '2'; // Close the job
                        $job->post->save();
                    } else {
                        $job->post->decrement('remaining_positions');
                    }
                    $job->status = '1';
                    $job->save();
                    $jobUser = $job->user_id;

                    $this->sendNotificationjobRequestAction($job, $job->status, $jobUser);

                }


                DB::commit();
            }

            return response()->json([
                'status' => $response->status(),
                'response' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Payment process failed.', ['error' => $e->getMessage()]);
            DB::rollBack();
            return response()->json(['error' => 'Payment failed', 'message' => $e->getMessage()], 500);
        }
    }

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
                'title' => " בקשת עבודה  $action",
                'body' => $notificationSender->first_name . " $action  בקשת העבודה שלך ",
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

}
































//public function testPayment(Request $request)
//{
//    Log::info('Payment process started.');
//
//    $timestamp = time();
//    Log::info("Generated Timestamp: $timestamp");
//
//    $nonce = bin2hex(random_bytes(16)); // Generate unique nonce
//    Log::info("Generated Nonce: $nonce");
//
//    $appKey = self::TRANZILA_PUBLIC_KEY;
//    $secret = self::TRANZILA_PRIVATE_KEY;
//
//    // Generate HMAC SHA256 Signature
//    $hmacKey = $secret . $timestamp . $nonce;
//    $accessKey = hash_hmac('sha256', $appKey, $hmacKey);
//    Log::info("Generated Access Key: $accessKey");
//
//    $amount = $request->input('amount', 1);
//    Log::info("Received Payment Amount: $amount");
//
//    $payload = [
//        "terminal_name" => self::TRANZILA_TERMINAL,
//        "txn_currency_code" => "ILS",
//        "txn_type" => "debit",
//        "expire_month" => 7, // Extracted from "0725"
//        "expire_year" => 2025,
//        "cvv" => "111",
//        "card_holder_id" => null,
//        "card_number" => "4580458045804580",
//        "payment_plan" => 1,
//        "installments_number" => 1,
//        "first_installment_amount" => null,
//        "other_installments_amount" => $amount,
//        "reference_txn_id" => null,
//        "client" => [
//            "external_id" => null,
//            "name" => "test_client",
//            "contact_person" => "John Doe",
//            "id" => "client1",
//            "email" => "johndoe@example.com",
//            "phone_country_code" => "972",
//            "phone_area_code" => "3",
//            "phone_number" => "501234567",
//            "address_line_1" => "123 Street Name",
//            "address_line_2" => "Suite 456",
//            "city" => "Tel Aviv",
//            "country_code" => "IL",
//            "zip" => "12345"
//        ],
//        "user_defined_fields" => [
//            [
//                "name" => "order_id",
//                "value" => "12345"
//            ]
//        ],
//        "remarks" => "Payment for order #12345",
//        "response_language" => "english",
//        "created_by_user" => null,
//        "created_by_system" => null
//    ];
//
//    Log::info('Payload prepared successfully.', ['payload' => $payload]);
//
//    $headers = [
//        'User-Agent' => 'Mozilla/5.0',
//        'X-Tranzila-Api-Access-Token' => $accessKey,
//        'X-Tranzila-Api-App-Key' => $appKey,
//        'X-Tranzila-Api-Nonce' => $nonce,
//        'X-Tranzila-Api-Request-Time' => $timestamp,
//        'Content-Type' => 'application/json',
//    ];
//
//    Log::info('Request headers prepared.', ['headers' => $headers]);
//
//    // Sending request to Tranzila
//    Log::info('Sending request to Tranzila...');
//    $response = Http::withHeaders($headers)->post(self::TRANZILA_URL, $payload);
//
//    Log::info('Tranzila Response:', [
//        'status' => $response->status(),
//        'headers' => $response->headers(),
//        'body' => $response->body()
//    ]);
//
//    Log::info('Payment process completed.');
//
//    return response()->json([
//        'status' => $response->status(),
//        'response' => $response->json() ?: $response->body()
//    ]);
//}
