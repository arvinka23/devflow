@extends('layouts.app')

@section('title', $project->name . ' — Milestones')

@section('content')
<div class="space-y-6" x-data="milestoneManager()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $project) }}" class="p-2 rounded-lg hover:bg-muted transition-colors">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-foreground">{{ $project->name }}</h1>
                <p class="text-muted-foreground mt-1">Milestones</p>
            </div>
        </div>
        <button @click="showForm = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Milestone
        </button>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 bg-green-500/10 text-green-500 rounded-xl text-sm font-medium">{{ session('success') }}</div>
    @endif

    <!-- New milestone form -->
    <div x-show="showForm" x-cloak class="bg-card border border-border rounded-2xl p-6">
        <h3 class="text-base font-semibold text-foreground mb-4">Create Milestone</h3>
        <form method="POST" action="{{ route('milestones.store', $project) }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="text" name="title" placeholder="Milestone title" required
                   class="flex-1 px-3 py-2 rounded-lg bg-muted border border-border text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50">
            <input type="date" name="due_date"
                   class="px-3 py-2 rounded-lg bg-muted border border-border text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/50">
            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:bg-primary/90 transition-colors">Create</button>
            <button type="button" @click="showForm = false" class="px-4 py-2 rounded-lg bg-muted text-muted-foreground text-sm hover:bg-muted/80 transition-colors">Cancel</button>
        </form>
    </div>

    <!-- Milestones list -->
    @forelse($milestones as $milestone)
    <div class="bg-card border border-border rounded-2xl p-6 space-y-3">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="font-semibold text-foreground">{{ $milestone->title }}</h3>
                @if($milestone->due_date)
                @php $overdue = $milestone->due_date->lt(today()) && $milestone->progress < 100; @endphp
                <p class="text-xs mt-0.5 {{ $overdue ? 'text-red-500' : 'text-muted-foreground' }}">
                    Due {{ $milestone->due_date->format('M j, Y') }}{{ $overdue ? ' — overdue' : '' }}
                </p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm font-medium text-foreground">{{ $milestone->progress }}%</span>
                <form method="POST" action="{{ route('milestones.destroy', [$project, $milestone]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-colors"
                            onclick="return confirm('Delete this milestone?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="h-2 bg-muted rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 {{ $milestone->progress === 100 ? 'bg-green-500' : 'bg-primary' }}"
                 style="width: {{ $milestone->progress }}%"></div>
        </div>
        <p class="text-xs text-muted-foreground">{{ $milestone->tasks_count }} task{{ $milestone->tasks_count !== 1 ? 's' : '' }} · {{ $milestone->done_count }} done</p>
    </div>
    @empty
    <div class="bg-card border border-border rounded-2xl py-16 text-center">
        <p class="text-muted-foreground">No milestones yet. Create one to group tasks and track progress.</p>
    </div>
    @endforelse
</div>

@push('scripts')
<script>
function milestoneManager() {
    return { showForm: {{ $errors->any() ? 'true' : 'false' }} };
}
</script>
@endpush
@endsection
