<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
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

        $task = Task::create($validated);

        ActivityLog::log(
            $request->user(),
            'task.created',
            'created task "' . $task->title . '"',
            $project,
            $task
        );

        if ($request->boolean('redirect_back')) {
            return redirect()->back()->with('success', 'Task "' . $task->title . '" created.');
        }

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

        $oldStatus = $task->status;
        $task->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $statusLabels = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'];
            if ($validated['status'] === 'done') {
                ActivityLog::log(
                    $request->user(),
                    'task.completed',
                    'completed "' . $task->title . '"',
                    $task->project,
                    $task,
                    ['from' => $oldStatus, 'to' => 'done']
                );
            } else {
                ActivityLog::log(
                    $request->user(),
                    'task.status_changed',
                    'moved "' . $task->title . '" to ' . ($statusLabels[$validated['status']] ?? $validated['status']),
                    $task->project,
                    $task,
                    ['from' => $oldStatus, 'to' => $validated['status']]
                );
            }
        } elseif (isset($validated['title']) || isset($validated['description']) || isset($validated['priority'])) {
            ActivityLog::log(
                $request->user(),
                'task.updated',
                'updated task "' . $task->title . '"',
                $task->project,
                $task
            );
        }

        return response()->json($task);
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($validated['order'] as $position => $taskId) {
            Task::where('id', $taskId)
                ->where('project_id', $project->id)
                ->update(['order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, Task $task)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);
        $project   = $task->project;
        $projectId = $task->project_id;

        ActivityLog::log(
            $request->user(),
            'task.deleted',
            'deleted task "' . $task->title . '"',
            $project,
            null
        );

        $task->delete();
        return redirect()->route('projects.show', $projectId);
    }
}
