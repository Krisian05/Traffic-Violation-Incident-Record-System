<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupAndHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_v1_health_endpoint_returns_ok_status(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'environment',
            'checks' => ['database', 'storage', 'cache'],
        ]);
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('checks.database.status', 'ok');
        $response->assertJsonPath('checks.storage.status', 'ok');
        $response->assertJsonPath('checks.cache.status', 'ok');
    }

    public function test_web_health_endpoint_returns_ok_status(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');
    }

    public function test_automated_database_backup_command_creates_valid_dump_and_checksum(): void
    {
        User::factory()->create(['name' => 'Backup Test User']);

        $exitCode = Artisan::call('tvirs:backup');
        $this->assertSame(0, $exitCode);

        $files = Storage::disk('local')->files('backups');
        $this->assertNotEmpty($files);

        $jsonFile = collect($files)->first(fn($f) => str_ends_with($f, '.json') && !str_ends_with($f, '.meta.json'));
        $metaFile = collect($files)->first(fn($f) => str_ends_with($f, '.meta.json'));

        $this->assertNotNull($jsonFile);
        $this->assertNotNull($metaFile);

        $content = Storage::disk('local')->get($jsonFile);
        $meta = json_decode(Storage::disk('local')->get($metaFile), true);

        $this->assertSame(md5($content), $meta['md5_checksum']);
        $this->assertGreaterThan(0, $meta['total_records']);
    }

    public function test_database_restore_command_restores_backup_data(): void
    {
        $user = User::factory()->create(['name' => 'Original User', 'username' => 'orig_user']);

        Artisan::call('tvirs:backup');

        // Delete user
        $user->delete();
        $this->assertNull(User::where('username', 'orig_user')->first());

        // Restore
        $exitCode = Artisan::call('tvirs:restore', ['--force' => true]);
        $this->assertSame(0, $exitCode);

        $restoredUser = User::where('username', 'orig_user')->first();
        $this->assertNotNull($restoredUser);
        $this->assertSame('Original User', $restoredUser->name);
    }
}
