<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\GoHostService;
use Illuminate\Http\Request;

class RoomsController extends Controller
{
    public function __construct(private GoHostService $gohost) {}

    public function index(Request $request)
    {
        $keyword  = (string) $request->input('keyword', '');
        $checkIn  = (string) $request->input('check_in', '');
        $checkOut = (string) $request->input('check_out', '');
        $adults   = max(1, (int) $request->input('adults', 1));
        $children = (int) $request->input('children', 0);
        $sort     = (string) $request->input('sort', 'recommended');

        $minPrice  = (int) $request->input('min_price', 0);
        $maxPrice  = (int) $request->input('max_price', 10000000);
        $branches  = array_filter((array) $request->input('branch', []));
        $amenities = array_filter((array) $request->input('amenity', []));

        // ── GoHost availability filter ──────────────────────────
        $availableGoHostIds = null; // null = not filtered, [] = all unavailable

        if (!empty($checkIn) && !empty($checkOut) && $this->gohost->isConfigured()) {
            $result = $this->gohost->searchRoomTypes($checkIn, $checkOut, $adults, $children);

            if (!empty($result['data'])) {
                $availableGoHostIds = collect($result['data'])
                    ->map(fn($item) => $item['room_types']['id'] ?? ($item['id'] ?? null))
                    ->filter()
                    ->values()
                    ->all();
            } else {
                $availableGoHostIds = []; // GoHost returned no rooms
            }
        }

        // ── DB Query ─────────────────────────────────────────────
        $query = Room::where('status', 'active');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name',    'like', "%$keyword%")
                  ->orWhere('branch', 'like', "%$keyword%")
                  ->orWhere('type',   'like', "%$keyword%");
            });
        }

        if ($minPrice > 0)        $query->where('price', '>=', $minPrice);
        if ($maxPrice < 10000000) $query->where('price', '<=', $maxPrice);
        if (!empty($branches))    $query->whereIn('branch', $branches);

        foreach ($amenities as $am) {
            $query->where('amenities', 'like', '%' . $am . '%');
        }

        match ($sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default      => $query->orderBy('branch')->orderBy('price'),
        };

        // ── Filter by GoHost availability trước khi paginate ────────
        // Chỉ áp dụng khi có ngày tìm kiếm — chỉ hiện phòng được GoHost xác nhận còn trống
        if ($availableGoHostIds !== null) {
            if (empty($availableGoHostIds)) {
                $query->whereRaw('1 = 0'); // không có phòng nào available
            } else {
                $query->whereIn('gohost_room_type_id', $availableGoHostIds);
            }
        }

        $rooms = $query->paginate(9)->withQueryString();

        // ── Map to view format ────────────────────────────────────
        $branchMeta = [
            'Hotel'     => ['stars' => 4, 'rating' => 9.1, 'rating_text' => 'Tuyệt vời', 'reviews' => 124],
            'Villa'     => ['stars' => 5, 'rating' => 9.4, 'rating_text' => 'Xuất sắc',  'reviews' => 87],
            'Residence' => ['stars' => 5, 'rating' => 9.2, 'rating_text' => 'Tuyệt vời', 'reviews' => 156],
        ];

        $mapped = $rooms->getCollection()->map(function (Room $room) use ($branchMeta, $availableGoHostIds) {
            $meta     = $branchMeta[$room->branch] ?? ['stars' => 4, 'rating' => 9.0, 'rating_text' => 'Tuyệt vời', 'reviews' => 100];
            $images   = array_values(array_filter(array_merge([$room->image], $room->gallery ?? [])));
            $discount = ($room->regular_price && $room->regular_price > $room->price)
                        ? (int) round((1 - $room->price / $room->regular_price) * 100) : null;

            return [
                'id'            => $room->id,
                'slug'          => $room->slug,
                'name'          => $room->name,
                'stars'         => $meta['stars'],
                'type'          => $room->branch,
                'rating'        => $meta['rating'],
                'rating_text'   => $meta['rating_text'],
                'reviews_count' => $meta['reviews'],
                'location'      => 'LuxNest — ' . $room->branch,
                'distance'      => $room->type ?? $room->branch,
                'images'        => $images,
                'price'         => $room->price,
                'old_price'     => $room->regular_price ?? null,
                'discount'      => $discount,
                'amenities'     => $room->amenities ?? [],
                'cancellation'  => 'Hủy miễn phí trước 24 giờ',
                'badge'         => $availableGoHostIds !== null ? 'Còn phòng' : null,
                'promo'         => null,
            ];
        });

        $hotels = $rooms->setCollection($mapped);

        $displayKeyword  = $keyword ?: 'LuxNest';
        $hasAvailability = $availableGoHostIds !== null;

        return view('rooms.index', compact(
            'hotels', 'keyword', 'checkIn', 'checkOut', 'adults', 'children',
            'sort', 'displayKeyword', 'hasAvailability'
        ));
    }
}
