<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()->projects()
            ->withCount('tasks')
            ->latest()
            ->get()
            ->map(function ($project) {
                $project->progress = $project->progress;
                return $project;
            });

        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];

        $request->user()->projects()->create([
            ...$validated,
            'color' => $colors[array_rand($colors)],
        ]);

        return redirect()->route('projects.index');
    }

    public function show(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $tasks = [
            'todo'        => $project->tasks()->where('status', 'todo')->orderBy('order')->with('checklists')->get(),
            'in_progress' => $project->tasks()->where('status', 'in_progress')->orderBy('order')->with('checklists')->get(),
            'done'        => $project->tasks()->where('status', 'done')->orderBy('order')->with('checklists')->get(),
        ];

        return view('projects.show', compact('project', 'tasks'));
    }

    public function destroy(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);
        $project->delete();
        return redirect()->route('projects.index');
    }
}
