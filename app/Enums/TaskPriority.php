<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label(): string
    {
        return match($this) {
            TaskPriority::Low => 'Low',
            TaskPriority::Medium => 'Medium',
            TaskPriority::High => 'High',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            TaskPriority::Low => 'bg-secondary',
            TaskPriority::Medium => 'bg-info text-dark',
            TaskPriority::High => 'bg-danger',
        };
    }
}
