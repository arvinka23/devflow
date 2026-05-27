<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskChecklist;
use Illuminate\Http\Request;

class TaskChecklistController extends Controller
{
    public function store(Request $request, Task $task)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $validated = $request->validate(['title' => 'required|string|max:255']);

        $checklist = $task->checklists()->create([
            'title'     => $validated['title'],
            'completed' => false,
        ]);

        return response()->json([
            'ok'   => true,
            'item' => [
                'id'        => $checklist->id,
                'title'     => $checklist->title,
                'completed' => false,
            ],
        ]);
    }

    public function update(Request $request, Task $task, TaskChecklist $checklist)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);
        abort_if($checklist->task_id !== $task->id, 403);

        $checklist->update(['completed' => !$checklist->completed]);

        return response()->json(['ok' => true, 'completed' => $checklist->completed]);
    }

    public function destroy(Request $request, Task $task, TaskChecklist $checklist)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);
        abort_if($checklist->task_id !== $task->id, 403);

        $checklist->delete();

        return response()->json(['ok' => true]);
    }
}
