<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'category',
        'description',
        'amount',
        'date',
        'note',
        'recurring',
        'frequency',
        'recurring_until',
        'last_generated_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'date' => 'date',
        'recurring' => 'boolean',
        'recurring_until' => 'date',
        'last_generated_at' => 'date',
    ];

    const INCOME_CATEGORIES = [
        'Salary',
        'Business',
        'Investment',
        'Gift',
        'Other',
    ];

    const EXPENSE_CATEGORIES = [
        'Food',
        'Transportation',
        'Entertainment',
        'Health',
        'Education',
        'Other',
    ];

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeForCategory($query, $category)
    {
        return $category ? $query->where('category', $category) : $query;
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }
        return $query;
    }

    public function scopeSearch($query, $term)
    {
        if (!$term) {
            return $query;
        }
        return $query->where(function ($q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%")
                ->orWhere('note', 'like', "%{$term}%");
        });
    }

    public static function totalIncome($query = null)
    {
        $query = $query ?? self::query();
        return $query->income()->sum('amount');
    }

    public static function totalExpense($query = null)
    {
        $query = $query ?? self::query();
        return $query->expense()->sum('amount');
    }

    public static function groupedByCategory(string $type = 'expense')
    {
        return static::where('type', $type)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
    }

    public static function monthlyTotals($year)
    {
        return static::selectRaw('MONTH(date) as month, type, SUM(amount) as total')
            ->whereYear('date', $year)
            ->groupByRaw('MONTH(date), type')
            ->orderByRaw('MONTH(date)')
            ->get()
            ->groupBy('month');
    }

    public function getIsIncomeAttribute()
    {
        return $this->type === 'income';
    }

    public static function allCategories()
    {
        return array_merge(self::INCOME_CATEGORIES, self::EXPENSE_CATEGORIES);
    }
}
