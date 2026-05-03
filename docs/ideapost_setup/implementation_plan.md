# IdeaPost 実装計画

`plan.ini` の要件に基づき、Laravelを用いたアイデア管理ツール「IdeaPost」の実装計画をまとめました。また、追加の要望として **Tailwind CSS** の使用を反映しています。

## User Review Required

> [!IMPORTANT]
> **Tailwind CSS の導入**
> 要件では「軽量で直感的に使える構成」「複雑なCSSは禁止」とありましたが、Tailwind CSS を利用することで、ユーティリティクラスを用いたシンプルかつ迅速なUI構築を行います。Tailwind CSSの導入について問題ないかご確認ください。

> [!IMPORTANT]
> **GitHub リポジトリの作成方法**
> ローカルで `git init` してコミットを作成したあと、GitHub側にリポジトリを自動作成してプッシュするために、GitHub CLI (`gh`) の使用を想定しています。もし `gh` コマンドがインストールされていない場合は手動で空リポジトリを作成していただく必要があります。

## Open Questions

- **Laravelのバージョン:** 現在の環境に入っているPHP・Composerのバージョンに合わせた最新のLaravel（例: Laravel 11）を使用する想定ですが、指定はありますか？
- **データベース:** 開発時のデータベースは軽量な SQLite を使用する想定でよろしいでしょうか？（Laravel 11のデフォルト）

## Proposed Changes

### 1. プロジェクトの初期化と環境構築

* **Laravel プロジェクト作成**
  * `composer create-project laravel/laravel .` (既存ディレクトリが空でない場合は一時フォルダに作成して移動)
* **Tailwind CSS 導入**
  * `npm install -D tailwindcss postcss autoprefixer`
  * `npx tailwindcss init -p`
  * `resources/css/app.css` の設定
* **Git / GitHub の初期化**
  * `git init`
  * `.gitignore` の確認
  * 初期コミットの作成

### 2. バックエンド実装 (Laravel)

* **パッケージ導入**
  * `composer require dompdf/dompdf`
  * `composer require league/commonmark`
* **データベース設計 (マイグレーション)**
  * `books` テーブル作成
  * `notes` テーブル作成
* **モデル・コントローラーの実装**
  * `Book` / `Note` モデル（論理削除 `SoftDeletes` トレイト使用）
  * `NoteController` (CRUD, `move` API)
  * `BookController` (CRUD, PDF出力機能)
* **ルーティング定義**
  * `/` (メイン画面)
  * APIルーティング (`/notes/move` など)
  * PDF出力ルーティング (`/books/{id}/pdf`)

### 3. フロントエンド実装 (Blade + Tailwind CSS + Vanilla JS)

* **レイアウト構成**
  * Tailwind CSSを活用した3カラムレイアウト
    1. 左: ブック一覧 (サイドバー)
    2. 中: メモ一覧 (カード表示)
    3. 右: 編集エリア (Markdown入力＋プレビュー)
* **JavaScriptロジック**
  * `SortableJS` の CDN 読み込みと D&D 初期化
  * D&D終了時(`onEnd`)の `sort_order` 差分更新API呼び出し
  * Markdownプレビューの非同期・またはフロントエンドでの簡易表示
  * (Markdown変換は仕様に従い「表示時に変換」するため、入力時のプレビューは JS または非同期通信で行うか検討します)

## Verification Plan

### Automated Tests
* LaravelのHTTPテスト（最低限のCRUDとD&DのAPIエンドポイント検証）

### Manual Verification
* ブラウザでの3カラムレイアウトの表示確認
* メモの作成・編集・削除機能の動作確認
* SortableJS を用いたメモのドラッグ＆ドロップ（未分類⇔ブック、ブック内並び替え）の動作と、リロード後の順序保持確認
* 対象ブックのPDF出力と、そのレイアウト確認
