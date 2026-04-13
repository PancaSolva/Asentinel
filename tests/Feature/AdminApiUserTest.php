<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock the admin session
        session(['admin_logged_in' => true]);
    }

    public function test_unauthorized_user_cannot_access_api()
    {
        // Clear the session mock
        session()->forget('admin_logged_in');

        $response = $this->getJson('/admin/api/users');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_admin_can_list_users()
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/admin/api/users');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $response = $this->postJson('/admin/api/users', $userData);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New User')
                 ->assertJsonPath('data.email', 'newuser@example.com')
                 ->assertJsonPath('data.role', 'user');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'user',
        ]);
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'role' => 'user',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'role' => 'admin',
        ];

        $response = $this->putJson("/admin/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Updated Name')
                 ->assertJsonPath('data.role', 'admin');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/admin/api/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
