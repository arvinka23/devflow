<!-- Dashboard Layout Editor -->
<div id="dashboard-editor" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center p-4">
    <div class="bg-card rounded-2xl border border-border w-full max-w-sm shadow-2xl">
        <div class="flex items-center justify-between p-5 border-b border-border">
            <h2 class="text-base font-semibold text-foreground">Customize Dashboard</h2>
            <button onclick="closeDashboardEditor()" class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-5">
            <p class="text-xs text-muted-foreground mb-4">Drag widgets to reorder. Changes take effect on save.</p>
            <ul id="widget-order-list" class="space-y-2">
                @php
                    $widgetLabels = [
                        'stats'           => ['label' => 'Stats Overview', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        'recent_projects' => ['label' => 'Recent Projects', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                        'activity_feed'   => ['label' => 'Activity Feed', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        'kanban_preview'  => ['label' => 'Active Sprint', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
                        'time_tracking'   => ['label' => 'Time Tracking', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ];
                @endphp
                @foreach($layout as $widget)
                @if(isset($widgetLabels[$widget]))
                <li class="flex items-center gap-3 p-3 bg-muted/50 rounded-xl cursor-grab active:cursor-grabbing select-none"
                    draggable="true" data-widget="{{ $widget }}"
                    ondragstart="editorDragStart(event, this)"
                    ondragover="editorDragOver(event, this)"
                    ondrop="editorDrop(event, this)"
                    ondragend="editorDragEnd()">
                    <svg class="w-4 h-4 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $widgetLabels[$widget]['icon'] }}"/>
                    </svg>
                    <span class="text-sm text-foreground flex-1">{{ $widgetLabels[$widget]['label'] }}</span>
                    <svg class="w-4 h-4 text-muted-foreground/50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                </li>
                @endif
                @endforeach
            </ul>
        </div>

        <div class="flex gap-3 p-5 border-t border-border">
            <button onclick="closeDashboardEditor()" class="flex-1 px-4 py-2.5 bg-muted rounded-xl text-sm font-medium text-foreground hover:bg-muted/70 transition-colors">
                Cancel
            </button>
            <button onclick="saveDashboardLayout()" class="flex-1 px-4 py-2.5 bg-primary rounded-xl text-sm font-medium text-primary-foreground hover:opacity-90 transition-opacity">
                Save Layout
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let editorDraggingEl = null;

function openDashboardEditor() {
    document.getElementById('dashboard-editor').classList.remove('hidden');
    document.getElementById('dashboard-editor').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDashboardEditor() {
    document.getElementById('dashboard-editor').classList.add('hidden');
    document.getElementById('dashboard-editor').classList.remove('flex');
    document.body.style.overflow = '';
}

document.getElementById('dashboard-editor').addEventListener('click', function(e) {
    if (e.target === this) closeDashboardEditor();
});

function editorDragStart(e, el) {
    editorDraggingEl = el;
    el.classList.add('opacity-50');
    e.dataTransfer.effectAllowed = 'move';
}

function editorDragEnd() {
    if (editorDraggingEl) editorDraggingEl.classList.remove('opacity-50');
    editorDraggingEl = null;
    document.querySelectorAll('#widget-order-list li').forEach(li => li.classList.remove('ring-2', 'ring-primary/40'));
}

function editorDragOver(e, el) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (editorDraggingEl && editorDraggingEl !== el) {
        el.classList.add('ring-2', 'ring-primary/40');
    }
}

function editorDrop(e, el) {
    e.preventDefault();
    if (!editorDraggingEl || editorDraggingEl === el) return;
    const list = document.getElementById('widget-order-list');
    const items = [...list.children];
    const fromIdx = items.indexOf(editorDraggingEl);
    const toIdx   = items.indexOf(el);

    if (fromIdx < toIdx) {
        list.insertBefore(editorDraggingEl, el.nextSibling);
    } else {
        list.insertBefore(editorDraggingEl, el);
    }

    el.classList.remove('ring-2', 'ring-primary/40');
}

async function saveDashboardLayout() {
    const items  = document.querySelectorAll('#widget-order-list li[data-widget]');
    const layout = [...items].map(li => li.dataset.widget);
    const csrf   = document.querySelector('meta[name="csrf-token"]').content;

    const res = await fetch('/dashboard/layout', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ layout }),
    });

    if (res.ok) {
        location.reload();
    }
}
</script>
@endpush
