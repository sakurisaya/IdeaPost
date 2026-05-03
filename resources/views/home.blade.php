@extends('layouts.app')

@section('content')
    <div class="flex w-full h-full" id="main-layout">

        <!-- Sidebar: Books -->
        <div id="sidebar-col" class="bg-slate-100 border-r border-slate-200 flex flex-col h-full shrink-0" style="width:256px; min-width:160px;">
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h2 class="font-bold text-slate-700">ブック一覧</h2>
            </div>

            <!-- Quick Add Book Form -->
            <div class="p-3 bg-slate-50 border-b border-slate-200 shrink-0">
                <form method="POST" action="{{ route('books.store') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="title" required
                        class="flex-1 border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition text-sm bg-white"
                        placeholder="新規ブック... (Enter)">
                    <button type="submit"
                        class="bg-cyan-400 hover:bg-cyan-700 text-white p-2 rounded-lg transition shadow-sm shrink-0"
                        title="追加">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="overflow-y-auto flex-1 p-2 space-y-1" id="book-sidebar">
                <div class="book-list-item cursor-pointer p-3 rounded-lg hover:bg-slate-200 text-slate-700 transition"
                    onclick="selectBook(null)" data-book-id="null" ondragover="event.preventDefault()"
                    ondrop="dropNoteToBook(event, null)">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <span class="font-medium">未分類</span>
                    </div>
                </div>

                @foreach($books as $book)
                    <div class="book-list-item cursor-pointer p-2 rounded-lg hover:bg-slate-200 text-slate-700 transition flex justify-between items-center group"
                        onclick="selectBook({{ $book->id }})" data-book-id="{{ $book->id }}" ondragover="event.preventDefault()"
                        ondrop="dropNoteToBook(event, {{ $book->id }})">
                        <!-- 通常表示 -->
                        <div class="book-label flex items-center gap-2 truncate pr-1 flex-1 min-w-0">
                            <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            <span class="font-medium truncate text-sm">{{ $book->title }}</span>
                        </div>
                        <!-- インライン編集フォーム（隠し） -->
                        <form class="book-edit-form hidden flex-1" method="POST" action="{{ route('books.update', $book) }}"
                            onclick="event.stopPropagation()" onsubmit="event.stopPropagation()">
                            @csrf
                            @method('PUT')
                            <input type="text" name="title" value="{{ $book->title }}"
                                class="w-full border border-cyan-400 rounded px-2 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-cyan-400"
                                onkeydown="if(event.key==='Escape'){cancelEditBook(this.closest('.book-list-item'))}">
                        </form>
                        <!-- アクションアイコン群 -->
                        <div class="flex items-center opacity-0 group-hover:opacity-100 transition shrink-0 rounded"
                            onclick="event.stopPropagation()">
                            <!-- 編集 -->
                            <button type="button" class="text-slate-400 hover:text-cyan-500 p-1.5 transition" title="名前変更"
                                onclick="startEditBook(this.closest('.book-list-item'))">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <!-- PDF -->
                            <a href="{{ route('books.pdf', $book) }}" target="_blank"
                                class="text-slate-400 hover:text-cyan-400 p-1.5 transition" title="PDF出力">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </a>
                            <!-- 削除 -->
                            <form method="POST" action="{{ route('books.destroy', $book) }}" class="inline-block m-0 p-0"
                                onsubmit="return confirm('本当にこのブックを削除しますか？\n（ゴミ箱から復元可能です）')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 p-1.5 transition" title="削除">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Trash Button -->
            <div class="p-3 bg-slate-50 border-t border-slate-200 shrink-0">
                <button onclick="document.getElementById('trash-modal').classList.remove('hidden')"
                    class="w-full flex items-center justify-center gap-2 text-slate-500 hover:text-slate-700 hover:bg-slate-200 p-2 rounded-lg transition font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    ゴミ箱
                </button>
            </div>
        </div>

        <!-- Resize Handle: sidebar | middle -->
        <div class="resize-handle" id="resize-1" style="width:5px;cursor:col-resize;background:transparent;hover:background:#e2e8f0;flex-shrink:0;"></div>

        <!-- Middle Column: Notes List -->
        <div id="middle-column"
            class="bg-white border-r border-slate-200 flex flex-col h-full shrink-0" style="width:320px;min-width:180px;">
            <div class="p-4 border-b border-slate-200 bg-white flex items-center gap-3 shadow-sm z-10">
                <h2 class="font-bold text-slate-800 truncate flex-shrink min-w-0" id="current-book-title">未分類</h2>
                <button onclick="copyCurrentBook()" class="text-slate-400 hover:text-cyan-400 transition shrink-0"
                    title="ブック全体をコピー">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Quick Add Form -->
            <div class="p-3 bg-slate-50 border-b border-slate-200 shrink-0 shadow-sm z-10">
                <form id="quick-add-form" method="POST" action="{{ route('notes.store') }}"
                    class="relative flex items-end gap-2">
                    @csrf
                    <input type="hidden" name="book_id" id="quick-add-book-id" value="">
                    <textarea name="content" id="quick-add-content" required
                        class="flex-1 border border-slate-300 rounded-lg p-2.5 resize-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition text-sm bg-white"
                        rows="1" placeholder="新しいメモを入力... (Enterで保存)"
                        oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                        style="max-height: 150px; min-height: 42px;"></textarea>
                    <button type="submit"
                        class="bg-cyan-400 hover:bg-cyan-700 text-white p-2.5 rounded-lg transition shadow-sm shrink-0"
                        title="保存">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="overflow-y-auto flex-1 bg-slate-50 p-3" id="notes-lists-wrapper">
                <!-- Unassigned Notes -->
                <div class="notes-container" id="notes-container-null">
                    <div class="sortable-list space-y-3 min-h-[100px]" data-book-id="null">
                        @foreach($unassignedNotes as $note)
                            <div class="note-card bg-white p-4 rounded-xl shadow-sm border border-slate-200 cursor-grab active:cursor-grabbing hover:shadow-md hover:border-cyan-300 transition"
                                data-id="{{ $note->id }}" data-content="{{ htmlspecialchars($note->content) }}"
                                onclick="selectNote(this)">
                                <div class="text-sm text-slate-600 line-clamp-3 note-preview">
                                    {{ Str::limit(strip_tags((new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($note->content)), 100) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Book Notes -->
                @foreach($books as $book)
                    <div class="notes-container hidden" id="notes-container-{{ $book->id }}">
                        <div class="sortable-list space-y-3 min-h-[100px]" data-book-id="{{ $book->id }}">
                            @foreach($book->notes as $note)
                                <div class="note-card bg-white p-4 rounded-xl shadow-sm border border-slate-200 cursor-grab active:cursor-grabbing hover:shadow-md hover:border-cyan-300 transition"
                                    data-id="{{ $note->id }}" data-content="{{ htmlspecialchars($note->content) }}"
                                    onclick="selectNote(this)">
                                    <div class="text-sm text-slate-600 line-clamp-3 note-preview">
                                        {{ Str::limit(strip_tags((new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($note->content)), 100) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Resize Handle: middle | right -->
        <div class="resize-handle" id="resize-2" style="width:5px;cursor:col-resize;background:transparent;flex-shrink:0;"></div>

        <!-- Right Column: Edit Area -->
        <div id="right-column" class="flex-1 bg-white h-full flex flex-col relative hidden" style="min-width:240px;">
            <div id="edit-area" class="h-full flex flex-col">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-white shadow-sm z-10">
                    <div class="flex gap-4 border-b border-slate-200 w-full mb-[-17px]">
                        <button class="px-4 py-2 text-cyan-400 border-b-2 border-cyan-400 font-medium" id="tab-edit"
                            onclick="switchTab('edit')">編集</button>
                        <button class="px-4 py-2 text-slate-500 font-medium hover:text-slate-700 transition"
                            id="tab-preview" onclick="switchTab('preview')">プレビュー</button>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        <button type="button" onclick="copyCurrentNote()"
                            class="text-slate-400 hover:text-cyan-400 transition p-1" title="メモをコピー">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                        </button>
                        <form id="delete-note-form" method="POST" action="" onsubmit="return confirm('このメモを削除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-500 transition p-1" title="削除">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <form id="update-note-form" method="POST" action="" class="flex-1 flex flex-col overflow-hidden">
                    @csrf
                    @method('PUT')
                    <div class="flex-1 relative">
                        <textarea name="content" id="note-content"
                            class="absolute inset-0 w-full h-full p-6 resize-none focus:outline-none focus:ring-0 border-0 text-slate-700 text-lg leading-relaxed bg-white"
                            placeholder="Markdownで記述... (Enterで保存、Shift+Enterで改行)"></textarea>
                        <div id="note-preview"
                            class="absolute inset-0 w-full h-full p-6 overflow-y-auto prose max-w-none bg-slate-50 hidden border-0">
                        </div>
                    </div>
                    <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                        <button type="submit"
                            class="bg-cyan-400 hover:bg-cyan-700 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-sm shadow-cyan-200">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Trash Modal -->
    <div id="trash-modal"
        class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[80vh] flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    最近削除された項目
                </h3>
                <button onclick="document.getElementById('trash-modal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-1 bg-slate-100 space-y-6">
                <!-- Deleted Books -->
                <div>
                    <h4 class="font-bold text-slate-600 mb-3 border-b border-slate-200 pb-2">ブック</h4>
                    @if($deletedBooks->isEmpty())
                        <p class="text-sm text-slate-400 bg-white p-3 rounded border border-slate-200">削除されたブックはありません。</p>
                    @else
                        <div class="space-y-2">
                            @foreach($deletedBooks as $dBook)
                                <div
                                    class="bg-white p-3 rounded-lg border border-slate-200 flex justify-between items-center shadow-sm">
                                    <span class="font-medium text-slate-700">{{ $dBook->title }}</span>
                                    <form method="POST" action="{{ route('books.restore', $dBook->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="text-cyan-400 hover:text-cyan-800 text-sm font-medium px-4 py-1.5 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition border border-cyan-200">復元</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Deleted Notes -->
                <div>
                    <h4 class="font-bold text-slate-600 mb-3 border-b border-slate-200 pb-2">メモ</h4>
                    @if($deletedNotes->isEmpty())
                        <p class="text-sm text-slate-400 bg-white p-3 rounded border border-slate-200">削除されたメモはありません。</p>
                    @else
                        <div class="space-y-2">
                            @foreach($deletedNotes as $dNote)
                                <div
                                    class="bg-white p-3 rounded-lg border border-slate-200 flex justify-between items-center shadow-sm gap-4">
                                    <div class="text-sm text-slate-600 line-clamp-2 flex-1">
                                        {{ strip_tags((new \League\CommonMark\GithubFlavoredMarkdownConverter())->convert($dNote->content)) }}
                                    </div>
                                    <form method="POST" action="{{ route('notes.restore', $dNote->id) }}" class="shrink-0">
                                        @csrf
                                        <button type="submit"
                                            class="text-cyan-400 hover:text-cyan-800 text-sm font-medium px-4 py-1.5 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition border border-cyan-200">復元</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // ── カラムリサイズ ──
        function makeResizable(handleId, leftColId) {
            const handle = document.getElementById(handleId);
            const leftCol = document.getElementById(leftColId);
            if (!handle || !leftCol) return;
            let startX, startW;
            handle.addEventListener('mousedown', e => {
                startX = e.clientX;
                startW = leftCol.offsetWidth;
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
                const onMove = ev => {
                    const newW = Math.max(leftCol.style.minWidth ? parseInt(leftCol.style.minWidth) : 160, startW + ev.clientX - startX);
                    leftCol.style.width = newW + 'px';
                };
                const onUp = () => {
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });
            handle.addEventListener('mouseenter', () => handle.style.background = '#cbd5e1');
            handle.addEventListener('mouseleave', () => handle.style.background = 'transparent');
        }

        // ── ブック名インライン編集 ──
        function startEditBook(item) {
            item.querySelector('.book-label').classList.add('hidden');
            const form = item.querySelector('.book-edit-form');
            form.classList.remove('hidden');
            const input = form.querySelector('input');
            input.focus();
            input.select();
            // Enter で submit
            input.onkeydown = function(e) {
                if (e.key === 'Enter') { e.preventDefault(); form.submit(); }
                if (e.key === 'Escape') cancelEditBook(item);
            };
        }
        function cancelEditBook(item) {
            item.querySelector('.book-label').classList.remove('hidden');
            item.querySelector('.book-edit-form').classList.add('hidden');
        }
    </script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let currentBookId = null;
        let selectedNoteCard = null;

        document.addEventListener('DOMContentLoaded', () => {
            let defaultBook = "{{ session('selected_book', 'null') }}";
            if (defaultBook === 'null' || defaultBook === '') {
                selectBook(null);
            } else {
                selectBook(defaultBook);
            }

            initSortable();
            makeResizable('resize-1', 'sidebar-col');
            makeResizable('resize-2', 'middle-column');

            // Enter to save note (Edit Area)
            const noteContent = document.getElementById('note-content');
            if (noteContent) {
                noteContent.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        document.getElementById('update-note-form').submit();
                    }
                });
            }

            // Enter to save note (Quick Add Area)
            const quickAddContent = document.getElementById('quick-add-content');
            if (quickAddContent) {
                quickAddContent.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        if (this.value.trim() !== '') {
                            document.getElementById('quick-add-form').submit();
                        }
                    }
                });
            }
        });

        function selectBook(bookId) {
            currentBookId = bookId;
            const targetBookIdStr = bookId === null ? 'null' : String(bookId);

            document.querySelectorAll('.book-list-item').forEach(el => {
                if (el.dataset.bookId === targetBookIdStr) {
                    el.classList.add('text-cyan-700', 'border-cyan-200');
                    el.classList.remove('text-slate-700');
                    document.getElementById('current-book-title').innerText = el.querySelector('span').innerText;
                } else {
                    el.classList.remove('text-cyan-700', 'border-cyan-200');
                    el.classList.add('text-slate-700');
                }
            });

            document.querySelectorAll('.notes-container').forEach(el => el.classList.add('hidden'));
            const container = document.getElementById('notes-container-' + bookId);
            if (container) container.classList.remove('hidden');

            // Update quick add form book_id
            const quickAddInput = document.getElementById('quick-add-book-id');
            if (quickAddInput) {
                quickAddInput.value = bookId || '';
            }

            deselectNote();
        }

        function selectNote(card) {
            if (selectedNoteCard) {
                selectedNoteCard.classList.remove('border-cyan-500', 'ring-2', 'ring-cyan-200');
                selectedNoteCard.classList.add('border-slate-200');
            }
            selectedNoteCard = card;
            card.classList.remove('border-slate-200');
            card.classList.add('border-cyan-500', 'ring-2', 'ring-cyan-200');

            const noteId = card.dataset.id;
            const content = card.dataset.content;

            document.getElementById('right-column').classList.remove('hidden');
            document.getElementById('middle-column').classList.remove('flex-1');
            document.getElementById('middle-column').classList.add('w-80');

            const form = document.getElementById('update-note-form');
            form.action = `/notes/${noteId}`;

            const deleteForm = document.getElementById('delete-note-form');
            deleteForm.action = `/notes/${noteId}`;

            document.getElementById('note-content').value = content;
            switchTab('edit');
        }

        function deselectNote() {
            if (selectedNoteCard) {
                selectedNoteCard.classList.remove('border-cyan-500', 'ring-2', 'ring-cyan-200');
                selectedNoteCard.classList.add('border-slate-200');
                selectedNoteCard = null;
            }
            document.getElementById('right-column').classList.add('hidden');
            document.getElementById('middle-column').classList.remove('w-80');
            document.getElementById('middle-column').classList.add('flex-1');
        }

        function switchTab(tab) {
            const editTab = document.getElementById('tab-edit');
            const prevTab = document.getElementById('tab-preview');
            const content = document.getElementById('note-content');
            const preview = document.getElementById('note-preview');

            if (tab === 'edit') {
                editTab.className = 'px-4 py-2 text-cyan-400 border-b-2 border-cyan-400 font-medium';
                prevTab.className = 'px-4 py-2 text-slate-500 font-medium hover:text-slate-700 transition';
                content.classList.remove('hidden');
                preview.classList.add('hidden');
            } else {
                prevTab.className = 'px-4 py-2 text-cyan-400 border-b-2 border-cyan-400 font-medium';
                editTab.className = 'px-4 py-2 text-slate-500 font-medium hover:text-slate-700 transition';
                content.classList.add('hidden');
                preview.classList.remove('hidden');

                const rawText = content.value;
                preview.innerHTML = DOMPurify.sanitize(marked.parse(rawText));
            }
        }

        function copyCurrentBook() {
            let text = "";
            if (currentBookId === null) {
                const notes = Array.from(document.querySelectorAll('#notes-container-null .note-card')).map(el => el.dataset.content);
                text = notes.join('\n\n---\n\n');
            } else {
                const title = document.getElementById('current-book-title').innerText;
                const notes = Array.from(document.querySelectorAll(`#notes-container-${currentBookId} .note-card`)).map(el => el.dataset.content);
                text = `# ${title}\n\n` + notes.join('\n\n---\n\n');
            }

            if (text) {
                navigator.clipboard.writeText(text).then(() => alert('ブックのメモを一括コピーしました')).catch(err => console.error(err));
            }
        }

        function copyCurrentNote() {
            const content = document.getElementById('note-content').value;
            if (content) {
                navigator.clipboard.writeText(content).then(() => alert('メモをコピーしました')).catch(err => console.error(err));
            }
        }

        function initSortable() {
            document.querySelectorAll('.sortable-list').forEach(list => {
                new Sortable(list, {
                    group: 'notes',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: function (evt) {
                        if (evt.to === evt.from) {
                            const item = evt.item;
                            const noteId = item.dataset.id;
                            const bookId = evt.to.dataset.bookId;

                            let prevId = null;
                            let nextId = null;

                            if (item.previousElementSibling) {
                                prevId = item.previousElementSibling.dataset.id;
                            }
                            if (item.nextElementSibling) {
                                nextId = item.nextElementSibling.dataset.id;
                            }

                            saveMoveAPI(noteId, bookId, prevId, nextId);
                        }
                    }
                });
            });
        }

        let draggedNoteId = null;
        let draggedNoteElement = null;

        document.addEventListener('dragstart', (e) => {
            const card = e.target.closest('.note-card');
            if (card) {
                draggedNoteId = card.dataset.id;
                draggedNoteElement = card;
            }
        });

        function dropNoteToBook(e, targetBookId) {
            e.preventDefault();
            e.currentTarget.classList.remove('bg-cyan-100');

            if (!draggedNoteId || targetBookId == currentBookId) return;

            const targetList = document.querySelector(`.sortable-list[data-book-id="${targetBookId}"]`);
            if (targetList && draggedNoteElement) {
                targetList.appendChild(draggedNoteElement);
                saveMoveAPI(draggedNoteId, targetBookId, null, null);
            }

            draggedNoteId = null;
            draggedNoteElement = null;
        }

        document.querySelectorAll('.book-list-item').forEach(item => {
            item.addEventListener('dragenter', function (e) {
                if (draggedNoteId && this.dataset.bookId != currentBookId) {
                    this.classList.add('bg-cyan-100');
                }
            });
            item.addEventListener('dragleave', function (e) {
                this.classList.remove('bg-cyan-100');
            });
        });

        function saveMoveAPI(noteId, bookId, prevId, nextId) {
            fetch('/notes/move', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    note_id: noteId,
                    book_id: bookId,
                    prev_note_id: prevId,
                    next_note_id: nextId
                })
            }).then(res => res.json()).then(data => {
                console.log('Move saved');
            }).catch(err => console.error(err));
        }
    </script>
@endsection