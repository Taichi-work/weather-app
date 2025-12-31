<?php

namespace App\Services;

use App\Models\Weather;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * Visual Crossing から天気を取得して保存
     */
    public function fetchAndStore(): void
    {
        $apiKey   = config('services.visual_crossing.key');
        $location = 'Tokyo';

        $url = "https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/{$location}"
             . "?unitGroup=metric&key={$apiKey}&contentType=json";

        $response = Http::get($url);

        if (! $response->successful()) {
            throw new \Exception('天気APIの取得に失敗しました');
        }

        $data = $response->json();
        $today = $data['days'][0];

        Weather::updateOrCreate(
            ['date' => $today['datetime']],
            [
                'location'      => $location,
                'temp_max'      => $today['tempmax'],
                'temp_min'      => $today['tempmin'],
                'precipitation' => $today['precipprob'] ?? 0,
                'advice'        => $this->getClothingAdvice($today['tempmax']),
            ]
        );
    }

    /**
     * 簡易服装アドバイス
     */
    private function getClothingAdvice(float $tempMax): string
    {
        return match (true) {
            $tempMax >= 30 => '軽装でOK。日焼け対策を',
            $tempMax >= 20 => '半袖で快適です',
            $tempMax >= 10 => '長袖やジャケットがおすすめ',
            default        => 'コートや厚手の服が必要です',
        };
    }
}
