<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ExpireOldPosts;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\ExpireOldJobApplications;
use App\Console\Commands\SendReviewAlerts;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Your existing scheduled commands
//app(Schedule::class)->command('posts:expire')->dailyAt('00:05');
app(Schedule::class)->command('jobapplications:expire')->dailyAt('00:10');

// Add your new review alerts command to run hourly
app(Schedule::class)->command('reviews:send-alerts')->hourly();
