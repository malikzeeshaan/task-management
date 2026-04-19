<?php

namespace App\Http\Controllers\API;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Task::with('assignedUser');

        if ($request->filled('status')) {
            $status = TaskStatus::tryFrom($request->status);
            if ($status) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('assigned_user_id')) {
            $query->assignedTo($request->integer('assigned_user_id'));
        }

        $tasks = $query->orderBy('due_date')->orderByDesc('updated_at')->get();

        return response()->json([
            'data' => $tasks->map(fn ($task) => [
                'id'               => $task->id,
                'title'            => $task->title,
                'description'      => $task->description,
                'due_date'         => $task->due_date->toDateString(),
                'priority'         => $task->priority->value,
                'priority_label'   => $task->priority->label(),
                'status'           => $task->status->value,
                'status_label'     => $task->status->label(),
                'corrective_action' => $task->corrective_action,
                'assigned_to'      => [
                    'id'   => $task->assignedUser->id,
                    'name' => $task->assignedUser->name,
                ],
                'created_at'       => $task->created_at->toDateTimeString(),
                'updated_at'       => $task->updated_at->toDateTimeString(),
            ]),
            'total' => $tasks->count(),
        ]);
    }
}
