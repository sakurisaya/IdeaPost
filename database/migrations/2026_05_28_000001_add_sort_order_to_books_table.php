<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('description');
        });

        // 既存のブックに連番のsort_orderを設定（データ保守）
        $books = DB::table('books')->whereNull('deleted_at')->orderBy('id')->get();
        foreach ($books as $i => $book) {
            DB::table('books')->where('id', $book->id)->update(['sort_order' => ($i + 1) * 100]);
        }
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
