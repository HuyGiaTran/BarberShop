<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    private AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:5120',
            'history' => 'nullable|json',
        ]);

        $userMessage = $request->input('message', '');
        $historyJson = $request->input('history', '[]');
        $history = json_decode($historyJson, true) ?? [];
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Chưa cấu hình API Key cho Chatbot.'], 500);
        }

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng Chatbot.'], 401);
        }

        $services = Service::all()->map(fn($s) => "- ID {$s->id}: {$s->name} (" . number_format($s->price, 0, ',', '.') . " VNĐ)")->implode("\n");
        $barbers = Barber::all()->map(fn($b) => "- ID {$b->id}: {$b->name}")->implode("\n");

        $systemPrompt = "Bạn là trợ lý AI chuyên nghiệp của Barber Shop 'Gentlemen's Barber'.
Khách hàng hiện tại là: {$user->name} (User ID: {$user->id}, SĐT: {$user->phone}).
Giờ mở cửa: 10:00 - 20:00.
Dịch vụ:
{$services}
Thợ cắt tóc:
{$barbers}

Nhiệm vụ: 
1. Phân tích khuôn mặt nếu khách gửi ảnh và gợi ý kiểu tóc (tròn, vuông, trái xoan, dài...).
2. Hỗ trợ đặt lịch bằng cách dùng công cụ (tools). Nếu khách muốn đặt lịch, hãy hỏi rõ ngày, giờ, thợ (hoặc gợi ý thợ rảnh), dịch vụ muốn làm. Sau đó gọi `check_availability` để kiểm tra. Nếu có thợ rảnh, xác nhận lại với khách và gọi `book_appointment`. Lịch hẹn phải cung cấp mảng ID dịch vụ.
Lưu ý: Chỉ dùng các thợ và dịch vụ có trong danh sách trên. Giao tiếp lịch sự, ngắn gọn, dùng tiếng Việt. Nếu khách chỉ nhắn ảnh mà không nói gì, hãy phân tích ảnh và chào đón họ.";

        $tools = [
            [
                'functionDeclarations' => [
                    [
                        'name' => 'check_availability',
                        'description' => 'Kiểm tra thợ nào rảnh trong một ngày và giờ cụ thể. Trả về danh sách ID và tên thợ rảnh.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'date' => ['type' => 'STRING', 'description' => 'Ngày theo định dạng YYYY-MM-DD'],
                                'time' => ['type' => 'STRING', 'description' => 'Giờ theo định dạng HH:MM (vd: 15:00)'],
                                'duration_minutes' => ['type' => 'INTEGER', 'description' => 'Tổng thời gian ước tính (mặc định 30)'],
                            ],
                            'required' => ['date', 'time']
                        ]
                    ],
                    [
                        'name' => 'book_appointment',
                        'description' => 'Đặt lịch hẹn cho khách sau khi đã xác nhận đầy đủ thông tin (ngày, giờ, ID thợ, mảng ID dịch vụ).',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'barber_id' => ['type' => 'INTEGER', 'description' => 'ID của thợ cắt tóc'],
                                'service_ids' => [
                                    'type' => 'ARRAY', 
                                    'items' => ['type' => 'INTEGER'], 
                                    'description' => 'Mảng chứa ID các dịch vụ khách chọn'
                                ],
                                'date' => ['type' => 'STRING', 'description' => 'Ngày YYYY-MM-DD'],
                                'time' => ['type' => 'STRING', 'description' => 'Giờ HH:MM'],
                            ],
                            'required' => ['barber_id', 'service_ids', 'date', 'time']
                        ]
                    ]
                ]
            ]
        ];

        $contents = [];
        // Add chat history
        foreach ($history as $msg) {
            if (!in_array($msg['role'], ['user', 'model'])) continue;
            
            // Format for Gemini API (functionCalls in history are complex to recreate, so we only pass text history if possible, or assume frontend passes clean history)
            $contents[] = [
                'role' => $msg['role'],
                'parts' => [['text' => $msg['text'] ?? '']]
            ];
        }

        // Build current user message parts
        $userParts = [];
        if (!empty($userMessage)) {
            $userParts[] = ['text' => $userMessage];
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $base64 = base64_encode(file_get_contents($image->path()));
            $mime = $image->getMimeType();
            $userParts[] = [
                'inlineData' => [
                    'mimeType' => $mime,
                    'data' => $base64
                ]
            ];
        }

        if (empty($userParts)) {
            return response()->json(['success' => false, 'message' => 'Vui lòng gửi tin nhắn hoặc hình ảnh.'], 400);
        }

        $contents[] = [
            'role' => 'user',
            'parts' => $userParts
        ];

        return $this->processGeminiRequest($apiKey, $systemPrompt, $contents, $tools, $user->id);
    }

    private function processGeminiRequest($apiKey, $systemPrompt, $contents, $tools, $userId, $depth = 0)
    {
        if ($depth > 3) {
            return response()->json(['success' => false, 'reply' => 'Hệ thống đang xử lý quá nhiều thao tác. Vui lòng thử lại.']);
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'contents' => $contents,
            'tools' => $tools,
        ];

        try {
            $response = Http::timeout(120)->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$apiKey}", $payload);

            if (!$response->successful()) {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json(['success' => false, 'message' => 'Chatbot đang bận xử lý. Vui lòng thử lại sau.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Gemini Request Timeout or Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối đến máy chủ AI (Timeout). Vui lòng thử lại sau.'], 500);
        }

        $data = $response->json();
        $parts = $data['candidates'][0]['content']['parts'] ?? [];

        // Add the model's response back to the conversational context
        $contents[] = $data['candidates'][0]['content'];

        foreach ($parts as $part) {
            if (isset($part['functionCall'])) {
                $name = $part['functionCall']['name'];
                $args = $part['functionCall']['args'] ?? [];
                
                $result = $this->handleFunctionCall($name, $args, $userId);

                // Add the function response to the conversation
                $contents[] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $name,
                                'response' => ['result' => $result]
                            ]
                        ]
                    ]
                ];

                // Call Gemini again with the function response
                return $this->processGeminiRequest($apiKey, $systemPrompt, $contents, $tools, $userId, $depth + 1);
            }
        }

        $replyText = $parts[0]['text'] ?? '';
        return response()->json([
            'success' => true,
            'reply' => $replyText,
            'history' => $contents // Trả về lịch sử để Frontend giữ context
        ]);
    }

    private function handleFunctionCall($name, $args, $userId)
    {
        if ($name === 'check_availability') {
            $date = $args['date'] ?? now()->toDateString();
            $time = substr($args['time'] ?? '10:00', 0, 5);
            $duration = $args['duration_minutes'] ?? 30;

            // Kiểm tra giờ làm việc hợp lệ
            if ($time < '10:00' || $time > '19:30') {
                return "Giờ không hợp lệ. Cửa hàng chỉ mở cửa từ 10:00 đến 20:00. Vui lòng chọn giờ khác.";
            }

            $availableBarbers = [];
            $barbers = Barber::all();
            foreach ($barbers as $b) {
                // Kiểm tra thợ có nghỉ không
                if ($this->appointmentService->isBarberOnApprovedLeave($b->id, $date)) continue;
                
                // Kiểm tra trùng lịch
                if (!$this->appointmentService->hasConflict($b->id, $date, $time, $duration)) {
                    $availableBarbers[] = ['id' => $b->id, 'name' => $b->name];
                }
            }
            if (empty($availableBarbers)) return "Rất tiếc, không có thợ nào rảnh lúc {$time} ngày {$date}.";
            return "Danh sách thợ rảnh lúc {$time}: " . json_encode($availableBarbers);
        }

        if ($name === 'book_appointment') {
            try {
                $barberId = $args['barber_id'];
                $serviceIds = $args['service_ids'] ?? [];
                $date = $args['date'];
                $time = substr($args['time'], 0, 5);
                
                if (empty($serviceIds)) return "Lỗi: Không có ID dịch vụ nào được cung cấp.";

                $services = Service::whereIn('id', $serviceIds)->get();
                if ($services->isEmpty()) return "Lỗi: Dịch vụ không tồn tại.";

                $totalDuration = $services->sum('duration_minutes');

                if ($this->appointmentService->isBarberOnApprovedLeave($barberId, $date)) {
                    return "Lỗi: Thợ này đang nghỉ phép vào ngày {$date}.";
                }

                if ($this->appointmentService->hasConflict($barberId, $date, $time, $totalDuration)) {
                    return "Lỗi: Thợ này đã có lịch hẹn trùng giờ. Vui lòng chọn giờ hoặc thợ khác.";
                }

                $currentTime = Carbon::createFromFormat('H:i', $time);
                $firstAppt = null;
                foreach ($serviceIds as $sid) {
                    $svc = $services->firstWhere('id', $sid);
                    if (!$svc) continue;
                    $appt = Appointment::create([
                        'user_id' => $userId,
                        'barber_id' => $barberId,
                        'service_id' => $sid,
                        'appointment_date' => $date,
                        'appointment_time' => $currentTime->format('H:i'),
                        'status' => 'pending',
                        'notes' => 'Đặt qua Chatbot AI',
                    ]);
                    if (!$firstAppt) $firstAppt = $appt;
                    $currentTime->addMinutes($svc->duration_minutes);
                }

                if ($firstAppt) {
                    $firstAppt->load('user');
                    $firstAppt->user->notify(new \App\Notifications\AppointmentBooked($firstAppt));
                }

                return "Thành công. Đã tạo lịch hẹn và gửi email thông báo.";
            } catch (\Exception $e) {
                Log::error('Chatbot Booking Error: ' . $e->getMessage());
                return "Lỗi hệ thống: Không thể tạo lịch hẹn lúc này.";
            }
        }

        return "Hàm không hợp lệ.";
    }
}
