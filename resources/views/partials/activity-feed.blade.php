<div class="bg-card rounded-2xl border border-border flex flex-col h-full">
    <div class="flex items-center justify-between p-6 border-b border-border">
        <h2 class="text-lg font-semibold text-foreground">Activity</h2>
    </div>

    @php
        $icons = [
            'task.created'        => ['bg' => 'bg-primary/10',     'text' => 'text-primary',    'path' => 'M12 4v16m8-8H4'],
            'task.completed'      => ['bg' => 'bg-green-500/10',   'text' => 'text-green-500',  'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'task.status_changed' => ['bg' => 'bg-amber-500/10',   'text' => 'text-amber-500',  'path' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
            'task.updated'        => ['bg' => 'bg-blue-500/10',    'text' => 'text-blue-500',   'path' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
            'task.deleted'        => ['bg' => 'bg-red-500/10',     'text' => 'text-red-500',    'path' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
            'project.created'     => ['bg' => 'bg-primary/10',     'text' => 'text-primary',    'path' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
            'project.deleted'     => ['bg' => 'bg-red-500/10',     'text' => 'text-red-500',    'path' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
        ];
        $defaultIcon = ['bg' => 'bg-muted', 'text' => 'text-muted-foreground', 'path' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
    @endphp

    <div class="divide-y divide-border overflow-y-auto flex-1">
        @forelse($activityFeed as $entry)
        @php $icon = $icons[$entry->event] ?? $defaultIcon; @endphp
        <div class="flex items-start gap-3 px-6 py-4">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 {{ $icon['bg'] }}">
                <svg class="w-4 h-4 {{ $icon['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon['path'] }}"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-foreground leading-snug">
                    <span class="font-medium">{{ auth()->user()->name }}</span>
                    {{ $entry->subject }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                    @if($entry->project)
                    <span class="text-xs px-1.5 py-0.5 rounded-md bg-muted text-muted-foreground">{{ $entry->project->name }}</span>
                    @endif
                    <span class="text-xs text-muted-foreground">{{ $entry->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="p-6 text-center text-muted-foreground">
            <p class="text-sm">No activity yet.</p>
            <p class="text-xs mt-1">Create or update tasks to see activity here.</p>
        </div>
        @endforelse
    </div>
</div>
