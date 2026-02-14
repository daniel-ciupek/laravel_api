<?php

namespace Tests\Feature\Api\V2;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_task()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v2/tasks', [
            'name' => 'New Task',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'is_completed',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'name' => 'New Task',
        ]);
    }

    public function test_update_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['name' => 'Old Task Name']);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/v2/tasks/{$task->id}", [
            'name' => 'Updated Task Name',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Task Name']);

        $this->assertDatabaseHas('tasks', [
            'id'   => $task->id,
            'name' => 'Updated Task Name',
        ]);
    }

    public function test_complete_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['is_completed' => false]);

        $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v2/tasks/{$task->id}/complete", [
            'is_completed' => true,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['is_completed' => true]);

        $this->assertDatabaseHas('tasks', [
            'id'           => $task->id,
            'is_completed' => true,
        ]);
    }

    public function test_delete_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v2/tasks/{$task->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_create_task_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v2/tasks', [
            'name' => '',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}