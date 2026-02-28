<?php

namespace Database\Factories;

use App\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entry>
 */
class EntryFactory extends Factory
{
    protected $model = Entry::class;
    public function definition(): array
    {
        // 40% income, 60% expense
        $type = $this->faker->randomElement(['income', 'income', 'expense', 'expense', 'expense']);

        $category = $type === 'income'
            ? $this->faker->randomElement(Entry::INCOME_CATEGORIES)
            : $this->faker->randomElement(Entry::EXPENSE_CATEGORIES);

        $amount = match ($category) {
            'Salary' => $this->faker->numberBetween(3000000, 15000000),
            'Business' => $this->faker->numberBetween(5000000, 20000000),
            'Investment' => $this->faker->numberBetween(1000000, 5000000),
            'Gift' => $this->faker->numberBetween(500000, 2000000),
            'Food' => $this->faker->numberBetween(50000, 500000),
            'Transportation' => $this->faker->numberBetween(20000, 200000),
            'Entertainment' => $this->faker->numberBetween(100000, 1000000),
            'Health' => $this->faker->numberBetween(100000, 1000000),
            'Education' => $this->faker->numberBetween(200000, 2000000),
            default => $this->faker->numberBetween(50000, 5000000),
        };

        $descriptions = [
            'Salary' => ['Monthly salary', 'Bonus', 'Freelance project'],
            'Business' => ['Product sales', 'Service income', 'Consulting fee'],
            'Investment' => ['Stock dividends', 'Real estate rental', 'Cryptocurrency gain'],
            'Gift' => ['Birthday gift', 'Holiday gift', 'Unexpected gift'],
            'Food' => ['Groceries', 'Dining out', 'Coffee'],
            'Transportation' => ['Gas', 'Public transit', 'Ride-sharing'],
            'Entertainment' => ['Movie tickets', 'Concert', 'Streaming subscription'],
            'Health' => ['Doctor visit', 'Medication', 'Gym membership'],
            'Education' => ['Tuition', 'Books', 'Online course'],
        ];

        $description = isset($descriptions[$category]) ? $this->faker->randomElement($descriptions[$category]) : $this->faker->sentence(3);

        return [
            'type' => $type,
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'note' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function thisMonth(): static
    {
        return $this->state(fn(array $attributes) => [
            'date' => $this->faker->dateTimeBetween('first day of this month', 'last day of this month')->format('Y-m-d'),
        ]);
    }

    public function trashed(): static
    {
        return $this->state(fn(array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
