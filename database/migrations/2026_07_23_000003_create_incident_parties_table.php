<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replaces the freeform incidents.other_involved JSON blob with a proper
     * related table covering the spec's non-motorist party roles: passenger,
     * witness, reporting_party, responding_personnel (plus "other" for the
     * pedestrian/cyclist/etc. entries the old JSON blob held).
     */
    public function up(): void
    {
        Schema::create('incident_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30); // passenger, witness, reporting_party, responding_personnel, other
            $table->string('name')->nullable();
            $table->string('contact_number', 50)->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable(); // party type detail, witness statement, notes, etc.
            $table->foreignId('responding_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Backfill existing other_involved JSON rows (type/name/contact/charge/notes)
        // into the new table as role=other, preserving all captured data.
        $incidents = DB::table('incidents')->whereNotNull('other_involved')->get(['id', 'other_involved']);

        foreach ($incidents as $incident) {
            $rows = json_decode($incident->other_involved, true);
            if (!is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                $descriptionParts = array_filter([
                    !empty($row['type']) ? 'Type: ' . $row['type'] : null,
                    !empty($row['charge']) ? 'Charge: ' . $row['charge'] : null,
                    !empty($row['notes']) ? $row['notes'] : null,
                ]);

                DB::table('incident_parties')->insert([
                    'incident_id'  => $incident->id,
                    'role'         => 'other',
                    'name'         => $row['name'] ?? null,
                    'contact_number' => $row['contact'] ?? null,
                    'description'  => !empty($descriptionParts) ? implode(' — ', $descriptionParts) : null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('other_involved');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->json('other_involved')->nullable();
        });

        Schema::dropIfExists('incident_parties');
    }
};
