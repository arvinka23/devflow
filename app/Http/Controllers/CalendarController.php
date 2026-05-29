<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    public function show(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        $month = max(1, min(12, $month));
        $year  = max(2000, min(2100, $year));

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();

        $tasks = $project->tasks()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn($t) => $t->due_date->day);

        $prev = $start->copy()->subMonth();
        $next = $start->copy()->addMonth();

        return view('projects.calendar', compact('project', 'tasks', 'start', 'prev', 'next'));
    }
}
