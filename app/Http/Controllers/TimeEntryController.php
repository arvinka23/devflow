<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Only one running entry at a time per user per task
        $running = TimeEntry::where('user_id', $request->user()->id)
            ->where('task_id', $task->id)
            ->running()
            ->first();

        if ($running) {
            return response()->json(['entry' => $this->entryArray($running)]);
        }

        $entry = TimeEntry::create([
            'task_id'    => $task->id,
            'user_id'    => $request->user()->id,
            'started_at' => now(),
        ]);

        return response()->json(['entry' => $this->entryArray($entry)], 201);
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
