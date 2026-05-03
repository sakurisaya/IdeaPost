<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = ['public_id', 'book_id', 'content', 'sort_order', 'status'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($note) {
            if (empty($note->public_id)) {
                $note->public_id = uniqid('note_');
            }
            if (empty($note->sort_order)) {
                $maxOrder = static::where('book_id', $note->book_id)->max('sort_order');
                $note->sort_order = $maxOrder ? $maxOrder + 100 : 100;
            }
        });
    }
}
