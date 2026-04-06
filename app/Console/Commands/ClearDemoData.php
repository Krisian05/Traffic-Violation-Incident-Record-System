<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDemoData extends Command
{
    protected $signature   = 'demo:clear';
    protected $description = 'Delete all demo/seed data — keeps admin user, violation types, and charge types';

    public function handle(): int
    {
        if (! $this->confirm('This will permanently delete all violators, vehicles, violations, incidents, and non-admin users. Continue?')) {
            $this->info('Aborted.');
            return 0;
        }

        // Delete in FK-safe order (children before parents)
        DB::table('incident_motorists')->delete();
        DB::table('incident_media')->delete();
        DB::table('incidents')->delete();
        DB::table('violation_vehicle_photos')->delete();
        DB::table('violations')->delete();
        DB::table('vehicle_photos')->delete();
        DB::table('vehicles')->delete();
        DB::table('violators')->delete();
        DB::table('users')->where('username', '!=', 'admin')->delete();

        $this->info('Done. Demo data cleared. Admin, violation types, and charge types are intact.');
        return 0;
    }
}
