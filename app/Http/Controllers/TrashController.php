<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $tasks = Task::onlyTrashed()
            ->where('project_id', $project->id)
            ->with('labels')
            ->latest('deleted_at')
            ->get();

        return view('projects.trash', compact('project', 'tasks'));
    }

    public function restore(Request $request, Task $task): RedirectResponse
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $task->restore();

        return redirect()->route('projects.show', $task->project_id)
            ->with('success', 'Task "'.$task->title.'" restored.');
    }

    public function forceDelete(Request $request, Task $task): RedirectResponse
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $task->forceDelete();

        return redirect()->route('projects.trash', $task->project_id)
            ->with('success', 'Task permanently deleted.');
    }
}
