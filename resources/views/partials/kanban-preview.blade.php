@if($activeProject)
<div class="bg-card rounded-2xl border border-border">
    <div class="flex items-center justify-between p-6 border-b border-border">
        <div>
            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-0.5">Active Sprint</p>
            <h2 class="text-lg font-semibold text-foreground leading-tight">
                <a href="{{ route('projects.show', $activeProject->id) }}" class="hover:text-primary transition-colors">
                    {{ $activeProject->name }}
                </a>
            </h2>
        </div>
        <a href="{{ route('projects.show', $activeProject->id) }}"
           class="text-sm text-primary hover:underline flex items-center gap-1">
            Full board
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @php
        $inProgress = $sprintTasks->where('status', 'in_progress');
        $todo = $sprintTasks->where('status', 'todo');
        $priorityColors = [
            'high'   => 'text-red-500 bg-red-500/10',
            'medium' => 'text-amber-500 bg-amber-500/10',
            'low'    => 'text-green-500 bg-green-500/10',
        ];
    @endphp

    @if($sprintTasks->isEmpty())
    <div class="p-6 text-center text-muted-foreground">
        <p class="text-sm">No open tasks in this project.</p>
        <a href="{{ route('projects.show', $activeProject->id) }}" class="mt-1 inline-block text-sm text-primary hover:underline">Open kanban board</a>
    </div>
    @else
    <div class="grid grid-cols-2 divide-x divide-border">
        <!-- In Progress -->
        <div class="p-4">
            <p class="text-xs font-semibold text-amber-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                In Progress ({{ $inProgress->count() }})
            </p>
            @forelse($inProgress as $task)
            <a href="{{ route('projects.show', $activeProject->id) }}"
               class="flex items-start gap-2 py-2 group/task">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-foreground leading-snug line-clamp-2 group-hover/task:text-primary transition-colors">{{ $task->title }}</p>
                    @if($task->due_date)
                    <p class="text-xs text-muted-foreground mt-0.5">{{ $task->due_date->format('M j') }}</p>
                    @endif
                </div>
                <span class="text-xs px-1.5 py-0.5 rounded-md flex-shrink-0 {{ $priorityColors[$task->priority] ?? '' }}">
                    {{ ucfirst($task->priority) }}
                </span>
            </a>
            @empty
            <p class="text-xs text-muted-foreground">Nothing in progress</p>
            @endforelse
        </div>

        <!-- To Do -->
        <div class="p-4">
            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground/40 inline-block"></span>
                To Do ({{ $todo->count() }})
            </p>
            @forelse($todo as $task)
            <a href="{{ route('projects.show', $activeProject->id) }}"
               class="flex items-start gap-2 py-2 group/task">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-foreground leading-snug line-clamp-2 group-hover/task:text-primary transition-colors">{{ $task->title }}</p>
                    @if($task->due_date)
                    <p class="text-xs text-muted-foreground mt-0.5">{{ $task->due_date->format('M j') }}</p>
                    @endif
                </div>
                <span class="text-xs px-1.5 py-0.5 rounded-md flex-shrink-0 {{ $priorityColors[$task->priority] ?? '' }}">
                    {{ ucfirst($task->priority) }}
                </span>
            </a>
            @empty
            <p class="text-xs text-muted-foreground">Nothing to do</p>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endif
