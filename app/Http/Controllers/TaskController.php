<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Models\TaskActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Task::with('assignedUser');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_user_id')) {
            $query->assignedTo($request->integer('assigned_user_id'));
        }

        if ($request->due_filter === 'due_today') {
            $query->dueToday();
        } elseif ($request->due_filter === 'overdue') {
            $query->overdue();
        }

        match($request->sort) {
            'updated' => $query->orderByDesc('updated_at'),
            'created' => $query->orderByDesc('created_at'),
            default   => $query->orderBy('due_date')->orderByDesc('updated_at'),
        };

        $tasks = $query->paginate(10)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'users'));
    }

    public function create(): View
    {
        $users      = User::orderBy('name')->get();
        $priorities = TaskPriority::cases();

        return view('tasks.create', compact('users', 'priorities'));
    }

    public function show(Task $task): View
    {
        $task->load(['assignedUser', 'activityLogs.user']);

        return view('tasks.show', compact('task'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Task::create($request->validated());

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $oldStatus = $task->status->value;

        $task->status = $request->status;

        if ($request->status === TaskStatus::NonCompliant->value) {
            $task->corrective_action = $request->corrective_action;
        }

        $task->save();

        TaskActivityLog::create([
            'task_id'    => $task->id,
            'user_id'    => 1, // placeholder until auth is added
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'notes'      => $request->corrective_action,
        ]);

        return response()->json([
            'success'         => true,
            'status'          => $task->status->value,
            'new_status'      => $task->status->label(),
            'badge_class'     => $task->status->badgeClass(),
            'old_status'      => TaskStatus::from($oldStatus)->label(),
            'old_badge_class' => TaskStatus::from($oldStatus)->badgeClass(),
        ]);
    }
}
