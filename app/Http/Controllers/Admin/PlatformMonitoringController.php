<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Lgu;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class PlatformMonitoringController extends Controller
{
    public function index(): View
    {
        $stats = $this->collectPlatformStats();
        return view('admin.monitoring.index', compact('stats'));
    }

    public function getStats(): JsonResponse
    {
        return response()->json($this->collectPlatformStats());
    }

    private function collectPlatformStats(): array
    {
        // 1. Database Health
        $dbStatus = 'ok';
        $dbLatencyMs = 0;
        $dbMessage = 'Database connected successfully.';
        $dbDriver = 'unknown';

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $dbLatencyMs = round((microtime(true) - $start) * 1000, 2);
            $dbDriver = DB::getDriverName();
        } catch (Throwable $e) {
            $dbStatus = 'error';
            $dbMessage = $e->getMessage();
        }

        // 2. Storage & Disk Health
        $storageStatus = 'ok';
        $storageMessage = 'Read/Write test passed.';
        try {
            $testKey = 'health_check_' . time() . '.txt';
            Storage::put($testKey, 'ok');
            $readVal = Storage::get($testKey);
            Storage::delete($testKey);
            if ($readVal !== 'ok') {
                $storageStatus = 'warning';
                $storageMessage = 'Content mismatch on disk write test.';
            }
        } catch (Throwable $e) {
            $storageStatus = 'error';
            $storageMessage = $e->getMessage();
        }

        // Disk Usage %
        $diskTotalSpace = @disk_total_space(base_path()) ?: 0;
        $diskFreeSpace  = @disk_free_space(base_path()) ?: 0;
        $diskUsedSpace  = $diskTotalSpace - $diskFreeSpace;
        $diskUsedPercent = $diskTotalSpace > 0 ? round(($diskUsedSpace / $diskTotalSpace) * 100, 1) : 0;

        // 3. Cache Health
        $cacheStatus = 'ok';
        try {
            $testKey = 'cache_test_' . time();
            Cache::put($testKey, 'val', 10);
            if (Cache::get($testKey) !== 'val') {
                $cacheStatus = 'warning';
            }
            Cache::forget($testKey);
        } catch (Throwable $e) {
            $cacheStatus = 'error';
        }

        // 4. Memory & Environment Info
        $memoryUsage = round(memory_get_usage(true) / (1024 * 1024), 2);
        $memoryPeak = round(memory_get_peak_usage(true) / (1024 * 1024), 2);

        // 5. System Analytics Counters
        $totalLgus = Lgu::count();
        $totalUsers = User::count();
        $totalViolations = Violation::count();
        $totalIncidents = Incident::count();
        $totalCollections = Payment::sum('amount_paid');

        // 6. Active Sessions & OCR Status
        $activeSessionsCount = 0;
        try {
            $activeSessionsCount = DB::table('sessions')->count();
        } catch (Throwable $e) {}

        $ocrPrimary = SystemSetting::get('ocr_primary_engine', 'gemini');
        $ocrEnabled = SystemSetting::get('ocr_enabled', true);

        return [
            'timestamp'           => now()->toIso8601String(),
            'formatted_time'      => now()->format('Y-m-d H:i:s T'),
            'environment'         => config('app.env'),
            'php_version'         => PHP_VERSION,
            'laravel_version'     => app()->version(),
            'database' => [
                'status'    => $dbStatus,
                'latency_ms'=> $dbLatencyMs,
                'driver'    => $dbDriver,
                'message'   => $dbMessage,
            ],
            'storage' => [
                'status'        => $storageStatus,
                'message'       => $storageMessage,
                'used_percent'  => $diskUsedPercent,
                'total_gb'      => round($diskTotalSpace / (1024 * 1024 * 1024), 2),
                'free_gb'       => round($diskFreeSpace / (1024 * 1024 * 1024), 2),
            ],
            'cache' => [
                'status' => $cacheStatus,
                'driver' => config('cache.default'),
            ],
            'memory' => [
                'usage_mb' => $memoryUsage,
                'peak_mb'  => $memoryPeak,
            ],
            'analytics' => [
                'total_lgus'        => $totalLgus,
                'total_users'       => $totalUsers,
                'total_violations'  => $totalViolations,
                'total_incidents'   => $totalIncidents,
                'total_collections' => number_format((float)$totalCollections, 2),
                'active_sessions'   => $activeSessionsCount,
            ],
            'ocr' => [
                'enabled' => $ocrEnabled,
                'engine'  => $ocrPrimary,
            ],
        ];
    }
}
