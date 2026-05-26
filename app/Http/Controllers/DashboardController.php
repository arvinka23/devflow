<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_projects' => $user->projects()->count(),
            'open_tasks' => \App\Models\Task::whereHas('project', fn($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['todo', 'in_progress'])->count(),
            'completed_tasks' => \App\Models\Task::whereHas('project', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'done')->count(),
        ];

        $recentProjects = $user->projects()
            ->withCount('tasks')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'recentProjects'));
    }
}
