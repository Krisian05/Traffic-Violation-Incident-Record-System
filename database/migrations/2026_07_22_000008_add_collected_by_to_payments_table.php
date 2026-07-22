<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Free-text cashier_name stays for the printed receipt; this FK gives
            // reconciliation/audit reporting a stable identity to group and filter by.
            $table->foreignId('collected_by')->nullable()->after('cashier_name')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collected_by');
        });
    }
};
