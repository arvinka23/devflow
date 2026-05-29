<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Task;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index(Request $request)
    {
        $labels = $request->user()->labels()->orderBy('name')->get();
        return response()->json($labels);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'required|string|max:20',
        ]);

        $label = $request->user()->labels()->create($validated);

        return response()->json($label, 201);
    }

    public function destroy(Request $request, Label $label)
    {
        abort_if($label->user_id !== $request->user()->id, 403);
        $label->delete();
        return response()->json(['ok' => true]);
    }

    public function attach(Request $request, Task $task)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $validated = $request->validate(['label_id' => 'required|integer|exists:labels,id']);

        $label = Label::findOrFail($validated['label_id']);
        abort_if($label->user_id !== $request->user()->id, 403);

        $task->labels()->syncWithoutDetaching([$label->id]);

        return response()->json(['ok' => true]);
    }

    public function detach(Request $request, Task $task, Label $label)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);
        abort_if($label->user_id !== $request->user()->id, 403);

        $task->labels()->detach($label->id);

        return response()->json(['ok' => true]);
    }
}
