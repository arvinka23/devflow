@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.index') }}" class="p-2 rounded-lg hover:bg-muted transition-colors">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-foreground">{{ $project->name }}</h1>
                @if($project->description)
                <p class="text-muted-foreground mt-1">{{ $project->description }}</p>
                @endif
            </div>
        </div>
        <button onclick="openAddTaskModal('todo')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Task
        </button>
    </div>

    <!-- Kanban Board -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $columns = [
                ['id' => 'todo',        'title' => 'To Do',       'color' => 'bg-slate-500'],
                ['id' => 'in_progress', 'title' => 'In Progress', 'color' => 'bg-amber-500'],
                ['id' => 'done',        'title' => 'Done',        'color' => 'bg-green-500'],
            ];
            $priorityClasses = [
                'high'   => 'bg-red-500/10 text-red-500',
                'medium' => 'bg-amber-500/10 text-amber-500',
                'low'    => 'bg-green-500/10 text-green-500',
            ];
        @endphp

        @foreach($columns as $column)
        <div class="bg-muted/30 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full {{ $column['color'] }}"></div>
                    <h3 class="font-medium text-foreground">{{ $column['title'] }}</h3>
                    <span class="px-2 py-0.5 text-xs font-medium bg-muted rounded-full text-muted-foreground">
                        {{ $tasks[$column['id']]->count() }}
                    </span>
                </div>
            </div>

            <div class="space-y-3 min-h-16" id="column-{{ $column['id'] }}" data-status="{{ $column['id'] }}">
                @foreach($tasks[$column['id']] as $task)
                <div class="task-card bg-card rounded-xl border border-border p-4 cursor-grab hover:border-primary/50 hover:shadow-md transition-all group"
                     draggable="true"
                     data-task-id="{{ $task->id }}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h4 class="font-medium text-foreground text-sm">{{ $task->title }}</h4>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full shrink-0 {{ $priorityClasses[$task->priority] }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                    @if($task->description)
                    <p class="text-xs text-muted-foreground line-clamp-2">{{ $task->description }}</p>
                    @endif
                    <div class="flex items-center justify-end mt-3 pt-3 border-t border-border">
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                              onsubmit="return confirm('Delete task?')"
                              class="opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1 rounded hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach

                <button onclick="openAddTaskModal('{{ $column['id'] }}')"
                        class="w-full p-3 rounded-xl border-2 border-dashed border-border hover:border-primary/50 hover:bg-muted/50 transition-colors text-sm text-muted-foreground flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add task
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Add Task Modal -->
<div id="add-task-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-card rounded-2xl border border-border w-full max-w-md p-6 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-foreground">Add Task</h2>
            <button onclick="closeAddTaskModal()" class="p-2 rounded-lg hover:bg-muted">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <input type="hidden" name="status" id="task-status" value="todo">

            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Task Title</label>
                <input type="text" name="title" required placeholder="e.g. Design homepage layout"
                       class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Description <span class="text-muted-foreground font-normal">(optional)</span></label>
                <textarea name="description" rows="2" placeholder="What needs to be done?"
                          class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Priority</label>
                <select name="priority" class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeAddTaskModal()" class="px-4 py-2.5 bg-muted text-foreground rounded-xl text-sm font-medium hover:bg-muted/80 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let draggedTaskId = null;

// Drag & Drop
document.querySelectorAll('.task-card').forEach(card => {
    card.addEventListener('dragstart', e => {
        draggedTaskId = card.dataset.taskId;
        card.classList.add('opacity-50', 'rotate-1', 'scale-105');
    });
    card.addEventListener('dragend', e => {
        card.classList.remove('opacity-50', 'rotate-1', 'scale-105');
        draggedTaskId = null;
    });
});

document.querySelectorAll('[id^="column-"]').forEach(col => {
    col.addEventListener('dragover', e => {
        e.preventDefault();
        col.classList.add('bg-primary/5', 'ring-1', 'ring-primary/20', 'rounded-xl');
    });
    col.addEventListener('dragleave', () => {
        col.classList.remove('bg-primary/5', 'ring-1', 'ring-primary/20', 'rounded-xl');
    });
    col.addEventListener('drop', async e => {
        e.preventDefault();
        col.classList.remove('bg-primary/5', 'ring-1', 'ring-primary/20', 'rounded-xl');

        if (!draggedTaskId) return;
        const newStatus = col.dataset.status;

        const res = await fetch(`/tasks/${draggedTaskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: newStatus }),
        });

        if (res.ok) location.reload();
    });
});

// Add Task Modal
function openAddTaskModal(status = 'todo') {
    document.getElementById('task-status').value = status;
    const modal = document.getElementById('add-task-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeAddTaskModal() {
    const modal = document.getElementById('add-task-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('add-task-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAddTaskModal();
});
</script>
@endpush
@endsection
