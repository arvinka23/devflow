<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeEntryController extends Controller
{
    public function index(Request $request, Task $task): JsonResponse
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $entries = $task->timeEntries()
            ->where('user_id', $request->user()->id)
            ->latest('started_at')
            ->take(20)
            ->get()
            ->map(fn($e) => [
                'id'               => $e->id,
                'started_at'       => $e->started_at->toIso8601String(),
                'ended_at'         => $e->ended_at?->toIso8601String(),
                'duration_seconds' => $e->duration_seconds,
                'duration_human'   => $e->duration_human,
                'running'          => $e->ended_at === null,
            ]);

        return response()->json(['entries' => $entries]);
    }

    public function start(Request $request, Task $task): JsonResponse
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        // Only one running entry at a time per user per task.
        // Wrapped in a transaction with a pessimistic lock to prevent duplicate
        // running timers under concurrent requests.
        $entry = DB::transaction(function () use ($request, $task) {
            $running = TimeEntry::where('user_id', $request->user()->id)
                ->where('task_id', $task->id)
                ->running()
                ->lockForUpdate()
                ->first();

            if ($running) {
                return $running;
            }

            return TimeEntry::create([
                'task_id'    => $task->id,
                'user_id'    => $request->user()->id,
                'started_at' => now(),
            ]);
        });

        $statusCode = $entry->wasRecentlyCreated ? 201 : 200;
        return response()->json(['entry' => $this->entryArray($entry)], $statusCode);
    }

    public function stop(Request $request, Task $task): JsonResponse
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $entry = TimeEntry::where('user_id', $request->user()->id)
            ->where('task_id', $task->id)
            ->running()
            ->first();

        if (!$entry) {
            return response()->json(['error' => 'No running timer found.'], 404);
        }

        $entry->ended_at         = now();
        $entry->duration_seconds = (int) $entry->started_at->diffInSeconds($entry->ended_at);
        $entry->save();

        return response()->json(['entry' => $this->entryArray($entry)]);
    }

    public function destroy(Request $request, TimeEntry $timeEntry): JsonResponse
    {
        abort_if($timeEntry->task->project->user_id !== $request->user()->id, 403);

        $timeEntry->delete();
        return response()->json(['ok' => true]);
    }

    public function projectReport(Request $request, Project $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $entries = TimeEntry::whereHas('task', fn($q) => $q->where('project_id', $project->id))
            ->where('user_id', $request->user()->id)
            ->with('task:id,title')
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->get();

        $byTask = $entries->groupBy('task_id')->map(fn($group) => [
            'task_title'    => $group->first()->task->title,
            'total_seconds' => $group->sum('duration_seconds'),
            'entry_count'   => $group->count(),
        ]);

        $totalSeconds = $entries->sum('duration_seconds');

        return response()->json([
            'total_seconds' => $totalSeconds,
            'total_human'   => $this->formatSeconds($totalSeconds),
            'by_task'       => $byTask->values(),
        ]);
    }

    private function formatSeconds(int $s): string
    {
        if ($s < 60) return "{$s}s";
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        if ($h > 0) return $h . 'h' . ($m > 0 ? " {$m}m" : '');
        return "{$m}m";
    }

    private function entryArray(TimeEntry $e): array
    {
        return [
            'id'               => $e->id,
            'started_at'       => $e->started_at->toIso8601String(),
            'ended_at'         => $e->ended_at?->toIso8601String(),
            'duration_seconds' => $e->duration_seconds,
            'duration_human'   => $e->duration_human,
            'running'          => $e->ended_at === null,
        ];
    }
}
