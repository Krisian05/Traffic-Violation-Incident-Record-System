<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * All violations/incidents recorded before the LGU feature (or whose location
     * never resolved to a PSGC city) are stuck with lgu_id = NULL, making them
     * invisible to LGU-scoped cashiers/operators. Since only one LGU (Balamban)
     * has ever been onboarded, there's no ambiguity — backfill them all to it.
     */
    public function up(): void
    {
        $baLguId = DB::table('lgus')->where('code', 'BAL')->value('id');

        if (! $baLguId) {
            return;
        }

        DB::table('violations')->whereNull('lgu_id')->update(['lgu_id' => $baLguId]);
        DB::table('incidents')->whereNull('lgu_id')->update(['lgu_id' => $baLguId]);
    }

    public function down(): void
    {
        // Not reversible — we can't distinguish backfilled rows from ones that
        // were genuinely tagged Balamban through the normal resolver.
    }
};
