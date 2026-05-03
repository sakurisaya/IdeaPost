<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Note;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'book_id' => 'nullable|exists:books,id'
        ]);

        $note = Note::create($validated);
        
        return back()->with('success', 'メモを作成しました。')->with('selected_book', $validated['book_id'] ?? 'null');
    }

    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $note->update($validated);
        
        return back()->with('success', 'メモを更新しました。')->with('selected_book', $note->book_id ?? 'null');
    }

    public function destroy(Note $note)
    {
        $bookId = $note->book_id ?? 'null';
        $note->delete();
        
        return back()->with('success', 'メモを削除しました。')->with('selected_book', $bookId);
    }

    public function restore($id)
    {
        $note = Note::withTrashed()->findOrFail($id);
        $note->restore();
        
        return back()->with('success', 'メモを復元しました。')->with('selected_book', $note->book_id ?? 'null');
    }

    public function move(Request $request)
    {
        $validated = $request->validate([
            'note_id' => 'required|exists:notes,id',
            'book_id' => 'nullable|exists:books,id',
            'prev_note_id' => 'nullable|exists:notes,id',
            'next_note_id' => 'nullable|exists:notes,id',
        ]);

        $note = Note::findOrFail($validated['note_id']);
        $note->book_id = $validated['book_id'] ?? null;
        
        $prevOrder = 0;
        $nextOrder = 0;
        
        if (!empty($validated['prev_note_id'])) {
            $prevOrder = Note::find($validated['prev_note_id'])->sort_order;
        }
        
        if (!empty($validated['next_note_id'])) {
            $nextOrder = Note::find($validated['next_note_id'])->sort_order;
        }
        
        if ($prevOrder && $nextOrder) {
            $note->sort_order = (int) (($prevOrder + $nextOrder) / 2);
        } elseif ($prevOrder) {
            $note->sort_order = $prevOrder + 100;
        } elseif ($nextOrder) {
            $note->sort_order = (int) ($nextOrder / 2);
        } else {
            $note->sort_order = 100;
        }
        
        // Prevent sort_order from becoming exactly same as another by accident if space is exhausted
        // Though plan.ini says "全件更新は禁止（差分更新のみ）", we'll just do a basic check
        if ($note->sort_order == $prevOrder) {
             $note->sort_order = $prevOrder + 1;
        }
        
        $note->save();
        
        return response()->json(['success' => true]);
    }
}
