@extends('layouts.app')

@section('title', 'Search')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-semibold text-foreground">Search results</h1>
        <p class="text-muted-foreground mt-1">
            {{ $projects->count() + $tasks->count() }} results for
            <span class="text-foreground font-medium">"{{ $query }}"</span>
        </p>
    </div>

    @if($projects->isEmpty() && $tasks->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="w-12 h-12 text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-foreground font-medium">No results found</p>
            <p class="text-sm text-muted-foreground mt-1">Try a different search term</p>
        </div>
    @endif

    {{-- Projects --}}
    @if($projects->isNotEmpty())
    <div class="bg-card rounded-2xl border border-border">
        <div class="flex items-center gap-3 p-6 border-b border-border">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <h2 class="text-lg font-semibold text-foreground">Projects</h2>
            <span class="px-2 py-0.5 text-xs font-medium bg-muted rounded-full text-muted-foreground">{{ $projects->count() }}</span>
        </div>
        <div class="divide-y divide-border">
            @foreach($projects as $project)
            <a href="{{ route('projects.show', $project->id) }}" class="flex items-center justify-between p-6 hover:bg-muted/50 transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-medium shrink-0" style="background-color: {{ $project->color }}">
                        {{ strtoupper(substr($project->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-medium text-foreground group-hover:text-primary transition-colors">
                            @php
                                $highlighted = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark class="bg-primary/20 text-primary rounded px-0.5">$1</mark>', e($project->name));
                            @endphp
                            {!! $highlighted !!}
                        </p>
                        @if($project->description)
                        <p class="text-sm text-muted-foreground line-clamp-1">{{ $project->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm text-muted-foreground shrink-0">
                    <span>{{ $project->tasks_count }} tasks</span>
                    <div class="w-16 h-1.5 bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full" style="width: {{ $project->progress }}%"></div>
                    </div>
                    <span>{{ $project->progress }}%</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tasks --}}
    @if($tasks->isNotEmpty())
    <div class="bg-card rounded-2xl border border-border">
        <div class="flex items-center gap-3 p-6 border-b border-border">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h2 class="text-lg font-semibold text-foreground">Tasks</h2>
            <span class="px-2 py-0.5 text-xs font-medium bg-muted rounded-full text-muted-foreground">{{ $tasks->count() }}</span>
        </div>
        <div class="divide-y divide-border">
            @foreach($tasks as $task)
            @php
                $priorityClasses = ['high' => 'bg-red-500/10 text-red-500', 'medium' => 'bg-amber-500/10 text-amber-500', 'low' => 'bg-green-500/10 text-green-500'];
                $statusLabels = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'];
            @endphp
            <a href="{{ route('projects.show', $task->project_id) }}" class="flex items-center justify-between p-6 hover:bg-muted/50 transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-medium shrink-0" style="background-color: {{ $task->project->color }}">
                        {{ strtoupper(substr($task->project->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-medium text-foreground group-hover:text-primary transition-colors">
                            @php
                                $highlighted = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark class="bg-primary/20 text-primary rounded px-0.5">$1</mark>', e($task->title));
                            @endphp
                            {!! $highlighted !!}
                        </p>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ $task->project->name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $priorityClasses[$task->priority] }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-muted text-muted-foreground">
                        {{ $statusLabels[$task->status] }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
