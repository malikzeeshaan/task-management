@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Back to Dashboard</a>
    <button
        class="btn btn-sm btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#statusModal">
        Update Status
    </button>
</div>

<div class="row g-4">

    {{-- Task Details --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Task Details</span>
                <div class="d-flex gap-2">
                    <span class="badge {{ $task->priority->badgeClass() }}">{{ $task->priority->label() }}</span>
                    <span id="status-badge" class="badge {{ $task->status->badgeClass() }}">{{ $task->status->label() }}</span>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title mb-3">{{ $task->title }}</h5>

                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Description</dt>
                    <dd class="col-sm-8">{{ $task->description ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Assigned To</dt>
                    <dd class="col-sm-8">{{ $task->assignedUser->name }}</dd>

                    <dt class="col-sm-4 text-muted">Created By</dt>
                    <dd class="col-sm-8">{{ $task->createdBy->name }}</dd>

                    <dt class="col-sm-4 text-muted">Due Date</dt>
                    <dd class="col-sm-8">
                        {{ $task->due_date->format('d M Y') }}
                        @if($task->status === \App\Enums\TaskStatus::Pending && !$task->due_date->isFuture())
                            <span id="overdue-badge" class="badge bg-danger ms-1">Overdue</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted" id="corrective-label" @if(!$task->corrective_action) style="display:none" @endif>Corrective Action</dt>
                    <dd class="col-sm-8" id="corrective-value" @if(!$task->corrective_action) style="display:none" @endif>
                        <div class="alert alert-warning mb-0 py-2 px-3 small">
                            {{ $task->corrective_action }}
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Activity Log --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span>Activity Log</span>
                <span id="log-count" class="badge bg-secondary">{{ $task->activityLogs->count() }}</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="activity-log-list">
                    @forelse($task->activityLogs->sortByDesc('created_at') as $log)
                        @php
                            $oldStatus = \App\Enums\TaskStatus::from($log->old_status);
                            $newStatus = \App\Enums\TaskStatus::from($log->new_status);
                        @endphp
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <span class="badge {{ $oldStatus->badgeClass() }}">{{ $oldStatus->label() }}</span>
                                    <span class="text-muted mx-1">&rarr;</span>
                                    <span class="badge {{ $newStatus->badgeClass() }}">{{ $newStatus->label() }}</span>
                                </div>
                                <small class="text-muted text-nowrap ms-2">{{ $log->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            <small class="text-muted">by {{ $log->user->name }}</small>
                            @if($log->notes)
                                <div class="mt-1 small text-secondary fst-italic">"{{ $log->notes }}"</div>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted" id="empty-log">No status changes recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- Status Update Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Task Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-errors" class="alert alert-danger d-none"></div>

                <div class="mb-3">
                    <label for="modal-status" class="form-label fw-semibold">New Status</label>
                    <select id="modal-status" class="form-select">
                        @foreach(\App\Enums\TaskStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($task->status === $status)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="corrective-action-group" class="mb-3 {{ $task->status === \App\Enums\TaskStatus::NonCompliant ? '' : 'd-none' }}">
                    <label for="modal-corrective-action" class="form-label fw-semibold">
                        Corrective Action <span class="text-danger">*</span>
                    </label>
                    <textarea id="modal-corrective-action" class="form-control" rows="4"
                        placeholder="Describe the corrective action taken (min. 10 characters)...">{{ $task->corrective_action }}</textarea>
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
    const taskId = {{ $task->id }};
    const today  = "{{ today()->toDateString() }}";
    const dueDate = "{{ $task->due_date->toDateString() }}";

    // Show/hide corrective action field
    $('#modal-status').on('change', function () {
        if ($(this).val() === 'non_compliant') {
            $('#corrective-action-group').removeClass('d-none');
        } else {
            $('#corrective-action-group').addClass('d-none');
        }
    });

    // Reset modal errors when opened
    $('#statusModal').on('show.bs.modal', function () {
        $('#modal-errors').addClass('d-none').html('');
    });

    $('#save-status-btn').on('click', function () {
        const status = $('#modal-status').val();
        const correctiveAction = $('#modal-corrective-action').val();
        const $btn = $(this);

        $('#modal-errors').addClass('d-none').html('');
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: `/tasks/${taskId}/status`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
            },
            data: { status, corrective_action: correctiveAction },
            success: function (response) {
                // Update status badge in card header
                $('#status-badge')
                    .attr('class', `badge ${response.badge_class}`)
                    .text(response.new_status);

                // Update overdue badge
                const isOverdue = response.status === 'pending' && dueDate <= today;
                $('#overdue-badge').remove();
                if (isOverdue) {
                    $('dd.col-sm-8').eq(2).append('<span id="overdue-badge" class="badge bg-danger ms-1">Overdue</span>');
                }

                // Update corrective action block
                if (response.status === 'non_compliant' && correctiveAction) {
                    $('#corrective-value .alert').text(correctiveAction);
                    $('#corrective-label, #corrective-value').show();
                } else if (response.status !== 'non_compliant') {
                    $('#corrective-label, #corrective-value').hide();
                }

                // Prepend new entry to activity log
                $('#empty-log').remove();
                const now = new Date().toLocaleString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                const logHtml = `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <span class="badge ${response.old_badge_class}">${response.old_status}</span>
                                <span class="text-muted mx-1">&rarr;</span>
                                <span class="badge ${response.badge_class}">${response.new_status}</span>
                            </div>
                            <small class="text-muted text-nowrap ms-2">${now}</small>
                        </div>
                        <small class="text-muted">by {{ $task->assignedUser->name }}</small>
                        ${correctiveAction && response.status === 'non_compliant' ? `<div class="mt-1 small text-secondary fst-italic">"${correctiveAction}"</div>` : ''}
                    </li>`;
                $('#activity-log-list').prepend(logHtml);

                // Update log count badge
                const count = parseInt($('#log-count').text()) + 1;
                $('#log-count').text(count);

                $('#statusModal').modal('hide');
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    $('#modal-errors').removeClass('d-none').html(Object.values(errors).flat().join('<br>'));
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
