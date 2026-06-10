<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Chua trien khai endpoint chatbot.',
        ], 501);
    }
}
