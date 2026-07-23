<?php

namespace App\Console\Commands;

use App\Models\Violation;
use Illuminate\Console\Command;

class DataRetentionCleanupCommand extends Command
{
    protected $signature = 'tvirs:retention-cleanup {--years=5 : Retention period in years per RA 10173 and COA rules}';
    protected $description = 'Enforce statutory data retention policies by archiving and purging expired settled records';

    public function handle(): int
    {
        $years = (int) $this->option('years');
        $cutoff = now()->subYears($years);

        $this->info("Enforcing Data Retention Policy (RA 10173 / COA threshold: {$years} years)...");

        // Identify settled violations older than statutory retention limit
        $expiredViolations = Violation::where('status', 'settled')
            ->where('settled_at', '<', $cutoff)
            ->get();

        $count = $expiredViolations->count();

        if ($count === 0) {
            $this->info("No expired records found past the {$years}-year retention period.");
            return Command::SUCCESS;
        }

        foreach ($expiredViolations as $violation) {
            $violation->delete(); // Soft-delete for compliance archiving
        }

        activity()
            ->useLog('privacy')
            ->event('data_retention_cleanup')
            ->withProperties(['count' => $count, 'cutoff_date' => $cutoff->toDateString()])
            ->log("Data Retention Cleanup executed: Archived {$count} settled records older than {$years} years.");

        $this->info("Successfully processed and archived {$count} expired records.");
        return Command::SUCCESS;
    }
}
