@extends('layouts.app')
@section('title', 'Monthly Budgets')
@section('page-title', '💸 Monthly Budgets')

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <p class="text-sm text-slate-500">Set monthly spending limits per category. Overspent categories are highlighted
                in red.</p>
            <p class="text-xs text-slate-400 mt-0.5">Tracking period: <span
                    class="font-semibold">{{ now()->format('F Y') }}</span></p>
        </div>
    </div>

    {{-- ===== SET / EDIT BUDGET FORM ===== --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <p class="font-semibold text-slate-700 mb-4">➕ Set a Budget</p>
        <form method="POST" action="{{ route('budgets.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf

            <div class="flex-1 min-w-40">
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Category</label>
                <select name="category" required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-400 {{ $errors->has('category') ? 'border-red-400' : '' }}">
                    <option value="">Select category…</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Monthly Limit
                    (Rp)</label>
                <input type="number" name="amount" min="1" step="1000" value="{{ old('amount') }}"
                    placeholder="e.g. 2000000"
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm mono focus:outline-none focus:ring-2 focus:ring-slate-400 w-44 {{ $errors->has('amount') ? 'border-red-400' : '' }}">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                class="bg-slate-900 hover:bg-slate-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                Save Budget
            </button>
        </form>
        <p class="text-xs text-slate-400 mt-3">Saving a budget for a category that already has one will overwrite it.</p>
    </div>

    {{-- ===== BUDGET STATUS LIST ===== --}}
    @if ($budgets->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 px-5 py-16 text-center">
            <p class="text-3xl mb-3">📋</p>
            <p class="text-slate-400">No budgets set yet. Use the form above to add your first one.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($budgets as $b)
                @php
                    $pct = min($b->percentage, 100);
                    $overPct = $b->percentage > 100 ? min($b->percentage - 100, 100) : 0;
                    $isOver = $b->overspent;
                    $isWarn = !$isOver && $b->percentage >= 80;
                @endphp
                <div
                    class="bg-white rounded-xl border {{ $isOver ? 'border-red-300' : ($isWarn ? 'border-amber-300' : 'border-slate-200') }} p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            @if ($isOver)
                                <span
                                    class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full">🔴
                                    Overspent</span>
                            @elseif ($isWarn)
                                <span
                                    class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">⚠️
                                    Near Limit</span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">✅
                                    On Track</span>
                            @endif
                            <span class="font-semibold text-slate-800">{{ $b->category }}</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-xs text-slate-400">Spent</p>
                                <p class="mono text-sm font-bold {{ $isOver ? 'text-red-600' : 'text-slate-700' }}">
                                    Rp {{ number_format($b->spent, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-400">Limit</p>
                                <p class="mono text-sm font-semibold text-slate-500">
                                    Rp {{ number_format($b->amount, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-400">{{ $b->percentage }}%</p>
                                <p
                                    class="mono text-sm font-semibold {{ $isOver ? 'text-red-600' : ($isWarn ? 'text-amber-600' : 'text-emerald-600') }}">
                                    @if ($isOver)
                                        +Rp {{ number_format($b->spent - $b->amount, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($b->amount - $b->spent, 0, ',', '.') }} left
                                    @endif
                                </p>
                            </div>

                            <form method="POST" action="{{ route('budgets.destroy', $b) }}"
                                onsubmit="return confirm('Remove budget for {{ $b->category }}?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-slate-400 hover:text-red-500 transition"
                                    title="Remove budget">✕</button>
                            </form>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500
                                                {{ $isOver ? 'bg-red-500' : ($isWarn ? 'bg-amber-400' : 'bg-emerald-400') }}"
                            style="width: {{ $pct }}%">
                        </div>
                    </div>
                    @if ($isOver)
                        <p class="text-xs text-red-500 mt-1">
                            Exceeded by Rp {{ number_format($b->spent - $b->amount, 0, ',', '.') }} ({{ $b->percentage }}% of budget
                            used)
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- ===== UNBUDGETED CATEGORIES ===== --}}
    @php
        $unbudgeted = array_diff($categories, $budgets->pluck('category')->toArray());
    @endphp
    @if (count($unbudgeted) > 0)
        <div class="mt-6 bg-slate-50 border border-slate-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">No Budget Set</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($unbudgeted as $cat)
                    <span class="text-xs bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-slate-500">{{ $cat }}</span>
                @endforeach
            </div>
        </div>
    @endif

@endsection