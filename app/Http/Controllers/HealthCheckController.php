<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $status = 'ok';
        $checks = [
            'database' => $this->checkDatabase(),
            'storage'  => $this->checkStorage(),
            'cache'    => $this->checkCache(),
        ];

        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                $status = 'degraded';
                break;
            }
        }

        $statusCode = $status === 'ok' ? 200 : 503;

        return response()->json([
            'status'     => $status,
            'timestamp'  => now()->toIso8601String(),
            'environment'=> config('app.env'),
            'checks'     => $checks,
        ], $statusCode);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status'  => 'ok',
                'driver'  => DB::getDriverName(),
                'message' => 'Database connection established.',
            ];
        } catch (Throwable $e) {
            return [
                'status'  => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            Storage::put($testFile, 'health');
            $content = Storage::get($testFile);
            Storage::delete($testFile);

            if ($content === 'health') {
                return [
                    'status'  => 'ok',
                    'message' => 'Storage read/write succeeded.',
                ];
            }

            return [
                'status'  => 'error',
                'message' => 'Storage read content mismatch.',
            ];
        } catch (Throwable $e) {
            return [
                'status'  => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_cache_test_' . time();
            Cache::put($key, 'ok', 10);
            $val = Cache::get($key);
            Cache::forget($key);

            if ($val === 'ok') {
                return [
                    'status'  => 'ok',
                    'message' => 'Cache read/write succeeded.',
                ];
            }

            return [
                'status'  => 'error',
                'message' => 'Cache read content mismatch.',
            ];
        } catch (Throwable $e) {
            return [
                'status'  => 'error',
                'message' => 'Cache check failed: ' . $e->getMessage(),
            ];
        }
    }
}
