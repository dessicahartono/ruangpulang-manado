<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use App\Services\Chat\ChatbotService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function send(ChatMessageRequest $request, ChatbotService $chatbot): JsonResponse
    {
        return response()->json($chatbot->reply($request->conversation()));
    }
}
