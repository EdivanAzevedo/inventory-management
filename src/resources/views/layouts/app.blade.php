<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestão de Estoque')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-56 min-h-screen bg-slate-900 text-slate-100 flex flex-col shrink-0">
        <div class="px-6 py-5 border-b border-slate-700">
            <span class="font-bold text-lg tracking-tight">📦 Estoque</span>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="/products"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium
                      {{ request()->is('products*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                🗂 Produtos
            </a>
            <a href="/stock"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium
                      {{ request()->is('stock*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                📊 Estoque
            </a>
        </nav>
    </aside>

    {{-- Main --}}
    <main class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-slate-800">@yield('title', 'Gestão de Estoque')</h1>
            @yield('header-action')
        </header>
        <div class="flex-1 p-8">
            @yield('content')
        </div>
    </main>

</body>
</html>
