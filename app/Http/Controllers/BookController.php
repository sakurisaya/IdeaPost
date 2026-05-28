<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Book;
use App\Models\Note;
use Dompdf\Dompdf;
use Dompdf\Options;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with(['notes' => function($query) {
            $query->orderBy('sort_order');
        }])->orderBy('sort_order')->get();
        
        $unassignedNotes = Note::whereNull('book_id')->orderBy('sort_order')->get();
        
        $deletedBooks = Book::onlyTrashed()->latest()->take(5)->get();
        $deletedNotes = Note::onlyTrashed()->latest()->take(10)->get();
        
        return view('home', compact('books', 'unassignedNotes', 'deletedBooks', 'deletedNotes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $book = Book::create($validated);
        
        return back()->with('success', 'ブックを作成しました。')->with('selected_book', $book->id);
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $book->update($validated);
        
        return back()->with('success', 'ブックを更新しました。')->with('selected_book', $book->id);
    }

    public function destroy(Book $book)
    {
        // For soft delete, we don't nullify book_id on notes, we just soft delete the book.
        // Wait, if book is soft deleted, notes are hidden if we query them via book, but maybe we should keep book_id.
        $book->delete();
        
        return back()->with('success', 'ブックを削除しました。')->with('selected_book', 'null');
    }

    public function restore($id)
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();
        
        return back()->with('success', 'ブックを復元しました。')->with('selected_book', $book->id);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'book_id'      => 'required|exists:books,id',
            'prev_book_id' => 'nullable|exists:books,id',
            'next_book_id' => 'nullable|exists:books,id',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        $prevOrder = 0;
        $nextOrder = 0;

        if (!empty($validated['prev_book_id'])) {
            $prevOrder = Book::find($validated['prev_book_id'])->sort_order;
        }

        if (!empty($validated['next_book_id'])) {
            $nextOrder = Book::find($validated['next_book_id'])->sort_order;
        }

        if ($prevOrder && $nextOrder) {
            $book->sort_order = (int) (($prevOrder + $nextOrder) / 2);
        } elseif ($prevOrder) {
            $book->sort_order = $prevOrder + 100;
        } elseif ($nextOrder) {
            $book->sort_order = max(1, (int) ($nextOrder / 2));
        } else {
            $book->sort_order = 100;
        }

        if ($book->sort_order == $prevOrder) {
            $book->sort_order = $prevOrder + 1;
        }

        $book->save();

        return response()->json(['success' => true]);
    }

    public function pdf(Book $book)
    {
        $notes = $book->notes()->orderBy('sort_order')->get();
        
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // Build content with numeric entity encoding for Japanese text
        $encode = fn(string $text): string =>
            mb_encode_numericentity($text, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');

        $bodyHtml = '<h1>' . $encode(strip_tags($book->title)) . '</h1>';

        if ($book->description) {
            $bodyHtml .= '<p>' . nl2br($encode(strip_tags($book->description))) . '</p><hr>';
        }

        foreach ($notes as $note) {
            $noteHtml = (string) $converter->convert($note->content);
            $noteHtml = mb_encode_numericentity($noteHtml, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');
            $bodyHtml .= '<div class="note">' . $noteHtml . '</div>';
        }

        // NO @font-face here - dompdf loads the font from fontDir/fontCache automatically
        // by the font-family name "ipaexg" which matches installed-fonts.json
        $html = '<html><head><meta charset="UTF-8"><style>
            * { font-family: "ipaexg"; }
            body { line-height: 1.6; margin: 40px; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all; }
            h1, h2, h3, h4, h5, h6 { color: #333; word-wrap: break-word; overflow-wrap: break-word; }
            p, li, td, th, blockquote { word-wrap: break-word; overflow-wrap: break-word; word-break: break-all; }
            pre, code { white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word; word-break: break-all; background: #f5f5f5; padding: 2px 4px; }
            table { width: 100%; border-collapse: collapse; table-layout: fixed; }
            img { max-width: 100%; }
            .note { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
        </style></head><body>' . $bodyHtml . '</body></html>';

        $options = new Options();
        $options->set('defaultFont', 'ipaexg');
        $options->set('isRemoteEnabled', false);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('fontDir', storage_path('fonts'));
        $options->set('fontCache', storage_path('fonts'));
        $options->set('chroot', storage_path('fonts'));
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="book_'.$book->id.'.pdf"'
        ]);
    }
}
