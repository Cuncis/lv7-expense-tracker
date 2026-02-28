@extends('layouts.app')
@section('title', 'All Entries')
@section('page-title', '📋 All Entries')

@section('content')

    {{-- ===== FILTER BAR ===== --}}
    <form method="GET" action="{{ route('entries.index') }}"
        class="bg-white border border-slate-200 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">

        {{-- Search --}}
        <div class="flex-1 min-w-40">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Description, category..."
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>

        {{-- Type --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Type</label>
            <select name="type"
                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 bg-white">
                <option value="">All types</option>
                <option value="income" @selected(($filters['type'] ?? '') === 'income')>💚 Income</option>
                <option value="expense" @selected(($filters['type'] ?? '') === 'expense')>🔴 Expense</option>
            </select>
        </div>

        {{-- Category --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Category</label>
            <select name="category"
                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 bg-white">
                <option value="">All categories</option>
                @foreach ($allCategories as $cat)
                    <option value="{{ $cat }}" @selected(($filters['category'] ?? '') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        {{-- Date from --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">From</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>

        {{-- Date to --}}
        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">To</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>

        <button type="submit"
            class="bg-slate-900 hover:bg-slate-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
            Filter
        </button>
        @if (array_filter($filters))
            <a href="{{ route('entries.index') }}"
                class="border border-slate-300 text-slate-500 hover:text-slate-800 text-sm px-4 py-2 rounded-lg transition">
                Clear
            </a>
        @endif
    </form>

    {{-- ===== FILTERED SUMMARY STRIP ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Filtered Income</span>
            <span class="mono font-bold text-emerald-700">Rp {{ number_format($filteredIncome, 0, ',', '.') }}</span>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-xs font-semibold text-red-600 uppercase tracking-wider">Filtered Expenses</span>
            <span class="mono font-bold text-red-700">Rp {{ number_format($filteredExpense, 0, ',', '.') }}</span>
        </div>
        <div class="bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Net</span>
            <span class="mono font-bold {{ $filteredNet >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                {{ $filteredNet >= 0 ? '+' : '−' }}Rp {{ number_format(abs($filteredNet), 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- ===== TABLE ===== --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <p class="text-sm text-slate-500">
                {{ $entries->total() }} {{ Str::plural('entry', $entries->total()) }}
                @if (array_filter($filters)) <span class="text-slate-400">(filtered)</span> @endif
            </p>
            <a href="{{ route('entries.create') }}" class="text-sm text-emerald-600 hover:underline font-medium">+ Add
                entry</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[560px]">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-left px-5 py-3">Description</th>
                        <th class="text-left px-5 py-3 hidden md:table-cell">Category</th>
                        <th class="text-left px-5 py-3 hidden sm:table-cell">Type</th>
                        <th class="text-right px-5 py-3">Amount</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-5 py-3 mono text-xs text-slate-400 whitespace-nowrap">
                                {{ $entry->date->format('M j, Y') }}
                            </td>
                            <td class="px-5 py-3 font-medium text-slate-700 max-w-xs">
                                <div class="truncate">{{ $entry->description }}</div>
                                @if ($entry->note)
                                    <div class="text-xs text-slate-400 truncate mt-0.5">{{ $entry->note }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500 text-xs hidden md:table-cell">{{ $entry->category }}</td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold
                                                    {{ $entry->is_income ? 'badge-income' : 'badge-expense' }}">
                                    {{ ucfirst($entry->type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right mono font-bold
                                                       {{ $entry->is_income ? 'amount-income' : 'amount-expense' }}">
                                {{ $entry->is_income ? '+' : '−' }}Rp {{ number_format($entry->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 sm:opacity-0 sm:group-hover:opacity-100 transition">
                                    <a href="{{ route('entries.edit', $entry) }}"
                                        class="text-xs text-slate-500 hover:text-slate-800 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('entries.destroy', $entry) }}"
                                        onsubmit="return confirm('Move to trash?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="text-3xl mb-3">💸</p>
                                <p class="text-slate-400">No entries found.</p>
                                @if (array_filter($filters))
                                    <a href="{{ route('entries.index') }}"
                                        class="text-sm text-emerald-600 hover:underline mt-2 inline-block">Clear filters</a>
                                @else
                                    <a href="{{ route('entries.create') }}"
                                        class="text-sm text-emerald-600 hover:underline mt-2 inline-block">Add your first entry</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($entries->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

@endsection