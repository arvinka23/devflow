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
        <div class="flex items-center gap-2 flex-wrap">
            <!-- View switcher -->
            <div class="hidden sm:flex items-center gap-1 bg-muted rounded-xl p-1">
                <span class="px-3 py-1.5 text-sm rounded-lg bg-card text-foreground font-medium shadow-sm">Kanban</span>
                <a href="{{ route('projects.list', $project) }}" class="px-3 py-1.5 text-sm rounded-lg text-muted-foreground hover:text-foreground transition-colors">List</a>
                <a href="{{ route('projects.calendar', $project) }}" class="px-3 py-1.5 text-sm rounded-lg text-muted-foreground hover:text-foreground transition-colors">Calendar</a>
            </div>
            <!-- Export & Trash links -->
            <div class="flex items-center gap-1">
                <a href="{{ route('export.ical', $project) }}"
                   class="p-2 rounded-lg hover:bg-muted text-muted-foreground transition-colors" title="Export iCal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </a>
                <a href="{{ route('projects.trash', $project) }}"
                   class="p-2 rounded-lg hover:bg-muted text-muted-foreground transition-colors" title="Trash">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </a>
            </div>
            <button onclick="openAddTaskModal('todo')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Task <span class="hidden sm:inline text-xs opacity-70 font-normal ml-0.5">[N]</span>
            </button>
        </div>
    </div>

    <!-- Project stats strip -->
    @php
        $totalTasks = $tasks['todo']->count() + $tasks['in_progress']->count() + $tasks['done']->count();
        $donePct    = $totalTasks > 0 ? round($tasks['done']->count() / $totalTasks * 100, 1) : 0;
        $inPct      = $totalTasks > 0 ? round($tasks['in_progress']->count() / $totalTasks * 100, 1) : 0;
    @endphp
    @if($totalTasks > 0)
    <div class="bg-card rounded-2xl border border-border p-4 flex flex-col sm:flex-row sm:items-center gap-4 stagger-animate" style="animation-delay: 40ms">
        <div class="flex-1">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-muted-foreground font-medium">Overall Progress</span>
                <span class="text-foreground font-semibold">{{ $project->progress }}%</span>
            </div>
            <div class="h-2.5 bg-muted rounded-full overflow-hidden flex">
                @if($tasks['done']->count() > 0)
                <div class="h-full bg-green-500 transition-all duration-1000 ease-out" style="width: 0%" data-pw="{{ $donePct }}"></div>
                @endif
                @if($tasks['in_progress']->count() > 0)
                <div class="h-full bg-amber-500 transition-all duration-1000 ease-out" style="width: 0%" data-pw="{{ $inPct }}"></div>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-5 sm:border-l sm:border-border sm:pl-5 shrink-0">
            <div class="text-center">
                <div class="text-xl font-semibold text-foreground">{{ $tasks['todo']->count() }}</div>
                <div class="text-xs text-muted-foreground flex items-center justify-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>To Do
                </div>
            </div>
            <div class="text-center">
                <div class="text-xl font-semibold text-amber-500">{{ $tasks['in_progress']->count() }}</div>
                <div class="text-xs text-muted-foreground flex items-center justify-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>In Progress
                </div>
            </div>
            <div class="text-center">
                <div class="text-xl font-semibold text-green-500">{{ $tasks['done']->count() }}</div>
                <div class="text-xs text-muted-foreground flex items-center justify-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>Done
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab Bar -->
    <div x-data="{ tab: 'kanban' }">
        <div class="flex items-center gap-1 border-b border-border mb-6">
            <button @click="tab = 'kanban'"
                    :class="tab === 'kanban' ? 'border-b-2 border-primary text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    class="px-4 py-2.5 text-sm font-medium transition-colors -mb-px flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Kanban
            </button>
            <button @click="tab = 'time'"
                    :class="tab === 'time' ? 'border-b-2 border-primary text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    class="px-4 py-2.5 text-sm font-medium transition-colors -mb-px flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Time
            </button>
        </div>

        <!-- Kanban Tab -->
        <div x-show="tab === 'kanban'">

    <!-- Filter Bar -->
    <div x-data="{
            search: '',
            priority: '',
            overdueOnly: false,
            today: '{{ now()->toDateString() }}',
            applyFilter() {
                document.querySelectorAll('.task-card').forEach(card => {
                    const ms = !this.search   || card.dataset.title.toLowerCase().includes(this.search.toLowerCase());
                    const mp = !this.priority || card.dataset.priority === this.priority;
                    const mo = !this.overdueOnly || (card.dataset.dueDate && card.dataset.dueDate < this.today && card.dataset.dueDate !== '');
                    card.style.display = (ms && mp && mo) ? '' : 'none';
                });
            }
         }"
         class="flex flex-wrap items-center gap-2 mb-4">
        <div class="relative flex-1 min-w-[160px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input x-model="search" @input="applyFilter()" type="text" placeholder="Search tasks…"
                   class="w-full pl-9 pr-4 py-2 bg-muted border-0 rounded-xl text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
        </div>
        <select x-model="priority" @change="applyFilter()"
                class="px-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            <option value="">All priorities</option>
            <option value="high">↑ High</option>
            <option value="medium">→ Medium</option>
            <option value="low">↓ Low</option>
        </select>
        <label class="flex items-center gap-2 px-3 py-2 bg-muted rounded-xl text-sm text-foreground cursor-pointer select-none">
            <input type="checkbox" x-model="overdueOnly" @change="applyFilter()" class="rounded">
            Overdue only
        </label>
        <button x-show="search || priority || overdueOnly"
                @click="search=''; priority=''; overdueOnly=false; applyFilter()"
                class="px-3 py-2 bg-muted rounded-xl text-sm text-muted-foreground hover:text-foreground transition-colors">
            Clear
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
        @php
            $colBadgeClass = match($column['id']) {
                'in_progress' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                'done'        => 'bg-green-500/10 text-green-600 dark:text-green-400',
                default       => 'bg-muted text-muted-foreground',
            };
        @endphp
        <div class="bg-muted/30 rounded-2xl p-4 stagger-animate" style="animation-delay: {{ 80 + $loop->index * 80 }}ms">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full {{ $column['color'] }}"></div>
                    <h3 class="font-medium text-foreground">{{ $column['title'] }}</h3>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $colBadgeClass }}">
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
                <div class="task-card bg-card rounded-xl border border-border p-4 cursor-pointer hover:border-primary/50 hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 group"
                     draggable="true"
                     data-task-id="{{ $task->id }}"
                     data-title="{{ e($task->title) }}"
                     data-description="{{ e($task->description ?? '') }}"
                     data-priority="{{ $task->priority }}"
                     data-due-date="{{ $task->due_date?->format('Y-m-d') ?? '' }}"
                     data-labels="{{ e($task->labels->pluck('name')->join(',')) }}"
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
        </div>{{-- end kanban tab --}}

        <!-- Time Report Tab -->
        <div x-show="tab === 'time'" x-cloak x-data="projectTimeReport({{ $project->id }})">
            <div class="bg-card rounded-2xl border border-border p-6">
                <h3 class="text-base font-semibold text-foreground mb-4">Time Report</h3>

                <!-- Loading state -->
                <template x-if="loading">
                    <div class="text-muted-foreground text-sm py-6 text-center">Loading…</div>
                </template>

                <!-- Error state -->
                <template x-if="!loading && error">
                    <div class="text-destructive text-sm py-4" x-text="error"></div>
                </template>

                <!-- Data loaded -->
                <template x-if="!loading && data">
                    <div>
                        <!-- Total summary -->
                        <div class="flex items-center gap-6 p-4 bg-muted/40 rounded-xl mb-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-foreground" x-text="data.total_human || '0s'"></div>
                                <div class="text-xs text-muted-foreground mt-0.5">Total logged</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-foreground" x-text="data.by_task ? data.by_task.length : 0"></div>
                                <div class="text-xs text-muted-foreground mt-0.5">Tasks tracked</div>
                            </div>
                        </div>

                        <!-- Per-task breakdown -->
                        <template x-if="data.by_task && data.by_task.length > 0">
                            <div>
                                <h4 class="text-sm font-medium text-foreground mb-3">Breakdown by Task</h4>
                                <div class="space-y-2">
                                    <template x-for="row in data.by_task" :key="row.task_title">
                                        <div class="flex items-center justify-between p-3 bg-muted/30 rounded-xl">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-foreground truncate" x-text="row.task_title"></div>
                                                <div class="text-xs text-muted-foreground mt-0.5" x-text="row.entry_count + (row.entry_count === 1 ? ' session' : ' sessions')"></div>
                                            </div>
                                            <div class="font-mono text-sm font-semibold text-foreground ml-4 shrink-0" x-text="formatSeconds(row.total_seconds)"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="!data.by_task || data.by_task.length === 0">
                            <p class="text-sm text-muted-foreground text-center py-4">No time logged for this project yet. Start a timer on any task to track time.</p>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>{{-- end x-data tab wrapper --}}
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

            <!-- Time Tracking (persistent Alpine component driven by store) -->
            <div x-data="timerWidget(null, null)" x-init="init()">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-foreground">Time Tracking</label>
                    <span class="text-xs text-muted-foreground" x-show="!running && entries.length === 0">No time logged</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-muted/50 rounded-xl mb-3">
                    <div class="font-mono text-lg font-semibold text-foreground min-w-[80px]" x-text="display">00:00</div>
                    <div class="flex-1"></div>
                    <button x-show="!running" @click="start()"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500/10 text-green-500 rounded-lg text-xs font-medium hover:bg-green-500/20 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Start
                    </button>
                    <button x-show="running" @click="stop()"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-red-500/10 text-red-500 rounded-lg text-xs font-medium hover:bg-red-500/20 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h4v12H6zm8 0h4v12h-4z"/></svg>
                        Stop
                    </button>
                </div>
                <template x-if="entries.length > 0">
                    <div class="space-y-1.5">
                        <template x-for="entry in entries.filter(e => !e.running)" :key="entry.id">
                            <div class="flex items-center justify-between text-xs text-muted-foreground">
                                <span x-text="new Date(entry.started_at).toLocaleDateString(undefined,{month:'short',day:'numeric'})"></span>
                                <span class="font-mono font-medium text-foreground" x-text="entry.duration_human"></span>
                                <button @click="deleteEntry(entry.id)" class="p-0.5 rounded hover:text-destructive transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Comments -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-3">Comments</label>
                <div id="comment-items" class="space-y-2 mb-3"></div>
                <div class="flex gap-2">
                    <input type="text" id="new-comment-body" placeholder="Add a comment…"
                           class="flex-1 px-4 py-2 bg-muted border-0 rounded-xl text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    <button onclick="addComment()" class="px-4 py-2 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors shrink-0">
                        Post
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 p-6 border-t border-border shrink-0">
            <button type="button" onclick="duplicateTask()" class="px-4 py-2.5 bg-muted text-foreground rounded-xl text-sm font-medium hover:bg-muted/80 transition-colors flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Duplicate
            </button>
            <div class="flex-1"></div>
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
// Animate segmented progress bars from 0 to their actual widths on load.
document.addEventListener('DOMContentLoaded', () => {
    requestAnimationFrame(() => setTimeout(() => {
        document.querySelectorAll('[data-pw]').forEach(el => {
            el.style.width = el.dataset.pw + '%';
        });
    }, 80));
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let draggedTaskId   = null;
let dragSourceCol   = null;
let editingTaskId   = null;
let currentChecklists = [];

// ── Drag & Drop ─────────────────────────────────────────────────────────────

// Returns the task-card element that the dragged card should be inserted before,
// based on the cursor's Y position. Returns null to insert at the end.
function getDragAfterElement(container, y) {
    const cards = [...container.querySelectorAll('.task-card:not(.opacity-50)')];
    return cards.reduce((closest, child) => {
        const box    = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element ?? null;
}

document.querySelectorAll('.task-card').forEach(card => {
    card.addEventListener('dragstart', e => {
        draggedTaskId = card.dataset.taskId;
        dragSourceCol = card.closest('[id^="column-"]');
        card.classList.add('opacity-50', 'rotate-1', 'scale-105');
        e.stopPropagation();
    });
    card.addEventListener('dragend', () => {
        card.classList.remove('opacity-50', 'rotate-1', 'scale-105');
        draggedTaskId = null;
        dragSourceCol = null;
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
        const newStatus    = col.dataset.status;
        const isSameColumn = dragSourceCol === col;
        const draggedCard  = document.querySelector(`[data-task-id="${draggedTaskId}"]`);

        if (isSameColumn) {
            // ── Same-column reorder: move card in DOM, persist order, no reload ──
            const afterEl = getDragAfterElement(col, e.clientY);
            const addBtn  = col.querySelector('button[onclick^="openAddTaskModal"]');
            col.insertBefore(draggedCard, afterEl ?? addBtn);

            const ids = [...col.querySelectorAll('.task-card')].map(c => parseInt(c.dataset.taskId));
            await fetch(`/projects/{{ $project->id }}/tasks/reorder`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body:    JSON.stringify({ order: ids }),
            });
        } else {
            // Move card first for instant visual feedback, revert on failure
            const addBtn = col.querySelector('button[onclick^="openAddTaskModal"]');
            col.insertBefore(draggedCard, addBtn);
            const srcBadge = dragSourceCol.closest('.bg-muted\\/30')?.querySelector('span.px-2');
            const dstBadge = col.closest('.bg-muted\\/30')?.querySelector('span.px-2');
            if (srcBadge) srcBadge.textContent = dragSourceCol.querySelectorAll('.task-card').length;
            if (dstBadge) dstBadge.textContent = col.querySelectorAll('.task-card').length;

            const res = await fetch(`/tasks/${draggedTaskId}`, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body:    JSON.stringify({ status: newStatus }),
            });
            if (!res.ok) location.reload();
        }
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
    loadComments();
    Alpine.store('editModal').taskId = parseInt(editingTaskId);

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

const PRIORITY_CLASSES = { high: 'bg-red-500/10 text-red-500', medium: 'bg-amber-500/10 text-amber-500', low: 'bg-green-500/10 text-green-500' };
const PRIORITY_ICONS   = {
    high:   '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>',
    medium: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14"/></svg>',
    low:    '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>',
};

async function saveEditModal() {
    const titleVal = document.getElementById('edit-title').value.trim();
    if (!titleVal) return;
    const payload = {
        title:       titleVal,
        description: document.getElementById('edit-description').value,
        priority:    document.getElementById('edit-priority').value,
        due_date:    document.getElementById('edit-due-date').value || null,
    };
    const res = await fetch(`/tasks/${editingTaskId}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body:    JSON.stringify(payload),
    });
    if (!res.ok) return;
    const task = await res.json();

    const card = document.querySelector(`[data-task-id="${editingTaskId}"]`);
    if (card) {
        card.dataset.title       = task.title;
        card.dataset.description = task.description ?? '';
        card.dataset.priority    = task.priority;
        card.dataset.dueDate     = task.due_date ?? '';
        const titleEl = card.querySelector('h4');
        if (titleEl) titleEl.textContent = task.title;
        const badge = card.querySelector('.inline-flex.items-center.gap-1.px-2');
        if (badge) {
            badge.className = `inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full shrink-0 ${PRIORITY_CLASSES[task.priority]}`;
            badge.innerHTML = PRIORITY_ICONS[task.priority] + ' ' + task.priority.charAt(0).toUpperCase() + task.priority.slice(1);
        }
    }
    closeEditModal();
}

async function duplicateTask() {
    const res = await fetch(`/tasks/${editingTaskId}/duplicate`, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    });
    if (res.ok) location.reload();
}

// ── Checklist ────────────────────────────────────────────────────────────────

function loadChecklists() {
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

function syncChecklistState() {
    const el = document.getElementById(`checklist-data-${editingTaskId}`);
    if (el) el.textContent = JSON.stringify(currentChecklists);
    updateCardBadge(editingTaskId);
}

function updateCardBadge(taskId) {
    const badge = document.querySelector(`[data-checklist-badge="${taskId}"]`);
    if (!badge) return;
    const total = currentChecklists.length;
    const done  = currentChecklists.filter(c => c.completed).length;
    badge.classList.toggle('hidden', total === 0);
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
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Keyboard Shortcuts ───────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
    if (e.key === 'n' || e.key === 'N') { e.preventDefault(); openAddTaskModal('todo'); }
    if (e.key === 'Escape') { closeAddTaskModal(); closeEditModal(); closeShortcuts(); }
    if (e.key === '?') toggleShortcuts();
});
function toggleShortcuts() {
    const el = document.getElementById('shortcuts-overlay');
    el.classList.toggle('hidden'); el.classList.toggle('flex');
}
function closeShortcuts() {
    const el = document.getElementById('shortcuts-overlay');
    el.classList.add('hidden'); el.classList.remove('flex');
}

// ── Comments ─────────────────────────────────────────────────────────────────
let currentComments = [];

function loadComments() {
    const raw = document.getElementById(`comment-data-${editingTaskId}`);
    currentComments = raw ? JSON.parse(raw.textContent) : [];
    renderComments(currentComments);
}
function renderComments(items) {
    const currentUserId = {{ auth()->id() }};
    document.getElementById('comment-items').innerHTML = items.length === 0
        ? '<p class="text-xs text-muted-foreground">No comments yet.</p>'
        : items.map(c => `
            <div class="group/c flex gap-2.5 p-2.5 bg-muted/40 rounded-xl" id="cmt-${c.id}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-medium text-foreground">${escHtml(c.user_name)}</span>
                        <span class="text-xs text-muted-foreground">${escHtml(c.created_at_human)}</span>
                    </div>
                    <p class="text-sm text-foreground whitespace-pre-wrap comment-body-${c.id}">${escHtml(c.body)}</p>
                    <div class="hidden mt-2 comment-edit-${c.id}">
                        <textarea class="w-full px-3 py-2 bg-muted border-0 rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none" rows="2">${escHtml(c.body)}</textarea>
                        <div class="flex gap-2 mt-1.5">
                            <button onclick="saveComment(${c.id})" class="px-3 py-1 bg-primary text-primary-foreground rounded-lg text-xs font-medium hover:bg-primary/90">Save</button>
                            <button onclick="cancelEditComment(${c.id})" class="px-3 py-1 bg-muted text-foreground rounded-lg text-xs font-medium hover:bg-muted/70">Cancel</button>
                        </div>
                    </div>
                </div>
                ${c.user_id === currentUserId ? `
                <div class="flex gap-1 opacity-0 group-hover/c:opacity-100 transition-opacity shrink-0">
                    <button onclick="startEditComment(${c.id})" class="p-1 rounded hover:bg-muted text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="deleteComment(${c.id})" class="p-1 rounded hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>` : ''}
            </div>
        `).join('');
}
async function addComment() {
    const input = document.getElementById('new-comment-body');
    const body  = input.value.trim();
    if (!body) return;
    const res = await fetch(`/tasks/${editingTaskId}/comments`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body:    JSON.stringify({ body }),
    });
    if (res.ok) {
        const data = await res.json();
        currentComments.unshift({ id: data.id, body: data.body, user_id: data.user.id, user_name: data.user.name, created_at_human: 'just now' });
        renderComments(currentComments);
        input.value = '';
    }
}
document.getElementById('new-comment-body').addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); addComment(); }
});
function startEditComment(id) {
    document.querySelector(`.comment-body-${id}`)?.classList.add('hidden');
    document.querySelector(`.comment-edit-${id}`)?.classList.remove('hidden');
}
function cancelEditComment(id) {
    document.querySelector(`.comment-body-${id}`)?.classList.remove('hidden');
    document.querySelector(`.comment-edit-${id}`)?.classList.add('hidden');
}
async function saveComment(id) {
    const textarea = document.querySelector(`#cmt-${id} textarea`);
    const body     = textarea?.value.trim();
    if (!body) return;
    const res = await fetch(`/tasks/${editingTaskId}/comments/${id}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body:    JSON.stringify({ body }),
    });
    if (res.ok) {
        const c = currentComments.find(c => c.id === id);
        if (c) c.body = body;
        renderComments(currentComments);
    }
}
async function deleteComment(id) {
    const res = await fetch(`/tasks/${editingTaskId}/comments/${id}`, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    });
    if (res.ok) {
        currentComments = currentComments.filter(c => c.id !== id);
        renderComments(currentComments);
    }
}
</script>

<!-- Keyboard Shortcuts Overlay -->
<div id="shortcuts-overlay" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" onclick="closeShortcuts()">
    <div class="bg-card rounded-2xl border border-border shadow-xl p-6 w-full max-w-xs" onclick="event.stopPropagation()">
        <h2 class="text-base font-semibold text-foreground mb-4">Keyboard Shortcuts</h2>
        <div class="space-y-2 text-sm">
            @foreach([['N', 'New task'], ['Esc', 'Close modal'], ['?', 'Toggle shortcuts']] as [$key, $desc])
            <div class="flex items-center justify-between">
                <span class="text-muted-foreground">{{ $desc }}</span>
                <kbd class="px-2 py-0.5 bg-muted rounded text-xs font-mono text-foreground border border-border">{{ $key }}</kbd>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Embedded task data blobs: checklist + comment state (avoids page reload after mutations). --}}
@foreach(array_merge($tasks['todo']->all(), $tasks['in_progress']->all(), $tasks['done']->all()) as $task)
<script type="application/json" id="checklist-data-{{ $task->id }}">
    {!! json_encode($task->checklists->map(fn($c) => ['id' => $c->id, 'title' => $c->title, 'completed' => $c->completed]), JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
<script type="application/json" id="comment-data-{{ $task->id }}">
    {!! json_encode($task->comments->map(fn($c) => ['id' => $c->id, 'body' => $c->body, 'user_id' => $c->user_id, 'user_name' => $c->user->name, 'created_at_human' => $c->created_at->diffForHumans()]), JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endforeach

@endpush

@include('partials.ai-assistant', ['project' => $project])

@endsection
