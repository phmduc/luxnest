<?php

namespace App\Console\Commands;

use App\Mail\RemarketingVoucher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendRemarketingEmails extends Command
{
    protected $signature = 'remarketing:send {--test : Send test emails to fixed addresses instead of real orders}';
    protected $description = 'Send remarketing voucher emails to guests who stayed 30-60 days ago';

    private array $testEmails = [
        'phmduc1012@gmail.com',
        'phmduc241200@gmail.com',
    ];

    public function handle(): int
    {
        if ($this->option('test')) {
            return $this->sendTestEmails();
        }

        $orders = DB::table('orders')
            ->whereNull('remarketing_sent_at')
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->whereNotNull('checkout_date')
            ->whereDate('checkout_date', '>=', now()->subDays(60)->toDateString())
            ->whereDate('checkout_date', '<=', now()->subDays(30)->toDateString())
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No orders eligible for remarketing.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($orders as $order) {
            try {
                $voucher = $this->generateVoucherCode($order->id);
                Mail::to($order->customer_email)->send(new RemarketingVoucher($order, $voucher, 10));
                DB::table('orders')->where('id', $order->id)->update(['remarketing_sent_at' => now()]);
                $this->info("Sent to {$order->customer_email} (order #{$order->id})");
                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed order #{$order->id}: " . $e->getMessage());
                \Log::error('[Remarketing] email error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Done — {$sent}/{$orders->count()} emails sent.");
        return self::SUCCESS;
    }

    private function sendTestEmails(): int
    {
        $fakeOrder = (object) [
            'id'            => 0,
            'customer_name' => 'Nguyễn Văn Test',
            'customer_email'=> '',
            'checkin_date'  => now()->subDays(35)->toDateString(),
            'checkout_date' => now()->subDays(32)->toDateString(),
            'total_amount'  => 1200000,
        ];

        foreach ($this->testEmails as $email) {
            try {
                $fakeOrder->customer_email = $email;
                $voucher = 'LUXNEST-TEST10';
                Mail::to($email)->send(new RemarketingVoucher($fakeOrder, $voucher, 10));
                $this->info("Test email sent to {$email}");
            } catch (\Exception $e) {
                $this->error("Failed to {$email}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    private function generateVoucherCode(int $orderId): string
    {
        $hash = strtoupper(substr(md5($orderId . config('app.key') . date('Y')), 0, 6));
        return "LUXNEST{$hash}";
    }
}
