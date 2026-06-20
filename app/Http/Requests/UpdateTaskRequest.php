<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('task')->project->user_id;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|min:1|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'status' => 'sometimes|in:todo,in_progress,done',
            'priority' => 'sometimes|in:low,medium,high',
            'order' => 'sometimes|integer',
            'due_date' => 'sometimes|nullable|date',
        ];
    }
}
