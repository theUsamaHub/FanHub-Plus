<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageBroadcast;
use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LiveChatController extends Controller
{
    public function index(Request $request): View
    {
        $channels = ChatChannel::where('is_active', true)->orderBy('sort_order')->get();
        $activeSlug = $request->query('channel', $channels->first()?->slug ?? 'general');
        $activeChannel = $channels->firstWhere('slug', $activeSlug) ?? $channels->first();

        $messages = $activeChannel
            ? ChatMessage::with('user:id,name')->where('channel_id', $activeChannel->id)
                ->orderByDesc('created_at')->limit(100)->get()->reverse()->values()
            : collect();

        $onlineUsers = User::where('id', '!=', $request->user()->id)->inRandomOrder()->limit(12)->get(['id', 'name']);
        $onlineUsers->prepend($request->user()->only(['id', 'name']));

        return view('live-chat.index', compact('channels', 'activeChannel', 'messages', 'onlineUsers'));
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'integer', 'exists:chat_channels,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = ChatMessage::create([
            'channel_id' => $validated['channel_id'],
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $message->load('user:id,name');

        try {
            broadcast(new ChatMessageBroadcast($message))->toOthers();
        } catch (\Throwable $e) {
            // Broadcast may fail if Reverb is not running — message is still saved
        }

        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'user' => ['id' => $message->user->id, 'name' => $message->user->name],
            'created_at' => $message->created_at->toISOString(),
        ]);
    }
}
