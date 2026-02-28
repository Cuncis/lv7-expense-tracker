<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Models\Entry;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'category', 'from', 'to', 'search']);

        $query = Entry::query()
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            })
            ->forCategory($filters['category'] ?? null)
            ->forDateRange($filters['from'] ?? null, $filters['to'] ?? null)
            ->search($filters['search'] ?? null)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        $filteredIncome = (clone $query)->income()->sum('amount');
        $filteredExpense = (clone $query)->expense()->sum('amount');
        $filteredNet = $filteredIncome - $filteredExpense;

        $entries = $query->paginate(20)->withQueryString();

        $allCategories = Entry::allCategories();

        return view('entries.index', compact('entries', 'filters', 'filteredIncome', 'filteredExpense', 'filteredNet', 'allCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('entries.create', [
            'incomeCategories' => Entry::INCOME_CATEGORIES,
            'expenseCategories' => Entry::EXPENSE_CATEGORIES,
            'today' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEntryRequest $request)
    {
        Entry::create($request->validated());

        return redirect()->route('entries.index')
            ->with('success', $request->type === 'income'
                ? '💰 Income entry added!' : '💸 Expense entry added!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Entry $entry)
    {
        return view('entries.edit', [
            'entry' => $entry,
            'incomeCategories' => Entry::INCOME_CATEGORIES,
            'expenseCategories' => Entry::EXPENSE_CATEGORIES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreEntryRequest $request, Entry $entry)
    {
        $entry->update($request->validated());

        return redirect()->route('entries.index')
            ->with('success', '✏️ Entry updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entry $entry)
    {
        $entry->delete();

        return redirect()->route('entries.index')
            ->with('success', '🗑️ Entry moved to trash.');
    }

    public function trash()
    {
        $trashed = Entry::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(20);

        return view('entries.trashed', compact('trashed'));
    }

    public function restore($id)
    {
        $entry = Entry::onlyTrashed()->findOrFail($id);
        $entry->restore();

        return redirect()->route('entries.index')
            ->with('success', '♻️ Entry restored from trash.');
    }

    public function forceDelete($id)
    {
        $entry = Entry::onlyTrashed()->findOrFail($id);
        $entry->forceDelete();

        return redirect()->route('entries.trash')
            ->with('success', '❌ Entry permanently deleted.');
    }

    public function emptyTrash()
    {
        Entry::onlyTrashed()->forceDelete();

        return redirect()->route('entries.trash')
            ->with('success', '🧹 Trash emptied. All trashed entries permanently deleted.');
    }
}
