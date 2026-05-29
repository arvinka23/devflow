<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);

        $validated = $request->validate(['body' => 'required|string|max:2000']);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $validated['body'],
        ]);

        $comment->load('user');

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, Task $task, TaskComment $comment)
    {
        abort_if($task->project->user_id !== $request->user()->id, 403);
        abort_if($comment->user_id !== $request->user()->id, 403);

        $comment->delete();

        return response()->json(['ok' => true]);
    }
}
