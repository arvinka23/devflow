<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function json(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $filename = 'project-' . \Illuminate\Support\Str::slug($project->name) . '-' . now()->format('Ymd') . '.json';

        $headers = [
            'Content-Type'        => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Stream JSON incrementally using lazy() so tasks are fetched in chunks
        // and never fully materialised into a single PHP array.
        $projectMeta = [
            'name'        => $project->name,
            'description' => $project->description,
            'color'       => $project->color,
            'status'      => $project->status,
            'archived'    => (bool) $project->archived,
            'exported_at' => now()->toIso8601String(),
        ];

        $callback = function () use ($project, $projectMeta) {
            echo '{"project":' . json_encode($projectMeta, JSON_UNESCAPED_UNICODE) . ',"tasks":[';

            $first = true;
            foreach ($project->tasks()->with(['labels', 'milestone', 'checklists', 'comments.user'])->orderBy('status')->orderBy('order')->lazy() as $task) {
                $row = [
                    'title'       => $task->title,
                    'description' => $task->description,
                    'status'      => $task->status,
                    'priority'    => $task->priority,
                    'due_date'    => $task->due_date?->toDateString(),
                    'milestone'   => $task->milestone?->title,
                    'labels'      => $task->labels->pluck('name'),
                    'checklists'  => $task->checklists->map(fn($c) => ['title' => $c->title, 'completed' => $c->completed]),
                    'comments'    => $task->comments->map(fn($c) => ['user' => $c->user->name, 'body' => $c->body, 'at' => $c->created_at->toIso8601String()]),
                ];

                if (!$first) {
                    echo ',';
                }
                echo json_encode($row, JSON_UNESCAPED_UNICODE);
                $first = false;
            }

            echo ']}';
        };

        return response()->stream($callback, 200, $headers);
    }

    public function csv(Request $request, Project $project)
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $filename = 'project-' . \Illuminate\Support\Str::slug($project->name) . '-' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Use lazy() so tasks are fetched in chunks inside the stream callback —
        // avoids loading the entire collection into PHP memory at once.
        $callback = function () use ($project) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Project', 'Task', 'Description', 'Status', 'Priority', 'Due Date', 'Milestone', 'Labels', 'Checklist Total', 'Checklist Done']);

            foreach ($project->tasks()->with(['labels', 'milestone', 'checklists'])->orderBy('status')->orderBy('order')->lazy() as $task) {
                fputcsv($handle, [
                    $this->csvSafe($project->name),
                    $this->csvSafe($task->title),
                    $this->csvSafe($task->description ?? ''),
                    $task->status,
                    $task->priority,
                    $task->due_date?->toDateString() ?? '',
                    $this->csvSafe($task->milestone?->title ?? ''),
                    $this->csvSafe($task->labels->pluck('name')->join(', ')),
                    $task->checklists->count(),
                    $task->checklists->where('completed', true)->count(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Prevent CSV formula injection by prefixing cells that start with
     * =, +, -, or @ with a single quote so spreadsheets treat them as text.
     */
    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }

}
