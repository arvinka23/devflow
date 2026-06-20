<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_projects' => $user->projects()->count(),
            'open_tasks' => Task::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['todo', 'in_progress'])->count(),
            'completed_tasks' => Task::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', 'done')->count(),
        ];

        $recentProjects = $user->projects()
            ->withCount('tasks')
            ->latest()
            ->take(5)
            ->get();

        $activityFeed = ActivityLog::where('user_id', $user->id)
            ->with('project')
            ->latest()
            ->take(20)
            ->get();

        // Active sprint: most recently active project + its open tasks
        $activeProjectId = ActivityLog::where('user_id', $user->id)
            ->whereNotNull('project_id')
            ->latest()
            ->value('project_id');

        $activeProject = $activeProjectId
            ? Project::find($activeProjectId)
            : $user->projects()->latest()->first();

        $sprintTasks = $activeProject
            ? $activeProject->tasks()
                ->whereIn('status', ['in_progress', 'todo'])
                ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 ELSE 1 END")
                ->take(8)
                ->get()
            : collect();

        // Weekly time: daily totals for past 7 days
        $weeklyTime = TimeEntry::where('user_id', $user->id)
            ->whereNotNull('ended_at')
            ->where('started_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(started_at) as day, SUM(duration_seconds) as total_seconds')
            ->groupBy('day')
            ->pluck('total_seconds', 'day');

        $weeklyDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $weeklyDays[] = [
                'label' => now()->subDays($i)->format('D'),
                'seconds' => (int) ($weeklyTime[$date] ?? 0),
            ];
        }
        $maxSeconds = max(array_column($weeklyDays, 'seconds') ?: [1]);

        $overdueTasks = Task::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->where('due_date', '<', today())
            ->whereIn('status', ['todo', 'in_progress'])
            ->with('project:id,name,color')
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $layout = $user->dashboard_layout ?? User::getDefaultLayout();

        return view('dashboard', compact('stats', 'recentProjects', 'activityFeed', 'activeProject', 'sprintTasks', 'weeklyDays', 'maxSeconds', 'layout', 'overdueTasks'));
    }
}
