<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Legacy single remarketing: run hourly, command checks auto/schedule flags
Schedule::command('remarketing:send')->hourly();

// Email campaigns: fire scheduled campaigns
Schedule::call(function () {
    $campaigns = \App\Models\EmailCampaign::with('voucher')
        ->where('status', 'scheduled')
        ->where('send_at', '<=', now())
        ->get();

    foreach ($campaigns as $campaign) {
        $campaign->update(['status' => 'sending']);
        (new \App\Services\CampaignMailerService())->send($campaign);
    }
})->everyMinute()->name('fire-scheduled-campaigns');
