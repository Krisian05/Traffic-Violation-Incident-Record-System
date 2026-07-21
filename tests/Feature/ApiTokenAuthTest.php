<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role'     => 'admin',
            'username' => 'admin-test',
            'password' => bcrypt('secret-password'),
        ]);
    }

    public function test_login_returns_plaintext_token_but_stores_only_its_hash(): void
    {
        $this->createAdmin();

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin-test',
            'password' => 'secret-password',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'username', 'role']]);

        $plaintext = $response->json('token');
        $this->assertNotEmpty($plaintext);

        $stored = ApiToken::firstOrFail();
        // The database must never hold the plaintext token, only its hash.
        $this->assertNotSame($plaintext, $stored->token);
        $this->assertSame(hash('sha256', $plaintext), $stored->token);
    }

    public function test_authenticated_request_succeeds_with_the_issued_token(): void
    {
        $this->createAdmin();

        $token = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin-test',
            'password' => 'secret-password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('username', 'admin-test');
    }

    public function test_request_with_an_invalid_token_is_rejected(): void
    {
        $this->withHeader('Authorization', 'Bearer not-a-real-token')
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_request_with_no_token_is_rejected(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_logout_deletes_the_token_and_it_can_no_longer_authenticate(): void
    {
        $this->createAdmin();

        $token = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin-test',
            'password' => 'secret-password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertSame(0, ApiToken::count());

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }
}
