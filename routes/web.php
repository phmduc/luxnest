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
use App\Http\Controllers\PageController;
use App\Services\GoHostService;

// ---------------------------------------------------------------
// Public routes
// ---------------------------------------------------------------
Route::get('/', [HomeController::class, 'index']);
Route::get('/hotel/{slug}', [HotelController::class, 'show'])->name('hotel.show');
Route::get('/rooms', [RoomsController::class, 'index'])->name('rooms.index');
Route::get('/villa', [VillaController::class, 'index'])->name('villa.index');
Route::get('/villa/{slug}', [VillaController::class, 'show'])->name('villa.show');
Route::get('/thue-xe', [CarRentalController::class, 'index'])->name('car-rental.index');

Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about.index');
Route::get('/cau-hoi-thuong-gap', [PageController::class, 'faq'])->name('faq.index');
Route::get('/hop-tac', [PageController::class, 'partner'])->name('partner.index');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact.index');
Route::get('/tin-tuc', [PageController::class, 'news'])->name('news.index');
Route::get('/tin-tuc/{slug}', [PageController::class, 'newsShow'])->name('news.show');

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
        Route::post('/rooms/upload-video', [AdminDashboardController::class, 'uploadRoomVideo']);

        // Villa listings management
        Route::get('/villas', [AdminDashboardController::class, 'getVillas']);
        Route::post('/villas', [AdminDashboardController::class, 'storeVilla']);
        Route::put('/villas/{id}', [AdminDashboardController::class, 'updateVilla']);
        Route::delete('/villas/{id}', [AdminDashboardController::class, 'destroyVilla']);
        Route::patch('/villas/{id}/status', [AdminDashboardController::class, 'toggleVillaStatus']);
        Route::post('/villas/upload-image', [AdminDashboardController::class, 'uploadVillaImage']);
        Route::post('/villas/upload-video', [AdminDashboardController::class, 'uploadVillaVideo']);

        // Business settings
        Route::get('/settings', [AdminDashboardController::class, 'getSettings']);
        Route::post('/settings', [AdminDashboardController::class, 'updateSettings']);
        Route::post('/settings/upload-logo', [AdminDashboardController::class, 'uploadSettingsLogo']);
        Route::post('/settings/upload-og-image', [AdminDashboardController::class, 'uploadSettingsOgImage']);

        // News management
        Route::get('/news', [AdminDashboardController::class, 'getNews']);
        Route::post('/news', [AdminDashboardController::class, 'storeNews']);
        Route::put('/news/{id}', [AdminDashboardController::class, 'updateNews']);
        Route::delete('/news/{id}', [AdminDashboardController::class, 'destroyNews']);
        Route::post('/news/upload-image', [AdminDashboardController::class, 'uploadNewsImage']);

        // FAQ management
        Route::get('/faqs', [AdminDashboardController::class, 'getFaqs']);
        Route::post('/faqs', [AdminDashboardController::class, 'storeFaq']);
        Route::put('/faqs/{id}', [AdminDashboardController::class, 'updateFaq']);
        Route::delete('/faqs/{id}', [AdminDashboardController::class, 'destroyFaq']);

        // Page content (Giới thiệu / Hợp tác)
        Route::get('/page-contents/{slug}', [AdminDashboardController::class, 'getPageContent']);
        Route::post('/page-contents/{slug}', [AdminDashboardController::class, 'updatePageContent']);
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
