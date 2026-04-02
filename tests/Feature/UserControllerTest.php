<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;


class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_get_all_users()
    {
        User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password123'),
        ]);

        User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function it_can_create_user()
    {
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Created User',
            'email' => 'created@example.com',
            'location' => 'Moscow',
        ]);

        $response->assertStatus(201)
            ->assertJson(['name' => 'Created User']);
    }

    #[Test]
    public function it_can_show_user()
    {
        $user = User::create([
            'name' => 'Show User',
            'email' => 'show@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->getJson('/api/v1/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $user->id]);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_user()
    {
        $response = $this->getJson('/api/v1/users/nonexistent-id');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_user()
    {
        $user = User::create([
            'name' => 'Old Name',
            'email' => 'update@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->putJson('/api/v1/users/' . $user->id, [
            'name' => 'Updated Name',
            'location' => 'New Location',
        ]);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Updated Name']);
    }

    #[Test]
    public function it_can_delete_user()
    {
        $user = User::create([
            'name' => 'Delete User',
            'email' => 'delete@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->deleteJson('/api/v1/users/' . $user->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
