<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WordPressImportSeeder extends Seeder
{
    private string $sqlPath;
    private string $sqlContent;

    public function run(): void
    {
        $this->sqlPath    = base_path('../sql/local.sql');
        $this->sqlContent = file_get_contents($this->sqlPath);

        $this->command->info('📥 Bắt đầu import từ WordPress backup...');

        $this->importUsers();
        $this->importRooms();
        $this->importOrders();

        $this->command->info('✅ Import hoàn tất!');
    }

    // ----------------------------------------------------------------
    // USERS  (wp_users → users)
    // ----------------------------------------------------------------
    private function importUsers(): void
    {
        $this->command->info('👤 Importing users...');

        // wp_users format: (ID, user_login, user_pass, user_nicename, user_email, ...)
        preg_match_all(
            "/INSERT INTO `wp_users` VALUES \((\d+),'([^']+)','([^']+)','([^']+)','([^']+)','[^']*','([^']+)'/",
            $this->sqlContent,
            $m,
            PREG_SET_ORDER
        );

        foreach ($m as $row) {
            [, $wpId, $login, $wpPass, $nicename, $email, $createdAt] = $row;

            // Strip WP bcrypt prefix "$wp$" → "$2y$..."
            $password = preg_replace('/^\$wp\$/', '$', $wpPass);

            // Detect role from wp_usermeta (simplified: admin=1 is admin, rest are members)
            $role = $wpId == 1 ? 'admin' : 'member';

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name'       => $nicename ?: $login,
                    'email'      => $email,
                    'role'       => $role,
                    'password'   => $password,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );
        }

        $this->command->info('   → ' . count($m) . ' users imported');
    }

    // ----------------------------------------------------------------
    // ROOMS  (pre-parsed JSON → rooms)
    // ----------------------------------------------------------------
    private function importRooms(): void
    {
        $this->command->info('🏨 Importing rooms...');

        $jsonPath = __DIR__ . '/wp_rooms.json';
        if (!file_exists($jsonPath)) {
            $this->command->error('wp_rooms.json not found, skipping rooms.');
            return;
        }

        $rooms = json_decode(file_get_contents($jsonPath), true);
        $count = 0;

        foreach ($rooms as $room) {
            $slug  = $room['slug'];
            $title = $room['name'];

            $type = null;
            foreach (['Suite', 'Deluxe', 'Standard', 'Family', 'Couple', 'Executive'] as $t) {
                if (stripos($title, $t) !== false) { $type = $t; break; }
            }

            DB::table('rooms')->updateOrInsert(
                ['slug' => $slug],
                [
                    'wp_id'         => $room['wp_id'],
                    'slug'          => $slug,
                    'name'          => $title,
                    'branch'        => $room['branch'],
                    'type'          => $type,
                    'description'   => null,
                    'price'         => $room['price'],
                    'regular_price' => $room['regular_price'],
                    'image'         => $room['image'],
                    'amenities'     => json_encode(['Wi-Fi', 'Điều hòa', 'TV', 'Nước nóng']),
                    'status'        => 'active',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
            $count++;
        }

        $this->command->info("   → $count rooms imported");
    }

    // ----------------------------------------------------------------
    // ORDERS  (wp_wc_orders + wp_wc_order_addresses → orders + order_items)
    // ----------------------------------------------------------------
    private function importOrders(): void
    {
        $this->command->info('📦 Importing orders...');

        // Extract wp_wc_orders
        // (id, status, currency, type, discount_tax, total_amount, customer_id, billing_email, date_created, ...)
        preg_match_all(
            "/INSERT INTO `wp_wc_orders` VALUES \((\d+),'([^']+)','([^']+)','shop_order',[^,]+,([0-9.]+),(\d+),'([^']*)','([^']+)'/",
            $this->sqlContent,
            $orders,
            PREG_SET_ORDER
        );

        // Extract billing addresses
        preg_match_all(
            "/INSERT INTO `wp_wc_order_addresses` VALUES \(\d+,(\d+),'billing','([^']*)','([^']*)','([^']*)','([^']*)'/",
            $this->sqlContent,
            $addresses,
            PREG_SET_ORDER
        );

        $billing = [];
        foreach ($addresses as $a) {
            $billing[$a[1]] = [
                'first_name' => $a[2],
                'last_name'  => $a[3],
                'email'      => $a[4],
                'phone'      => $a[5],
            ];
        }

        // Extract order items (only line_item type)
        preg_match_all(
            "/INSERT INTO `wp_woocommerce_order_items` VALUES \((\d+),'([^']+)','line_item',(\d+)\)/",
            $this->sqlContent,
            $items,
            PREG_SET_ORDER
        );

        // Extract order item meta
        preg_match_all(
            "/INSERT INTO `wp_woocommerce_order_itemmeta` VALUES \(\d+,(\d+),'([^']+)','([^']*)'\)/",
            $this->sqlContent,
            $itemMeta,
            PREG_SET_ORDER
        );

        $itemMetaMap = [];
        foreach ($itemMeta as $im) {
            $itemMetaMap[$im[1]][$im[2]] = $im[3];
        }

        // Group items by order
        $itemsByOrder = [];
        foreach ($items as $item) {
            $itemsByOrder[$item[3]][] = [
                'item_id'   => $item[1],
                'name'      => $item[2],
                'item_meta' => $itemMetaMap[$item[1]] ?? [],
            ];
        }

        $orderCount = 0;
        $itemCount  = 0;

        foreach ($orders as $o) {
            [, $wpOrderId, $status, $currency, $total, $customerId, $billingEmail, $createdAt] = $o;

            $addr = $billing[$wpOrderId] ?? [];
            $name  = trim(($addr['first_name'] ?? '') . ' ' . ($addr['last_name'] ?? '')) ?: null;
            $email = $addr['email'] ?? $billingEmail ?: null;
            $phone = $addr['phone'] ?? null;

            // Map WC status
            $statusMap = [
                'wc-completed'  => 'completed',
                'wc-processing' => 'processing',
                'wc-pending'    => 'pending',
                'wc-cancelled'  => 'cancelled',
                'wc-refunded'   => 'refunded',
                'wc-failed'     => 'failed',
            ];
            $mappedStatus = $statusMap[$status] ?? 'pending';

            $userId = ($customerId > 0)
                ? DB::table('users')->where('email', $email)->value('id')
                : null;

            DB::table('orders')->updateOrInsert(
                ['wp_order_id' => (int) $wpOrderId],
                [
                    'user_id'        => $userId,
                    'status'         => $mappedStatus,
                    'currency'       => $currency,
                    'total_amount'   => (int) $total,
                    'customer_name'  => $name,
                    'customer_email' => $email,
                    'customer_phone' => $phone,
                    'created_at'     => $createdAt,
                    'updated_at'     => $createdAt,
                ]
            );
            $orderId = DB::table('orders')->where('wp_order_id', (int) $wpOrderId)->value('id');

            $orderCount++;

            // Import order items
            foreach ($itemsByOrder[$wpOrderId] ?? [] as $item) {
                $im       = $item['item_meta'];
                $subtotal = (int) (($im['_line_subtotal'] ?? $im['_line_total'] ?? 0) * 100);
                $unitPrice = (int) (($im['_product_price'] ?? 0) * 100);
                if ($subtotal === 0 && isset($im['_line_total'])) {
                    $subtotal = (int) ((float)$im['_line_total']);
                }

                $checkin  = $im['_booking_checkin']  ?? $im['pa_check-in']   ?? null;
                $checkout = $im['_booking_checkout'] ?? $im['pa_check-out']  ?? null;

                // Try to find matching room
                $roomId = DB::table('rooms')->where('name', 'like', '%' . $item['name'] . '%')->value('id');

                DB::table('order_items')->insert([
                    'order_id'     => $orderId,
                    'room_id'      => $roomId,
                    'room_name'    => $item['name'],
                    'quantity'     => (int) ($im['_qty'] ?? 1),
                    'unit_price'   => $unitPrice ?: ($subtotal),
                    'subtotal'     => $subtotal ?: (int) $total,
                    'checkin_date' => $checkin  ? date('Y-m-d', strtotime($checkin))  : null,
                    'checkout_date'=> $checkout ? date('Y-m-d', strtotime($checkout)) : null,
                    'created_at'   => $createdAt,
                    'updated_at'   => $createdAt,
                ]);
                $itemCount++;
            }
        }

        $this->command->info("   → $orderCount orders, $itemCount items imported");
    }
}
