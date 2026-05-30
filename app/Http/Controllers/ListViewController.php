<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ListViewController extends Controller
{
    public function show(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $query = $project->tasks()->with(['labels', 'milestone', 'checklists']);

        if ($request->filled('status') && in_array($request->query('status'), ['todo', 'in_progress', 'done'])) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('priority') && in_array($request->query('priority'), ['low', 'medium', 'high'])) {
            $query->where('priority', $request->query('priority'));
        }

        $sortField = in_array($request->query('sort'), ['title', 'priority', 'due_date', 'status', 'created_at'])
            ? $request->query('sort')
            : 'created_at';
        $sortDir = $request->query('dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $tasks = $query->orderBy($sortField, $sortDir)->get();

        return view('projects.list', compact('project', 'tasks', 'sortField', 'sortDir'));
    }
}
