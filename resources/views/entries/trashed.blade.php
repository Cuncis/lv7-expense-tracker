@extends('layouts.app')
@section('title', 'Trash')
@section('page-title', '🗑 Trash')

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <p class="text-sm text-slate-500">
            {{ $trashed->total() }} {{ Str::plural('entry', $trashed->total()) }} in trash.
            Soft-deleted entries are kept here until permanently removed.
        </p>

        @if ($trashed->total() > 0)
            <form action="{{ route('entries.emptyTrash') }}" method="POST"
                onsubmit="return confirm('Permanently delete ALL trashed entries? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    💥 Empty Trash
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-left px-5 py-3">Description</th>
                        <th class="text-left px-5 py-3 hidden md:table-cell">Category</th>
                        <th class="text-left px-5 py-3 hidden sm:table-cell">Type</th>
                        <th class="text-right px-5 py-3">Amount</th>
                        <th class="text-left px-5 py-3 hidden sm:table-cell">Deleted</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($trashed as $entry)
                        <tr class="hover:bg-slate-50 transition opacity-70">
                            <td class="px-5 py-3 mono text-xs text-slate-400 whitespace-nowrap">
                                {{ $entry->date->format('M j, Y') }}</td>
                            <td class="px-5 py-3 line-through text-slate-400">{{ $entry->description }}</td>
                            <td class="px-5 py-3 text-slate-400 text-xs hidden md:table-cell">{{ $entry->category }}</td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold
                                                    {{ $entry->is_income ? 'badge-income' : 'badge-expense' }}">
                                    {{ ucfirst($entry->type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right mono font-semibold text-slate-400 whitespace-nowrap">
                                Rp {{ number_format($entry->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-xs text-slate-400 hidden sm:table-cell">
                                {{ $entry->deleted_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    {{-- Restore --}}
                                    <form action="{{ route('entries.restore', $entry->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button
                                            class="text-xs text-emerald-600 hover:text-emerald-800 font-medium hover:underline whitespace-nowrap">
                                            ♻️ Restore
                                        </button>
                                    </form>
                                    {{-- Permanent delete --}}
                                    <form action="{{ route('entries.forceDelete', $entry->id) }}" method="POST"
                                        onsubmit="return confirm('Permanently delete this entry? Cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button
                                            class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline whitespace-nowrap">
                                            Delete forever
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <p class="text-3xl mb-3">🎉</p>
                                <p class="text-slate-400">Trash is empty.</p>
                                <a href="{{ route('entries.index') }}"
                                    class="text-sm text-emerald-600 hover:underline mt-2 inline-block">← Back to entries</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($trashed->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $trashed->links() }}
            </div>
        @endif
    </div>

@endsection