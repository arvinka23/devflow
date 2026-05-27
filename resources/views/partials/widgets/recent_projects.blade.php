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
