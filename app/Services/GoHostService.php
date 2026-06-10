<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoHostService
{
    private string $baseUrl;
    private string $token;
    private string $tenantId;

    public function __construct()
    {
        $key    = config('services.gohost.api_key', '');
        $secret = config('services.gohost.api_secret', '');

        $this->baseUrl  = config('services.gohost.base_url');
        $this->tenantId = config('services.gohost.tenant_id', '');

        // Bearer format: "{api_key}:{api_secret}"
        if ($key && $secret) {
            $this->token = $key . ':' . $secret;
        } elseif ($key) {
            $this->token = $key; // legacy single-token fallback
        } else {
            $this->token = '';
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->tenantId);
    }

    // ----------------------------------------------------------------
    // Private HTTP helper
    // ----------------------------------------------------------------

    private function request(string $method, string $endpoint, array $payload = []): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'GoHost API chưa được cấu hình (thiếu API Key/Secret).'];
        }

        $url = $this->baseUrl . $endpoint;

        $http = Http::withToken($this->token)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->timeout(15);

        $response = match (strtoupper($method)) {
            'POST'  => $http->post($url, $payload),
            'PUT'   => $http->put($url, $payload),
            'PATCH' => $http->patch($url, $payload),
            default => $http->get($url, $payload),
        };

        if ($response->failed()) {
            Log::error('[GoHost] Error ' . $response->status(), [
                'url'  => $url,
                'body' => $response->body(),
            ]);
        }

        return $response->json() ?? ['success' => false, 'message' => 'Empty response from GoHost.'];
    }

    // ----------------------------------------------------------------
    // Room Types
    // ----------------------------------------------------------------

    /**
     * Search available room types for given dates & occupancy.
     * GET /properties/{tenant_id}/room_types/search
     */
    public function searchRoomTypes(
        string $checkIn,
        string $checkOut,
        int    $adults      = 1,
        int    $children    = 0,
        int    $infants     = 0,
        string $roomTypeId  = ''
    ): array {
        if (empty($this->tenantId)) {
            return ['success' => false, 'message' => 'GoHost Tenant ID chưa được cấu hình.'];
        }

        $params = [
            'checkin_date'       => $checkIn,
            'checkout_date'      => $checkOut,
            'occupancy_adults'   => $adults,
            'occupancy_children' => $children,
            'occupancy_infant'   => $infants,
        ];

        if ($roomTypeId) {
            $params['room_type_id'] = $roomTypeId;
        }

        $endpoint = '/properties/' . urlencode($this->tenantId) . '/room_types/search';

        return $this->request('GET', $endpoint, $params);
    }

    /**
     * Check availability for a single room type (product page).
     */
    public function checkAvailability(string $roomTypeId, string $checkIn, string $checkOut, int $guests = 1): array
    {
        return $this->searchRoomTypes($checkIn, $checkOut, $guests, 0, 0, $roomTypeId);
    }

    // ----------------------------------------------------------------
    // Bookings
    // ----------------------------------------------------------------

    /**
     * Create a new booking.
     * POST /properties/{tenant_id}/bookings
     *
     * Expected $data keys:
     *   checkin_date, checkout_date,
     *   customer: { name, email, phone },
     *   booking_rooms: [{ room_type_id, quantity }],
     *   note (optional)
     */
    public function createBooking(array $data): array
    {
        if (empty($this->tenantId)) {
            return ['success' => false, 'message' => 'GoHost Tenant ID chưa được cấu hình.'];
        }

        $endpoint = '/properties/' . urlencode($this->tenantId) . '/bookings';

        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Get booking details.
     * GET /properties/{tenant_id}/bookings/{booking_id}
     */
    public function getBooking(string $bookingId): array
    {
        $endpoint = '/properties/' . urlencode($this->tenantId) . '/bookings/' . urlencode($bookingId);

        return $this->request('GET', $endpoint);
    }

    /**
     * List bookings for a date range.
     * GET /properties/{tenant_id}/bookings
     */
    public function getBookings(string $startDate, string $endDate, int $page = 1, int $perPage = 50): array
    {
        $endpoint = '/properties/' . urlencode($this->tenantId) . '/bookings';

        return $this->request('GET', $endpoint, [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'page'       => $page,
            'per_page'   => $perPage,
        ]);
    }

    /**
     * Update a booking (e.g. check-in status).
     * POST /properties/{tenant_id}/bookings/{booking_id}/update
     */
    public function updateBooking(string $bookingId, array $data): array
    {
        $endpoint = '/properties/' . urlencode($this->tenantId)
                  . '/bookings/' . urlencode($bookingId) . '/update';

        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Add payment(s) to a booking.
     * POST /properties/{tenant_id}/bookings/{booking_id}/payments
     *
     * $payments: [{ amount, method, note }]
     */
    public function addPayment(string $bookingId, array $payments): array
    {
        $endpoint = '/properties/' . urlencode($this->tenantId)
                  . '/bookings/' . urlencode($bookingId) . '/payments';

        return $this->request('POST', $endpoint, ['payments' => $payments]);
    }

    // ----------------------------------------------------------------
    // Connection test
    // ----------------------------------------------------------------

    /**
     * Ping the API — search rooms for today/tomorrow with 1 adult.
     * Returns ['ok' => true/false, 'message' => '...', 'data' => ...]
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'message' => 'Chưa cấu hình GOHOST_API_KEY, GOHOST_API_SECRET hoặc GOHOST_TENANT_ID trong .env'];
        }

        $result = $this->searchRoomTypes(
            now()->toDateString(),
            now()->addDay()->toDateString(),
            1
        );

        $ok = isset($result['success']) ? (bool) $result['success'] : !isset($result['message']);

        return [
            'ok'      => $ok,
            'message' => $ok ? 'Kết nối GoHost thành công!' : ($result['message'] ?? 'Lỗi không xác định'),
            'data'    => $result,
        ];
    }
}
