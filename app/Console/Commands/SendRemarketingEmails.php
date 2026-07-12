<?php

namespace App\Console\Commands;

use App\Mail\RemarketingVoucher;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendRemarketingEmails extends Command
{
    protected $signature = 'remarketing:send
                            {--test : Send test emails to fixed addresses}
                            {--force : Bypass auto/schedule check — always send}';

    protected $description = 'Send remarketing voucher emails to eligible guests';

    private array $testEmails = [
        'phmduc1012@gmail.com',
        'phmduc241200@gmail.com',
    ];

    public function handle(): int
    {
        if ($this->option('test')) {
            return $this->sendTestEmails();
        }

        $settings = DB::table('settings')->where('id', 1)->first();

        // If not forced, check auto toggle and one-shot schedule
        if (!$this->option('force')) {
            $auto      = (bool) ($settings->remarketing_auto ?? false);
            $sendAt    = $settings->remarketing_send_at ?? null;
            $hasOneShot = $sendAt && strtotime($sendAt) <= time();

            if (!$auto && !$hasOneShot) {
                $this->info('Remarketing auto is off and no one-shot schedule pending. Skipping.');
                return self::SUCCESS;
            }
        }

        $sent = $this->sendToEligible($settings);

        // Clear one-shot schedule after firing
        if ($settings && $settings->remarketing_send_at) {
            DB::table('settings')->where('id', 1)->update([
                'remarketing_send_at' => null,
                'updated_at'          => now(),
            ]);
            \App\Models\Setting::current(); // bust cache after direct update
            \Illuminate\Support\Facades\Cache::forget('site_settings');
        }

        $this->info("Done — {$sent} email(s) sent.");
        return self::SUCCESS;
    }

    public function sendToEligible(mixed $settings): int
    {
        $discount  = (int) ($settings->remarketing_discount ?? 10);
        $greeting  = $settings->remarketing_greeting ?? '';
        $body      = $settings->remarketing_body ?? '';
        $subject   = $settings->remarketing_subject ?? '';
        $hotline   = $settings->hotline ?? '';

        $orders = DB::table('orders')
            ->whereNull('remarketing_sent_at')
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->whereNotNull('checkout_date')
            ->whereDate('checkout_date', '>=', now()->subDays(60)->toDateString())
            ->whereDate('checkout_date', '<=', now()->subDays(30)->toDateString())
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->get();

        $sent = 0;
        foreach ($orders as $order) {
            try {
                $voucher = $this->generateVoucherCode($order->id);
                Mail::to($order->customer_email)->send(
                    new RemarketingVoucher($order, $voucher, $discount, $greeting, $body, $hotline, $subject)
                );
                DB::table('orders')->where('id', $order->id)->update(['remarketing_sent_at' => now()]);
                $this->line("  ✓ {$order->customer_email} (order #{$order->id})");
                $sent++;
            } catch (\Exception $e) {
                $this->error("  ✗ order #{$order->id}: " . $e->getMessage());
                \Log::error('[Remarketing] email error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        return $sent;
    }

    private function sendTestEmails(): int
    {
        $settings  = DB::table('settings')->where('id', 1)->first();
        $discount  = (int) ($settings->remarketing_discount ?? 10);
        $greeting  = $settings->remarketing_greeting ?? '';
        $body      = $settings->remarketing_body ?? '';
        $subject   = $settings->remarketing_subject ?? '';
        $hotline   = $settings->hotline ?? '';

        $fakeOrder = (object) [
            'id'             => 0,
            'customer_name'  => 'Nguyễn Văn Test',
            'customer_email' => '',
            'checkin_date'   => now()->subDays(35)->toDateString(),
            'checkout_date'  => now()->subDays(32)->toDateString(),
            'total_amount'   => 1200000,
        ];

        $sent = 0;
        foreach ($this->testEmails as $email) {
            try {
                $fakeOrder->customer_email = $email;
                Mail::to($email)->send(
                    new RemarketingVoucher($fakeOrder, 'LUXNEST-TEST10', $discount, $greeting, $body, $hotline, $subject)
                );
                $this->info("Test email sent to {$email}");
                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to {$email}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    public static function generateVoucherCode(int $orderId): string
    {
        $hash = strtoupper(substr(md5($orderId . config('app.key') . date('Y')), 0, 6));
        return "LUXNEST{$hash}";
    }
}
