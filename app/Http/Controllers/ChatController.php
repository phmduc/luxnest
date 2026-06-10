<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\GoHostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    private const PERSONAS = [
        'Linh'  => 'Tư vấn viên',
        'Ngọc'  => 'Chuyên viên đặt phòng',
        'Mai'   => 'Tư vấn viên',
        'Hương' => 'Chuyên viên CSKH',
    ];

    public function __construct(private GoHostService $gohost) {}

    public function send(Request $request): JsonResponse
    {
        $message   = trim($request->input('message', ''));
        $history   = $request->input('history', []);
        $agentName = $request->input('agent_name', 'Linh');

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Tin nhắn trống']);
        }

        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Chưa cấu hình OpenAI API key']);
        }

        // ── Load rooms from DB ──────────────────────────────────
        $rooms = Room::where('status', 'active')->get()
            ->mapWithKeys(fn($r) => [$r->id => [
                'id'        => $r->id,
                'gohost_id' => $r->gohost_room_type_id,
                'name'      => $r->name,
                'price'     => number_format($r->price, 0, ',', '.'),
                'price_raw' => $r->price,
                'slug'      => $r->slug,
                'image'     => $r->image,
                'branch'    => $r->branch,
            ]])->all();

        // ── Extract dates & guests from message + recent history ─
        $fullContext = $message;
        foreach (array_slice($history, -2) as $msg) {
            $fullContext .= ' ' . ($msg['content'] ?? '');
        }
        $dates  = $this->extractDates($fullContext);
        $guests = $this->extractGuests($fullContext);

        // ── Build context ────────────────────────────────────────
        $context = '';

        if (!empty($dates) && $guests > 0 && $this->gohost->isConfigured()) {
            $result = $this->gohost->searchRoomTypes(
                $dates['check_in'], $dates['check_out'], $guests
            );

            $availableRooms = [];
            $goHostMap = collect($rooms)->filter(fn($r) => $r['gohost_id'])->keyBy('gohost_id')->all();

            if (!empty($result['data'])) {
                foreach ($result['data'] as $item) {
                    $rtId = $item['room_types']['id'] ?? ($item['id'] ?? '');
                    if ($rtId && isset($goHostMap[$rtId])) {
                        $availableRooms[] = $goHostMap[$rtId];
                    }
                }
            }

            if (!empty($availableRooms)) {
                $rag = $this->ragFilter($message, $availableRooms);
                $context  = "[KẾT QUẢ TỪ HỆ THỐNG]: Các phòng còn trống:\n";
                foreach ($rag as $p) {
                    $context .= "• {$p['name']} — {$p['price']} VNĐ/đêm\n";
                }
                $context .= "\n=> HÃY BÁO NGAY KẾT QUẢ CHO KHÁCH. KHÔNG nói 'Để em kiểm tra' hay 'Đợi em'.";
            } else {
                $context = "TÌNH TRẠNG: HẾT PHÒNG cho ngày khách yêu cầu. Hãy xin lỗi khách lịch sự. KHÔNG gạ hỏi đổi ngày.";
            }
        } else {
            $fallback = array_slice(array_values($rooms), 0, 4);
            $context  = "DANH SÁCH HẠNG PHÒNG:\n";
            foreach ($fallback as $p) {
                $context .= "• {$p['name']} — từ {$p['price']} VNĐ/đêm\n";
            }
            $context .= "\n[CHỈ THỊ]: Khách chưa cung cấp ngày đi và số người. Hỏi khách một cách thân thiện. KHÔNG tự báo còn phòng.";
        }

        // ── System prompt ────────────────────────────────────────
        $systemPrompt = "Bạn là {$agentName}, chuyên viên tư vấn của LuxNest Homestay.
- Xưng 'Em', gọi khách là 'Anh/Chị' theo cách khách xưng. KHÔNG gọi khách là 'Mình'.
- Văn phong: ngắn gọn, thân thiện, giống người thật nhắn Zalo. Không văn mẫu dài dòng.
- Khi khách hỏi giá/phòng mà thiếu ngày hoặc số người, hỏi lại vui vẻ.
- Khi khách chốt đặt phòng, chèn [LINK_DAT_PHONG] ở cuối tin nhắn.
- Dữ liệu hiện hành:\n{$context}";

        // ── Build messages array ─────────────────────────────────
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach (array_slice($history, -3) as $msg) {
            $messages[] = [
                'role'    => in_array($msg['role'] ?? '', ['assistant', 'model']) ? 'assistant' : 'user',
                'content' => $msg['content'] ?? '',
            ];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        // ── Call OpenAI ──────────────────────────────────────────
        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-4o-mini',
                'messages'   => $messages,
                'max_tokens' => 300,
                'temperature'=> 0.7,
            ]);

        if ($response->failed()) {
            return response()->json(['success' => false, 'message' => 'AI đang bận, thử lại sau.']);
        }

        $reply = $response->json('choices.0.message.content', '');

        // ── Handle booking link tag ──────────────────────────────
        $showBookingLink = str_contains($reply, '[LINK_DAT_PHONG]');
        $reply = trim(str_replace('[LINK_DAT_PHONG]', '', $reply));

        $roomsUrl = null;
        if ($showBookingLink) {
            $params = [];
            if (!empty($dates['check_in']))  $params['checkin']  = $dates['check_in'];
            if (!empty($dates['check_out'])) $params['checkout'] = $dates['check_out'];
            if ($guests > 0)                 $params['guests']   = $guests . ' người';
            $roomsUrl = url('/rooms') . (!empty($params) ? '?' . http_build_query($params) : '');
        }

        // ── Detect suggested room cards ──────────────────────────
        $suggestedRooms = [];
        foreach ($rooms as $r) {
            if (mb_stripos($reply, $r['name']) !== false) {
                $suggestedRooms[] = [
                    'id'     => $r['id'],
                    'name'   => $r['name'],
                    'price'  => $r['price'],
                    'url'    => url('/hotel/' . $r['slug']),
                    'image'  => $r['image'],
                    'branch' => $r['branch'],
                ];
            }
        }

        return response()->json([
            'success'         => true,
            'reply'           => $reply,
            'suggested_rooms' => array_slice($suggestedRooms, 0, 4),
            'rooms_url'       => $roomsUrl,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function extractDates(string $text): array
    {
        $year = date('Y');
        preg_match_all('/(\d{1,2})[\/\-](\d{1,2})/', $text, $m);
        $found = count($m[0] ?? []);

        if ($found >= 2) {
            $ci = "$year-" . str_pad($m[2][0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1][0], 2, '0', STR_PAD_LEFT);
            $co = "$year-" . str_pad($m[2][1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1][1], 2, '0', STR_PAD_LEFT);
            if (strtotime($co) < strtotime($ci)) [$ci, $co] = [$co, $ci];
            return ['check_in' => $ci, 'check_out' => $co];
        }
        if ($found === 1) {
            $ci = "$year-" . str_pad($m[2][0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1][0], 2, '0', STR_PAD_LEFT);
            return ['check_in' => $ci, 'check_out' => date('Y-m-d', strtotime("$ci +1 day"))];
        }
        return [];
    }

    private function extractGuests(string $text): int
    {
        if (preg_match('/(\d+)\s*(người|khách|pax|adult)/iu', $text, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    private function ragFilter(string $message, array $rooms): array
    {
        $msg = mb_strtolower($message, 'UTF-8');
        $scored = array_map(function ($p) use ($msg) {
            $score = 0;
            if (str_contains($msg, 'rẻ') && $p['price_raw'] < 500000) $score += 2;
            if (str_contains($msg, 'cao cấp') && $p['price_raw'] > 1000000) $score += 2;
            return ['score' => $score, 'data' => $p];
        }, $rooms);

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice(array_column($scored, 'data'), 0, 4);
    }
}
