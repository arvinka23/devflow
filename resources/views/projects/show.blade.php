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
            $priorityIcons = [
                'high'   => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>',
                'medium' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14"/></svg>',
                'low'    => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>',
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
                @php
                    $checklistTotal = $task->checklists->count();
                    $checklistDone  = $task->checklists->where('completed', true)->count();
                    $isOverdue = $task->due_date && now()->startOfDay()->gt($task->due_date) && $task->status !== 'done';
                @endphp
                <div class="task-card bg-card rounded-xl border border-border p-4 cursor-pointer hover:border-primary/50 hover:shadow-md transition-all group"
                     draggable="true"
                     data-task-id="{{ $task->id }}"
                     data-title="{{ e($task->title) }}"
                     data-description="{{ e($task->description ?? '') }}"
                     data-priority="{{ $task->priority }}"
                     data-due-date="{{ $task->due_date?->format('Y-m-d') ?? '' }}"
                     onclick="openEditModal(this)">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h4 class="font-medium text-foreground text-sm">{{ $task->title }}</h4>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full shrink-0 {{ $priorityClasses[$task->priority] }}">
                            {!! $priorityIcons[$task->priority] !!}
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                    @if($task->description)
                    <p class="text-xs text-muted-foreground line-clamp-2">{{ $task->description }}</p>
                    @endif

                    @if($task->due_date || $checklistTotal > 0)
                    <div class="flex items-center gap-3 mt-3 flex-wrap">
                        @if($task->due_date)
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $isOverdue ? 'bg-red-500/10 text-red-500' : 'bg-muted text-muted-foreground' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $task->due_date->format('M d') }}
                        </span>
                        @endif
                        @if($checklistTotal > 0)
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $checklistDone === $checklistTotal ? 'bg-green-500/10 text-green-500' : 'bg-muted text-muted-foreground' }}"
                            data-checklist-badge="{{ $task->id }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span data-checklist-count>{{ $checklistDone }}/{{ $checklistTotal }}</span>
                        </span>
                        @endif
                    </div>
                    @endif

                    <div class="flex items-center justify-end mt-3 pt-3 border-t border-border">
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                              onsubmit="return confirm('Delete task?')"
                              onclick="event.stopPropagation()"
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
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Priority</label>
                    <select name="priority" class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Due Date <span class="text-muted-foreground font-normal">(optional)</span></label>
                    <input type="date" name="due_date"
                           class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
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

<!-- Edit Task Modal -->
<div id="edit-task-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-card rounded-2xl border border-border w-full max-w-lg shadow-xl flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between p-6 border-b border-border shrink-0">
            <h2 class="text-lg font-semibold text-foreground">Edit Task</h2>
            <button onclick="closeEditModal()" class="p-2 rounded-lg hover:bg-muted">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 p-6 space-y-5">
            <!-- Task fields -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Title</label>
                <input type="text" id="edit-title"
                       class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Description</label>
                <textarea id="edit-description" rows="3"
                          class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Priority</label>
                    <select id="edit-priority" class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="low">↓ Low</option>
                        <option value="medium">→ Medium</option>
                        <option value="high">↑ High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Due Date</label>
                    <input type="date" id="edit-due-date"
                           class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
            </div>

            <!-- Checklist -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-3">Checklist</label>
                <div id="checklist-items" class="space-y-2 mb-3"></div>
                <div class="flex gap-2">
                    <input type="text" id="new-checklist-item" placeholder="Add an item..."
                           class="flex-1 px-4 py-2 bg-muted border-0 rounded-xl text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    <button onclick="addChecklistItem()" class="px-4 py-2 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors shrink-0">
                        Add
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 p-6 border-t border-border shrink-0">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-muted text-foreground rounded-xl text-sm font-medium hover:bg-muted/80 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="saveEditModal()" class="px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                Save Changes
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let draggedTaskId = null;
let editingTaskId = null;
let currentChecklists = [];   // in-memory state for the open edit modal

// ── Drag & Drop ─────────────────────────────────────────────────────────────

document.querySelectorAll('.task-card').forEach(card => {
    card.addEventListener('dragstart', e => {
        draggedTaskId = card.dataset.taskId;
        card.classList.add('opacity-50', 'rotate-1', 'scale-105');
        e.stopPropagation();
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
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ status: newStatus }),
        });

        if (res.ok) location.reload();
    });
});

// ── Add Task Modal ───────────────────────────────────────────────────────────

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

// ── Edit Task Modal ──────────────────────────────────────────────────────────

function openEditModal(card) {
    editingTaskId = card.dataset.taskId;
    document.getElementById('edit-title').value = card.dataset.title;
    document.getElementById('edit-description').value = card.dataset.description;
    document.getElementById('edit-priority').value = card.dataset.priority;
    document.getElementById('edit-due-date').value = card.dataset.dueDate;

    loadChecklists();

    const modal = document.getElementById('edit-task-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeEditModal() {
    const modal = document.getElementById('edit-task-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    editingTaskId = null;
}
document.getElementById('edit-task-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

async function saveEditModal() {
    const payload = {
        title: document.getElementById('edit-title').value,
        description: document.getElementById('edit-description').value,
        priority: document.getElementById('edit-priority').value,
        due_date: document.getElementById('edit-due-date').value || null,
    };
    const res = await fetch(`/tasks/${editingTaskId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    });
    if (res.ok) location.reload();
}

// ── Checklist ────────────────────────────────────────────────────────────────

function loadChecklists() {
    // Read checklist data from the pre-rendered JSON blob embedded in the page.
    const raw = document.getElementById(`checklist-data-${editingTaskId}`);
    currentChecklists = raw ? JSON.parse(raw.textContent) : [];
    renderChecklists(currentChecklists);
}

function renderChecklists(items) {
    const container = document.getElementById('checklist-items');
    container.innerHTML = items.map(item => `
        <div class="flex items-center gap-3 p-2.5 bg-muted/50 rounded-xl group/item" id="cl-${item.id}">
            <button onclick="toggleChecklist(${item.id})" class="w-5 h-5 rounded flex items-center justify-center shrink-0 border-2 transition-colors
                ${item.completed ? 'bg-primary border-primary text-primary-foreground' : 'border-border hover:border-primary'}">
                ${item.completed ? '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' : ''}
            </button>
            <span class="flex-1 text-sm ${item.completed ? 'line-through text-muted-foreground' : 'text-foreground'}">${escHtml(item.title)}</span>
            <button onclick="deleteChecklist(${item.id})" class="opacity-0 group-hover/item:opacity-100 p-1 rounded hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `).join('');
}

async function addChecklistItem() {
    const input = document.getElementById('new-checklist-item');
    const title = input.value.trim();
    if (!title) return;

    const res = await fetch(`/tasks/${editingTaskId}/checklists`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ title }),
    });
    if (res.ok) {
        const data = await res.json();
        currentChecklists.push(data.item);
        renderChecklists(currentChecklists);
        syncChecklistState();
        input.value = '';
    }
}

document.getElementById('new-checklist-item').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); addChecklistItem(); }
});

async function toggleChecklist(checklistId) {
    const res = await fetch(`/tasks/${editingTaskId}/checklists/${checklistId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({}),
    });
    if (res.ok) {
        const data = await res.json();
        const item = currentChecklists.find(c => c.id === checklistId);
        if (item) item.completed = data.completed;
        renderChecklists(currentChecklists);
        syncChecklistState();
    }
}

async function deleteChecklist(checklistId) {
    const res = await fetch(`/tasks/${editingTaskId}/checklists/${checklistId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    });
    if (res.ok) {
        currentChecklists = currentChecklists.filter(c => c.id !== checklistId);
        renderChecklists(currentChecklists);
        syncChecklistState();
    }
}

// Keeps the in-page JSON blob and card badge in sync after checklist mutations,
// so the modal state survives without a full page reload.
function syncChecklistState() {
    const el = document.getElementById(`checklist-data-${editingTaskId}`);
    if (el) el.textContent = JSON.stringify(currentChecklists);
    updateCardBadge(editingTaskId);
}

function updateCardBadge(taskId) {
    const badge = document.querySelector(`[data-checklist-badge="${taskId}"]`);
    if (!badge) return;   // badge only exists if there were items on page load; reloads on nav
    const total = currentChecklists.length;
    const done  = currentChecklists.filter(c => c.completed).length;
    const countEl = badge.querySelector('[data-checklist-count]');
    if (countEl) countEl.textContent = `${done}/${total}`;
    if (done === total && total > 0) {
        badge.classList.remove('bg-muted', 'text-muted-foreground');
        badge.classList.add('bg-green-500/10', 'text-green-500');
    } else {
        badge.classList.remove('bg-green-500/10', 'text-green-500');
        badge.classList.add('bg-muted', 'text-muted-foreground');
    }
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

{{-- Inline checklist data for each task (read by JS when edit modal opens).
     JSON_HEX_TAG escapes < and > so a title like "</script>" cannot break
     out of this script block. --}}
@foreach(array_merge($tasks['todo']->all(), $tasks['in_progress']->all(), $tasks['done']->all()) as $task)
<script type="application/json" id="checklist-data-{{ $task->id }}">
    {!! json_encode($task->checklists->map(fn($c) => ['id' => $c->id, 'title' => $c->title, 'completed' => $c->completed]), JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endforeach

@endpush
@endsection
