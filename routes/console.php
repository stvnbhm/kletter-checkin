<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\DatabaseBackup;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('checkins:auto-checkout')
    ->name('checkins-auto-checkout')
    ->everyMinute();

Schedule::command(DatabaseBackup::class)->dailyAt('03:00');