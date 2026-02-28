{{-- resources/views/entries/_form.blade.php --}}
{{-- $entry is optional (null for create, model for edit) --}}

<div x-data="{ type: '{{ old('type', $entry->type ?? $defaultType ?? 'expense') }}' }" class="space-y-5">

    {{-- Type selector --}}
    <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Type *</label>
        <div class="flex gap-3">
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="type" value="income"
                       x-model="type"
                       class="sr-only"
                       {{ old('type', $entry->type ?? $defaultType ?? '') === 'income' ? 'checked' : '' }}>
                <div :class="type === 'income'
                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                        : 'border-slate-300 text-slate-500 hover:border-slate-400'"
                     class="border-2 rounded-xl px-4 py-3 text-sm font-semibold text-center transition cursor-pointer">
                    💚 Income
                </div>
            </label>
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="type" value="expense"
                       x-model="type"
                       class="sr-only"
                       {{ old('type', $entry->type ?? $defaultType ?? 'expense') === 'expense' ? 'checked' : '' }}>
                <div :class="type === 'expense'
                        ? 'border-red-500 bg-red-50 text-red-700'
                        : 'border-slate-300 text-slate-500 hover:border-slate-400'"
                     class="border-2 rounded-xl px-4 py-3 text-sm font-semibold text-center transition cursor-pointer">
                    🔴 Expense
                </div>
            </label>
        </div>
        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Category — changes options based on type --}}
    <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Category *</label>

        <template x-if="type === 'income'">
            <select name="category"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 bg-white {{ $errors->has('category') ? 'border-red-400' : '' }}">
                <option value="">Select category</option>
                @foreach ($incomeCategories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $entry->category ?? '') === $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                @endforeach
            </select>
        </template>

        <template x-if="type === 'expense'">
            <select name="category"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 bg-white {{ $errors->has('category') ? 'border-red-400' : '' }}">
                <option value="">Select category</option>
                @foreach ($expenseCategories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $entry->category ?? '') === $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                @endforeach
            </select>
        </template>

        @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Description + Amount in a row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Description *</label>
            <input type="text" name="description"
                   value="{{ old('description', $entry->description ?? '') }}"
                   placeholder="What was this for?"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 {{ $errors->has('description') ? 'border-red-400' : '' }}">
            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Amount (Rp) *</label>
            <input type="number" name="amount" step="1" min="1"
                   value="{{ old('amount', $entry->amount ?? '') }}"
                   placeholder="0"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 mono {{ $errors->has('amount') ? 'border-red-400' : '' }}">
            @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Date --}}
    <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Date *</label>
        <input type="date" name="date"
               value="{{ old('date', isset($entry) ? $entry->date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               max="{{ now()->format('Y-m-d') }}"
               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 {{ $errors->has('date') ? 'border-red-400' : '' }}">
        @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Note --}}
    <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
            Note <span class="font-normal text-slate-300">(optional)</span>
        </label>
        <textarea name="note" rows="2"
                  placeholder="Any extra detail..."
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">{{ old('note', $entry->note ?? '') }}</textarea>
        @error('note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

</div>

{{-- Alpine.js for reactive type toggle --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>