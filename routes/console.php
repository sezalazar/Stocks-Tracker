<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('balanceSheet:fetch')->dailyAt('19:00');
Schedule::command('companyData:fetch')->weeklyOn(7, '8:00');
Schedule::command('rsi:fetch')->dailyAt('20:10');
Schedule::command('stockprices:fetch')->dailyAt('10:00');