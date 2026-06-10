<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\VillaController;
use App\Http\Controllers\CarRentalController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\MemberDashboardController;
use App\Services\GoHostService;

// ---------------------------------------------------------------
// Public routes
// ---------------------------------------------------------------
Route::get('/', [HomeController::class, 'index']);
Route::get('/hotel/{slug}', [HotelController::class, 'show'])->name('hotel.show');
Route::get('/rooms', [RoomsController::class, 'index'])->name('rooms.index');
Route::get('/villa', [VillaController::class, 'index'])->name('villa.index');
Route::get('/thue-xe', [CarRentalController::class, 'index'])->name('car-rental.index');

Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');

Route::get('/dat-phong', [BookingController::class, 'show'])->name('booking.show');
Route::post('/dat-phong', [BookingController::class, 'store'])->name('booking.store');
Route::get('/dat-phong/thanh-cong', [BookingController::class, 'success'])->name('booking.success');

Route::get('/api/availability', function (\Illuminate\Http\Request $req, GoHostService $gohost) {
    $slug    = $req->get('slug');
    $checkIn = $req->get('check_in');
    $checkOut= $req->get('check_out');
    $adults  = max(1, (int) $req->get('adults', 1));

    if (!$slug || !$checkIn || !$checkOut) {
        return response()->json(['available' => null, 'message' => 'Thiếu thông tin']);
    }

    $room = \App\Models\Room::where('slug', $slug)->where('status', 'active')->first();
    if (!$room) return response()->json(['available' => false, 'message' => 'Không tìm thấy phòng']);

    if (!$gohost->isConfigured() || !$room->gohost_room_type_id) {
        return response()->json(['available' => false, 'message' => 'Hết phòng', 'price' => $room->price]);
    }

    $result = $gohost->checkAvailability($room->gohost_room_type_id, $checkIn, $checkOut, $adults);
    $available = !empty($result['data']);

    return response()->json([
        'available' => $available,
        'message'   => $available ? 'Còn phòng' : 'Hết phòng cho ngày này',
        'price'     => $room->price,
    ]);
})->name('api.availability');

Route::get('/gohost/test', function (GoHostService $gohost) {
    return response()->json($gohost->testConnection());
})->name('gohost.test');

// ---------------------------------------------------------------
// Auth routes
// ---------------------------------------------------------------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Smart redirect after login
Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    if (Auth::user()->isStaff()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('member.dashboard');
})->middleware('auth')->name('dashboard');

// ---------------------------------------------------------------
// Admin routes (admin + employee)
// ---------------------------------------------------------------
Route::prefix('admin')->middleware(['auth', 'role:admin,employee'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('api')->group(function () {
        Route::get('/bookings', [AdminDashboardController::class, 'getBookings']);
        Route::get('/bookings/{id}', [AdminDashboardController::class, 'getBookingDetail']);
        Route::post('/bookings/{id}/checkin', [AdminDashboardController::class, 'checkin']);
        Route::post('/bookings/{id}/cancel', [AdminDashboardController::class, 'cancelBooking']);
        Route::post('/qr-checkin', [AdminDashboardController::class, 'qrCheckin']);
    });

    // Member management (admin only)
    Route::prefix('api')->middleware('role:admin')->group(function () {
        Route::get('/members', [AdminDashboardController::class, 'getMembers']);
        Route::post('/members', [AdminDashboardController::class, 'storeMember']);
        Route::put('/members/{id}', [AdminDashboardController::class, 'updateMember']);
        Route::delete('/members/{id}', [AdminDashboardController::class, 'destroyMember']);

        // Room management
        Route::get('/rooms', [AdminDashboardController::class, 'getRooms']);
        Route::post('/rooms', [AdminDashboardController::class, 'storeRoom']);
        Route::put('/rooms/{id}', [AdminDashboardController::class, 'updateRoom']);
        Route::delete('/rooms/{id}', [AdminDashboardController::class, 'destroyRoom']);
        Route::patch('/rooms/{id}/status', [AdminDashboardController::class, 'toggleRoomStatus']);
        Route::post('/rooms/upload-image', [AdminDashboardController::class, 'uploadRoomImage']);

        // Business settings
        Route::get('/settings', [AdminDashboardController::class, 'getSettings']);
        Route::post('/settings', [AdminDashboardController::class, 'updateSettings']);
        Route::post('/settings/upload-logo', [AdminDashboardController::class, 'uploadSettingsLogo']);
    });
});

// ---------------------------------------------------------------
// Member (personal) dashboard
// ---------------------------------------------------------------
Route::prefix('me')->middleware('auth')->group(function () {
    Route::get('/', [MemberDashboardController::class, 'index'])->name('member.dashboard');
    Route::post('/profile', [MemberDashboardController::class, 'updateProfile'])->name('member.profile.update');

    Route::prefix('api')->group(function () {
        Route::get('/bookings', [MemberDashboardController::class, 'getMyBookings']);
    });
});
