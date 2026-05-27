<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|integer',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|in:todo,in_progress,done',
            'priority'    => 'required|in:low,medium,high',
            'due_date'    => 'nullable|date',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        abort_if($project->user_id !== $request->user()->id, 403);

        Task::create($validated);

        return redirect()->route('projects.show', $project->id);
    }

    public function update(Request $request, Task $task)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title'       => 'sometimes|string|min:1|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'status'      => 'sometimes|in:todo,in_progress,done',
            'priority'    => 'sometimes|in:low,medium,high',
            'order'       => 'sometimes|integer',
            'due_date'    => 'sometimes|nullable|date',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);
        $projectId = $task->project_id;
        $task->delete();
        return redirect()->route('projects.show', $projectId);
    }
}
