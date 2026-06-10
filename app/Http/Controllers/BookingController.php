<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\Room;
use App\Services\GoHostService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function __construct(private GoHostService $gohost) {}

    public function show(Request $request)
    {
        $slug     = $request->query('slug');
        $checkin  = $request->query('checkin', '');
        $checkout = $request->query('checkout', '');
        $guests   = (int) $request->query('guests', 1);

        if (!$slug) {
            return redirect()->route('rooms.index');
        }

        $room = Room::where('slug', $slug)->where('status', 'active')->firstOrFail();

        // Calculate nights & price
        $nights     = $this->calcNights($checkin, $checkout);
        $totalPrice = $room->price * $nights;
        $user       = Auth::user();

        return view('booking.index', compact(
            'room', 'checkin', 'checkout', 'guests',
            'nights', 'totalPrice', 'user'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug'            => 'required|string',
            'checkin'         => 'required|string',
            'checkout'        => 'required|string',
            'guests'          => 'integer|min:1',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'required|email',
            'phone'           => 'required|string|max:20',
            'special_request' => 'nullable|string|max:1000',
        ]);

        $room         = Room::where('slug', $request->slug)->where('status', 'active')->firstOrFail();
        $checkin      = $request->checkin;
        $checkout     = $request->checkout;
        $guests       = (int) $request->get('guests', 1);
        $nights     = $this->calcNights($checkin, $checkout);
        $totalPrice = $room->price * $nights;
        $customerName = trim($request->first_name . ' ' . $request->last_name);
        $note         = "Khách: {$guests} người\nYêu cầu đặc biệt: " . ($request->special_request ?: 'Không có');

        // Create order in DB
        $orderId = DB::table('orders')->insertGetId([
            'status'         => 'pending',
            'currency'       => 'VND',
            'total_amount'   => $totalPrice,
            'customer_name'  => $customerName,
            'customer_email' => $request->email,
            'customer_phone' => $request->phone,
            'checkin_date'   => $checkin,
            'checkout_date'  => $checkout,
            'note'           => $note,
            'user_id'        => Auth::id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id'      => $orderId,
            'room_id'       => $room->id,
            'room_name'     => $room->name,
            'quantity'      => 1,
            'unit_price'    => $room->price,
            'subtotal'      => $totalPrice,
            'checkin_date'  => $checkin,
            'checkout_date' => $checkout,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Push to GoHost if configured
        $gohostBookingId = null;
        if ($this->gohost->isConfigured() && $room->gohost_room_type_id) {

            // Lấy rate_plan_id từ GoHost search
            $ratePlanId = null;
            $searchResult = $this->gohost->searchRoomTypes($checkin, $checkout, $guests);
            if (!empty($searchResult['data'])) {
                foreach ($searchResult['data'] as $item) {
                    $rt = $item['room_types'] ?? $item;
                    if (($rt['id'] ?? '') === $room->gohost_room_type_id) {
                        $ratePlans = $rt['rate_plans'] ?? [];
                        foreach ($ratePlans as $rp) {
                            if ($rp['is_default'] ?? false) {
                                $ratePlanId = $rp['id'];
                                break;
                            }
                        }
                        if (!$ratePlanId && !empty($ratePlans)) {
                            $ratePlanId = $ratePlans[0]['id'];
                        }
                        break;
                    }
                }
            }

            // Build days_breakdown với giá từ DB
            $daysBreakdown = [];
            $current = new \DateTime($checkin);
            $end     = new \DateTime($checkout);
            while ($current < $end) {
                $daysBreakdown[] = [
                    'day'   => $current->format('Y-m-d'),
                    'price' => $room->price,
                ];
                $current->modify('+1 day');
            }

            $bookingRoom = [
                'room_type_id'   => $room->gohost_room_type_id,
                'quantity'       => 1,
                'days_breakdown' => $daysBreakdown,
            ];
            if ($ratePlanId) $bookingRoom['rate_plan_id'] = $ratePlanId;

            $payload = [
                'checkin_date'    => $checkin,
                'checkout_date'   => $checkout,
                'source_name'     => 'website',
                'occupancy_adults'=> $guests,
                'amount'          => $totalPrice,
                'customer'        => [
                    'name'  => $customerName,
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                ],
                'booking_rooms'   => [$bookingRoom],
                'note'            => $note,
            ];

            $result = $this->gohost->createBooking($payload);

            \Log::info('[Booking] GoHost payload', $payload);
            \Log::info('[Booking] GoHost response', $result);

            if (!empty($result['data']['id'])) {
                $gohostBookingId = $result['data']['id'];
                DB::table('orders')->where('id', $orderId)->update(['gohost_booking_id' => $gohostBookingId]);
            }
        } else {
            \Log::warning('[Booking] GoHost skip — isConfigured:'.(int)$this->gohost->isConfigured().' | room_type_id:'.($room->gohost_room_type_id ?? 'NULL'));
        }

        // Send confirmation email with QR code
        try {
            $orderObj    = DB::table('orders')->where('id', $orderId)->first();
            $itemObj     = DB::table('order_items')->where('order_id', $orderId)->first();
            $auth        = hash_hmac('sha256', $orderId . 'luxnest-checkin', config('app.key'));
            $checkinUrl  = url('/admin/dashboard') . '?checkin=1&oid=' . $orderId . '&auth=' . $auth;
            $qrImageUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($checkinUrl);
            Mail::to($request->input('email'))->send(
                new BookingConfirmation($orderObj, $itemObj, $room, $qrImageUrl, $checkinUrl)
            );
        } catch (\Exception $e) {
            \Log::error('[Booking] Mail error: ' . $e->getMessage());
        }

        return redirect()->route('car-rental.index', ['booking_success' => 1, 'order_id' => $orderId]);
    }

    public function success(Request $request)
    {
        $orderId = $request->get('order_id');
        $order   = $orderId ? DB::table('orders')->where('id', $orderId)->first() : null;
        $item    = $orderId ? DB::table('order_items')->where('order_id', $orderId)->first() : null;
        $room    = $item ? Room::find($item->room_id) : null;

        return view('booking.success', compact('order', 'item', 'room'));
    }

    private function calcNights(string $checkin, string $checkout): int
    {
        try {
            $d1 = new DateTime($checkin);
            $d2 = new DateTime($checkout);
            return max(1, (int) $d1->diff($d2)->days);
        } catch (\Exception) {
            return 1;
        }
    }
}
