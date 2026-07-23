<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseRestoreCommand extends Command
{
    protected $signature = 'tvirs:restore {--file= : Specific backup file name to restore from} {--force : Force restoration without confirmation}';
    protected $description = 'Restore database state from a validated TVIRS backup file';

    public function handle(): int
    {
        $this->info('Starting TVIRS database restoration...');

        $backupDir = 'backups';
        $fileName  = $this->option('file');

        if (empty($fileName)) {
            // Find latest backup file
            $files = collect(Storage::disk('local')->files($backupDir))
                ->filter(fn($f) => str_ends_with($f, '.json') && !str_ends_with($f, '.meta.json'))
                ->sortDesc()
                ->values();

            if ($files->isEmpty()) {
                $this->error('No backup files found in storage/app/backups/');
                return Command::FAILURE;
            }

            $filePath = $files->first();
            $fileName = basename($filePath);
        } else {
            $filePath = "{$backupDir}/{$fileName}";
        }

        if (!Storage::disk('local')->exists($filePath)) {
            $this->error("Backup file not found: {$filePath}");
            return Command::FAILURE;
        }

        $metaPath = str_replace('.json', '.meta.json', $filePath);
        $jsonContent = Storage::disk('local')->get($filePath);
        $calculatedChecksum = md5($jsonContent);

        if (Storage::disk('local')->exists($metaPath)) {
            $metaData = json_decode(Storage::disk('local')->get($metaPath), true);
            $expectedChecksum = $metaData['md5_checksum'] ?? null;

            if ($expectedChecksum && $expectedChecksum !== $calculatedChecksum) {
                $this->error("Checksum mismatch! Backup file may be corrupted. Expected: {$expectedChecksum}, Got: {$calculatedChecksum}");
                return Command::FAILURE;
            }
            $this->info("Checksum verified OK: {$calculatedChecksum}");
        }

        $backupData = json_decode($jsonContent, true);

        if (!isset($backupData['system']) || $backupData['system'] !== 'TVIRS') {
            $this->error('Invalid backup format. Missing TVIRS system marker.');
            return Command::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm("Are you sure you want to restore from {$fileName}?")) {
            $this->info('Restoration cancelled.');
            return Command::SUCCESS;
        }

        try {
            DB::transaction(function () use ($backupData) {
                // Disable foreign key checks for clean restore
                if (DB::getDriverName() === 'pgsql') {
                    DB::statement("SET CONSTRAINTS ALL DEFERRED;");
                } elseif (DB::getDriverName() === 'mysql') {
                    DB::statement("SET FOREIGN_KEY_CHECKS=0;");
                }

                foreach ($backupData['tables'] as $table => $records) {
                    if (\Schema::hasTable($table) && !empty($records)) {
                        foreach ($records as $record) {
                            $row = (array) $record;
                            DB::table($table)->updateOrInsert(
                                ['id' => $row['id'] ?? null],
                                $row
                            );
                        }
                        $this->line(" - Restored table [{$table}]: " . count($records) . " records");
                    }
                }

                if (DB::getDriverName() === 'mysql') {
                    DB::statement("SET FOREIGN_KEY_CHECKS=1;");
                }
            });

            activity()
                ->useLog('system')
                ->event('restore')
                ->withProperties(['file' => $fileName])
                ->log("Database restored from backup: {$fileName}");

            $this->info("Restoration completed successfully from {$fileName}!");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Restoration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
