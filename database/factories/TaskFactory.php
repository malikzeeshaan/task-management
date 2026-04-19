<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'            => fake()->sentence(4),
            'description'      => fake()->paragraph(),
            'due_date'         => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'assigned_user_id' => User::factory(),
            'priority'         => fake()->randomElement(TaskPriority::cases())->value,
            'status'           => TaskStatus::Pending->value,
            'corrective_action' => null,
        ];
    }
}
