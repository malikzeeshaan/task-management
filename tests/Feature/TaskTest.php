<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_a_task_can_be_created_with_valid_data(): void
    {
        $user = User::factory()->create();

        $payload = [
            'title'            => 'Inspect fire extinguishers',
            'description'      => 'Check all extinguishers on site.',
            'due_date'         => now()->addDays(5)->toDateString(),
            'assigned_user_id' => $user->id,
            'priority'         => TaskPriority::High->value,
        ];

        $response = $this->post(route('tasks.store'), $payload);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'title'            => 'Inspect fire extinguishers',
            'assigned_user_id' => $user->id,
            'priority'         => TaskPriority::High->value,
            'status'           => TaskStatus::Pending->value,
        ]);
    }

    public function test_updating_status_to_non_compliant_without_corrective_action_fails_validation(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'assigned_user_id' => $user->id,
            'status'           => TaskStatus::Pending,
        ]);

        $response = $this->patchJson(
            route('tasks.updateStatus', $task),
            ['status' => TaskStatus::NonCompliant->value]
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['corrective_action']);
    }
}
