@extends('layouts.app')

@section('content')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .glass-button {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-button:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
</style>

<div class="gradient-bg relative overflow-hidden">
    {{-- 背景装飾 --}}
    <div class="absolute top-20 left-10 w-72 h-72 bg-white opacity-10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-300 opacity-20 rounded-full blur-3xl"></div>

    <div class="container mx-auto p-6 md:p-20 lg:py-20 lg:max-w-4xl relative z-10">

        {{-- ヘッダー --}}
        <h1 class="text-4xl font-extrabold mb-8 text-white text-center">
            天気予報（{{ $location ?? 'Tokyo' }}）~Visual Crossing~
        </h1>

        {{-- 更新ボタン --}}
        <div class="mb-6">
            <a href="{{ route('weather.fetch', ['location' => $location ?? 'Tokyo']) }}"
               class="glass-button inline-block px-6 py-3 text-white font-semibold rounded-xl shadow-lg transition-all">
                天気を更新
            </a>
        </div>

        {{-- 地域選択フォーム --}}
        <form method="GET" action="{{ route('weather.fetch') }}" class="flex flex-wrap items-center gap-3 mb-8">
            <select name="location" class="glass-button appearance-none text-center rounded-xl px-4 py-3 text-white font-medium focus:ring-2 focus:ring-white focus:outline-none w-1/2 lg:w-auto">
                <option value="Tokyo" @selected(($location ?? '') === 'Tokyo') class="text-gray-900">東京</option>
                <option value="Osaka" @selected(($location ?? '') === 'Osaka') class="text-gray-900">大阪</option>
                <option value="Okayama" @selected(($location ?? '') === 'Okayama') class="text-gray-900">岡山</option>
                <option value="Hiroshima" @selected(($location ?? '') === 'Hiroshima') class="text-gray-900">広島</option>
                <optgroup label="海外" class="text-gray-900">
                    <option value="New York" @selected(($location ?? '') === 'New York') class="text-gray-900">ニューヨーク</option>
                    <option value="Vancouver" @selected(($location ?? '') === 'Vancouver') class="text-gray-900">バンクーバー</option>
                    <option value="Cebu" @selected(($location ?? '') === 'Cebu') class="text-gray-900">セブ</option>
                    <option value="Gold Coast" @selected(($location ?? '') === 'Gold Coast') class="text-gray-900">ゴールドコースト</option>
                    <option value="Melbourne" @selected(($location ?? '') === 'Melbourne') class="text-gray-900">メルボルン</option>
                </optgroup>
            </select>

            <button type="submit"
                class="glass-button px-6 py-3 text-white font-semibold rounded-xl shadow-lg transition-all">
                選択したエリアの天気を更新
            </button>
        </form>

        {{-- 最新の天気 --}}
        @if($weather)
        <div class="glass-card rounded-2xl p-8 mb-10 shadow-2xl">
            <div class="flex flex-col md:flex-row md:justify-between gap-6">
                <div class="space-y-2">
                    <p class="text-xl font-semibold text-white">場所: <span class="font-bold">{{ $weather->location }}</span></p>
                    <p class="text-xl font-semibold text-white">日付: <span class="font-bold">{{ $weather->date }}</span></p>
                </div>
                <div class="flex gap-8 items-center">
                    <div class="text-center">
                        <p class="text-sm text-white mb-1">最高気温</p>
                        <p class="text-3xl font-bold text-red-300">{{ $weather->temp_max }}℃</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-white mb-1">最低気温</p>
                        <p class="text-3xl font-bold text-blue-300">{{ $weather->temp_min }}℃</p>
                    </div>
                </div>
            </div>
            <div class="mt-6 space-y-3">
                <p class="text-lg font-medium text-white">降水確率: <span class="text-cyan-200 font-bold">{{ $weather->precipitation }}%</span></p>
                <p class="text-lg text-white">服装アドバイス: <span class="font-medium">{{ $weather->advice }}</span></p>
            </div>
            <p class="mt-6 text-sm text-gray-200">
                最終更新: {{ optional($weather->updated_at)->timezone('Asia/Tokyo')->format('Y/m/d H:i') }}
            </p>
        </div>
        @else
            <div class="glass-card rounded-2xl p-6 mb-6 border-red-300">
                <p class="text-white font-semibold">天気情報がありません。</p>
            </div>
        @endif

        {{-- 過去2週間の天気履歴 --}}
        <div class="glass-card rounded-2xl p-8 shadow-2xl">
            <h2 class="text-3xl font-bold mb-6 text-white">天気履歴（過去2週間）</h2>

            @if(isset($weatherHistory) && $weatherHistory->isNotEmpty())
                <div class="overflow-x-auto -mx-4 px-4 md:mx-0 md:px-0" style="-webkit-overflow-scrolling: touch;">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-white border-opacity-30">
                                <th class="px-4 py-3 text-left text-white font-semibold whitespace-nowrap">日付</th>
                                <th class="px-4 py-3 text-center text-white font-semibold whitespace-nowrap">最高</th>
                                <th class="px-4 py-3 text-center text-white font-semibold whitespace-nowrap">最低</th>
                                <th class="px-4 py-3 text-center text-white font-semibold whitespace-nowrap">降水%</th>
                                <th class="px-4 py-3 text-left text-white font-semibold">服装アドバイス</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($weatherHistory as $history)
                                @php
                                    $isToday = $history->date === now()->toDateString();
                                @endphp
                                <tr class="{{ $isToday ? 'bg-yellow-400 bg-opacity-20' : '' }} border-b border-white border-opacity-20 hover:bg-white hover:bg-opacity-10 transition-colors">
                                    <td class="px-4 py-3 text-white font-medium whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($history->date)->format('m/d') }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-red-300 font-bold whitespace-nowrap">
                                        {{ $history->temp_max }}℃
                                    </td>
                                    <td class="px-4 py-3 text-center text-blue-300 font-bold whitespace-nowrap">
                                        {{ $history->temp_min }}℃
                                    </td>
                                    <td class="px-4 py-3 text-center text-cyan-200 font-semibold whitespace-nowrap">
                                        {{ $history->precipitation }}%
                                    </td>
                                    <td class="px-4 py-3 text-white min-w-[200px] md:min-w-[300px]">
                                        <div class="line-clamp-3 leading-relaxed">
                                            {{ $history->advice }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-200 p-4 text-center border border-dashed border-white/20 rounded-xl">
                    履歴データがありません。
                </p>
            @endif
        </div>

    </div>
</div>
@endsection