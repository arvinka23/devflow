<div class="bg-card rounded-2xl border border-border">
    <div class="flex items-center justify-between p-6 border-b border-border">
        <div>
            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-0.5">Attention needed</p>
            <h2 class="text-lg font-semibold text-foreground leading-tight">Overdue Tasks</h2>
        </div>
        @if($overdueTasks->isNotEmpty())
        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-500">
            {{ $overdueTasks->count() }} overdue
        </span>
        @endif
    </div>

    @if($overdueTasks->isEmpty())
    <div class="p-6 text-center text-muted-foreground">
        <svg class="w-8 h-8 mx-auto mb-2 text-green-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium text-foreground">All caught up!</p>
        <p class="text-xs mt-0.5">No overdue tasks across your projects.</p>
    </div>
    @else
    <div class="divide-y divide-border">
        @foreach($overdueTasks as $task)
        @php
            $daysOverdue = $task->due_date->diffInDays(today());
            $priorityColors = ['high' => 'text-red-500', 'medium' => 'text-amber-500', 'low' => 'text-green-500'];
        @endphp
        <a href="{{ route('projects.show', $task->project_id) }}"
           class="flex items-center gap-3 px-6 py-3.5 hover:bg-muted/30 transition-colors group">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-foreground truncate group-hover:text-primary transition-colors">
                    {{ $task->title }}
                </p>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs text-muted-foreground">{{ $task->project->name }}</span>
                    <span class="text-muted-foreground/40">·</span>
                    <span class="text-xs {{ $priorityColors[$task->priority] }}">{{ ucfirst($task->priority) }}</span>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs font-medium text-red-500">{{ $task->due_date->format('M j') }}</p>
                <p class="text-xs text-muted-foreground">{{ $daysOverdue }}d ago</p>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
