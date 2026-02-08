<?php

namespace Tests\Feature\Api\V1;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    // POST – tworzenie nowego zadania
    public function test_create_task()
    {
        $response = $this->postJson('/api/v1/tasks', [
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

    // PUT – aktualizacja istniejącego zadania
    public function test_update_task()
    {
        $task = Task::factory()->create([
            'name' => 'Old Task Name',
        ]);

        $response = $this->putJson("/api/v1/tasks/{$task->id}", [
            'name' => 'Updated Task Name',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Task Name',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => 'Updated Task Name',
        ]);
    }

    // PATCH – oznaczenie zadania jako completed
    public function test_complete_task()
    {
        $task = Task::factory()->create([
            'is_completed' => false,
        ]);

        $response = $this->patchJson("/api/v1/tasks/{$task->id}/complete", [
            'is_completed' => true,
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'is_completed' => true,
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => true,
        ]);
    }

    // DELETE – usuwanie zadania
    public function test_delete_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    // POST – walidacja: puste pole name
public function test_create_task_requires_name()
{
    $response = $this->postJson('/api/v1/tasks', [
        'name' => '',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
}
}
