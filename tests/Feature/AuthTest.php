<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // --- REGISTER ---

    public function test_user_can_register_and_get_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Bambang Saputra',
            'email' => 'bambang@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ])
            ->assertJsonPath('user.email', 'bambang@example.com')
            ->assertJsonPath('user.role', 'staff'); // default

        $this->assertDatabaseHas('users', [
            'email' => 'bambang@example.com',
            'role' => 'staff',
        ]);

        // Verify token is valid: hit /api/auth/me with the token
        $token = $response->json('token');
        $me = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/auth/me');
        $me->assertOk()->assertJsonPath('data.email', 'bambang@example.com');
    }

    public function test_register_with_admin_role(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_validates_email_format(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Bambang',
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_validates_password_minimum_length(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Bambang',
            'email' => 'bambang@example.com',
            'password' => 'short',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_invalid_role(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Bambang',
            'email' => 'bambang@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_register_password_is_hashed(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Bambang',
            'email' => 'bambang@example.com',
            'password' => 'plainpassword',
        ])->assertCreated();

        $user = User::where('email', 'bambang@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    // --- LOGIN ---

    public function test_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'bambang@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'bambang@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message', 'user' => ['id', 'email'], 'token'])
            ->assertJsonPath('user.email', 'bambang@example.com');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'bambang@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'bambang@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // --- ME ---

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'me@example.com');
    }

    public function test_me_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();
    }

    // --- LOGOUT ---

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        // Create a real Sanctum token so we can verify it's deleted from DB
        $token = $user->createToken('api')->plainTextToken;
        $tokenId = $user->tokens()->latest('id')->first()->id;

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenId]);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->postJson('/api/auth/logout');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Logged out');

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_logout_returns_401_without_token(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();
    }

    public function test_revoked_token_cannot_be_used(): void
    {
        $user = User::factory()->create();

        // Create a real Sanctum token (persisted in DB)
        $newToken = $user->createToken('api');
        $tokenPlain = $newToken->plainTextToken;
        $tokenId = $newToken->accessToken->id;

        // Logout via header (deletes the token row)
        $this->withHeaders(['Authorization' => 'Bearer '.$tokenPlain])
            ->postJson('/api/auth/logout')
            ->assertOk();

        // Token row should be gone from the DB
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);

        // Verify Sanctum cannot resolve the deleted token anymore
        $found = PersonalAccessToken::findToken($tokenPlain);
        $this->assertNull($found);
    }
}
