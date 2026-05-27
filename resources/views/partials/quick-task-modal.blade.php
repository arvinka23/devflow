<!-- Quick Task Creator Modal -->
<div id="quick-task-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-card rounded-2xl border border-border w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Quick Add Task</h2>
            <button onclick="closeQuickTaskModal()" class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tasks.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="redirect_back" value="1">

            <div>
                <label class="block text-sm font-medium text-foreground mb-1.5">Project</label>
                <select name="project_id" required
                        class="w-full px-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    <option value="">Select a project…</option>
                    @foreach($quickTaskProjects ?? [] as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-foreground mb-1.5">Task title</label>
                <input type="text" name="title" required maxlength="255" placeholder="What needs to be done?"
                       class="w-full px-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1.5">Status</label>
                    <select name="status"
                            class="w-full px-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1.5">Priority</label>
                    <select name="priority"
                            class="w-full px-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-foreground mb-1.5">Due date <span class="text-muted-foreground font-normal">(optional)</span></label>
                <input type="date" name="due_date"
                       class="w-full px-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeQuickTaskModal()"
                        class="flex-1 px-4 py-2.5 bg-muted rounded-xl text-sm font-medium text-foreground hover:bg-muted/70 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-primary rounded-xl text-sm font-medium text-primary-foreground hover:opacity-90 transition-opacity">
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openQuickTaskModal() {
    document.getElementById('quick-task-modal').classList.remove('hidden');
    document.getElementById('quick-task-modal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeQuickTaskModal() {
    document.getElementById('quick-task-modal').classList.add('hidden');
    document.getElementById('quick-task-modal').classList.remove('flex');
    document.body.style.overflow = '';
}

document.getElementById('quick-task-modal').addEventListener('click', function(e) {
    if (e.target === this) closeQuickTaskModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeQuickTaskModal();
});
</script>
