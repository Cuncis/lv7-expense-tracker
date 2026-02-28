@extends('layouts.app')
@section('title', 'Edit Entry')
@section('page-title', '✏️ Edit Entry')

@section('content')

    <div class="w-full max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('entries.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back</a>
            <h2 class="text-lg font-semibold">Edit Entry</h2>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 mb-4">
            <form action="{{ route('entries.update', $entry) }}" method="POST">
                @csrf @method('PUT')
                @include('entries._form')
                <div class="flex gap-3 mt-6 pt-5 border-t border-slate-100">
                    <button type="submit"
                        class="bg-slate-900 hover:bg-slate-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition">
                        Save Changes
                    </button>
                    <a href="{{ route('entries.index') }}"
                        class="border border-slate-300 text-slate-500 hover:text-slate-800 px-5 py-2.5 rounded-xl text-sm transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Danger zone --}}
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-red-600 mb-2">Danger Zone</p>
            <form action="{{ route('entries.destroy', $entry) }}" method="POST" onsubmit="return confirm('Move to trash?')">
                @csrf @method('DELETE')
                <button class="text-sm text-red-500 hover:underline">🗑 Move to trash</button>
            </form>
        </div>
    </div>

@endsection