<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatRoomUsers;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpParser\Node\Scalar\String_;
use PHPSocketIO\SocketIO;
use Workerman\Worker;

class PhpSocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start';
    protected $firebaseService;


    public function __construct(FirebaseNotificationService $firebaseService)
    {
        parent::__construct(); // Ensures the parent class is initialized
        $this->firebaseService = $firebaseService;
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chat server sockets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        date_default_timezone_set('Asia/Jerusalem');
        try {
            if (env('APP_ENV') !== 'local') {
                $context = [
                    'ssl' => [
//                        'local_cert' => env('LOCAL_CERT'), // Path to your SSL certificate
//                        'local_pk' => env('LOCAL_PK'),     // Path to your private key
                        'local_cert' => '/etc/letsencrypt/live/chat.sharunity.com/fullchain.pem',
                        'local_pk' => '/etc/letsencrypt/live/chat.sharunity.com/privkey.pem',
                        'verify_peer' => false
                    ]
                ];

                $io = new SocketIO('9991', $context);
                $io->worker->transport = 'ssl';
            } else {
                $io = new SocketIO('9991');
            }

            $onlineUsers = [];
            $io->on('connection', function ($socket) use ($io, &$onlineUsers) {
                echo "connected " . $socket->id . "\n";
                Log::info("New Client Connected: " . $socket->id);


                $socket->on('join-room', function ($data) use ($socket, $io, &$onlineUsers) {
                    try {
                        echo "connected " . $socket->id . "\n";

                        $validator = Validator::make($data, [
                            'room_id' => 'required_without:user_id|exists:chat_rooms,id',
                            'user_id' => 'required_without:room_id|exists:users,id',
                            'receiver_id' => 'nullable|exists:users,id',
                        ]);
                        if ($validator->fails()) {
                            $io->to($socket->id)->emit('room-join', [
                                'success' => false,
                                'message' => $validator->errors()->first()
                            ]);
                            return;
                        }

                        if (!empty($data['room_id'])) {
                            $chatRoom = ChatRoom::find($data['room_id']);
                            $socket->join((string)$chatRoom->id);
                            $onlineUsers[$data['user_id']] = $socket->id; // Track user as online
                            echo "joined " . $socket->id . "\n";
                            $io->to($socket->id)->emit('join-room', [
                                'success' => true,
                                'message' => 'Room ' . $chatRoom->id . ' joined',
                                'room_id' => $chatRoom->id
                            ]);
                            return;
                        }

                        $chatRoomData = ['user_id' => $data['user_id']];
                        $roomName = $data['user_id'];

                        if (!empty($data['receiver_id'])) {
                            $roomName = $data['user_id'] . '-' . $data['receiver_id'];
                            $chatRoomData['receiver_id'] = $data['receiver_id'];

                            $receiver = User::find($data['receiver_id']);
                            $user = User::find($data['user_id']);

                            $chatRoom = ChatRoom::query()
                                ->where(function ($query) use ($user, $receiver) {
                                    $query->where(function ($subQuery) use ($user, $receiver) {
                                        $subQuery->where('user_id', $user->id)
                                            ->where('receiver_id', $receiver->id);
                                    })->orWhere(function ($subQuery) use ($user, $receiver) {
                                        $subQuery->where('user_id', $receiver->id)
                                            ->where('receiver_id', $user->id);
                                    });
                                })->first();
                        }
                        $chatRoomData['name'] = $roomName;
                        $chatRoomData['name_slug'] = Str::slug($roomName);

                        if (empty($chatRoom)) {
                            $chatRoom = ChatRoom::create($chatRoomData);
                        }
                        ChatRoomUsers::updateOrCreate([
                            'chat_room_id' => $chatRoom->id,
                            'user_id' => $data['user_id'],
                        ]);
                        if (!empty($receiver)) {
                            ChatRoomUsers::updateOrCreate([
                                'chat_room_id' => $chatRoom->id,
                                'user_id' => $receiver->id,
                            ]);
                        }

                        $socket->join((string)$chatRoom->id);
                        $onlineUsers[$data['user_id']] = $socket->id; // Track user as online
                        echo "joined " . $socket->id . "\n";
                        $io->to($socket->id)->emit('join-room', [
                            'success' => true,
                            'message' => 'Room ' . $chatRoom->id . ' joined',
                            'room_id' => $chatRoom->id
                        ]);
                    } catch (\Exception $e) {
                        Log::emergency($e->getMessage());
                        echo $e->getMessage() . $e->getLine() . "\n";
                        $io->to($socket->id)->emit('join-room', [
                            'success' => false,
                            'message' => $e->getMessage()
                        ]);
                    }
                });

                $socket->on('send-message', function ($data) use ($socket, $io, &$onlineUsers) {

                    $validator = Validator::make($data, [
                        'room_id' => 'required|integer|exists:chat_rooms,id',
                        'type' => 'required|integer',
                        'message' => 'required_without:file_path|string',
                        'file_path' => 'nullable',
                        'sender_id' => 'required|integer|exists:users,id',
                        'receiver_id' => 'nullable|integer|exists:users,id'
                    ]);
                    if ($validator->fails()) {
                        $io->to($socket->id)->emit('send-message', [
                            'success' => false,
                            'message' => $validator->errors()->first()
                        ]);
                        return;
                    }
                    try {
                        $findChatRoom = ChatRoom::find($data['room_id']);
                        if (!$findChatRoom) {
                            $io->to($socket->id)->emit('send-message', [
                                'success' => false,
                                'message' => 'Chat room not found'
                            ]);
                            return;
                        }
                        $roomId = (string)$findChatRoom->id;
                        $receiverOnline = false;
                        if (!empty($clientsInRoom)) {
                            foreach ($clientsInRoom as $socketId => $val) {
                                if (isset($onlineUsers[$socketId]) && $onlineUsers[$socketId] == $data['receiver_id']) {
                                    $receiverOnline = true;
                                    break;
                                }
                            }
                        }
                        $chatMsgData = [
                            'chat_room_id' => $roomId,
                            'type' => $data['type'],
                            'sender_id' => $data['sender_id'],
                            'receiver_id' => $data['receiver_id'] ?? null,
                            'message' => $data['message'],
                            'is_read' => '1',
                            'file_name' => $data['file_name'] ?? null,
                            'file_path' => $data['file_path'] ?? null,
                            'file_size' => $data['file_size'] ?? null
                        ];
                        $message = ChatMessage::create($chatMsgData);

                        $messageData = [
                            'success' => true,
                            'message' => 'התקבלה הודעה חדשה',
                            'messageText' => $data['message'],
                            'data' => ChatMessage::with(['sender', 'receiver'])->find($message->id)
                        ];


                        // Send message to all users in the room
                        $io->to($roomId)->emit('new-message', $messageData);
                        Log::info("Checking furqan if receiver is offline for push notification. Receiver ID: {$data['receiver_id']}, Room ID: {$data['room_id']}");

                        $receiverSocketId = null;
                        foreach ($onlineUsers as $socketId => $userId) {
                            if ($userId == $data['receiver_id']) {
                                $receiverSocketId = $socketId;
                                break;
                            }
                        }


                        if (!empty($clientsInRoom)) {
                            foreach ($clientsInRoom as $socketId => $val) {
                                if (isset($onlineUsers[$socketId]) && $onlineUsers[$socketId] == $data['receiver_id']) {
                                    $receiverSocketId = $socketId;
                                    break;
                                }
                                Log::info("Clients in Room {$roomId}:", [$socketId => $val]); // Fix logging format
                            }
                        }

                        if (!$receiverOnline) {
                            $this->sendPushNotification($data['receiver_id'], $data['message'], $roomId);
                            Log::info("Notification sent to Receiver ID: {$data['receiver_id']}, Room ID: {$roomId}");
                        }

                        $io->to($socket->id)->emit('send-message', [
                            'success' => true,
                            'message' => 'Message sent successfully',
                        ]);
                    } catch (\Exception $e) {
                        Log::emergency($e->getMessage());
                        echo $e->getMessage() . $e->getLine() . "\n";
                        $io->to($socket->id)->emit('send-message', [
                            'success' => false,
                            'message' => $e->getMessage()
                        ]);
                    }
                });

                $socket->on('disconnect', function () use ($socket, &$onlineUsers) {
                    echo "disconnected " . $socket->id . "\n";
                    Log::info("Client Disconnected: " . $socket->id);
                    unset($onlineUsers[array_search($socket->id, $onlineUsers)]); // Remove user by user_id
                });


            });
            Worker::runAll();
        } catch (\Exception $e) {
            Log::emergency($e->getMessage());
            echo $e->getMessage() . $e->getLine() . "\n";
        }
    }
    private function sendPushNotification($userId, $message, $roomID) {
        Log::info("Starting push notification process for User ID: {$userId}, Room ID: {$roomID}");

        // First, mark the last message as unread (0) since we're sending a notification
        ChatMessage::where('chat_room_id', $roomID)
            ->where('receiver_id', $userId)
            ->latest()
            ->limit(1)
            ->update(['is_read' => 0]);
        // Get receiver with their device info
        $notificationReceiver = User::with('userDevices')->find($userId);

        if (!$notificationReceiver || $notificationReceiver->userDevices->isEmpty()) {
            Log::warning("No device token found for user ID: {$userId}");
            return;
        }

        $receiverDeviceToken = $notificationReceiver->userDevices->first()->device_token;


        $chatData = ChatRoom::query()
            ->has('chatMessages')
            ->where('id', $roomID)
            ->withCount('unreadChatMessages')
            ->with(['chatListLastMessage'])
            ->get()
            ->map(function ($chatRoom) {
                $sender_user = User::find($chatRoom->chatListLastMessage->sender_id);
                return [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'name_slug' => $chatRoom->name_slug,
                    'unread_chat_messages_count' => $chatRoom->unread_chat_messages_count,
                    'user' => $sender_user, // Keep only the other user
                    'last_message' => $chatRoom->chatListLastMessage,
                ];
            });
        // Prepare notification data to match your working example
        $data = [
            'sender_id' => $userId,
            'fcm_token' => $receiverDeviceToken,
            'type' => 'Message_sent',
            'title' => 'התקבלה הודעה חדשה',
            'body' => !empty($message) ? $message : 'התקבלה תמונה',
            'data' => [
                'id' => $roomID,
                'type' => 'Message_sent',
                'chat' => $chatData ? json_encode($chatData->toArray()) : '{}',            ]
        ];

        // Send notification through Firebase service
        $response = $this->firebaseService->sendNotification($data);

        // Save notification in database
        Notification::create([
            'user_id' => $userId,
            'title' => 'התקבלה הודעה חדשה',
            'body' => !empty($message) ? $message : 'התקבלה תמונה',
            'payload' => json_encode([
                'type' => 'Message_sent',
                'id' => $roomID,
                'chat' => ChatRoom::with('lastMessage')->find($roomID)
            ]),
            'status' => 'pending',
        ]);
    }

}

