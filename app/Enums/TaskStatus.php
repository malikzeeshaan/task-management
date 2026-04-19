<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case NonCompliant = 'non_compliant';

    public function label(): string
    {
        return match($this) {
            TaskStatus::Pending => 'Pending',
            TaskStatus::Completed => 'Completed',
            TaskStatus::NonCompliant => 'Non-Compliant',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            TaskStatus::Pending => 'bg-warning text-dark',
            TaskStatus::Completed => 'bg-success',
            TaskStatus::NonCompliant => 'bg-danger',
        };
    }
}
