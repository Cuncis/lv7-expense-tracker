@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', '📊 Dashboard — ' . now()->format('F Y'))

@section('content')

    {{-- ===== THIS MONTH STAT CARDS ===== --}}
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">This month</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Income</p>
            <p class="mono text-2xl font-bold income-text">Rp {{ number_format($monthIncome, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ now()->format('M Y') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Expenses</p>
            <p class="mono text-2xl font-bold expense-text">Rp {{ number_format($monthExpense, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ now()->format('M Y') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Net Balance</p>
            <p class="mono text-2xl font-bold {{ $monthNet >= 0 ? 'net-pos' : 'net-neg' }}">
                {{ $monthNet >= 0 ? '+' : '' }}Rp {{ number_format(abs($monthNet), 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1">income − expenses</p>
        </div>
    </div>

    {{-- ===== ALL TIME STAT CARDS ===== --}}
    <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">All time</p>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Total Income</p>
            <p class="mono text-xl font-bold text-emerald-700">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Total Expenses</p>
            <p class="mono text-xl font-bold text-red-700">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>
        <div class="bg-slate-100 border border-slate-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Net Worth</p>
            <p class="mono text-xl font-bold {{ $totalNet >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                {{ $totalNet >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalNet), 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-slate-100 border border-slate-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Entries</p>
            <p class="mono text-xl font-bold text-slate-700">{{ number_format($totalEntries) }}</p>
            @if ($trashCount)
                <p class="text-xs text-red-400 mt-1">{{ $trashCount }} in trash</p>
            @endif
        </div>
    </div>

    {{-- ===== CHART + CATEGORY BREAKDOWN ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">

        {{-- Line/Bar Chart — 6 month trend --}}
        <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 p-5">
            <p class="font-semibold text-slate-700 mb-4">Income vs Expenses — Last 6 Months</p>
            <canvas id="trendChart" height="120"></canvas>
        </div>

        {{-- Expense category breakdown this month --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
            <p class="font-semibold text-slate-700 mb-4">Expenses by Category</p>
            @if ($expenseByCategory->isEmpty())
                <p class="text-slate-400 text-sm text-center py-8">No expenses this month.</p>
            @else
                <div class="space-y-2.5">
                    @php $maxExpense = $expenseByCategory->max('total'); @endphp
                    @foreach ($expenseByCategory as $cat)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-slate-600 font-medium">{{ $cat->category }}</span>
                                <span class="mono text-xs font-semibold text-red-600">Rp
                                    {{ number_format($cat->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-400 rounded-full"
                                    style="width: {{ $maxExpense > 0 ? ($cat->total / $maxExpense * 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ===== RECENT ENTRIES ===== --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <p class="font-semibold text-slate-700">Recent Entries</p>
            <a href="{{ route('entries.index') }}" class="text-xs text-slate-400 hover:text-slate-700">View all →</a>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[540px]">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                <tr>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Description</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Category</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Type</th>
                    <th class="text-right px-5 py-3">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($recentEntries as $entry)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3 mono text-xs text-slate-500 whitespace-nowrap">{{ $entry->date->format('M j, Y') }}</td>
                        <td class="px-5 py-3 font-medium text-slate-700">
                            <a href="{{ route('entries.edit', $entry) }}" class="hover:text-slate-900 hover:underline">
                                {{ $entry->description }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-slate-500 hidden sm:table-cell">{{ $entry->category }}</td>
                        <td class="px-5 py-3 hidden sm:table-cell">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold
                                            {{ $entry->is_income ? 'badge-income' : 'badge-expense' }}">
                                {{ ucfirst($entry->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right mono font-semibold
                                               {{ $entry->is_income ? 'amount-income' : 'amount-expense' }}">
                            {{ $entry->is_income ? '+' : '−' }}Rp {{ number_format($entry->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">No entries yet. <a
                                href="{{ route('entries.create') }}" class="text-emerald-600 underline">Add one!</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Chart.js init --}}
    <script>
        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($monthLabels),
                datasets: [
                    {
                        label: 'Income',
                        data: @json($incomeData),
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderColor: 'rgba(16,185,129,1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Expense',
                        data: @json($expenseData),
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        borderColor: 'rgba(239,68,68,1)',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 12 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' Rp ' + ctx.raw.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: v => 'Rp ' + v.toLocaleString('id-ID')
                        },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>

@endsection