<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;
use App\Http\Controllers\NoteController;

Route::get('/', [BookController::class, 'index'])->name('home');

Route::post('/books', [BookController::class, 'store'])->name('books.store');
Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
Route::post('/books/{id}/restore', [BookController::class, 'restore'])->name('books.restore');
Route::get('/books/{book}/pdf', [BookController::class, 'pdf'])->name('books.pdf');
Route::post('/books/reorder', [BookController::class, 'reorder'])->name('books.reorder');

Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
Route::post('/notes/{id}/restore', [NoteController::class, 'restore'])->name('notes.restore');
Route::post('/notes/move', [NoteController::class, 'move'])->name('notes.move');
