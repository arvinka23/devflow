<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');

        $query = $request->user()->projects()
            ->where('archived', $showArchived);

        if (!$showArchived && $request->filled('status') && in_array($request->input('status'), ['active', 'on-hold'])) {
            $query->where('status', $request->input('status'));
        }

        $projects = $query
            ->withCount([
                'tasks',
                'tasks as todo_count'        => fn($q) => $q->where('status', 'todo'),
                'tasks as in_progress_count' => fn($q) => $q->where('status', 'in_progress'),
                'tasks as done_count'        => fn($q) => $q->where('status', 'done'),
            ])
            ->addSelect([
                'projects.*',
                'next_due_date' => Task::select('due_date')
                    ->whereColumn('project_id', 'projects.id')
                    ->whereNotNull('due_date')
                    ->where('due_date', '>=', now()->toDateString())
                    ->where('status', '!=', 'done')
                    ->orderBy('due_date')
                    ->limit(1),
                'last_activity_at' => ActivityLog::select('created_at')
                    ->whereColumn('project_id', 'projects.id')
                    ->latest()
                    ->limit(1),
            ])
            ->latest()
            ->get()
            ->map(function ($project) {
                $project->progress = $project->progress;
                return $project;
            });

        $archivedCount = $request->user()->projects()->where('archived', true)->count();

        return view('projects.index', compact('projects', 'showArchived', 'archivedCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
        ]);

        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];

        $data = [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => !empty($validated['color']) ? $validated['color'] : $colors[array_rand($colors)],
        ];

        if ($request->filled('picture_base64')) {
            $path = $this->saveBase64Picture($request->input('picture_base64'));
            if ($path === null) {
                return back()->withInput($request->except('picture_base64'))
                    ->withErrors(['picture' => 'The image could not be processed. Use a JPG, PNG, WebP, or GIF under 2 MB.']);
            }
            $data['picture'] = $path;
        }

        $project = $request->user()->projects()->create($data);

        ActivityLog::log(
            $request->user(),
            'project.created',
            'created project "' . $project->name . '"',
            $project
        );

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

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'color'          => 'nullable|string|max:20',
            'remove_picture' => 'nullable|boolean',
        ]);

        $data = [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => !empty($validated['color']) ? $validated['color'] : $project->color,
            'picture'     => $project->picture,
        ];

        if ($request->filled('picture_base64')) {
            $path = $this->saveBase64Picture($request->input('picture_base64'));
            if ($path === null) {
                return back()->withInput($request->except('picture_base64'))
                    ->withErrors(['picture' => 'The image could not be processed. Use a JPG, PNG, WebP, or GIF under 2 MB.']);
            }
            // Store new file first, then delete old one so a write failure never
            // leaves a dangling DB reference.
            if ($project->picture) {
                Storage::disk('public')->delete($project->picture);
            }
            $data['picture'] = $path;
        } elseif ($request->boolean('remove_picture')) {
            if ($project->picture) {
                Storage::disk('public')->delete($project->picture);
            }
            $data['picture'] = null;
        }

        $project->update($data);

        return redirect()->route('projects.index')->with('success', 'Project updated.');
    }

    private function saveBase64Picture(string $base64): ?string
    {
        if (!preg_match('/^data:image\/[a-z]+;base64,(.+)$/s', $base64, $matches)) {
            return null;
        }

        $imageData = base64_decode($matches[1], true);

        if ($imageData === false || strlen($imageData) > 2 * 1024 * 1024) {
            return null;
        }

        // Verify actual content — never trust the client-supplied MIME prefix.
        $mime       = (new \finfo(FILEINFO_MIME_TYPE))->buffer($imageData);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

        if (!array_key_exists($mime, $extensions)) {
            return null;
        }

        $path    = 'projects/' . Str::uuid() . '.' . $extensions[$mime];
        $written = Storage::disk('public')->put($path, $imageData);

        return $written ? $path : null;
    }

    public function toggleStatus(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);
        $project->update(['status' => $project->status === 'active' ? 'on-hold' : 'active']);
        return back()->with('success', 'Project status updated.');
    }

    public function archive(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);
        $project->update(['archived' => true]);

        ActivityLog::log($request->user(), 'project.archived', 'archived project "' . $project->name . '"', $project);

        return redirect()->route('projects.index')->with('success', 'Project archived.');
    }

    public function unarchive(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);
        $project->update(['archived' => false]);

        ActivityLog::log($request->user(), 'project.unarchived', 'unarchived project "' . $project->name . '"', $project);

        return redirect()->route('projects.index', ['archived' => 1])->with('success', 'Project restored.');
    }

    public function destroy(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        ActivityLog::log(
            $request->user(),
            'project.deleted',
            'deleted project "' . $project->name . '"'
        );

        if ($project->picture) {
            Storage::disk('public')->delete($project->picture);
        }

        $project->delete();
        return redirect()->route('projects.index');
    }
}
