@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header fw-semibold">Create New Task</div>
            <div class="card-body">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}"
                            placeholder="e.g. Inspect fire extinguishers"
                        >
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Optional details about this task..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                            <input
                                type="date"
                                id="due_date"
                                name="due_date"
                                class="form-control @error('due_date') is-invalid @enderror"
                                value="{{ old('due_date') }}"
                                min="{{ today()->toDateString() }}"
                            >
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="assigned_user_id" class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                            <select
                                id="assigned_user_id"
                                name="assigned_user_id"
                                class="form-select @error('assigned_user_id') is-invalid @enderror"
                            >
                                <option value="">— Select a user —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('assigned_user_id') == $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <select
                            id="priority"
                            name="priority"
                            class="form-select @error('priority') is-invalid @enderror"
                        >
                            <option value="">— Select priority —</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority') === $priority->value)>
                                    {{ $priority->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary">Create Task</button>
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
