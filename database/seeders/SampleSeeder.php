<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Note;

class SampleSeeder extends Seeder
{
    public function run(): void
    {
        // サンプルブック1: アイデアメモ
        $book1 = Book::create([
            'title' => 'アイデアメモ',
            'description' => 'ふとひらめいたアイデアをここに残す',
        ]);

        Note::create(['book_id' => $book1->id, 'content' => "# 新しいアプリのアイデア\n\nユーザーが直感的に使えるメモアプリ。\n\n- シンプルなUI\n- Markdown対応\n- PDF出力機能", 'sort_order' => 100]);
        Note::create(['book_id' => $book1->id, 'content' => "毎朝3つのアイデアを書き出す習慣をつける。\n\n量より質より、とにかく続けることが大事。", 'sort_order' => 200]);

        // サンプルブック2: 学習ノート
        $book2 = Book::create([
            'title' => '学習ノート',
            'description' => '勉強したことのまとめ',
        ]);

        Note::create(['book_id' => $book2->id, 'content' => "## Laravel Tips\n\n- `php artisan make:model` でモデル生成\n- `php artisan migrate` でDB更新\n- ルートは `routes/web.php` に記述", 'sort_order' => 100]);
        Note::create(['book_id' => $book2->id, 'content' => "## Tailwind CSS\n\n`flex`, `grid` を使ったレイアウトが便利。\n\nレスポンシブは `sm:`, `md:`, `lg:` プレフィックスで対応。", 'sort_order' => 200]);

        // 未分類メモ
        Note::create(['book_id' => null, 'content' => "買い物リスト\n\n- 牛乳\n- パン\n- コーヒー豆", 'sort_order' => 100]);
        Note::create(['book_id' => null, 'content' => "IdeaPostを使い始めた！\n\nメモがどんどん溜まりそう 🚀", 'sort_order' => 200]);
    }
}
