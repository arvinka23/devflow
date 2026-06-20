@extends('layouts.app')

@section('title', 'Trash — ' . $project->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('projects.show', $project) }}" class="p-2 rounded-lg hover:bg-muted transition-colors">
            <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Trash</h1>
            <p class="text-muted-foreground mt-0.5">{{ $project->name }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-green-500/10 text-green-600 dark:text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($tasks->isEmpty())
    <div class="bg-card rounded-2xl border border-border p-12 text-center">
        <svg class="w-10 h-10 mx-auto mb-3 text-muted-foreground/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        <p class="text-sm font-medium text-foreground">Trash is empty</p>
        <p class="text-xs text-muted-foreground mt-1">Deleted tasks will appear here for recovery.</p>
    </div>
    @else
    <div class="bg-card rounded-2xl border border-border overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <p class="text-sm text-muted-foreground">{{ $tasks->count() }} deleted {{ Str::plural('task', $tasks->count()) }} — restore or permanently delete them below.</p>
        </div>
        <div class="divide-y divide-border">
            @foreach($tasks as $task)
            @php
                $priorityColors = ['high' => 'bg-red-500/10 text-red-500', 'medium' => 'bg-amber-500/10 text-amber-500', 'low' => 'bg-green-500/10 text-green-500'];
            @endphp
            <div class="flex items-center gap-4 px-6 py-4">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-foreground truncate">{{ $task->title }}</p>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="text-xs text-muted-foreground">Deleted {{ $task->deleted_at->diffForHumans() }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$task->priority] }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                        @foreach($task->labels as $label)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white"
                              style="background-color: {{ $label->color }}">
                            {{ $label->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('tasks.restore', $task) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors">
                            Restore
                        </button>
                    </form>
                    <form action="{{ route('tasks.forceDelete', $task) }}" method="POST"
                          onsubmit="return confirm('Permanently delete \'{{ addslashes($task->title) }}\'? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 bg-destructive/10 text-destructive rounded-lg text-xs font-medium hover:bg-destructive/20 transition-colors">
                            Delete forever
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
