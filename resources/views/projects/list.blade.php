@extends('layouts.app')

@section('title', $project->name . ' — List')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $project) }}" class="p-2 rounded-lg hover:bg-muted transition-colors">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-foreground">{{ $project->name }}</h1>
                <p class="text-muted-foreground mt-1">{{ $tasks->count() }} task{{ $tasks->count() !== 1 ? 's' : '' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.show', $project) }}" class="px-3 py-1.5 text-sm rounded-lg text-muted-foreground hover:bg-muted transition-colors">Kanban</a>
            <span class="px-3 py-1.5 text-sm rounded-lg bg-primary text-primary-foreground">List</span>
            <a href="{{ route('projects.calendar', $project) }}" class="px-3 py-1.5 text-sm rounded-lg text-muted-foreground hover:bg-muted transition-colors">Calendar</a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('projects.list', $project) }}" class="flex flex-wrap gap-3 bg-card border border-border rounded-2xl p-4">
        <select name="status" class="px-3 py-2 rounded-lg bg-muted text-sm text-foreground border border-border focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">All statuses</option>
            @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'] as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="priority" class="px-3 py-2 rounded-lg bg-muted text-sm text-foreground border border-border focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">All priorities</option>
            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $val => $label)
            <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="sort" class="px-3 py-2 rounded-lg bg-muted text-sm text-foreground border border-border focus:outline-none focus:ring-2 focus:ring-primary/50">
            @foreach(['created_at' => 'Date created', 'title' => 'Title', 'priority' => 'Priority', 'due_date' => 'Due date', 'status' => 'Status'] as $val => $label)
            <option value="{{ $val }}" {{ $sortField === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="dir" class="px-3 py-2 rounded-lg bg-muted text-sm text-foreground border border-border focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="asc" {{ $sortDir === 'asc' ? 'selected' : '' }}>Asc</option>
            <option value="desc" {{ $sortDir === 'desc' ? 'selected' : '' }}>Desc</option>
        </select>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-colors">Apply</button>
        @if(request()->hasAny(['status','priority','sort','dir']))
        <a href="{{ route('projects.list', $project) }}" class="px-4 py-2 rounded-lg bg-muted text-muted-foreground text-sm hover:bg-muted/80 transition-colors">Reset</a>
        @endif
    </form>

    <!-- Table -->
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        @if($tasks->isEmpty())
        <div class="py-16 text-center text-muted-foreground">No tasks match your filters.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-border bg-muted/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Title</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Priority</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Due Date</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Milestone</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Labels</th>
                        <th class="text-left px-4 py-3 font-medium text-muted-foreground">Checklist</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($tasks as $task)
                    @php
                        $statusColors = ['todo' => 'bg-slate-500/10 text-slate-500', 'in_progress' => 'bg-amber-500/10 text-amber-500', 'done' => 'bg-green-500/10 text-green-600'];
                        $priorityColors = ['low' => 'text-muted-foreground', 'medium' => 'text-amber-500', 'high' => 'text-red-500'];
                        $statusLabels = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'];
                        $doneChecks = $task->checklists->where('completed', true)->count();
                        $totalChecks = $task->checklists->count();
                    @endphp
                    <tr class="hover:bg-muted/40 transition-colors">
                        <td class="px-4 py-3 font-medium text-foreground max-w-xs">
                            <span class="truncate block">{{ $task->title }}</span>
                            @if($task->description)
                            <span class="text-xs text-muted-foreground truncate block">{{ Str::limit($task->description, 60) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$task->status] ?? '' }}">
                                {{ $statusLabels[$task->status] ?? $task->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium capitalize {{ $priorityColors[$task->priority] ?? '' }}">{{ $task->priority }}</span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            @if($task->due_date)
                                @php $overdue = !$task->due_date->isFuture() && $task->status !== 'done'; @endphp
                                <span class="{{ $overdue ? 'text-red-500 font-medium' : '' }}">{{ $task->due_date->format('M j, Y') }}</span>
                            @else
                                <span class="text-muted-foreground/50">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-muted-foreground text-xs">{{ $task->milestone?->title ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($task->labels as $label)
                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium text-white" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground text-xs">
                            @if($totalChecks > 0)
                            {{ $doneChecks }}/{{ $totalChecks }}
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Export -->
    <div class="flex gap-3">
        <a href="{{ route('export.json', $project) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-border text-sm text-muted-foreground hover:bg-muted transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export JSON
        </a>
        <a href="{{ route('export.csv', $project) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-border text-sm text-muted-foreground hover:bg-muted transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export CSV
        </a>
    </div>
</div>
@endsection
