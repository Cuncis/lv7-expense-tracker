<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Entry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');

        $monthIncome = Entry::income()->forDateRange($monthStart, $monthEnd)->selectRaw('COALESCE(SUM(amount * exchange_rate), 0) as total')->value('total') ?? 0;
        $monthExpense = Entry::expense()->forDateRange($monthStart, $monthEnd)->selectRaw('COALESCE(SUM(amount * exchange_rate), 0) as total')->value('total') ?? 0;
        $monthNet = $monthIncome - $monthExpense;

        // All time
        $totalIncome = Entry::income()->selectRaw('COALESCE(SUM(amount * exchange_rate), 0) as total')->value('total') ?? 0;
        $totalExpense = Entry::expense()->selectRaw('COALESCE(SUM(amount * exchange_rate), 0) as total')->value('total') ?? 0;
        $totalNet = $totalIncome - $totalExpense;

        $expenseByCategory = Entry::expense()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount * exchange_rate) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // ── Income by category (all time) ─────────────────────────────────────
        $incomeByCategory = Entry::income()
            ->selectRaw('category, SUM(amount * exchange_rate) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // selectRaw with SQLite's strftime for month grouping
        $monthlyIncome = Entry::income()
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("strftime('%Y-%m', date) as month, SUM(amount * exchange_rate) as total")
            ->groupByRaw("strftime('%Y-%m', date)")
            ->orderBy('month')
            ->pluck('total', 'month');

        $monthlyExpense = Entry::expense()
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("strftime('%Y-%m', date) as month, SUM(amount * exchange_rate) as total")
            ->groupByRaw("strftime('%Y-%m', date)")
            ->orderBy('month')
            ->pluck('total', 'month');

        // Build complete 6-month labels (fill missing months with 0)
        $monthLabels = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthLabels[] = now()->subMonths($i)->format('M Y');
            $incomeData[] = $monthlyIncome[$key] ?? 0;
            $expenseData[] = $monthlyExpense[$key] ?? 0;
        }

        // ── Recent entries ────────────────────────────────────────────────────
        $recentEntries = Entry::orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // ── Budgets with current month spending ────────────────────────────────
        $budgets = Budget::withCurrentMonthSpending();

        // ── Totals count ──────────────────────────────────────────────────────
        $totalEntries = Entry::count();
        $trashCount = Entry::onlyTrashed()->count();

        return view('dashboard.index', compact(
            'monthIncome',
            'monthExpense',
            'monthNet',
            'totalIncome',
            'totalExpense',
            'totalNet',
            'expenseByCategory',
            'incomeByCategory',
            'monthLabels',
            'incomeData',
            'expenseData',
            'recentEntries',
            'totalEntries',
            'trashCount',
            'budgets'
        ));
    }
}
