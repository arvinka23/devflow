<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function destroy(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        if ($project->picture) {
            Storage::disk('public')->delete($project->picture);
        }

        $project->delete();
        return redirect()->route('projects.index');
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
}
