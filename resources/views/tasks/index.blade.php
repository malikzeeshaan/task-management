@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Task Dashboard</h4>
    <small class="text-muted">{{ $tasks->total() }} task(s)</small>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('tasks.index') }}" class="card card-body mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label mb-1 small fw-semibold">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                @foreach(\App\Enums\TaskStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1 small fw-semibold">Assigned To</label>
            <select name="assigned_user_id" class="form-select form-select-sm">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(request('assigned_user_id') == $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 small fw-semibold">Due Date</label>
            <select name="due_filter" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="due_today" @selected(request('due_filter') === 'due_today')>Due Today</option>
                <option value="overdue" @selected(request('due_filter') === 'overdue')>Overdue</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1 small fw-semibold">Sort By</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="due_date" @selected(request('sort', 'due_date') === 'due_date')>Due Date</option>
                <option value="updated"  @selected(request('sort') === 'updated')>Recently Updated</option>
                <option value="created"  @selected(request('sort') === 'created')>Newly Created</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
    </div>
</form>

{{-- Task Table --}}
@if($tasks->isEmpty())
    <div class="alert alert-info">No tasks found matching your filters.</div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        @php
                            $isOverdue = !$task->due_date->isFuture() && $task->status === \App\Enums\TaskStatus::Pending;
                        @endphp
                        <tr class="{{ $isOverdue ? 'table-danger' : '' }}" data-task-id="{{ $task->id }}" data-due-date="{{ $task->due_date->toDateString() }}">
                            <td>
                                <div class="fw-semibold">{{ $task->title }}</div>
                                @if($task->description)
                                    <small class="text-muted">{{ Str::limit($task->description, 60) }}</small>
                                @endif
                            </td>
                            <td>{{ $task->assignedUser->name }}</td>
                            <td>
                                <span class="badge {{ $task->priority->badgeClass() }}">
                                    {{ $task->priority->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $task->status->badgeClass() }} status-badge">
                                    {{ $task->status->label() }}
                                </span>
                            </td>
                            <td>
                                {{ $task->due_date->format('d M Y') }}
                                @if($task->status === \App\Enums\TaskStatus::Pending && !$task->due_date->isFuture())
                                    <span class="badge bg-danger ms-1 due-badge">Overdue</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    <button
                                        class="btn btn-sm btn-outline-primary update-status-btn"
                                        data-task-id="{{ $task->id }}"
                                        data-task-title="{{ $task->title }}"
                                        data-current-status="{{ $task->status->value }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#statusModal">
                                        Update Status
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Pagination --}}
@if($tasks->hasPages())
    <div class="mt-4">
        {{ $tasks->links() }}
    </div>
@endif

{{-- Status Update Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Task Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Task: <strong id="modal-task-title"></strong>
                </p>

                <div id="modal-errors" class="alert alert-danger d-none"></div>

                <div class="mb-3">
                    <label for="modal-status" class="form-label fw-semibold">New Status</label>
                    <select id="modal-status" class="form-select">
                        @foreach(\App\Enums\TaskStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="corrective-action-group" class="mb-3 d-none">
                    <label for="modal-corrective-action" class="form-label fw-semibold">
                        Corrective Action <span class="text-danger">*</span>
                    </label>
                    <textarea id="modal-corrective-action" class="form-control" rows="4"
                        placeholder="Describe the corrective action taken (min. 10 characters)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-status-btn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    let currentTaskId = null;
    const today = "{{ today()->toDateString() }}";

    // Populate modal when opened
    $(document).on('click', '.update-status-btn', function () {
        currentTaskId = $(this).data('task-id');
        const currentStatus = $(this).data('current-status');
        const taskTitle = $(this).data('task-title');

        $('#modal-task-title').text(taskTitle);
        $('#modal-status').val(currentStatus);
        $('#modal-errors').addClass('d-none').html('');
        $('#modal-corrective-action').val('');
        toggleCorrectiveAction(currentStatus);
    });

    // Show/hide corrective action field
    $('#modal-status').on('change', function () {
        toggleCorrectiveAction($(this).val());
    });

    function toggleCorrectiveAction(status) {
        if (status === 'non_compliant') {
            $('#corrective-action-group').removeClass('d-none');
        } else {
            $('#corrective-action-group').addClass('d-none');
        }
    }

    // Submit via AJAX
    $('#save-status-btn').on('click', function () {
        const status = $('#modal-status').val();
        const correctiveAction = $('#modal-corrective-action').val();
        const $btn = $(this);

        $('#modal-errors').addClass('d-none').html('');
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: `/tasks/${currentTaskId}/status`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
            },
            data: {
                status: status,
                corrective_action: correctiveAction,
            },
            success: function (response) {
                const $row = $(`tr[data-task-id="${currentTaskId}"]`);
                const dueDate = $row.data('due-date');
                const isOverdue = response.status === 'pending' && dueDate <= today;

                // Update status badge
                $row.find('.status-badge')
                    .attr('class', `badge status-badge ${response.badge_class}`)
                    .text(response.new_status);

                // Update the button's stored status so next modal open pre-selects correctly
                $row.find('.update-status-btn').data('current-status', response.status);

                // Re-apply overdue styling — pending + due today or earlier = overdue
                $row.find('.due-badge').remove();
                $row.removeClass('table-danger');

                if (isOverdue) {
                    $row.addClass('table-danger');
                    $row.find('td').eq(4).append('<span class="badge bg-danger ms-1 due-badge">Overdue</span>');
                }

                $('#statusModal').modal('hide');
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    const messages = Object.values(errors).flat().join('<br>');
                    $('#modal-errors').removeClass('d-none').html(messages);
                } else {
                    $('#modal-errors').removeClass('d-none').text('Something went wrong. Please try again.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Save');
            }
        });
    });
});
</script>
@endsection
