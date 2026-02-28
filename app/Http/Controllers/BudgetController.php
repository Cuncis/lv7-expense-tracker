<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Entry;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::withCurrentMonthSpending();
        $budgetByCategory = $budgets->keyBy('category');
        $categories = Entry::EXPENSE_CATEGORIES;

        return view('budgets.index', compact('budgets', 'budgetByCategory', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => ['required', 'string', 'in:' . implode(',', Entry::EXPENSE_CATEGORIES)],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        Budget::updateOrCreate(
            ['category' => $request->category],
            ['amount' => $request->amount]
        );

        return redirect()->route('budgets.index')
            ->with('success', '✅ Budget set for ' . $request->category . '.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('success', '🗑️ Budget for ' . $budget->category . ' removed.');
    }
}
