<?php

use App\Models\User;

test('ical export requires authentication', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);

    $this->get(route('export.ical', $project))->assertRedirect(route('login'));
});

test('ical export returns 403 for non-owner', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = $owner->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);

    $this->actingAs($other)
        ->get(route('export.ical', $project))
        ->assertForbidden();
});

test('ical export returns text/calendar content type', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $project->tasks()->create([
        'title'    => 'Task with due date',
        'status'   => 'todo',
        'priority' => 'high',
        'order'    => 1,
        'due_date' => now()->addDays(7)->toDateString(),
    ]);

    $response = $this->actingAs($user)->get(route('export.ical', $project));

    $response->assertOk();
    $this->assertStringContainsString('text/calendar', $response->headers->get('Content-Type'));
});

test('ical export contains VCALENDAR and VEVENT for tasks with due dates', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'My Project', 'color' => '#6366f1', 'status' => 'active']);
    $project->tasks()->create([
        'title'    => 'Deploy to production',
        'status'   => 'todo',
        'priority' => 'high',
        'order'    => 1,
        'due_date' => '2026-07-15',
    ]);
    $project->tasks()->create([
        'title'    => 'No due date task',
        'status'   => 'todo',
        'priority' => 'low',
        'order'    => 2,
    ]);

    $response = $this->actingAs($user)->get(route('export.ical', $project));
    $body     = $response->streamedContent();

    expect($body)->toContain('BEGIN:VCALENDAR');
    expect($body)->toContain('BEGIN:VEVENT');
    expect($body)->toContain('Deploy to production');
    expect($body)->toContain('20260715');
    expect($body)->not->toContain('No due date task');
    expect($body)->toContain('END:VCALENDAR');
});
