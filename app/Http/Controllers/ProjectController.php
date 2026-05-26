<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
            'picture'     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];

        $data = [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => !empty($validated['color']) ? $validated['color'] : $colors[array_rand($colors)],
        ];

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('projects', 'public');
        }

        $request->user()->projects()->create($data);

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

    public function update(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        Log::info('ProjectController::update called', [
            'project_id' => $project->id,
            'hasFile'    => $request->hasFile('picture'),
            'allInput'   => array_keys($request->all()),
        ]);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'color'          => 'nullable|string|max:20',
            'picture'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'remove_picture' => 'nullable|boolean',
        ]);

        $data = [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => !empty($validated['color']) ? $validated['color'] : $project->color,
            'picture'     => $project->picture, // always preserve unless explicitly changed below
        ];

        if ($request->hasFile('picture')) {
            if ($project->picture) {
                Storage::disk('public')->delete($project->picture);
            }
            $data['picture'] = $request->file('picture')->store('projects', 'public');
        } elseif ($request->boolean('remove_picture')) {
            if ($project->picture) {
                Storage::disk('public')->delete($project->picture);
            }
            $data['picture'] = null;
        }

        $project->update($data);

        return redirect()->route('projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        if ($project->picture) {
            Storage::disk('public')->delete($project->picture);
        }

        $project->delete();
        return redirect()->route('projects.index');
    }
}
