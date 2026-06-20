<?php

use App\Models\User;

test('deleted tasks do not appear on kanban board', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'Gone', 'status' => 'todo', 'priority' => 'low', 'order' => 1]);

    $task->delete();

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertDontSee('Gone');
});

test('trash index shows soft-deleted tasks', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'In Trash', 'status' => 'todo', 'priority' => 'low', 'order' => 1]);
    $task->delete();

    $this->actingAs($user)
        ->get(route('projects.trash', $project))
        ->assertOk()
        ->assertSee('In Trash');
});

test('trash index returns 403 for non-owner', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = $owner->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);

    $this->actingAs($other)
        ->get(route('projects.trash', $project))
        ->assertForbidden();
});

test('owner can restore a soft-deleted task', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'Restore Me', 'status' => 'todo', 'priority' => 'low', 'order' => 1]);
    $task->delete();

    $this->actingAs($user)
        ->post(route('tasks.restore', $task->id))
        ->assertRedirect(route('projects.show', $project));

    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
});

test('owner can force-delete a soft-deleted task', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'Delete Me', 'status' => 'todo', 'priority' => 'low', 'order' => 1]);
    $task->delete();

    $this->actingAs($user)
        ->delete(route('tasks.forceDelete', $task->id))
        ->assertRedirect(route('projects.trash', $project));

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});
