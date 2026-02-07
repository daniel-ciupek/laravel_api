<?php

namespace Tests\Feature\Api\V1;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_get_list_of_tasks(): void 
    {
       $tasks = Task::factory()->count(2)->create();

         $response = $this->getJson('/api/v1/tasks');

         $response->assertOk();

         $response->assertJsonCount(2, 'data');

         $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'is_completed',
                ]
            ]
         ]);

    }

    public function test_user_can_get_single_task(): void 
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'is_completed',
            ]
         ]);

         $response->assertJson([
            'data' => [
                'id' => $task->id,
                'name' => $task->name,
                'is_completed' => $task->is_completed,
            ]
         ]);
    }
}
