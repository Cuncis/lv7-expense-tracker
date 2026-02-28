<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringEntries extends Command
{
    protected $signature = 'entries:process-recurring {--dry-run : Preview without saving}';
    protected $description = 'Auto-create entries from recurring templates that are due today or earlier';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $today = now()->startOfDay();
        $count = 0;

        Entry::query()
            ->where('recurring', true)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->each(function (Entry $template) use ($today, $dryRun, &$count) {

                // Next due = last_generated_at (or original date) + one frequency unit
                $base = $template->last_generated_at
                    ? Carbon::parse($template->last_generated_at)
                    : Carbon::parse($template->date);

                $nextDue = $this->addFrequency($base, $template->frequency);

                // Generate all overdue occurrences up to today
                while ($nextDue->lte($today)) {
                    // Respect the recurring_until boundary
                    if ($template->recurring_until && $nextDue->gt($template->recurring_until)) {
                        break;
                    }

                    if ($dryRun) {
                        $this->line("  [dry-run] Would create: [{$template->type}] {$template->description} on {$nextDue->toDateString()}");
                    } else {
                        Entry::create([
                            'type' => $template->type,
                            'category' => $template->category,
                            'description' => $template->description,
                            'amount' => $template->amount,
                            'currency' => $template->currency ?? 'IDR',
                            'exchange_rate' => $template->exchange_rate ?? 1,
                            'date' => $nextDue->toDateString(),
                            'note' => $template->note,
                            'recurring' => false,
                            'frequency' => null,
                            'recurring_until' => null,
                            'last_generated_at' => null,
                        ]);

                        $template->update(['last_generated_at' => $nextDue->toDateString()]);
                    }

                    $count++;
                    $base = $nextDue->copy();
                    $nextDue = $this->addFrequency($base, $template->frequency);
                }
            });

        $label = $dryRun ? 'Would create' : 'Created';
        $this->info("✅ {$label} {$count} recurring " . str('entry')->plural($count) . '.');

        return Command::SUCCESS;
    }

    private function addFrequency(Carbon $date, ?string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $date->copy()->addDay(),
            'weekly' => $date->copy()->addWeek(),
            'monthly' => $date->copy()->addMonth(),
            'yearly' => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }
}
