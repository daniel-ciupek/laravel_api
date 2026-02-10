<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_register_and_receive_token(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => $email = 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        
        $response->assertStatus(201);
        $response->assertJsonStructure(['token', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);
    }

    public function test_user_cannot_register_with_invalid_email_format(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        
        $this->assertDatabaseMissing('users', [
            'email' => 'not-an-email',
        ]);
    }
    
    public function test_user_cannot_register_without_email(): void
    {
        $payload = [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
    
    public function test_user_cannot_register_with_existing_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    public function test_user_cannot_register_with_short_password(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
    
    public function test_user_cannot_register_without_password(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
    
    public function test_user_cannot_register_with_mismatched_passwords(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }
    
    public function test_user_cannot_register_without_password_confirmation(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_register_without_name(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }
    
    public function test_user_cannot_register_with_empty_name(): void
    {
        $payload = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }
    
    public function test_user_cannot_register_with_too_long_name(): void
    {
        $payload = [
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        
        $response->assertOk();
        $response->assertJsonStructure(['token', 'user']);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
    
    public function test_user_cannot_login_with_non_existing_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
    
    public function test_user_cannot_login_without_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
    
    public function test_user_cannot_login_without_password(): void
    {
        $user = User::factory()->create();
        
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }
    
    public function test_user_cannot_login_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_multiple_validation_errors(): void
    {
        $payload = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/auth/register', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/auth/logout');

        $response->assertNoContent();

        
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'auth_token',
        ]);
    }

    public function test_quest_cannot_access_user_endopint(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_user_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/user');

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }
}