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

        // ── Extract dates & guests from current message only ─────
        $dates  = $this->extractDates($message);
        $guests = $this->extractGuests($message);

        // Track if current message has exactly 1 date (could be a checkout reply)
        // e.g., AI asked "khi nào trả?" and user replied "21/7"
        $currentSingleDate = (!empty($dates) && empty($dates['explicit'])) ? $dates['check_in'] : null;
        $histDates = null;

        // Fall back to scanning recent user turns for missing info
        if (empty($dates) || $guests === 0 || $currentSingleDate !== null) {
            foreach (array_reverse(array_slice($history, -4)) as $msg) {
                if (($msg['role'] ?? '') !== 'user') continue;
                $userText = $msg['content'] ?? '';
                if (empty($dates)) {
                    $dates = $this->extractDates($userText);
                } elseif ($currentSingleDate !== null && $histDates === null) {
                    // Collect the most recent prior date to pair as check-in
                    $histDates = $this->extractDates($userText);
                }
                if ($guests === 0) $guests = $this->extractGuests($userText);
                // When pairing checkout, scan all turns; otherwise exit early
                if ($currentSingleDate === null && !empty($dates) && $guests > 0) break;
            }
        }

        // If current message = 1 date AND history has an earlier check-in → treat as checkout
        if ($currentSingleDate !== null && !empty($histDates) && empty($dates['explicit'])) {
            $histCI = $histDates['check_in'];
            if (strtotime($currentSingleDate) > strtotime($histCI)) {
                $dates = ['check_in' => $histCI, 'check_out' => $currentSingleDate, 'explicit' => true];
            }
        }

        // If check-in known but checkout still missing, try multiple patterns from current message
        if (!empty($dates) && empty($dates['explicit'])) {
            $ciTS   = strtotime($dates['check_in']);
            $ciMon  = date('m', $ciTS);
            $ciYear = date('Y', $ciTS);
            $coDay  = null;

            // Pattern 1: keyword BEFORE number — "trả ngày 21", "đến 21", "ngày 21"
            if (preg_match('/(?:đến|trả|checkout|out|ngày\s*trả|ngày)[^\d]*(\d{1,2})(?!\s*[\/\-\d])(?!\s*(?:người|khách|pax))/ui', $message, $dm)) {
                $coDay = (int) $dm[1];
            }
            // Pattern 2: number BEFORE keyword — "21 trả", "21 anh trả"
            // Use preg_match_all to skip false positives (small numbers like "2 người" before "trả")
            if (!$coDay && preg_match_all('/(\d{1,2})[^\d\n]{0,20}(?:trả|checkout|check[\s\-]?out)/ui', $message, $dms)) {
                foreach ($dms[1] as $d) {
                    $d  = (int) $d;
                    $co = sprintf('%s-%s-%02d', $ciYear, $ciMon, $d);
                    if ($d >= 1 && $d <= 31 && strtotime($co) > $ciTS) { $coDay = $d; break; }
                }
            }

            if ($coDay && $coDay >= 1 && $coDay <= 31) {
                $co = sprintf('%s-%s-%02d', $ciYear, $ciMon, $coDay);
                if (strtotime($co) > $ciTS) {
                    $dates['check_out'] = $co;
                    $dates['explicit']  = true;
                }
            }

            // Pattern 3: "X đêm/night" duration fallback — checkout = check_in + X nights
            if (empty($dates['explicit']) && preg_match('/(\d{1,2})\s*(?:đêm|night)/ui', $message, $dm)) {
                $nights = (int) $dm[1];
                if ($nights >= 1 && $nights <= 30) {
                    $dates['check_out'] = date('Y-m-d', strtotime($dates['check_in'] . " +{$nights} days"));
                    $dates['explicit']  = true;
                }
            }
        }

        // ── Build context ────────────────────────────────────────
        $context       = '';
        $goHostCalled  = false;

        if (!empty($dates) && !empty($dates['explicit']) && $guests > 0 && $this->gohost->isConfigured()) {
            $goHostCalled = true;
            $result = $this->gohost->searchRoomTypes(
                $dates['check_in'], $dates['check_out'], $guests
            );

            $availableRooms = [];
            // Group by gohost_id to support multiple DB rooms sharing the same GoHost room type
            $goHostMap = collect($rooms)->filter(fn($r) => $r['gohost_id'])->groupBy('gohost_id')->all();

            if (!empty($result['data'])) {
                foreach ($result['data'] as $item) {
                    $rtId = $item['room_types']['id'] ?? ($item['id'] ?? '');
                    if ($rtId && isset($goHostMap[$rtId])) {
                        foreach ($goHostMap[$rtId] as $room) {
                            $availableRooms[] = $room;
                        }
                    }
                }
            }

            if (!empty($availableRooms)) {
                $count   = count($availableRooms);
                $context = "[KẾT QUẢ ĐÃ CÓ - KHÔNG CẦN KIỂM TRA THÊM]: Hệ thống vừa kiểm tra xong, còn {$count} phòng trống. "
                         . "Thông báo ngay kết quả cho khách (VD: 'Dạ còn {$count} phòng trống ạ!'). "
                         . "TUYỆT ĐỐI KHÔNG nói 'sẽ kiểm tra', 'đợi em', 'một chút' — kết quả đã có rồi. "
                         . "Danh sách phòng sẽ tự hiển thị, không cần liệt kê tên.";
            } else {
                $context = "TÌNH TRẠNG: HẾT PHÒNG cho ngày khách yêu cầu. Hãy xin lỗi khách lịch sự. KHÔNG gạ hỏi đổi ngày.";
            }
        } else {
            $missing = [];
            if (empty($dates))                      $missing[] = 'ngày nhận phòng và ngày trả phòng';
            elseif (empty($dates['explicit']))       $missing[] = 'ngày trả phòng';
            if ($guests === 0)                       $missing[] = 'số lượng khách';
            $missingStr = implode(', ', $missing);
            $context = "[CHỈ THỊ]: Khách chưa cung cấp đủ thông tin để kiểm tra phòng trống. Còn thiếu: {$missingStr}. "
                     . "Hãy hỏi lại khách để lấy đủ thông tin. Hỏi tự nhiên, thân thiện. KHÔNG báo phòng hay giá lúc này.";
        }

        // ── System prompt ────────────────────────────────────────
        $systemPrompt = "Bạn là {$agentName}, chuyên viên tư vấn của LuxNest Homestay.
- Xưng 'Em', gọi khách là 'Anh/Chị' theo cách khách xưng. KHÔNG gọi khách là 'Mình'.
- Văn phong: ngắn gọn, thân thiện, giống người thật nhắn Zalo. Không văn mẫu dài dòng.
- Khi khách hỏi giá/phòng mà thiếu ngày hoặc số người, hỏi lại vui vẻ.
- Khi có kết quả phòng từ hệ thống: thông báo NGAY, KHÔNG nói 'sẽ kiểm tra' hay 'chờ em'.
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
                'max_tokens' => 700,
                'temperature'=> 0.7,
            ]);

        if ($response->failed()) {
            return response()->json(['success' => false, 'message' => 'AI đang bận, thử lại sau.']);
        }

        $reply = $response->json('choices.0.message.content', '');

        // ── Handle booking link tag ──────────────────────────────
        $showBookingLink = str_contains($reply, '[LINK_DAT_PHONG]');
        $reply = trim(str_replace('[LINK_DAT_PHONG]', '', $reply));

        $willCheckPattern = '/(?:sẽ|đang|ngay)\s*kiểm\s*tra|chờ\s*(em|xíu|một\s*chút)|một\s*chút\s*xíu|em\s*kiểm\s*tra/ui';

        // ── Override AI reply if it said "will check" despite results existing ──
        if (!empty($availableRooms)) {
            $willCheck = preg_match($willCheckPattern, $reply);
            $hasResult = preg_match('/\d+\s*phòng|phòng\s*trống|còn\s*phòng/ui', $reply);
            if ($willCheck || !$hasResult) {
                $count = count($availableRooms);
                $ci    = date('d/m', strtotime($dates['check_in']));
                $co    = date('d/m', strtotime($dates['check_out']));
                $reply = "Dạ em vừa kiểm tra xong ạ! Còn **{$count} phòng trống** cho ngày {$ci} đến {$co}. Anh/Chị xem các phòng bên dưới nhé!";
            }
        }

        // ── Override when GoHost searched but found no rooms ──
        // Always override regardless of what AI said — it cannot know the real result.
        if (empty($availableRooms) && $goHostCalled) {
            $ci    = date('d/m', strtotime($dates['check_in']));
            $co    = date('d/m', strtotime($dates['check_out']));
            $reply = "Dạ em vừa kiểm tra rồi ạ, tiếc là hiện **không còn phòng trống** cho ngày {$ci} đến {$co} ạ. Anh/Chị thử chọn ngày khác xem ạ?";
        }

        // ── Append room list when GoHost returned results ────────
        if (!empty($availableRooms)) {
            $list = "\n\n**Danh sách phòng còn trống:**\n";
            foreach ($availableRooms as $r) {
                $list .= "• {$r['name']} — {$r['price']} VNĐ/đêm\n";
            }
            $reply .= rtrim($list);
        }

        $params = [];
        if (!empty($dates['check_in']))  $params['checkin']  = $dates['check_in'];
        if (!empty($dates['check_out'])) $params['checkout'] = $dates['check_out'];
        if ($guests > 0)                 $params['guests']   = $guests . ' người';
        $roomsUrl = url('/rooms') . (!empty($params) ? '?' . http_build_query($params) : '');

        // ── Suggested room cards ─────────────────────────────────
        // If GoHost returned available rooms, show those directly.
        // Otherwise scan AI reply for room names (fallback path).
        $suggestedRooms = [];
        if (!empty($availableRooms)) {
            foreach ($availableRooms as $r) {
                $suggestedRooms[] = [
                    'id'     => $r['id'],
                    'name'   => $r['name'],
                    'price'  => $r['price'],
                    'url'    => url('/hotel/' . $r['slug']),
                    'image'  => $r['image'],
                    'branch' => $r['branch'],
                ];
            }
        } else {
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
        }

        return response()->json([
            'success'         => true,
            'reply'           => $reply,
            'suggested_rooms' => $suggestedRooms,
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
            return ['check_in' => $ci, 'check_out' => $co, 'explicit' => true];
        }
        if ($found === 1) {
            $ci = "$year-" . str_pad($m[2][0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1][0], 2, '0', STR_PAD_LEFT);
            // check_out auto-filled — mark as incomplete so we ask customer
            return ['check_in' => $ci, 'check_out' => date('Y-m-d', strtotime("$ci +1 day")), 'explicit' => false];
        }

        // No dd/mm patterns — try standalone day references, assume current month
        $month = date('m');
        $ciDay = null;
        $coDay = null;

        // "nhận/vào/từ [ngày] X" → check-in day
        if (preg_match('/(?:nhận|check[\s\-]?in|vào|từ)\s*(?:ngày\s*)?(\d{1,2})(?!\s*[\/\-]\d)/ui', $text, $md)) {
            $ciDay = (int) $md[1];
        }
        // "trả/checkout/ra [ngày] X" → check-out day
        if (preg_match('/(?:trả|checkout|check[\s\-]?out|ra)\s*(?:ngày\s*)?(\d{1,2})(?!\s*[\/\-]\d)/ui', $text, $md)) {
            $coDay = (int) $md[1];
        }
        // "[từ/ngày] X đến/tới Y" range — not followed by người/khách (avoids "2 đến 4 người")
        if (!$ciDay || !$coDay) {
            if (preg_match('/(?:(?:từ|ngày)\s*)?(\d{1,2})\s*(?:đến|tới)\s*(?:ngày\s*)?(\d{1,2})(?!\s*[\/\-\d])(?!\s*(?:người|khách|pax))/ui', $text, $md)) {
                if (!$ciDay) $ciDay = (int) $md[1];
                if (!$coDay) $coDay = (int) $md[2];
            }
        }
        // "ngày X Y" or just "X Y" — two consecutive day numbers, assume check-in/out pair
        // e.g. "ngày 20 21", "20 21 tháng 7"
        if (!$ciDay || !$coDay) {
            if (preg_match('/(?:ngày\s*)?(\d{1,2})\s+(\d{1,2})(?!\s*[\/\-\d])(?!\s*(?:người|khách|pax|phòng))/ui', $text, $md)) {
                if (!$ciDay) $ciDay = (int) $md[1];
                if (!$coDay) $coDay = (int) $md[2];
            }
        }
        // Bare "ngày X" as check-in fallback — only when no other keyword matched
        // e.g. "ngày 20 còn phòng k", "ngày 20 tháng này"
        if (!$ciDay && !$coDay) {
            if (preg_match('/ngày\s*(\d{1,2})(?!\s*[\/\-]\d)(?!\s*(?:người|khách|pax))/ui', $text, $md)) {
                $ciDay = (int) $md[1];
            }
        }

        if ($ciDay && $ciDay >= 1 && $ciDay <= 31) {
            $ci = "$year-$month-" . str_pad($ciDay, 2, '0', STR_PAD_LEFT);
            if ($coDay && $coDay >= 1 && $coDay <= 31 && $coDay !== $ciDay) {
                $co = "$year-$month-" . str_pad($coDay, 2, '0', STR_PAD_LEFT);
                if (strtotime($co) <= strtotime($ci)) {
                    // Checkout day earlier than check-in in same month → next month
                    $co = date('Y-m-d', mktime(0, 0, 0, (int) $month + 1, $coDay, (int) $year));
                }
                return ['check_in' => $ci, 'check_out' => $co, 'explicit' => true];
            }
            return ['check_in' => $ci, 'check_out' => date('Y-m-d', strtotime("$ci +1 day")), 'explicit' => false];
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
        return array_column($scored, 'data');
    }
}
