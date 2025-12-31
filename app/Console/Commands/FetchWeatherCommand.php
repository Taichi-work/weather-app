<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Weather;
use Illuminate\Support\Facades\Http;

class FetchWeatherCommand extends Command
{
    /**
     * コマンド名
     */
    protected $signature = 'weather:fetch';

    /**
     * 説明
     */
    protected $description = 'Visual Crossing APIから天気を取得して保存する';

    /**
     * 実行処理
     */
    public function handle(): int
    {
        $apiKey = config('services.visual_crossing.key');
        $location = 'Tokyo';

        if (empty($apiKey)) {
            $this->error('APIキーが設定されていません');
            return Command::FAILURE;
        }

        $url = "https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/{$location}"
            . "?unitGroup=metric&key={$apiKey}&contentType=json";

        $response = Http::get($url);

        if (!$response->ok()) {
            $this->error('API通信に失敗しました');
            return Command::FAILURE;
        }

        $data = $response->json();

        if (!isset($data['days'][0])) {
            $this->error('天気データが取得できませんでした');
            return Command::FAILURE;
        }

        $day = $data['days'][0];

        Weather::updateOrCreate(
            ['date' => $day['datetime']],
            [
                'location'      => $location,
                'temp_max'      => $day['tempmax'],
                'temp_min'      => $day['tempmin'],
                'precipitation' => $day['precipprob'] ?? 0,
                'advice'        => $this->getClothingAdvice($day['tempmax']),
            ]
        );

        $this->info('天気データを更新しました');

        return Command::SUCCESS;
    }

    /**
     * 服装アドバイス
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
