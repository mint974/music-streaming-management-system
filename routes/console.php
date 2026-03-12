<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động đánh dấu expired + hạ role về free + gửi email hết hạn — 00:05 mỗi ngày
Schedule::command('subscription:expire')->dailyAt('00:05');

// Gửi email nhắc nhở trước 1 ngày hết hạn (Premium + Nghệ sĩ) — 09:00 mỗi ngày
Schedule::command('subscription:remind')->dailyAt('09:00');
