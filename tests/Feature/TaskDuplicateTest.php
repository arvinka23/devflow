<?php

use App\Models\Label;
use App\Models\User;

test('owner can duplicate a task', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'Original', 'status' => 'todo', 'priority' => 'medium', 'order' => 1]);

    $this->actingAs($user)
        ->post("/tasks/{$task->id}/duplicate")
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', ['title' => 'Original (copy)', 'project_id' => $project->id]);
});

test('duplicate copies checklists with completed reset to false', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'T', 'status' => 'todo', 'priority' => 'medium', 'order' => 1]);
    $task->checklists()->create(['title' => 'Item A', 'completed' => true]);

    $this->actingAs($user)->post("/tasks/{$task->id}/duplicate");

    $clone = $project->tasks()->where('title', 'T (copy)')->with('checklists')->first();
    expect($clone)->not->toBeNull();
    expect($clone->checklists)->toHaveCount(1);
    expect($clone->checklists->first()->completed)->toBeFalse();
});

test('duplicate copies labels', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'T', 'status' => 'todo', 'priority' => 'medium', 'order' => 1]);
    $label   = Label::create(['user_id' => $user->id, 'name' => 'bug', 'color' => '#ef4444']);
    $task->labels()->attach($label);

    $this->actingAs($user)->post("/tasks/{$task->id}/duplicate");

    $clone = $project->tasks()->where('title', 'T (copy)')->with('labels')->first();
    expect($clone->labels->pluck('id'))->toContain($label->id);
});

test('other user cannot duplicate task', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = $owner->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'T', 'status' => 'todo', 'priority' => 'medium', 'order' => 1]);

    $this->actingAs($other)
        ->post("/tasks/{$task->id}/duplicate")
        ->assertForbidden();
});
