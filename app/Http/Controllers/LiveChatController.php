<?php

namespace App\Http\Controllers;

use App\Models\LiveMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LiveChatController extends Controller
{
    public function index(): View
    {
        $messages = LiveMessage::with('user')->latest()->limit(50)->get()->reverse();
        return view('public.live-chat', compact('messages'));
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Please log in to send messages.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $message = LiveMessage::create([
            'user_id' => $request->user()->id,
            'message' => trim($validator->validated()['message']),
        ]);

        $message->load('user');

        return response()->json([
            'id' => $message->id,
            'message' => $message->message,
            'user' => $message->user->name,
            'avatar' => mb_strtoupper(mb_substr($message->user->name, 0, 1)),
            'time' => $message->created_at->diffForHumans(),
            'mine' => true,
        ]);
    }

    public function fetch(Request $request): JsonResponse
    {
        $after = $request->input('after', 0);

        $messages = LiveMessage::with('user')
            ->when($after, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->limit(50)
            ->get();

        return response()->json([
            'messages' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'user' => $m->user->name,
                'avatar' => mb_strtoupper(mb_substr($m->user->name, 0, 1)),
                'time' => $m->created_at->diffForHumans(),
                'mine' => $request->user()?->id === $m->user_id,
            ]),
        ]);
    }
}