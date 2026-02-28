<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding expense tracker data...');

        Entry::withTrashed()->forceDelete();

        for ($i = 11; $i >= 1; $i--) {
            Entry::factory()->count(50)->create([
                'type' => 'income',
                'category' => 'Salary',
                'description' => 'Monthly salary',
                'amount' => fake()->numberBetween(10000000, 15000000),
                'date' => now()->subMonths($i)->startOfMonth()->addDays(4)->format('Y-m-d'),
            ]);
        }

        Entry::factory()->count(200)->create();

        Entry::factory(15)->thisMonth()->create();

        Entry::factory(8)->trashed()->create();

        $total = Entry::count();
        $income = Entry::income()->count();
        $expense = Entry::expense()->count();
        $trashed = Entry::onlyTrashed()->count();

        $this->command->info("✅ Seeded: {$total} entries ({$income} income, {$expense} expense) + {$trashed} trashed");
        $this->command->info('💰 Total income:  Rp ' . number_format(Entry::income()->sum('amount'), 0, ',', '.'));
        $this->command->info('💸 Total expense: Rp ' . number_format(Entry::expense()->sum('amount'), 0, ',', '.'));
    }
}
