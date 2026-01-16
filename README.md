# Weather App（天気予報アプリ）

---

## 概要

Visual Crossing Weather API を利用して、地域ごとの **最新天気** と **過去2週間の天気履歴** を表示するWebアプリです。

外部APIから取得した天気データを **DBに保存** し、

* 最新情報の表示
* 過去履歴の参照

を分離することで、実務を意識した **「外部API × DB蓄積型」構成** を採用しています。

---

## 使用技術 / Laravelメソッド

### 使用技術

* PHP 8.x
* Laravel 10
* MySQL
* Docker / Laravel Sail
* Tailwind CSS
* Visual Crossing Weather API

### 主なLaravel機能（改行なし）

Eloquent（where, updateOrCreate, orderBy）/ Controller / Service分離 / Bladeテンプレート / Facade（Http, Log）/ Carbon（日付操作）

---

## アプリ構成メモ

### CRUD

* **Create / Update**：天気データ取得時（updateOrCreate）
* **Read**：最新天気・過去2週間の履歴表示

### MVC

* **Model**：Weather
* **Controller**：WeatherController
* **View**：weather/index.blade.php

---

## 環境構築・インストール方法（簡潔・コピペ可）

```bash
git clone <リポジトリURL>
cd <プロジェクト名>

cp .env.example .env
docker run --rm -v $(pwd):/app composer install

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

### APIキー設定

`.env` に Visual Crossing の APIキーを設定してください。

```env
VISUAL_CROSSING_API_KEY=your_api_key_here
```

---

## 使い方

1. トップページにアクセス
2. 地域（国内・海外）を選択
3. **「天気を更新」** ボタンを押す
4. 最新の天気情報を表示
5. 同じ地域の **過去2週間分の天気履歴** を一覧で確認可能

※ 天気データは API 取得時に DB へ保存され、履歴として蓄積されます。

---

## こだわって実装した機能

### 地域ごとの天気管理

* 国内・国外を含めた複数地域対応
* 地域ごとに最新天気・履歴を切り替え表示

### DB蓄積型の設計

* API取得 → DB保存 → 表示 の流れを明確化
* 過去データを API に依存せず再利用可能

### 過去2週間の天気履歴表示

* Carbon を用いた日付計算
* 実データに基づく履歴一覧表示

### エラーハンドリングとログ

* API通信失敗・レスポンス異常時のログ出力
* ユーザー向けエラーメッセージ表示

### 最終更新日時の可視化

* `updated_at` を利用し、データ更新状況を明確化

### 拡張を意識した構成

* 定期実行（cron）
* 通知機能
* 履歴期間変更

など、将来的な機能追加を想定した設計

---
