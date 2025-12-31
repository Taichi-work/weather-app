<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Weather;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class WeatherController extends Controller
{
    /**
     * 一覧表示（READ）
     */
    public function index(Request $request)
        {
            $location = $request->input('location', 'Tokyo');

            // 最新の天気（現在表示用）
            $latestWeather = Weather::where('location', $location)
                ->orderByDesc('date')
                ->first();

            // 過去14日分（履歴一覧用）
            $fromDate = Carbon::today()->subDays(13);

            $weatherHistory = Weather::where('location', $location)
                ->whereBetween('date', [
                    now()->subDays(14)->toDateString(),
                    now()->toDateString(),
                ])
                ->orderBy('date', 'desc')
                ->get();

            return view('weather.index', [
                'weather'         => $latestWeather,
                'weatherHistory'  => $weatherHistory,
                'location'        => $location,
            ]);
        }



    /**
     * Visual Crossing から天気を取得して保存
     */
    public function fetchAndStore()
        {
            $apiKey = config('services.visual_crossing.key');

            if (empty($apiKey)) {
                Log::error('Visual Crossing APIキー未設定');

                return redirect()
                    ->route('weather.index')
                    ->with('error', 'APIキーが設定されていません');
            }

            /*
            |--------------------------------------------------------------------------
            | 地域対応（ここから追加）
            |--------------------------------------------------------------------------
            */

            // 利用可能な地域（国外OK）
            $availableLocations = [
                'Tokyo',
                'Osaka',
                'Okayama',
                'Hiroshima',
                'New York',
                'Vancouver',
                'Cebu',
                'Gold Coast',
                'Melbourne',
            ];

            // リクエストから地域取得（なければ Tokyo）
            $location = request('location', 'Tokyo');

            // 不正な地域指定を防ぐ
            if (!in_array($location, $availableLocations, true)) {
                Log::warning('不正な地域指定', ['location' => $location]);

                return redirect()
                    ->route('weather.index')
                    ->with('error', '選択された地域は対応していません');
            }

            /*
            |--------------------------------------------------------------------------
            | API 呼び出し
            |--------------------------------------------------------------------------
            */

            $url = "https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/{$location}"
                . "?unitGroup=metric&key={$apiKey}&contentType=json";

            $response = Http::get($url);

            if (!$response->ok()) {
                Log::error('Visual Crossing API通信失敗', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return redirect()
                    ->route('weather.index')
                    ->with('error', 'API通信に失敗しました');
            }

            $data = $response->json();

            if (!isset($data['days'][0])) {
                Log::error('Visual Crossing APIレスポンス異常', [
                    'response' => $data,
                ]);

                return redirect()
                    ->route('weather.index')
                    ->with('error', '天気データが取得できませんでした');
            }

            $day = $data['days'][0];

            /*
            |--------------------------------------------------------------------------
            | DB 保存
            |--------------------------------------------------------------------------
            */

            $weather = Weather::updateOrCreate(
                [
                    'date'     => $day['datetime'],
                    'location' => $location,
                ],
                [
                    'temp_max'      => $day['tempmax'],
                    'temp_min'      => $day['tempmin'],
                    'precipitation' => $day['precipprob'] ?? 0,
                    'advice'        => $this->getClothingAdvice($day['tempmax']),
                ]
            );

            // updated_at を確実に更新（最終更新表示用）
            $weather->touch();

            Log::info('天気データ更新成功', [
                'location' => $location,
                'date'     => $day['datetime'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | 画面へ戻す（選択地域を保持）
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('weather.index', ['location' => $location])
                ->with('success', "{$location} の天気を更新しました");
        }   

    /**
     * 簡易服装アドバイス生成
     */
    private function getClothingAdvice(float $tempMax): string
    {
        if ($tempMax >= 30) {
            return '軽装でOK。日焼け対策を';
        } elseif ($tempMax >= 20) {
            return '半袖で快適です';
        } elseif ($tempMax >= 10) {
            return '長袖やジャケットがおすすめ';
        } else {
            return 'コートや厚手の服が必要です';
        }
    }
}
