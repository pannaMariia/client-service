<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register()
    {

        $response = $this->postJson('/api/v1/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'location' => 'Moscow',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'location'],
                'token'
            ]);
    }

    #[Test]
    public function registration_fails_with_invalid_data()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'New User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    public function user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);
    }

    #[Test]
    public function login_fails_with_wrong_credentials()
    {
        $user = User::factory()->create();
//        $user = User::create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//            'password' => bcrypt('password123'),
//        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    #[Test]
    public function authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
//        $user = User::create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//            'password' => bcrypt('password123'),
//        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email']]);
    }

    #[Test]
    public function unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    #[Test]
    public function user_can_update_profile()
    {
        $user = User::factory()->create();
//        $user = User::create([
//            'name' => 'Old Name',
//            'email' => 'test@example.com',
//            'password' => bcrypt('password123'),
//        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/profile', [
            'name' => 'New Name',
            'location' => 'Saint Petersburg',
        ]);

        $response->assertStatus(200)
            ->assertJson(['user' => ['name' => 'New Name']]);
    }

    #[Test]
    public function user_can_logout()
    {
        $user = User::factory()->create();
//        $user = User::create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//            'password' => bcrypt('password123'),
//        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }
}
