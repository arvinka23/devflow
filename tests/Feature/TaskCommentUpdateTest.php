<?php

use App\Models\User;

test('owner can update their own comment', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'T', 'status' => 'todo', 'priority' => 'medium', 'order' => 1]);
    $comment = $task->comments()->create(['user_id' => $user->id, 'body' => 'Original body']);

    $this->actingAs($user)
        ->putJson("/tasks/{$task->id}/comments/{$comment->id}", ['body' => 'Updated body'])
        ->assertOk()
        ->assertJson(['ok' => true, 'body' => 'Updated body']);

    $this->assertDatabaseHas('task_comments', ['id' => $comment->id, 'body' => 'Updated body']);
});

test('other user cannot update comment', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = $owner->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'T', 'status' => 'todo', 'priority' => 'medium', 'order' => 1]);
    $comment = $task->comments()->create(['user_id' => $owner->id, 'body' => 'Original']);

    $this->actingAs($other)
        ->putJson("/tasks/{$task->id}/comments/{$comment->id}", ['body' => 'Hacked'])
        ->assertForbidden();
});

test('comment body is required', function () {
    $user    = User::factory()->create();
    $project = $user->projects()->create(['name' => 'P', 'color' => '#6366f1', 'status' => 'active']);
    $task    = $project->tasks()->create(['title' => 'T', 'status' => 'todo', 'priority' => 'medium', 'order' => 1]);
    $comment = $task->comments()->create(['user_id' => $user->id, 'body' => 'Original']);

    $this->actingAs($user)
        ->putJson("/tasks/{$task->id}/comments/{$comment->id}", ['body' => ''])
        ->assertUnprocessable();
});
