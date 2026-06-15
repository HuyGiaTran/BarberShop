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

        $userMessage = strtolower(trim($request->input('message', '')));
        $apiKey = env('GEMINI_API_KEY');

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng Chatbot.'], 401);
        }

        // Nếu có API Key thì dùng Gemini
        if ($apiKey) {
            return $this->askGemini($request, $user);
        }

        // Fallback: Local rule-based engine
        return $this->askLocal($userMessage, $user);
    }

    private function askLocal(string $message, $user): JsonResponse
    {
        $reply = '';

        // Chào hỏi
        if (preg_match('/chào|hello|hi|hey|xin chào/', $message)) {
            $reply = "Xin chào {$user->name}! Tôi là trợ lý ảo của Barber Shop. Bạn cần tư vấn gì ạ?";
        }
        // Giờ mở cửa
        elseif (preg_match('/giờ mở cửa|mấy giờ|thời gian|khi nào.*mở/', $message)) {
            $reply = "Cửa hàng mở cửa tất cả các ngày trong tuần (trừ chủ nhật) từ 08:00 đến 22:00. Chủ nhật chúng tôi nghỉ.";
        }
        // Dịch vụ & giá
        elseif (preg_match('/dịch vụ|giá|bảng giá|bao nhiêu tiền|cắt tóc/', $message)) {
            $services = Service::all();
            if ($services->isEmpty()) {
                $reply = "Hiện tại chưa có dịch vụ nào. Vui lòng quay lại sau.";
            } else {
                $reply = "Dưới đây là các dịch vụ của chúng tôi:\n";
                foreach ($services as $s) {
                    $reply .= "- {$s->name}: " . number_format($s->price, 0, ',', '.') . "đ ({$s->duration_minutes} phút)\n";
                }
            }
        }
        // Danh sách thợ
        elseif (preg_match('/thợ|barber|thợ cắt|ai cắt/', $message)) {
            $barbers = Barber::where('is_active', true)->get();
            if ($barbers->isEmpty()) {
                $reply = "Hiện tại chưa có thợ nào.";
            } else {
                $reply = "Đội ngũ Barber của chúng tôi:\n";
                foreach ($barbers as $b) {
                    $reply .= "- {$b->name}: {$b->bio}\n";
                }
            }
        }
        // Đặt lịch
        elseif (preg_match('/đặt lịch|book|hẹn|đặt/', $message)) {
            $reply = "Để đặt lịch, bạn vui lòng làm theo các bước sau:\n";
            $reply .= "1. Chọn ngày bạn muốn đến\n";
            $reply .= "2. Chọn Barber (xem danh sách thợ)\n";
            $reply .= "3. Chọn khung giờ trống\n";
            $reply .= "4. Chọn dịch vụ\n";
            $reply .= "5. Xác nhận đặt lịch\n\n";
            $reply .= "Bạn có thể đặt lịch trực tiếp trên website qua chức năng 'Book a seat' nhé!";
        }
        // Liên hệ
        elseif (preg_match('/liên hệ|địa chỉ|số điện thoại|hotline/', $message)) {
            $reply = "Bạn có thể liên hệ với chúng tôi qua:\n- Hotline: 0123 456 789\n- Email: barbershop@example.com\n- Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM";
        }
        // Cảm ơn
        elseif (preg_match('/cảm ơn|cám ơn|thank/', $message)) {
            $reply = "Cảm ơn bạn {$user->name}! Nếu cần hỗ trợ thêm, cứ nhắn tin cho tôi nhé. Chúc bạn một ngày tốt lành!";
        }
        // Mặc định
        else {
            $reply = "Xin chào {$user->name}! Tôi có thể hỗ trợ bạn các thông tin sau:\n";
            $reply .= "- Xem giờ mở cửa\n";
            $reply .= "- Xem dịch vụ & bảng giá\n";
            $reply .= "- Xem danh sách Barber\n";
            $reply .= "- Hướng dẫn đặt lịch\n";
            $reply .= "- Thông tin liên hệ\n\n";
            $reply .= "Bạn muốn biết thông tin gì ạ?";
        }

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'history' => [],
            'mode' => 'local'
        ]);
    }

    private function askGemini(Request $request, $user): JsonResponse
    {
        $userMessage = $request->input('message', '');
        $historyJson = $request->input('history', '[]');
        $history = json_decode($historyJson, true) ?? [];
        $apiKey = env('GEMINI_API_KEY');

        $services = Service::all()->map(fn($s) => "- ID {$s->id}: {$s->name} (" . number_format($s->price, 0, ',', '.') . " VNĐ)")->implode("\n");
        $barbers = Barber::all()->map(fn($b) => "- ID {$b->id}: {$b->name}")->implode("\n");

        $systemPrompt = "Bạn là trợ lý AI chuyên nghiệp của Barber Shop 'Gentlemen's Barber'.
Khách hàng hiện tại là: {$user->name} (User ID: {$user->id}, SĐT: {$user->phone}).
Giờ mở cửa: 08:00 - 22:00 (Thứ 2 - Thứ 7), Chủ nhật nghỉ.
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
        foreach ($history as $msg) {
            if (!in_array($msg['role'], ['user', 'model'])) continue;
            $contents[] = [
                'role' => $msg['role'],
                'parts' => [['text' => $msg['text'] ?? '']]
            ];
        }

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

        // V1 không hỗ trợ systemInstruction và tools, đưa instruction vào content đầu tiên
        $geminiContents = $contents;
        // Thêm system prompt vào đầu như user message
        array_unshift($geminiContents, [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt . "\n\nHãy ghi nhớ các thông tin trên và trả lời khách hàng."]]
        ]);
        // Thêm model xác nhận
        array_unshift($geminiContents, [
            'role' => 'model',
            'parts' => [['text' => 'Tôi đã ghi nhớ. Sẵn sàng hỗ trợ khách hàng.']]
        ]);

        try {
            $response = Http::timeout(120)
                ->withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key={$apiKey}", [
                    'contents' => $geminiContents,
                ]);

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

        $contents[] = $data['candidates'][0]['content'];

        foreach ($parts as $part) {
            if (isset($part['functionCall'])) {
                $name = $part['functionCall']['name'];
                $args = $part['functionCall']['args'] ?? [];
                
                $result = $this->handleFunctionCall($name, $args, $userId);

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

                return $this->processGeminiRequest($apiKey, $systemPrompt, $contents, $tools, $userId, $depth + 1);
            }
        }

        $replyText = $parts[0]['text'] ?? '';
        return response()->json([
            'success' => true,
            'reply' => $replyText,
            'history' => $contents,
            'mode' => 'gemini'
        ]);
    }

    private function handleFunctionCall($name, $args, $userId)
    {
        if ($name === 'check_availability') {
            $date = $args['date'] ?? now()->toDateString();
            $time = substr($args['time'] ?? '10:00', 0, 5);
            $duration = $args['duration_minutes'] ?? 30;

            if ($time < '08:00' || $time > '21:30') {
                return "Giờ không hợp lệ. Cửa hàng chỉ mở cửa từ 08:00 đến 22:00. Vui lòng chọn giờ khác.";
            }

            $availableBarbers = [];
            $barbers = Barber::all();
            foreach ($barbers as $b) {
                if ($this->appointmentService->isBarberOnApprovedLeave($b->id, $date)) continue;
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
                    try {
                        $firstAppt->user->notify(new \App\Notifications\AppointmentBooked($firstAppt));
                    } catch (\Exception $e) {
                        Log::warning('Không thể gửi email thông báo: ' . $e->getMessage());
                    }
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