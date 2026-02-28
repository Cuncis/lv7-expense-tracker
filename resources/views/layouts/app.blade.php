<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Expense Tracker')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .income-text {
            color: #10b981;
        }

        .expense-text {
            color: #ef4444;
        }

        .net-pos {
            color: #10b981;
        }

        .net-neg {
            color: #ef4444;
        }

        .badge-income {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-expense {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Sidebar active */
        .nav-active {
            background: #1e293b;
            color: white;
        }

        /* Amount display */
        .amount-income {
            color: #10b981;
        }

        .amount-expense {
            color: #ef4444;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex">

    {{-- ===== SIDEBAR OVERLAY (mobile) ===== --}}
    <div id="sidebar-overlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"></div>

    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar" class="fixed lg:sticky top-0 left-0 h-screen w-64 lg:w-56 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 z-30
               -translate-x-full lg:translate-x-0 transition-transform duration-300">
        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-slate-700 flex items-center justify-between">
            <div>
                <span class="font-bold text-white text-lg tracking-tight">💰 Tracker</span>
                <p class="text-xs text-slate-500 mt-0.5">Expense Manager</p>
            </div>
            <button onclick="closeSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('dashboard') ? 'nav-active' : 'hover:bg-slate-800 hover:text-white' }}">
                <span>📊</span> Dashboard
            </a>
            <a href="{{ route('entries.index') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('entries.index', 'entries.create', 'entries.edit') ? 'nav-active' : 'hover:bg-slate-800 hover:text-white' }}">
                <span>📋</span> All Entries
            </a>
            <a href="{{ route('entries.create') }}?type=income" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition hover:bg-slate-800 hover:text-white">
                <span class="text-emerald-400">+</span>
                <span class="text-emerald-400">Add Income</span>
            </a>
            <a href="{{ route('entries.create') }}?type=expense" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition hover:bg-slate-800 hover:text-white">
                <span class="text-red-400">−</span>
                <span class="text-red-400">Add Expense</span>
            </a>
            <div class="border-t border-slate-700 my-3"></div>
            <a href="{{ route('entries.trash') }}" onclick="closeSidebar()"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('entries.trash') ? 'nav-active' : 'hover:bg-slate-800 hover:text-white' }}">
                <span>🗑</span> Trash
            </a>
        </nav>

        {{-- Bottom info --}}
        <div class="px-5 py-4 border-t border-slate-700 text-xs text-slate-500">
            Laravel 12 · SQLite<br>
            {{ now()->format('F Y') }}
        </div>
    </aside>

    {{-- ===== MAIN AREA ===== --}}
    <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
        {{-- Top bar --}}
        <header
            class="bg-white border-b border-slate-200 px-4 lg:px-6 py-4 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                {{-- Hamburger (mobile only) --}}
                <button onclick="openSidebar()" class="lg:hidden p-1.5 rounded-lg hover:bg-slate-100 text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-base lg:text-lg font-semibold text-slate-800 truncate">
                    @yield('page-title', 'Dashboard')
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('entries.create') }}"
                    class="bg-slate-900 hover:bg-slate-700 text-white text-xs lg:text-sm font-semibold px-3 lg:px-4 py-2 rounded-lg transition whitespace-nowrap">
                    + New Entry
                </a>
            </div>
        </header>

        {{-- Flash --}}
        @if (session('success'))
            <div
                class="mx-4 lg:mx-6 mt-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                {{ session('success') }}
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 p-4 lg:p-6 pb-24 lg:pb-6">
            @yield('content')
        </main>
    </div>

    {{-- ===== MOBILE BOTTOM NAV ===== --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-10 flex">
        <a href="{{ route('dashboard') }}" class="flex-1 flex flex-col items-center justify-center py-2 text-xs font-medium gap-0.5
                  {{ request()->routeIs('dashboard') ? 'text-slate-900' : 'text-slate-400' }}">
            <span class="text-xl leading-none">📊</span>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('entries.index') }}" class="flex-1 flex flex-col items-center justify-center py-2 text-xs font-medium gap-0.5
                  {{ request()->routeIs('entries.index') ? 'text-slate-900' : 'text-slate-400' }}">
            <span class="text-xl leading-none">📋</span>
            <span>Entries</span>
        </a>
        <a href="{{ route('entries.create') }}"
            class="flex-1 flex flex-col items-center justify-center py-2 text-xs font-medium gap-0.5 text-emerald-600">
            <span class="text-xl leading-none">➕</span>
            <span>Add</span>
        </a>
        <a href="{{ route('entries.trash') }}" class="flex-1 flex flex-col items-center justify-center py-2 text-xs font-medium gap-0.5
                  {{ request()->routeIs('entries.trash') ? 'text-slate-900' : 'text-slate-400' }}">
            <span class="text-xl leading-none">🗑</span>
            <span>Trash</span>
        </a>
    </nav>

    <script>
        function openSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.remove('hidden');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.add('hidden');
        }
    </script>

</body>

</html>