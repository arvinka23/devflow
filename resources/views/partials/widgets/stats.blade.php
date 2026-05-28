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
