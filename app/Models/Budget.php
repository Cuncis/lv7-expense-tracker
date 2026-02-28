<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['category', 'amount'];

    protected $casts = [
        'amount' => 'integer',
    ];

    /**
     * Get all budgets keyed by category with current-month spending attached.
     * Returns a collection of Budget models plus ->spent and ->percentage.
     */
    public static function withCurrentMonthSpending(): \Illuminate\Support\Collection
    {
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');

        $spent = Entry::expense()
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return static::orderBy('category')->get()->map(function (Budget $budget) use ($spent) {
            $budget->spent = $spent[$budget->category] ?? 0;
            $budget->percentage = $budget->amount > 0
                ? round(($budget->spent / $budget->amount) * 100)
                : 0;
            $budget->overspent = $budget->spent > $budget->amount;
            return $budget;
        });
    }
}
