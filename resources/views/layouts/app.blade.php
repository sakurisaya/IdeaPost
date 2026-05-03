<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IdeaPost</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.8/dist/purify.min.js"></script>
</head>

<body class="bg-slate-50 text-slate-800 h-screen overflow-hidden flex flex-col font-sans">
    <header
        class="bg-white border-b border-slate-200 px-6 py-3 flex justify-between items-center z-10 shrink-0 shadow-sm">
        <h1 class="text-2xl font-bold text-cyan-400 tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z">
                </path>
            </svg>
            IdeaPost
        </h1>
    </header>

    <main class="flex-1 overflow-hidden flex">
        @yield('content')
    </main>

    @if(session('success'))
        <div id="toast"
            class="fixed bottom-6 right-6 bg-slate-800 text-white px-6 py-3 rounded-lg shadow-xl transition-opacity duration-500 z-50 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => { document.getElementById('toast').style.opacity = '0'; }, 3000);
        </script>
    @endif
</body>

</html>