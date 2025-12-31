<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\WeatherService;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
| Artisan コマンドとスケジューラを定義する
|--------------------------------------------------------------------------
*/

// デフォルトの inspire コマンド（そのままでOK）
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// ======================================
// 天気取得コマンド（Visual Crossing）
// ======================================
Artisan::command('weather:fetch', function () {
    app(WeatherService::class)->fetchAndStore();
    $this->info('天気データを更新しました');
})->purpose('Fetch weather data from Visual Crossing');


// ======================================
// スケジューラ設定（毎日 06:00）
// ======================================
Schedule::command('weather:fetch')->dailyAt('06:00');
