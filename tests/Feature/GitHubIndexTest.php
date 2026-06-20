<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('guests are redirected to login', function () {
    $this->get(route('github.index'))->assertRedirect(route('login'));
});

test('github index requires authentication', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('github.index'))
        ->assertOk();
});

test('shows no-token empty state when github token is not set', function () {
    $user = User::factory()->create(['github_token' => null]);

    $this->actingAs($user)
        ->get(route('github.index'))
        ->assertOk()
        ->assertSee('No GitHub token configured')
        ->assertSee(route('settings'));
});

test('shows no-projects empty state when user has no projects', function () {
    $user = User::factory()->create(['github_token' => 'fake-token']);

    $this->actingAs($user)
        ->get(route('github.index'))
        ->assertOk()
        ->assertSee('No projects yet');
});

test('shows all projects including those without a linked repo', function () {
    $user = User::factory()->create(['github_token' => 'fake-token']);
    $user->projects()->create(['name' => 'Unlinked Project', 'color' => '#6366f1', 'status' => 'active']);

    $this->actingAs($user)
        ->get(route('github.index'))
        ->assertOk()
        ->assertSee('Unlinked Project');
});

test('shows linked projects with repo name and open pr count', function () {
    Http::fake([
        'api.github.com/repos/acme/my-repo/pulls*'    => Http::response([
            ['number' => 1, 'title' => 'Fix bug', 'user' => ['login' => 'dev'], 'html_url' => 'https://github.com/acme/my-repo/pull/1', 'state' => 'open'],
            ['number' => 2, 'title' => 'Add feature', 'user' => ['login' => 'dev'], 'html_url' => 'https://github.com/acme/my-repo/pull/2', 'state' => 'open'],
        ], 200),
        'api.github.com/repos/acme/my-repo/branches*' => Http::response([['name' => 'main']], 200),
    ]);

    $user = User::factory()->create(['github_token' => 'fake-token']);
    $user->projects()->create([
        'name'        => 'My Project',
        'color'       => '#6366f1',
        'status'      => 'active',
        'github_repo' => 'acme/my-repo',
    ]);

    $this->actingAs($user)
        ->get(route('github.index'))
        ->assertOk()
        ->assertSee('My Project')
        ->assertSee('acme/my-repo')
        ->assertSee('2');
});

test('shows dash for pr count when github api throws a connection exception', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
    });

    $user = User::factory()->create(['github_token' => 'bad-token']);
    $user->projects()->create([
        'name'        => 'Broken Repo',
        'color'       => '#6366f1',
        'status'      => 'active',
        'github_repo' => 'acme/broken',
    ]);

    $this->actingAs($user)
        ->get(route('github.index'))
        ->assertOk()
        ->assertSee('Broken Repo')
        ->assertSee('—');
});

test('only shows projects belonging to the authenticated user', function () {
    $user  = User::factory()->create(['github_token' => 'fake-token']);
    $other = User::factory()->create(['github_token' => 'other-token']);

    $other->projects()->create([
        'name'   => 'Other User Project',
        'color'  => '#6366f1',
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('github.index'))
        ->assertOk()
        ->assertDontSee('Other User Project');
});

test('per-project github show route no longer exists', function () {
    $user    = User::factory()->create(['github_token' => 'fake-token']);
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);

    $this->actingAs($user)
        ->get("/projects/{$project->id}/github")
        ->assertNotFound();
});
