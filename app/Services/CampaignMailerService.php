<?php

namespace App\Services;

use App\Mail\RemarketingVoucher;
use App\Models\EmailCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CampaignMailerService
{
    public function getEligible(EmailCampaign $campaign): array
    {
        $cond     = $campaign->conditions ?? [];
        $minDays  = (int) ($cond['checkout_min_days'] ?? 0);
        $maxDays  = (int) ($cond['checkout_max_days'] ?? 3650);
        $minBooks = (int) ($cond['min_bookings'] ?? 1);
        $minSpent = (int) ($cond['min_spent'] ?? 0);
        $statuses = $cond['order_statuses'] ?? ['confirmed', 'completed', 'pending'];

        // Emails already sent for this campaign
        $sentEmails = DB::table('campaign_sends')
            ->where('campaign_id', $campaign->id)
            ->pluck('customer_email')
            ->toArray();

        // Distinct emails with at least one qualifying order in the date window
        $emails = DB::table('orders')
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->whereIn('status', $statuses)
            ->whereDate('checkout_date', '>=', now()->subDays($maxDays)->toDateString())
            ->whereDate('checkout_date', '<=', now()->subDays($minDays)->toDateString())
            ->whereNotIn('customer_email', $sentEmails)
            ->distinct()
            ->pluck('customer_email')
            ->toArray();

        $eligible = [];
        foreach ($emails as $email) {
            // Total bookings & spent across ALL orders (not just the window)
            $stats = DB::table('orders')
                ->where('customer_email', $email)
                ->whereIn('status', $statuses)
                ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total')
                ->first();

            if ($stats->cnt < $minBooks) continue;
            if ($minSpent > 0 && $stats->total < $minSpent) continue;

            // Pick most recent order in the window for personalization
            $order = DB::table('orders')
                ->where('customer_email', $email)
                ->whereIn('status', $statuses)
                ->whereDate('checkout_date', '>=', now()->subDays($maxDays)->toDateString())
                ->whereDate('checkout_date', '<=', now()->subDays($minDays)->toDateString())
                ->orderByDesc('checkout_date')
                ->first();

            if ($order) $eligible[] = $order;
        }

        return $eligible;
    }

    public function send(EmailCampaign $campaign): int
    {
        $eligible  = $this->getEligible($campaign);
        $settings  = DB::table('settings')->where('id', 1)->first();
        $hotline   = $settings->hotline ?? '';
        $subject   = $campaign->subject ?: '';
        $greeting  = $campaign->greeting ?: '';
        $body      = $campaign->body ?: '';
        $sent      = 0;

        // Resolve fixed voucher code
        $fixedCode = null;
        if ($campaign->voucher_mode === 'fixed' && $campaign->voucher_id) {
            $fixedCode = DB::table('vouchers')->where('id', $campaign->voucher_id)->value('code');
        }

        foreach ($eligible as $order) {
            try {
                $voucherCode    = '';
                $discountPercent = $campaign->auto_discount_percent ?? 10;

                if ($campaign->voucher_mode === 'fixed') {
                    $voucherCode = $fixedCode ?? '';
                } elseif ($campaign->voucher_mode === 'auto') {
                    $voucherCode = $this->generateCode($order->id, $campaign->id);
                }

                // Determine discount for display
                if ($campaign->voucher_mode === 'fixed' && $campaign->voucher) {
                    $vr = $campaign->voucher;
                    $discountPercent = $vr->discount_type === 'percent' ? $vr->discount_value : 0;
                }

                Mail::to($order->customer_email)->send(
                    new RemarketingVoucher(
                        $order, $voucherCode, $discountPercent,
                        $greeting, $body, $hotline, $subject,
                        $campaign->voucher_mode
                    )
                );

                DB::table('campaign_sends')->insertOrIgnore([
                    'campaign_id'    => $campaign->id,
                    'customer_email' => $order->customer_email,
                    'order_id'       => $order->id,
                    'voucher_code'   => $voucherCode ?: null,
                    'sent_at'        => now(),
                ]);

                $sent++;
            } catch (\Exception $e) {
                Log::error('[Campaign] email error', [
                    'campaign_id' => $campaign->id,
                    'email'       => $order->customer_email,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $campaign->update([
            'sent_count' => $campaign->sent_count + $sent,
            'sent_at'    => now(),
            'status'     => 'sent',
        ]);

        return $sent;
    }

    public function generateCode(int $orderId, int $campaignId): string
    {
        $hash = strtoupper(substr(md5($orderId . $campaignId . config('app.key')), 0, 6));
        return "LX{$hash}";
    }
}
