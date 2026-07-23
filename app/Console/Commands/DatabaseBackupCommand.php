<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'tvirs:backup {--retention=30 : Number of days to retain backup files}';
    protected $description = 'Perform automated database backup with MD5 checksum verification and retention management';

    public function handle(): int
    {
        $this->info('Starting TVIRS automated database backup...');

        try {
            $timestamp  = now()->format('Y-m-d_H-i-s');
            $backupDir  = 'backups';
            $fileName   = "tvirs_backup_{$timestamp}.json";
            $filePath   = "{$backupDir}/{$fileName}";
            $metaPath   = "{$backupDir}/tvirs_backup_{$timestamp}.meta.json";

            $tables = [
                'users',
                'lgus',
                'violation_types',
                'violators',
                'vehicles',
                'violations',
                'incidents',
                'payments',
                'device_registrations',
                'activity_log',
            ];

            $backupData = [
                'system'    => 'TVIRS',
                'version'   => '1.0.0',
                'timestamp' => now()->toIso8601String(),
                'tables'    => [],
            ];

            $totalRecords = 0;
            foreach ($tables as $table) {
                if (\Schema::hasTable($table)) {
                    $records = DB::table($table)->get()->toArray();
                    $count   = count($records);
                    $backupData['tables'][$table] = $records;
                    $totalRecords += $count;
                    $this->line(" - Dumped table [{$table}]: {$count} records");
                }
            }

            $jsonContent = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $checksum    = md5($jsonContent);
            $fileSizeBytes = strlen($jsonContent);

            Storage::disk('local')->put($filePath, $jsonContent);

            $metaData = [
                'file_name'     => $fileName,
                'file_size'     => $fileSizeBytes,
                'total_records' => $totalRecords,
                'md5_checksum'  => $checksum,
                'created_at'    => now()->toIso8601String(),
            ];

            Storage::disk('local')->put($metaPath, json_encode($metaData, JSON_PRETTY_PRINT));

            // Log activity for audit trail
            activity()
                ->useLog('system')
                ->event('backup')
                ->withProperties(['file' => $fileName, 'size' => $fileSizeBytes, 'records' => $totalRecords, 'checksum' => $checksum])
                ->log("Automated system backup created: {$fileName}");

            $this->info("Backup completed successfully! Saved to storage/app/{$filePath}");
            $this->info("MD5 Checksum: {$checksum}");

            // Retention cleanup
            $retentionDays = (int) $this->option('retention');
            $this->cleanupOldBackups($backupDir, $retentionDays);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function cleanupOldBackups(string $dir, int $days): void
    {
        $files = Storage::disk('local')->files($dir);
        $cutoff = now()->subDays($days)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            if (Storage::disk('local')->lastModified($file) < $cutoff) {
                Storage::disk('local')->delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Purged {$deleted} backup file(s) older than {$days} days.");
        }
    }
}
