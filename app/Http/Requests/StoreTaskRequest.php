<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = Project::find($this->input('project_id'));

        return $project && $project->user_id === $this->user()->id;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:todo,in_progress,done',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ];
    }
}
