<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function index(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $milestones = $project->milestones()
            ->withCount(['tasks', 'tasks as done_count' => fn($q) => $q->where('status', 'done')])
            ->orderBy('due_date')
            ->get();

        return view('projects.milestones', compact('project', 'milestones'));
    }

    public function store(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $project->milestones()->create($validated);

        return redirect()->route('milestones.index', $project)
            ->with('success', 'Milestone created.');
    }

    public function update(Request $request, Project $project, Milestone $milestone)
    {
        abort_if($project->user_id !== $request->user()->id, 403);
        abort_if($milestone->project_id !== $project->id, 403);

        $validated = $request->validate([
            'title'    => 'sometimes|string|min:1|max:255',
            'due_date' => 'sometimes|nullable|date',
        ]);

        $milestone->update($validated);

        return redirect()->route('milestones.index', $project)
            ->with('success', 'Milestone updated.');
    }

    public function destroy(Request $request, Project $project, Milestone $milestone)
    {
        abort_if($project->user_id !== $request->user()->id, 403);
        abort_if($milestone->project_id !== $project->id, 403);

        $milestone->delete();

        return redirect()->route('milestones.index', $project)
            ->with('success', 'Milestone deleted.');
    }
}
