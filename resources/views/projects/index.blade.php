@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Projects</h1>
            <p class="text-muted-foreground mt-1">Manage and track all your projects in one place.</p>
        </div>
        <button onclick="openNewProjectModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Project
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($projects as $project)
        <div class="bg-card rounded-2xl border border-border p-6 hover:border-primary/50 transition-colors group">
            <div class="flex items-start justify-between mb-4">
                <a href="{{ route('projects.show', $project->id) }}" class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg font-medium" style="background-color: {{ $project->color }}">
                    {{ strtoupper(substr($project->name, 0, 2)) }}
                </a>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $project->status === 'active' ? 'bg-green-500/10 text-green-500' : 'bg-amber-500/10 text-amber-500' }}">
                        {{ ucfirst($project->status) }}
                    </span>
                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <a href="{{ route('projects.show', $project->id) }}">
                <h3 class="font-semibold text-foreground group-hover:text-primary transition-colors">{{ $project->name }}</h3>
                <p class="text-sm text-muted-foreground mt-1 line-clamp-2">{{ $project->description ?: 'No description' }}</p>
            </a>

            <div class="mt-4">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-muted-foreground">Progress</span>
                    <span class="text-foreground font-medium">{{ $project->progress }}%</span>
                </div>
                <div class="h-2 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $project->progress }}%"></div>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4 pt-4 border-t border-border text-sm">
                <div class="flex items-center gap-1.5 text-muted-foreground">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    {{ $project->tasks_count }} tasks
                </div>
                <div class="flex items-center gap-1.5 text-muted-foreground">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $project->updated_at->diffForHumans() }}
                </div>
            </div>
        </div>
        @empty
        <div class="md:col-span-2 xl:col-span-3 py-16 text-center">
            <p class="text-muted-foreground">No projects yet. Create your first one!</p>
        </div>
        @endforelse
    </div>
</div>

<!-- New Project Modal -->
<div id="new-project-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-card rounded-2xl border border-border w-full max-w-md p-6 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-foreground">New Project</h2>
            <button onclick="closeNewProjectModal()" class="p-2 rounded-lg hover:bg-muted">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Project Name</label>
                <input type="text" name="name" required placeholder="e.g. Website Redesign"
                       class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Description <span class="text-muted-foreground font-normal">(optional)</span></label>
                <textarea name="description" rows="3" placeholder="What is this project about?"
                          class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeNewProjectModal()" class="px-4 py-2.5 bg-muted text-foreground rounded-xl text-sm font-medium hover:bg-muted/80 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openNewProjectModal() {
    const modal = document.getElementById('new-project-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeNewProjectModal() {
    const modal = document.getElementById('new-project-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('new-project-modal').addEventListener('click', function(e) {
    if (e.target === this) closeNewProjectModal();
});
</script>
@endpush
@endsection
