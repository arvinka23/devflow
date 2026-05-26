<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->get('q', ''));

        if ($query === '') {
            return redirect()->route('projects.index');
        }

        $projects = $request->user()->projects()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->withCount('tasks')
            ->get()
            ->map(fn($p) => tap($p, fn($p) => $p->progress = $p->progress));

        $tasks = Task::whereHas('project', fn($q) => $q->where('user_id', $request->user()->id))
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('project')
            ->get();

        return view('search', compact('query', 'projects', 'tasks'));
    }
}
