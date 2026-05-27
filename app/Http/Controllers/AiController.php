<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class AiController extends Controller
{
    public function suggestTasks(Request $request): JsonResponse
    {
        if (!config('services.anthropic.key')) {
            return response()->json(['error' => 'AI is not configured.'], 503);
        }

        $request->validate(['project_id' => 'required|integer']);

        $project = Project::findOrFail($request->integer('project_id'));
        abort_if($project->user_id !== $request->user()->id, 403);

        if (!$this->checkRateLimit($request->user()->id)) {
            return response()->json(['error' => 'Too many requests. Please wait a moment.'], 429);
        }

        try {
            $existingTasks = $project->tasks()->select('title')->get()->toArray();
            $suggestions   = (new AiAssistantService())->suggestTasks($project, $existingTasks);
            return response()->json(['suggestions' => $suggestions]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'AI is unavailable right now.'], 503);
        }
    }

    public function chat(Request $request): JsonResponse
    {
        if (!config('services.anthropic.key')) {
            return response()->json(['error' => 'AI is not configured.'], 503);
        }

        $validated = $request->validate([
            'message'          => 'required|string|max:2000',
            'history'          => 'nullable|array|max:20',
            'history.*.role'   => 'required|string|in:user,assistant',
            'history.*.content'=> 'required|string|max:2000',
            'project_id'       => 'nullable|integer',
        ]);

        if (!$this->checkRateLimit($request->user()->id)) {
            return response()->json(['error' => 'Too many requests. Please wait a moment.'], 429);
        }

        $project = null;
        if (!empty($validated['project_id'])) {
            $project = Project::find($validated['project_id']);
            if ($project && $project->user_id !== $request->user()->id) {
                $project = null;
            }
        }

        try {
            $reply = (new AiAssistantService())->chat(
                $validated['message'],
                $validated['history'] ?? [],
                $project
            );
            return response()->json(['message' => $reply]);
        } catch (Throwable $e) {
            return response()->json(['error' => 'AI is unavailable right now.'], 503);
        }
    }

    private function checkRateLimit(int $userId): bool
    {
        $key = "ai:{$userId}";
        return RateLimiter::attempt($key, 20, fn() => true, 60);
    }
}
