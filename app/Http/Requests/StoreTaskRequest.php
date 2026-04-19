<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'due_date'         => ['required', 'date', 'after_or_equal:today'],
            'assigned_user_id' => ['required', Rule::exists('users', 'id')],
            'priority'         => ['required', new Enum(TaskPriority::class)],
            'status'           => ['sometimes', new Enum(TaskStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('status')) {
            $this->merge(['status' => TaskStatus::Pending->value]);
        }
    }
}
