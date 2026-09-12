<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function show(): View
    {
        return view('chatbot.index');
    }

    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $result = $chatbot->handle($user, $validated['message']);

        ChatMessage::create([
            'user_id' => $user->id,
            'role_at_time' => $user->role,
            'prompt' => $validated['message'],
            'response' => $result['response'],
            'was_rejected' => $result['rejected'],
            'rejection_reason' => $result['reason'],
        ]);

       return response()->json([
    'reply' => $result['response'],
    'rejected' => $result['rejected'],
    'reason' => $result['reason'],
]);
}}