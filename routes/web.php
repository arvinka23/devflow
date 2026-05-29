<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardLayoutController;
use App\Http\Controllers\GitHubController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskChecklistController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('/dashboard/layout', [DashboardLayoutController::class, 'update'])->name('dashboard.layout');
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/update', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{project}/toggle-status', [ProjectController::class, 'toggleStatus'])->name('projects.toggleStatus');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/projects/{project}/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');

    Route::post('/tasks/{task}/checklists', [TaskChecklistController::class, 'store'])->name('checklists.store');
    Route::put('/tasks/{task}/checklists/{checklist}', [TaskChecklistController::class, 'update'])->name('checklists.update');
    Route::delete('/tasks/{task}/checklists/{checklist}', [TaskChecklistController::class, 'destroy'])->name('checklists.destroy');

    Route::get('/tasks/{task}/time', [TimeEntryController::class, 'index'])->name('time.index');
    Route::get('/projects/{project}/time-report', [TimeEntryController::class, 'projectReport'])->name('projects.timeReport');
    Route::post('/tasks/{task}/time/start', [TimeEntryController::class, 'start'])->name('time.start');
    Route::post('/tasks/{task}/time/stop', [TimeEntryController::class, 'stop'])->name('time.stop');
    Route::delete('/time-entries/{timeEntry}', [TimeEntryController::class, 'destroy'])->name('time.destroy');

    Route::post('/ai/suggest-tasks', [AiController::class, 'suggestTasks'])->name('ai.suggest');
    Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');

    Route::get('/github/repos', [GitHubController::class, 'repos'])->name('github.repos');
    Route::get('/projects/{project}/github', [GitHubController::class, 'show'])->name('github.show');
    Route::post('/projects/{project}/github/link', [GitHubController::class, 'link'])->name('github.link');
    Route::post('/projects/{project}/github/unlink', [GitHubController::class, 'unlink'])->name('github.unlink');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('settings.avatar');
    Route::delete('/settings/avatar', [SettingsController::class, 'deleteAvatar'])->name('settings.avatar.delete');
    Route::post('/settings/github-token', [SettingsController::class, 'updateGithubToken'])->name('settings.github-token');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::delete('/account', [SettingsController::class, 'deleteAccount'])->name('account.delete');
});

require __DIR__.'/auth.php';
