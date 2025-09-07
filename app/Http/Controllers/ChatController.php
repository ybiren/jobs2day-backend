<?php

namespace App\Http\Controllers;


use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatRoomUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function createRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'nullable|exists:users,id|different:user_id',
            'room_name' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $receiver = User::find($request->receiver_id) ?? null;

        $chatRoom = ChatRoom::create([
            'name' => $request->room_name,
            'name_slug' => Str::slug($request->room_name),
            'user_id' => $user->id,
            'created_by' => $user->id,
            'receiver_id' => $request->receiver_id ?? null,
        ]);
        ChatRoomUsers::updateOrCreate([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
        ]);

        if (!empty($receiver)) {
            ChatRoomUsers::updateOrCreate([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $receiver,
            ]);
        }

        $message = "Room {$chatRoom->name} Created Successfully";

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => ChatRoom::find($chatRoom->id),
        ]);
    }

    public function ChatsList(Request $request)
    {
        $chatRooms = ChatRoom::query()
            ->has('chatMessages')
            ->where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere('receiver_id', Auth::id());
            })
            ->withCount([
                'unreadChatMessages as unread_chat_messages_count' => function($query) {
                    $query->where('receiver_id', Auth::id());
                }
            ])
            ->with(['chatListLastMessage']) // Load last message for sorting
            ->orderByDesc(ChatMessage::select('created_at')
                ->whereColumn('chat_messages.chat_room_id', 'chat_rooms.id')
                ->latest()
                ->limit(1)
            ) // Order by latest message timestamp
            ->get()
            ->map(function ($chatRoom) {
                // Get the other user who is not the authenticated user
                $other_user = $chatRoom->user_id == Auth::id()
                    ? User::find($chatRoom->receiver_id)
                    : User::find($chatRoom->user_id);

                return [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'name_slug' => $chatRoom->name_slug,
                    'unread_chat_messages_count' => $chatRoom->unread_chat_messages_count,
                    'user' => $other_user, // Keep only the other user
                    'last_message' => $chatRoom->chatListLastMessage,
                ];
            });






        return response()->json([
            'status' => true,
            'message' => 'Chats Lists',
            'data' => $chatRooms,
        ]);
    }

    public function ChatHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $perPage = $request->per_page ?? 1000; // Default 10 messages per page
        $receiver = User::find($request->receiver_id);
        $user = auth()->user();

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

        // **Check if ChatRoom Exists**
        if (!$chatRoom) {
            return response()->json([
                'status' => false,
                'message' => 'No chat history found',
                'data' => []
            ]);
        }

        // Mark messages as read
        ChatMessage::where('chat_room_id', $chatRoom->id)
            ->where('receiver_id', $user->id)
            ->update(['is_read' => 1]);

        // Paginate messages
        $messages = ChatMessage::where('chat_room_id', $chatRoom->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Chat History',
            'data' => [
                'current_page' => $messages->currentPage(),
                'total_messages' => $messages->total(),
                'per_page' => $messages->perPage(),
                'next_page_url' => $messages->nextPageUrl(),
                'prev_page_url' => $messages->previousPageUrl(),
//                'messages' => $messages->items(),
                'messages' => collect($messages->items())->map(function ($msg) {
                    $clone = clone $msg;

                    $clone->created_at = $clone->created_at->timezone('UTC');
                    $clone->updated_at = $clone->updated_at->timezone('UTC');

                    return $clone;
                }),

//                'messages' => collect($messages->items())->map(function ($msg) {
//                    $msg->created_at = \Carbon\Carbon::parse($msg->created_at)->timezone('Asia/Jerusalem')->toDateTimeString();
//                    return $msg;
//                }),
            ],
        ]);
    }



    public function uploadFile(Request $request, $path = 'images/chat/files')
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required', // Max 10MB
        ]);

        // Get the uploaded file
        $file = $request->file('file');

        // Check if the file is valid
        if ($file && $file->isValid()) {
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

            // The relative path for accessing the file
            $filePath = 'images/chat/files/' . $filename;

            // Return the response with file information
            return response()->json([
                'status' => true,
                'message' => 'File Uploaded',
                'data' => $filePath, // The relative path to the file
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid file',
        ], 400);
    }

}
