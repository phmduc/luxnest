<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Legacy single remarketing: run hourly, command checks auto/schedule flags
Schedule::command('remarketing:send')->hourly();

// Email campaigns: fire scheduled + recurring campaigns
Schedule::call(function () {
    $mailer = new \App\Services\CampaignMailerService();

    // One-shot: fire at the scheduled datetime
    \App\Models\EmailCampaign::with('voucher')
        ->where('status', 'scheduled')
        ->where('send_at', '<=', now())
        ->get()
        ->each(function ($c) use ($mailer) {
            $c->update(['status' => 'sending']);
            $mailer->send($c);
        });

    // Recurring: fire when interval has elapsed since last send
    \App\Models\EmailCampaign::with('voucher')
        ->where('status', 'recurring')
        ->where(function ($q) {
            $q->whereNull('sent_at')
              ->orWhere(fn($q2) => $q2
                  ->where('repeat_interval', 'daily')
                  ->where('sent_at', '<=', now()->subDay()))
              ->orWhere(fn($q2) => $q2
                  ->where('repeat_interval', 'weekly')
                  ->where('sent_at', '<=', now()->subWeek()));
        })
        ->get()
        ->each(fn($c) => $mailer->send($c));
})->everyMinute()->name('fire-scheduled-campaigns');
