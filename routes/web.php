<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeatherController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. 
| These routes are loaded by the RouteServiceProvider within a group 
| which contains the "web" middleware group. 
|
*/

// トップページ
Route::get('/', function () {
    return view('welcome');
});

// ダッシュボード（認証必須）
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 認証必須ルート群
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Weather
    // 一覧表示（READ）
    Route::get('/weather', [WeatherController::class, 'index'])
        ->name('weather.index');

    // データ取得（Visual Crossing APIから最新天気を取得して保存）
    Route::get('/weather/fetch', [WeatherController::class, 'fetchAndStore'])
        ->name('weather.fetch');
});

require __DIR__.'/auth.php';
