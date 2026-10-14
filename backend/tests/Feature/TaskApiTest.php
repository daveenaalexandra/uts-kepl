<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_return_tokens(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Mahasiswa UTS',
            'email' => 'mahasiswa@example.test',
            'password' => 'password123',
        ])->assertCreated()->assertJsonStructure(['user', 'token']);

        $this->postJson('/api/login', [
            'email' => 'mahasiswa@example.test',
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_guest_cannot_access_tasks(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();
    }

    public function test_authenticated_user_can_crud_tasks(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $response = $this->postJson('/api/tasks', [
            'title' => 'Menyelesaikan UTS',
            'description' => 'Uji CRUD Laravel',
        ], $headers)->assertCreated();
        $id = $response->json('data.id');

        $this->getJson('/api/tasks', $headers)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/tasks/'.$id, $headers)->assertOk();
        $this->putJson('/api/tasks/'.$id, [
            'title' => 'UTS sudah selesai',
            'status' => 'done',
        ], $headers)->assertOk()->assertJsonPath('data.status', 'done');
        $this->deleteJson('/api/tasks/'.$id, [], $headers)->assertOk();
        $this->getJson('/api/tasks/'.$id, $headers)->assertNotFound();
    }

    public function test_user_cannot_view_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = $owner->tasks()->create(['title' => 'Rahasia']);
        $token = $other->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/tasks/'.$task->id, $headers)->assertNotFound();
        $this->deleteJson('/api/tasks/'.$task->id, [], $headers)->assertNotFound();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_invalid_task_cannot_be_created(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $this->postJson('/api/tasks', [
            'title' => '',
        ], ['Authorization' => 'Bearer '.$token])->assertUnprocessable();
    }
}
