<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TechnicalAdminController extends Controller
{
    public function index(): View
    {
        $maintenanceMode = SystemSetting::get('maintenance_mode', false);
        $maintenanceMessage = SystemSetting::get('maintenance_message', 'System is undergoing scheduled maintenance.');

        $logPath = storage_path('logs/laravel.log');
        $logExists = File::exists($logPath);
        $logSizeBytes = $logExists ? File::size($logPath) : 0;
        $logSizeMb = round($logSizeBytes / (1024 * 1024), 2);

        $backupsDir = storage_path('app/backups');
        if (!File::exists($backupsDir)) {
            File::makeDirectory($backupsDir, 0755, true);
        }
        $backupFiles = File::files($backupsDir);
        $backupsCount = count($backupFiles);

        return view('admin.technical.index', compact(
            'maintenanceMode',
            'maintenanceMessage',
            'logExists',
            'logSizeMb',
            'backupsCount'
        ));
    }

    public function toggleMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'maintenance_mode'    => ['nullable', 'boolean'],
            'maintenance_message' => ['required', 'string', 'max:500'],
        ]);

        $enabled = isset($data['maintenance_mode']) && $data['maintenance_mode'];
        
        SystemSetting::set('maintenance_mode', $enabled ? '1' : '0', 'maintenance', 'boolean', 'System Under Scheduled Maintenance');
        SystemSetting::set('maintenance_message', $data['maintenance_message'], 'maintenance', 'string', 'Public Maintenance Notice Banner');

        activity()
            ->causedBy(Auth::user())
            ->useLog('system')
            ->log(($enabled ? 'Enabled' : 'Disabled') . ' system maintenance mode.');

        $statusMsg = $enabled ? 'System Maintenance Mode ENABLED.' : 'System Maintenance Mode DISABLED.';
        return back()->with('success', $statusMsg);
    }

    public function logsIndex(Request $request): View
    {
        $logPath = storage_path('logs/laravel.log');
        $level = strtolower(trim($request->input('level', '')));
        $search = trim($request->input('search', ''));

        $logLines = [];
        $logSizeBytes = 0;

        if (File::exists($logPath)) {
            $logSizeBytes = File::size($logPath);
            $rawContent = File::get($logPath);

            // Parse log entries
            $pattern = '/^\[\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}.*?\].*/m';
            preg_match_all($pattern, $rawContent, $matches);
            $allEntries = array_reverse($matches[0] ?? []);

            foreach ($allEntries as $line) {
                if ($level !== '') {
                    if (!str_contains(strtolower($line), '.' . $level . ':') && !str_contains(strtolower($line), 'local.' . $level)) {
                        continue;
                    }
                }

                if ($search !== '') {
                    if (!str_contains(strtolower($line), strtolower($search))) {
                        continue;
                    }
                }

                $logLines[] = $line;

                if (count($logLines) >= 300) {
                    break;
                }
            }
        }

        $logSizeMb = round($logSizeBytes / (1024 * 1024), 2);

        return view('admin.technical.logs', compact('logLines', 'logSizeMb', 'level', 'search'));
    }

    public function logsClear(): RedirectResponse
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }

        activity()
            ->causedBy(Auth::user())
            ->useLog('system')
            ->log('Cleared application system log file.');

        return back()->with('success', 'Application log file cleared successfully.');
    }

    public function logsDownload(): BinaryFileResponse|RedirectResponse
    {
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) {
            return back()->with('error', 'Log file does not exist.');
        }

        return response()->download($logPath, 'laravel-' . now()->format('Y-m-d-His') . '.log');
    }

    public function backupsIndex(): View
    {
        $backupsDir = storage_path('app/backups');
        if (!File::exists($backupsDir)) {
            File::makeDirectory($backupsDir, 0755, true);
        }

        $files = File::files($backupsDir);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename'   => $file->getFilename(),
                'size_mb'    => round($file->getSize() / (1024 * 1024), 2),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('admin.technical.backups', compact('backups'));
    }

    public function backupsCreate(): RedirectResponse
    {
        $backupsDir = storage_path('app/backups');
        if (!File::exists($backupsDir)) {
            File::makeDirectory($backupsDir, 0755, true);
        }

        $filename = 'tvrs_backup_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = $backupsDir . '/' . $filename;

        try {
            // Collect database snapshot
            $tables = ['lgus', 'users', 'violation_types', 'violators', 'vehicles', 'violations', 'incidents', 'incident_charge_types', 'payments', 'system_settings'];
            $exportData = [
                'metadata' => [
                    'timestamp'  => now()->toIso8601String(),
                    'created_by' => Auth::user()->name . ' (' . Auth::user()->username . ')',
                    'system'     => config('app.name'),
                ],
                'tables' => [],
            ];

            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $exportData['tables'][$table] = DB::table($table)->get()->toArray();
                }
            }

            File::put($filepath, json_encode($exportData, JSON_PRETTY_PRINT));

            activity()
                ->causedBy(Auth::user())
                ->useLog('system')
                ->log("Generated database backup snapshot '{$filename}'.");

            return redirect()->route('admin.technical.backups.index')
                ->with('success', "Database backup snapshot '{$filename}' created successfully.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.technical.backups.index')
                ->with('error', 'Failed to generate database backup: ' . $e->getMessage());
        }
    }

    public function backupsDownload(string $filename): BinaryFileResponse|RedirectResponse
    {
        $filename = basename($filename); // Sanitize filename against traversal
        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            return back()->with('error', 'Backup file not found.');
        }

        return response()->download($filepath);
    }

    public function backupsDestroy(string $filename): RedirectResponse
    {
        $filename = basename($filename);
        $filepath = storage_path('app/backups/' . $filename);

        if (File::exists($filepath)) {
            File::delete($filepath);
            
            activity()
                ->causedBy(Auth::user())
                ->useLog('system')
                ->log("Deleted database backup snapshot '{$filename}'.");

            return back()->with('success', "Backup '{$filename}' deleted successfully.");
        }

        return back()->with('error', 'Backup file not found.');
    }

    public function runArtisan(Request $request): RedirectResponse
    {
        $request->validate([
            'command' => ['required', 'in:cache:clear,route:clear,view:clear,config:clear,storage:link'],
        ]);

        $cmd = $request->input('command');

        try {
            Artisan::call($cmd);

            activity()
                ->causedBy(Auth::user())
                ->useLog('system')
                ->log("Executed artisan utility command '{$cmd}'.");

            return back()->with('success', "Executed command '{$cmd}' successfully: " . trim(Artisan::output()));
        } catch (\Throwable $e) {
            return back()->with('error', "Failed to execute '{$cmd}': " . $e->getMessage());
        }
    }
}
