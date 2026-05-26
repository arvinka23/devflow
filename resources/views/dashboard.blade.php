@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-semibold text-foreground">Dashboard</h1>
        <p class="text-muted-foreground mt-1">Welcome back, {{ auth()->user()->name }}. Here's what's happening.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-card rounded-2xl border border-border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Total Projects</p>
                    <p class="text-3xl font-semibold text-foreground mt-1">{{ $stats['total_projects'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-2xl border border-border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Open Tasks</p>
                    <p class="text-3xl font-semibold text-foreground mt-1">{{ $stats['open_tasks'] }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-card rounded-2xl border border-border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Completed</p>
                    <p class="text-3xl font-semibold text-foreground mt-1">{{ $stats['completed_tasks'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="bg-card rounded-2xl border border-border">
        <div class="flex items-center justify-between p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Recent Projects</h2>
            <a href="{{ route('projects.index') }}" class="text-sm text-primary hover:underline">View all</a>
        </div>
        <div class="divide-y divide-border">
            @forelse($recentProjects as $project)
            <a href="{{ route('projects.show', $project->id) }}" class="flex items-center justify-between p-6 hover:bg-muted/50 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-medium" style="background-color: {{ $project->color }}">
                        {{ strtoupper(substr($project->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-medium text-foreground">{{ $project->name }}</p>
                        <p class="text-sm text-muted-foreground">{{ $project->tasks_count }} tasks</p>
                    </div>
                </div>
                <div class="text-sm text-muted-foreground">{{ $project->updated_at->diffForHumans() }}</div>
            </a>
            @empty
            <div class="p-6 text-center text-muted-foreground">
                <p class="text-sm">No projects yet.</p>
                <a href="{{ route('projects.index') }}" class="mt-2 inline-block text-sm text-primary hover:underline">Create your first project</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
