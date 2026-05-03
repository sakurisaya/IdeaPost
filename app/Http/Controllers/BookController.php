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
        }])->get();
        
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
            body { line-height: 1.6; margin: 40px; }
            h1, h2, h3, h4, h5, h6 { color: #333; }
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
