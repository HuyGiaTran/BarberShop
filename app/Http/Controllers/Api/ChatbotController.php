<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->input('message');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình API Key cho Chatbot.',
            ], 500);
        }

        // Lấy dữ liệu động từ DB
        $services = Service::all()->map(function ($s) {
            return "- {$s->name}: " . number_format($s->price, 0, ',', '.') . " VNĐ";
        })->implode("\n");

        $barbers = Barber::all()->map(function ($b) {
            return "- {$b->name}";
        })->implode("\n");

        // Xây dựng System Prompt
        $systemPrompt = "Bạn là nhân viên tư vấn nhiệt tình, lịch sự của Barber Shop tên là 'Gentlemen's Barber'.
Dưới đây là thông tin về cửa hàng:
- Giờ mở cửa: 10:00 AM - 8:00 PM hàng ngày.
- Danh sách Dịch vụ và Giá tiền:
{$services}
- Đội ngũ Barber chuyên nghiệp của chúng tôi:
{$barbers}

Nhiệm vụ của bạn: Trả lời ngắn gọn, thân thiện và chính xác câu hỏi của khách hàng dựa trên thông tin trên. Nếu khách hỏi những thông tin không có, hãy lịch sự thông báo không rõ hoặc khuyên họ liên hệ trực tiếp. Đừng tự bịa thông tin.";

        // Chuẩn bị payload gọi API Gemini
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nCâu hỏi của khách hàng: " . $userMessage]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $replyText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi không thể trả lời lúc này.';
                
                return response()->json([
                    'success' => true,
                    'reply' => $replyText,
                ], 200);
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Chatbot đang bảo trì. Vui lòng thử lại sau.',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối đến Chatbot.',
            ], 500);
        }
    }
}
