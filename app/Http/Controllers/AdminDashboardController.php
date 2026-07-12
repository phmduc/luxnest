<?php

namespace App\Http\Controllers;

use App\Console\Commands\SendRemarketingEmails;
use App\Mail\RemarketingVoucher;
use App\Models\EmailCampaign;
use App\Models\Faq;
use App\Models\News;
use App\Models\PageContent;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
use App\Models\VillaListing;
use App\Models\Voucher;
use App\Services\CampaignMailerService;
use App\Services\GoHostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminDashboardController extends Controller
{
    public function __construct(private GoHostService $gohost) {}

    public function index(Request $request)
    {
        $totalBookings  = 0;
        $pendingCheckin = 0;
        $revenue        = 0;
        $recentBookings = [];
        $goHostError    = null;

        if ($this->gohost->isConfigured()) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate   = now()->endOfMonth()->toDateString();

            $result = $this->gohost->getBookings($startDate, $endDate, 1, 50);

            if (isset($result['data']) && \is_array($result['data'])) {
                $bookings = $result['data'];

                $totalBookings  = \count($bookings);
                $pendingCheckin = \count(\array_filter($bookings, fn ($b) => \in_array($b['status'] ?? '', ['confirmed', 'in_progress'], true)));
                $revenue        = (int) \array_sum(\array_column($bookings, 'amount'));
                $recentBookings = \array_slice($bookings, 0, 5);
            } else {
                $goHostError = $result['message'] ?? 'Không thể tải dữ liệu từ GoHost.';
            }
        } else {
            $goHostError = 'GoHost API chưa được cấu hình trong file .env.';
        }

        // ── QR Check-in detection ──────────────────────────────────
        $qrCheckinOrder = null;
        $qrCheckinError = null;

        if ($request->filled('checkin') && $request->filled('oid') && $request->filled('auth')) {
            $oid      = (int) $request->input('oid');
            $auth     = $request->input('auth');
            $expected = hash_hmac('sha256', $oid . 'luxnest-checkin', config('app.key'));

            if (hash_equals($expected, $auth)) {
                $order = DB::table('orders')->where('id', $oid)->first();
                $item  = $order ? DB::table('order_items')->where('order_id', $oid)->first() : null;
                $room  = $item  ? Room::find($item->room_id) : null;

                if ($order) {
                    if ($order->status === 'checked_in') {
                        $qrCheckinError = 'Đơn hàng #' . $oid . ' đã được check-in trước đó.';
                    } else {
                        $qrCheckinOrder = [
                            'id'                => $order->id,
                            'customer_name'     => $order->customer_name,
                            'customer_email'    => $order->customer_email,
                            'customer_phone'    => $order->customer_phone,
                            'checkin_date'      => $order->checkin_date,
                            'checkout_date'     => $order->checkout_date,
                            'total_amount'      => $order->total_amount,
                            'status'            => $order->status,
                            'gohost_booking_id' => $order->gohost_booking_id ?? null,
                            'room_name'         => $room?->name ?? ($item?->room_name ?? 'Phòng'),
                            'auth'              => $auth,
                        ];
                    }
                } else {
                    $qrCheckinError = 'Không tìm thấy đơn hàng #' . $oid . '.';
                }
            } else {
                $qrCheckinError = 'Mã QR không hợp lệ hoặc đã hết hạn.';
            }
        }

        return view('admin.dashboard', compact(
            'totalBookings',
            'pendingCheckin',
            'revenue',
            'recentBookings',
            'goHostError',
            'qrCheckinOrder',
            'qrCheckinError'
        ));
    }

    // ---------------------------------------------------------------
    // Bookings API
    // ---------------------------------------------------------------

    public function getBookings(Request $request): JsonResponse
    {
        $page   = (int) $request->input('page', 1);
        $month  = $request->input('month', now()->format('Y-m'));
        $search = strtolower(trim($request->input('search', '')));

        [$year, $monthNum] = explode('-', $month);
        $startDate = "$year-$monthNum-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        $result = $this->gohost->getBookings($startDate, $endDate, $page, 15);

        if (!isset($result['data'])) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Lỗi kết nối GoHost.',
            ]);
        }

        $bookings   = $result['data'];
        $pagination = $result['meta']['pagination'] ?? [];

        if ($search !== '') {
            $bookings = \array_values(\array_filter($bookings, function ($b) use ($search) {
                $customerName = strtolower($b['customer']['name'] ?? '');
                $id           = (string) ($b['id'] ?? '');
                $rooms        = $b['booking_rooms'] ?? [];
                $roomNames    = \array_map(fn ($r) => strtolower($r['room_type']['name'] ?? $r['room_unit'] ?? ''), $rooms);

                return str_contains($customerName, $search)
                    || str_contains($id, $search)
                    || !empty(\array_filter($roomNames, fn ($rn) => str_contains($rn, $search)));
            }));
        }

        return response()->json([
            'success'      => true,
            'data'         => $bookings,
            'total_pages'  => $pagination['total_pages'] ?? 1,
            'current_page' => $page,
        ]);
    }

    public function getBookingDetail(string $id): JsonResponse
    {
        $result = $this->gohost->getBooking($id);

        if (!isset($result['data'])) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Không tìm thấy booking.',
            ]);
        }

        return response()->json(['success' => true, 'data' => $result['data']]);
    }

    public function checkin(Request $request, string $id): JsonResponse
    {
        $roomId = $request->input('room_id');

        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Thiếu room_id.']);
        }

        $result = $this->gohost->updateBooking($id, [
            'rooms' => [['id' => $roomId, 'status' => 'checked_in']],
        ]);

        $ok = !isset($result['error']) && ($result['success'] ?? true);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Check-in thành công!' : ($result['message'] ?? 'Lỗi check-in.'),
        ]);
    }

    public function cancelBooking(Request $request, string $id): JsonResponse
    {
        $roomId = $request->input('room_id');

        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Thiếu room_id.']);
        }

        $result = $this->gohost->updateBooking($id, [
            'rooms' => [['id' => $roomId, 'status' => 'cancelled']],
        ]);

        $ok = !isset($result['error']) && ($result['success'] ?? true);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Hủy đặt phòng thành công!' : ($result['message'] ?? 'Lỗi hủy đặt phòng.'),
        ]);
    }

    // ---------------------------------------------------------------
    // Members API (admin only)
    // ---------------------------------------------------------------

    public function getMembers(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));
        $page   = (int) $request->input('page', 1);

        $query = User::query()->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $members = $query->paginate(10, ['*'], 'page', $page);

        return response()->json(['success' => true, 'data' => $members]);
    }

    public function storeMember(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role'     => 'required|in:admin,employee,member',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return response()->json(['success' => true, 'data' => $user], 201);
    }

    public function updateMember(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => "required|email|unique:users,email,$id",
            'role'     => 'required|in:admin,employee,member',
            'password' => 'nullable|min:8',
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['success' => true, 'data' => $user]);
    }

    public function destroyMember(int $id): JsonResponse
    {
        if ($id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa tài khoản của chính mình.']);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }

    // ---------------------------------------------------------------
    // Rooms CRUD (admin only)
    // ---------------------------------------------------------------

    public function getRooms(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $branch = (string) $request->input('branch', '');

        $query = Room::query()->orderBy('branch')->orderBy('name');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('type', 'like', "%$search%");
            });
        }

        if (!empty($branch)) {
            $query->where('branch', $branch);
        }

        $page = (int) $request->input('page', 1);

        return response()->json([
            'success' => true,
            'data'    => $query->paginate(10, ['*'], 'page', $page),
        ]);
    }

    public function storeRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'slug'               => 'required|string|unique:rooms,slug',
            'branch'             => 'required|in:Hotel,Villa,Residence',
            'type'               => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'price'              => 'required|integer|min:0',
            'regular_price'      => 'nullable|integer|min:0',
            'image'              => 'nullable|string|max:1000',
            'gallery'            => 'nullable|array|max:9',
            'gallery.*'          => 'string|max:1000',
            'video'              => 'nullable|string|max:1000',
            'amenities'          => 'nullable|array',
            'status'             => 'required|in:active,inactive',
            'gohost_room_type_id'=> 'nullable|string|max:255',
        ]);

        $room = Room::create($validated);

        return response()->json(['success' => true, 'data' => $room], 201);
    }

    public function updateRoom(Request $request, int $id): JsonResponse
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'slug'               => "required|string|unique:rooms,slug,$id",
            'branch'             => 'required|in:Hotel,Villa,Residence',
            'type'               => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'price'              => 'required|integer|min:0',
            'regular_price'      => 'nullable|integer|min:0',
            'image'              => 'nullable|string|max:1000',
            'gallery'            => 'nullable|array|max:9',
            'gallery.*'          => 'string|max:1000',
            'video'              => 'nullable|string|max:1000',
            'amenities'          => 'nullable|array',
            'status'             => 'required|in:active,inactive',
            'gohost_room_type_id'=> 'nullable|string|max:255',
        ]);

        $room->update($validated);

        return response()->json(['success' => true, 'data' => $room]);
    }

    public function destroyRoom(int $id): JsonResponse
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return response()->json(['success' => true]);
    }

    public function toggleRoomStatus(int $id): JsonResponse
    {
        $room         = Room::findOrFail($id);
        $room->status = $room->status === 'active' ? 'inactive' : 'active';
        $room->save();

        return response()->json(['success' => true, 'status' => $room->status]);
    }

    public function uploadRoomImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $path = $request->file('image')->store('rooms', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['success' => true, 'url' => $url, 'path' => $path]);
    }

    public function uploadRoomVideo(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|mimetypes:video/mp4,video/quicktime,video/webm|max:51200',
        ]);

        $path = $request->file('video')->store('rooms/videos', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['success' => true, 'url' => $url, 'path' => $path]);
    }

    // ---------------------------------------------------------------
    // Villa Listings CRUD (admin only)
    // ---------------------------------------------------------------

    public function getVillas(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));

        $query = VillaListing::query()->orderBy('location')->orderBy('name');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('location_desc', 'like', "%$search%");
            });
        }

        $page = (int) $request->input('page', 1);

        return response()->json([
            'success' => true,
            'data'    => $query->paginate(10, ['*'], 'page', $page),
        ]);
    }

    public function storeVilla(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'required|string|unique:villa_listings,slug',
            'location'      => 'required|string|max:100',
            'location_desc' => 'required|string|max:255',
            'beds'          => 'required|string|max:50',
            'guests'        => 'required|string|max:50',
            'description'   => 'nullable|string',
            'image'         => 'nullable|string|max:1000',
            'gallery'       => 'nullable|array|max:9',
            'gallery.*'     => 'string|max:1000',
            'video'         => 'nullable|string|max:1000',
            'status'        => 'required|in:active,inactive',
        ]);

        $villa = VillaListing::create($validated);

        return response()->json(['success' => true, 'data' => $villa], 201);
    }

    public function updateVilla(Request $request, int $id): JsonResponse
    {
        $villa = VillaListing::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => "required|string|unique:villa_listings,slug,$id",
            'location'      => 'required|string|max:100',
            'location_desc' => 'required|string|max:255',
            'beds'          => 'required|string|max:50',
            'guests'        => 'required|string|max:50',
            'description'   => 'nullable|string',
            'image'         => 'nullable|string|max:1000',
            'gallery'       => 'nullable|array|max:9',
            'gallery.*'     => 'string|max:1000',
            'video'         => 'nullable|string|max:1000',
            'status'        => 'required|in:active,inactive',
        ]);

        $villa->update($validated);

        return response()->json(['success' => true, 'data' => $villa]);
    }

    public function destroyVilla(int $id): JsonResponse
    {
        $villa = VillaListing::findOrFail($id);
        $villa->delete();

        return response()->json(['success' => true]);
    }

    public function toggleVillaStatus(int $id): JsonResponse
    {
        $villa         = VillaListing::findOrFail($id);
        $villa->status = $villa->status === 'active' ? 'inactive' : 'active';
        $villa->save();

        return response()->json(['success' => true, 'status' => $villa->status]);
    }

    public function uploadVillaImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $path = $request->file('image')->store('villas', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['success' => true, 'url' => $url, 'path' => $path]);
    }

    public function uploadVillaVideo(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|mimetypes:video/mp4,video/quicktime,video/webm|max:51200',
        ]);

        $path = $request->file('video')->store('villas/videos', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['success' => true, 'url' => $url, 'path' => $path]);
    }

    // ---------------------------------------------------------------
    // QR Check-in API
    // ---------------------------------------------------------------

    public function qrCheckin(Request $request): JsonResponse
    {
        $oid  = (int) $request->input('order_id');
        $auth = $request->input('auth', '');

        $expected = hash_hmac('sha256', $oid . 'luxnest-checkin', config('app.key'));

        if (!hash_equals($expected, $auth)) {
            return response()->json(['success' => false, 'message' => 'Xác thực không hợp lệ.']);
        }

        $order = DB::table('orders')->where('id', $oid)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.']);
        }

        if ($order->status === 'checked_in') {
            return response()->json(['success' => false, 'message' => 'Đơn hàng này đã được check-in.']);
        }

        // Cập nhật trạng thái local
        DB::table('orders')->where('id', $oid)->update([
            'status'     => 'checked_in',
            'updated_at' => now(),
        ]);

        // Gọi GoHost nếu có booking ID
        if (!empty($order->gohost_booking_id) && $this->gohost->isConfigured()) {
            $detail = $this->gohost->getBooking($order->gohost_booking_id);
            $roomId = $detail['data']['booking_rooms'][0]['id'] ?? null;

            if ($roomId) {
                $this->gohost->updateBooking($order->gohost_booking_id, [
                    'rooms' => [['id' => $roomId, 'status' => 'checked_in']],
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Check-in thành công!']);
    }

    // ---------------------------------------------------------------
    // Business Settings (admin only)
    // ---------------------------------------------------------------

    public function getSettings(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Setting::current()]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_name'          => 'required|string|max:255',
            'logo'               => 'nullable|string|max:1000',
            'og_image'           => 'nullable|string|max:1000',
            'hotline'            => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string|max:500',
            'map_link'           => 'nullable|url|max:2000',
            'facebook_url'       => 'nullable|url|max:500',
            'instagram_url'      => 'nullable|url|max:500',
            'youtube_url'        => 'nullable|url|max:500',
            'footer_description' => 'nullable|string|max:1000',
        ]);

        $setting = Setting::current();
        $setting->update($validated);

        return response()->json(['success' => true, 'data' => Setting::current()]);
    }

    public function uploadSettingsLogo(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $path = $request->file('image')->store('settings', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['success' => true, 'url' => $url, 'path' => $path]);
    }

    public function uploadSettingsOgImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $path = $request->file('image')->store('settings', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['success' => true, 'url' => $url, 'path' => $path]);
    }

    // ---------------------------------------------------------------
    // News CRUD (admin only)
    // ---------------------------------------------------------------

    public function getNews(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));

        $query = News::query()->orderByDesc('published_at')->orderByDesc('id');

        if (!empty($search)) {
            $query->where('title', 'like', "%$search%");
        }

        $page = (int) $request->input('page', 1);

        return response()->json([
            'success' => true,
            'data'    => $query->paginate(10, ['*'], 'page', $page),
        ]);
    }

    public function storeNews(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:news,slug',
            'excerpt'      => 'nullable|string|max:1000',
            'content'      => 'nullable|string',
            'tag'          => 'nullable|string|max:100',
            'image'        => 'nullable|string|max:1000',
            'published_at' => 'nullable|date',
            'status'       => 'required|in:active,draft',
        ]);

        $validated['published_at'] = $validated['published_at'] ?? now()->toDateString();
        $validated['slug']         = $this->uniqueNewsSlug($validated['slug'] ?? null, $validated['title']);

        $news = News::create($validated);

        return response()->json(['success' => true, 'data' => $news], 201);
    }

    public function updateNews(Request $request, int $id): JsonResponse
    {
        $news = News::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:news,slug,' . $id,
            'excerpt'      => 'nullable|string|max:1000',
            'content'      => 'nullable|string',
            'tag'          => 'nullable|string|max:100',
            'image'        => 'nullable|string|max:1000',
            'published_at' => 'nullable|date',
            'status'       => 'required|in:active,draft',
        ]);

        $validated['published_at'] = $validated['published_at'] ?? now()->toDateString();
        $validated['slug']         = $this->uniqueNewsSlug($validated['slug'] ?? null, $validated['title'], $id);

        $news->update($validated);

        return response()->json(['success' => true, 'data' => $news]);
    }

    private function uniqueNewsSlug(?string $slug, string $title, ?int $excludeId = null): string
    {
        $base = $slug ? \Illuminate\Support\Str::slug($slug) : \Illuminate\Support\Str::slug($title);
        if (!$base) $base = 'bai-viet';
        $candidate = $base;
        $i = 2;
        while (
            \App\Models\News::where('slug', $candidate)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $candidate = $base . '-' . $i++;
        }
        return $candidate;
    }

    public function destroyNews(int $id): JsonResponse
    {
        $news = News::findOrFail($id);
        $news->delete();

        return response()->json(['success' => true]);
    }

    public function uploadNewsImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $path = $request->file('image')->store('news', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['success' => true, 'url' => $url, 'path' => $path]);
    }

    // ---------------------------------------------------------------
    // FAQ CRUD (admin only)
    // ---------------------------------------------------------------

    public function getFaqs(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => Faq::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function storeFaq(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'question'   => 'required|string',
            'answer'     => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? ((int) Faq::max('sort_order') + 1);

        $faq = Faq::create($validated);

        return response()->json(['success' => true, 'data' => $faq], 201);
    }

    public function updateFaq(Request $request, int $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'question'   => 'required|string',
            'answer'     => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? $faq->sort_order;

        $faq->update($validated);

        return response()->json(['success' => true, 'data' => $faq]);
    }

    public function destroyFaq(int $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json(['success' => true]);
    }

    // ---------------------------------------------------------------
    // Page Content - Giới thiệu / Hợp tác (admin only)
    // ---------------------------------------------------------------

    public function getPageContent(string $slug): JsonResponse
    {
        if (!in_array($slug, ['about', 'partner'], true)) {
            return response()->json(['success' => false, 'message' => 'Trang không hợp lệ.'], 404);
        }

        return response()->json(['success' => true, 'data' => PageContent::dataFor($slug)]);
    }

    public function updatePageContent(Request $request, string $slug): JsonResponse
    {
        if (!in_array($slug, ['about', 'partner'], true)) {
            return response()->json(['success' => false, 'message' => 'Trang không hợp lệ.'], 404);
        }

        $defaults = PageContent::defaults($slug);
        $data     = [];

        foreach ($defaults as $key => $default) {
            $data[$key] = (string) $request->input($key, $default);
        }

        PageContent::updateOrCreate(['slug' => $slug], ['data' => $data]);

        return response()->json(['success' => true, 'data' => PageContent::dataFor($slug)]);
    }

    // ---------------------------------------------------------------
    // Remarketing email management (admin only)
    // ---------------------------------------------------------------

    public function getRemarketingSettings(): JsonResponse
    {
        $s = DB::table('settings')->where('id', 1)->first();

        $eligibleCount = DB::table('orders')
            ->whereNull('remarketing_sent_at')
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->whereNotNull('checkout_date')
            ->whereDate('checkout_date', '>=', now()->subDays(60)->toDateString())
            ->whereDate('checkout_date', '<=', now()->subDays(30)->toDateString())
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'subject'     => $s->remarketing_subject ?? '',
                'greeting'    => $s->remarketing_greeting ?? '',
                'body'        => $s->remarketing_body ?? '',
                'discount'    => (int) ($s->remarketing_discount ?? 10),
                'auto'        => (bool) ($s->remarketing_auto ?? false),
                'send_at'     => $s->remarketing_send_at ?? null,
                'eligible'    => $eligibleCount,
            ],
        ]);
    }

    public function updateRemarketingSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject'  => 'nullable|string|max:255',
            'greeting' => 'nullable|string|max:2000',
            'body'     => 'nullable|string|max:2000',
            'discount' => 'required|integer|min:1|max:100',
            'auto'     => 'required|boolean',
            'send_at'  => 'nullable|date',
        ]);

        DB::table('settings')->where('id', 1)->update([
            'remarketing_subject'  => $data['subject'] ?? null,
            'remarketing_greeting' => $data['greeting'] ?? null,
            'remarketing_body'     => $data['body'] ?? null,
            'remarketing_discount' => $data['discount'],
            'remarketing_auto'     => $data['auto'] ? 1 : 0,
            'remarketing_send_at'  => !empty($data['send_at']) ? $data['send_at'] : null,
            'updated_at'           => now(),
        ]);

        Cache::forget('site_settings');

        return response()->json(['success' => true, 'message' => 'Đã lưu cài đặt remarketing.']);
    }

    public function sendRemarketingNow(Request $request): JsonResponse
    {
        $s    = DB::table('settings')->where('id', 1)->first();
        $cmd  = new SendRemarketingEmails();
        $sent = $cmd->sendToEligible($s);

        return response()->json([
            'success' => true,
            'message' => $sent > 0
                ? "Đã gửi thành công {$sent} email remarketing."
                : 'Không có khách nào đủ điều kiện nhận email lúc này.',
            'sent'    => $sent,
        ]);
    }

    // ---------------------------------------------------------------
    // Vouchers (admin only)
    // ---------------------------------------------------------------

    public function getVouchers(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));
        $page   = max(1, (int) $request->input('page', 1));

        $query = Voucher::query()->orderByDesc('created_at');
        if ($search) {
            $query->where(fn($q) => $q->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        }

        return response()->json(['success' => true, 'data' => $query->paginate(15, ['*'], 'page', $page)]);
    }

    public function storeVoucher(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'              => 'required|string|max:50|unique:vouchers,code',
            'name'              => 'required|string|max:255',
            'discount_type'     => 'required|in:percent,fixed',
            'discount_value'    => 'required|integer|min:1',
            'min_order_amount'  => 'nullable|integer|min:0',
            'max_uses'          => 'nullable|integer|min:1',
            'expires_at'        => 'nullable|date',
            'is_active'         => 'boolean',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $voucher = Voucher::create($data);

        return response()->json(['success' => true, 'data' => $voucher], 201);
    }

    public function updateVoucher(Request $request, int $id): JsonResponse
    {
        $voucher = Voucher::findOrFail($id);

        $data = $request->validate([
            'code'              => "required|string|max:50|unique:vouchers,code,{$id}",
            'name'              => 'required|string|max:255',
            'discount_type'     => 'required|in:percent,fixed',
            'discount_value'    => 'required|integer|min:1',
            'min_order_amount'  => 'nullable|integer|min:0',
            'max_uses'          => 'nullable|integer|min:1',
            'expires_at'        => 'nullable|date',
            'is_active'         => 'boolean',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $voucher->update($data);

        return response()->json(['success' => true, 'data' => $voucher]);
    }

    public function destroyVoucher(int $id): JsonResponse
    {
        Voucher::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function toggleVoucherStatus(int $id): JsonResponse
    {
        $v = Voucher::findOrFail($id);
        $v->update(['is_active' => !$v->is_active]);
        return response()->json(['success' => true, 'is_active' => $v->is_active]);
    }

    // ---------------------------------------------------------------
    // Email Campaigns (admin only)
    // ---------------------------------------------------------------

    public function getCampaigns(Request $request): JsonResponse
    {
        $campaigns = EmailCampaign::with('voucher')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($c) {
                $eligible = (new CampaignMailerService())->getEligible($c);
                return array_merge($c->toArray(), ['eligible_count' => count($eligible)]);
            });

        return response()->json(['success' => true, 'data' => $campaigns]);
    }

    public function storeCampaign(Request $request): JsonResponse
    {
        $data = $this->validateCampaign($request);
        $campaign = EmailCampaign::create($data);

        return response()->json(['success' => true, 'data' => $campaign->load('voucher')], 201);
    }

    public function updateCampaign(Request $request, int $id): JsonResponse
    {
        $campaign = EmailCampaign::findOrFail($id);
        $data     = $this->validateCampaign($request);
        $campaign->update($data);

        return response()->json(['success' => true, 'data' => $campaign->fresh('voucher')]);
    }

    public function destroyCampaign(int $id): JsonResponse
    {
        DB::table('campaign_sends')->where('campaign_id', $id)->delete();
        EmailCampaign::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function getCampaignEligible(int $id): JsonResponse
    {
        $campaign = EmailCampaign::findOrFail($id);
        $eligible = (new CampaignMailerService())->getEligible($campaign);

        return response()->json([
            'success' => true,
            'count'   => count($eligible),
            'preview' => array_slice(array_map(fn($o) => [
                'email' => $o->customer_email,
                'name'  => $o->customer_name,
                'checkout' => $o->checkout_date,
            ], $eligible), 0, 10),
        ]);
    }

    public function sendCampaignNow(int $id): JsonResponse
    {
        $campaign = EmailCampaign::with('voucher')->findOrFail($id);

        if ($campaign->status === 'sending') {
            return response()->json(['success' => false, 'message' => 'Campaign đang được gửi.'], 409);
        }

        $campaign->update(['status' => 'sending']);
        $sent = (new CampaignMailerService())->send($campaign);

        return response()->json([
            'success' => true,
            'message' => $sent > 0
                ? "Đã gửi thành công {$sent} email."
                : 'Không có khách nào đủ điều kiện nhận email lúc này.',
            'sent'    => $sent,
        ]);
    }

    private function validateCampaign(Request $request): array
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'subject'                => 'nullable|string|max:255',
            'greeting'               => 'nullable|string|max:3000',
            'body'                   => 'nullable|string|max:3000',
            'voucher_mode'           => 'required|in:none,fixed,auto',
            'voucher_id'             => 'nullable|exists:vouchers,id',
            'auto_discount_percent'  => 'nullable|integer|min:1|max:100',
            'conditions'             => 'nullable|array',
            'conditions.checkout_min_days' => 'nullable|integer|min:0',
            'conditions.checkout_max_days' => 'nullable|integer|min:1',
            'conditions.min_bookings'      => 'nullable|integer|min:1',
            'conditions.min_spent'         => 'nullable|integer|min:0',
            'conditions.order_statuses'    => 'nullable|array',
            'status'                 => 'required|in:draft,scheduled',
            'send_at'                => 'nullable|date',
        ]);

        if (($data['status'] ?? '') === 'scheduled' && empty($data['send_at'])) {
            $data['status'] = 'draft';
        }

        return $data;
    }
}
