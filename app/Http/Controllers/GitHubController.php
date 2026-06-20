<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GitHubController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->github_token) {
            return view('github.index', ['projects' => collect(), 'hasToken' => false]);
        }

        $projects = $user->projects()
            ->orderBy('name')
            ->get()
            ->map(function (Project $project) use ($user) {
                $prCount = null;
                if ($project->github_repo) {
                    try {
                        $data    = $this->fetchGithubData($project, $user->github_token);
                        $prCount = count($data['prs'] ?? []);
                    } catch (Throwable) {
                        // leave as null on API failure
                    }
                }

                return ['project' => $project, 'pr_count' => $prCount];
            });

        return view('github.index', ['projects' => $projects, 'hasToken' => true]);
    }

    public function repos(Request $request): JsonResponse
    {
        $token = $request->user()->github_token;
        if (!$token) {
            return response()->json(['error' => 'No GitHub token configured.'], 422);
        }

        try {
            $data = Cache::remember("github.repos.{$request->user()->id}", 120, function () use ($token) {
                $res = Http::withToken($token)
                    ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
                    ->get('https://api.github.com/user/repos', [
                        'per_page' => 100,
                        'sort'     => 'updated',
                        'type'     => 'all',
                    ]);

                if (!$res->successful()) {
                    throw new \RuntimeException('GitHub API error: ' . $res->status());
                }

                return collect($res->json())->map(fn($r) => [
                    'full_name'   => $r['full_name'],
                    'name'        => $r['name'],
                    'private'     => $r['private'],
                    'description' => $r['description'] ?? null,
                    'updated_at'  => $r['pushed_at'] ?? $r['updated_at'],
                ])->values()->all();
            });

            return response()->json(['repos' => $data]);
        } catch (\Throwable $e) {
            Cache::forget("github.repos.{$request->user()->id}");
            return response()->json(['error' => 'Failed to fetch repositories. Please check your token and try again.'], 503);
        }
    }

    public function link(Request $request, Project $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'github_repo' => ['required', 'string', 'regex:/^[a-zA-Z0-9._\-]+\/[a-zA-Z0-9._\-]+$/'],
        ]);

        $project->update(['github_repo' => $validated['github_repo']]);
        Cache::forget("github.{$project->id}");

        return response()->json(['ok' => true, 'github_repo' => $project->github_repo]);
    }

    public function unlink(Request $request, Project $project): JsonResponse
    {
        abort_if($project->user_id !== $request->user()->id, 403);

        $project->update(['github_repo' => null]);
        Cache::forget("github.{$project->id}");

        return response()->json(['ok' => true]);
    }

    private function fetchGithubData(Project $project, string $token): array
    {
        return Cache::remember("github.{$project->id}", 300, function () use ($project, $token) {
            $headers = ['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'];
            $http    = Http::withToken($token)->withHeaders($headers);

            $prsRes      = $http->get("https://api.github.com/repos/{$project->github_repo}/pulls", ['state' => 'open', 'per_page' => 10]);
            $branchesRes = $http->get("https://api.github.com/repos/{$project->github_repo}/branches", ['per_page' => 10]);

            $prs = $prsRes->successful()
                ? collect($prsRes->json())->map(fn($pr) => [
                    'number' => $pr['number'],
                    'title'  => $pr['title'],
                    'author' => $pr['user']['login'] ?? '',
                    'url'    => $pr['html_url'],
                    'state'  => $pr['state'],
                ])->all()
                : [];

            $branches = $branchesRes->successful()
                ? collect($branchesRes->json())->map(fn($b) => $b['name'])->all()
                : [];

            return compact('prs', 'branches');
        });
    }
}
