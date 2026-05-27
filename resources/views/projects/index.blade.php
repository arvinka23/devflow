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

    @if(session('success'))
    <div class="px-4 py-3 bg-green-500/10 text-green-500 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($projects as $project)
        <div class="bg-card rounded-2xl border border-border p-6 hover:border-primary/50 transition-colors group">
            <div class="flex items-start justify-between mb-4">
                <!-- Project avatar (picture or color+initials) -->
                <a href="{{ route('projects.show', $project->id) }}"
                   class="w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center text-white text-lg font-medium shrink-0"
                   style="{{ !$project->picture ? 'background-color: ' . $project->color : '' }}">
                    @if($project->picture)
                    <img src="{{ asset('storage/' . $project->picture) }}"
                         alt="{{ $project->name }}"
                         class="w-full h-full object-cover">
                    @else
                    {{ strtoupper(substr($project->name, 0, 2)) }}
                    @endif
                </a>

                <div class="flex items-center gap-1">
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $project->status === 'active' ? 'bg-green-500/10 text-green-500' : 'bg-amber-500/10 text-amber-500' }}">
                        {{ ucfirst($project->status) }}
                    </span>
                    <!-- Edit button -->
                    <button
                            data-project-id="{{ $project->id }}"
                            data-project-name="{{ $project->name }}"
                            data-project-desc="{{ $project->description ?? '' }}"
                            data-project-color="{{ $project->color }}"
                            data-project-has-picture="{{ $project->picture ? '1' : '0' }}"
                            onclick="openEditProjectModal(this)"
                            class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <!-- Delete button -->
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
    <div class="bg-card rounded-2xl border border-border w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">New Project</h2>
            <button onclick="closeNewProjectModal()" class="p-2 rounded-lg hover:bg-muted">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="color" id="new-project-color" value="#6366f1">

            <!-- Picture upload -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Project Image <span class="text-muted-foreground font-normal">(optional)</span></label>
                <div class="flex items-center gap-4">
                    <label for="new-project-pic-input" class="relative cursor-pointer group/pic">
                        <div id="new-project-preview" class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-medium overflow-hidden"
                             style="background-color: #6366f1">
                            <svg class="w-6 h-6 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="absolute inset-0 bg-black/40 rounded-xl flex items-center justify-center opacity-0 group-hover/pic:opacity-100 transition-opacity">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                    </label>
                    <input type="file" id="new-project-pic-input" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden"
                           onchange="previewNewProjectPic(this)">
                    <input type="hidden" name="picture_base64" id="new-project-pic-base64" value="">
                    <div>
                        <p class="text-xs text-muted-foreground">Click to upload</p>
                        <p class="text-xs text-muted-foreground">JPG, PNG, WebP — max 2 MB</p>
                        @error('picture')<p class="text-xs text-destructive mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Color swatches -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Color</label>
                <div class="flex gap-2 flex-wrap" id="new-color-swatches">
                    @foreach(['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899'] as $c)
                    <button type="button" onclick="pickNewColor('{{ $c }}')"
                            class="color-swatch w-7 h-7 rounded-lg ring-2 ring-offset-2 ring-offset-card transition-all"
                            data-color="{{ $c }}"
                            style="background-color: {{ $c }}; {{ $c === '#6366f1' ? 'ring-color: '.$c : 'ring-color: transparent' }}">
                    </button>
                    @endforeach
                </div>
            </div>

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
            <div class="flex justify-end gap-3 pt-1">
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

<!-- Edit Project Modal -->
<div id="edit-project-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-card rounded-2xl border border-border w-full max-w-md shadow-xl">
        <div class="flex items-center justify-between p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Edit Project</h2>
            <button onclick="closeEditProjectModal()" class="p-2 rounded-lg hover:bg-muted">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="edit-project-form" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="color" id="edit-project-color">
            <input type="hidden" name="remove_picture" id="edit-remove-picture" value="0">

            <!-- Picture upload / current image -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Project Image</label>
                <div class="flex items-center gap-4">
                    <label for="edit-project-pic-input" class="relative cursor-pointer group/pic">
                        <div id="edit-project-preview" class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-medium overflow-hidden">
                        </div>
                        <div class="absolute inset-0 bg-black/40 rounded-xl flex items-center justify-center opacity-0 group-hover/pic:opacity-100 transition-opacity">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </label>
                    <input type="file" id="edit-project-pic-input" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden"
                           onchange="previewEditProjectPic(this)">
                    <input type="hidden" name="picture_base64" id="edit-project-pic-base64" value="">
                    <div class="space-y-1">
                        <p class="text-xs text-muted-foreground">Click to change image</p>
                        <button type="button" id="edit-remove-pic-btn" onclick="removeEditProjectPic()"
                                class="text-xs text-destructive hover:underline hidden">
                            Remove image
                        </button>
                    </div>
                </div>
            </div>

            <!-- Color swatches -->
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Color</label>
                <div class="flex gap-2 flex-wrap" id="edit-color-swatches">
                    @foreach(['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899'] as $c)
                    <button type="button" onclick="pickEditColor('{{ $c }}')"
                            class="edit-color-swatch w-7 h-7 rounded-lg ring-2 ring-offset-2 ring-offset-card transition-all"
                            data-color="{{ $c }}"
                            style="background-color: {{ $c }}; ring-color: transparent">
                    </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Project Name</label>
                <input type="text" name="name" id="edit-project-name" required
                       class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Description</label>
                <textarea name="description" id="edit-project-desc" rows="3"
                          class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" onclick="closeEditProjectModal()" class="px-4 py-2.5 bg-muted text-foreground rounded-xl text-sm font-medium hover:bg-muted/80 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ── New Project Modal ──────────────────────────────────────────────────────
function openNewProjectModal() {
    document.getElementById('new-project-modal').classList.remove('hidden');
    document.getElementById('new-project-modal').classList.add('flex');
}
function closeNewProjectModal() {
    document.getElementById('new-project-modal').classList.add('hidden');
    document.getElementById('new-project-modal').classList.remove('flex');
}
document.getElementById('new-project-modal').addEventListener('click', function(e) {
    if (e.target === this) closeNewProjectModal();
});

let currentNewColor = '#6366f1';

function pickNewColor(color) {
    currentNewColor = color;
    document.getElementById('new-project-color').value = color;
    // Update swatch rings
    document.querySelectorAll('#new-color-swatches .color-swatch').forEach(sw => {
        sw.style.ringColor = 'transparent';
        sw.style.outline = sw.dataset.color === color ? `2px solid ${color}` : 'none';
        sw.style.outlineOffset = '2px';
    });
    // Update preview background if no image picked
    const preview = document.getElementById('new-project-preview');
    if (!preview.querySelector('img')) {
        preview.style.backgroundColor = color;
    }
}

function previewNewProjectPic(input) {
    if (!input.files || !input.files[0]) return;
    if (input.files[0].size > 2 * 1024 * 1024) {
        input.value = '';
        document.getElementById('new-project-pic-base64').value = '';
        alert('Image must be under 2 MB.');
        return;
    }
    const reader = new FileReader();
    const preview = document.getElementById('new-project-preview');
    reader.onload = e => {
        preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        preview.style.backgroundColor = '';
        document.getElementById('new-project-pic-base64').value = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

// ── Edit Project Modal ─────────────────────────────────────────────────────
let editProjectHasPicture = false;
let editCurrentColor = '#6366f1';

function openEditProjectModal(btn) {
    const id          = btn.dataset.projectId;
    const name        = btn.dataset.projectName;
    const desc        = btn.dataset.projectDesc;
    const color       = btn.dataset.projectColor;
    const hasPicture  = btn.dataset.projectHasPicture === '1';

    editProjectHasPicture = hasPicture;
    editCurrentColor = color;

    document.getElementById('edit-project-form').action = `/projects/${id}/update`;
    document.getElementById('edit-project-name').value = name;
    document.getElementById('edit-project-desc').value = desc;
    document.getElementById('edit-project-color').value = color;
    document.getElementById('edit-remove-picture').value = '0';

    // Update swatch selection
    document.querySelectorAll('.edit-color-swatch').forEach(sw => {
        sw.style.outline = sw.dataset.color === color ? `2px solid ${sw.dataset.color}` : 'none';
        sw.style.outlineOffset = '2px';
    });

    // Render preview
    renderEditPreview(hasPicture, color, name, id);

    // Remove pic button
    document.getElementById('edit-remove-pic-btn').classList.toggle('hidden', !hasPicture);

    document.getElementById('edit-project-modal').classList.remove('hidden');
    document.getElementById('edit-project-modal').classList.add('flex');
}

function renderEditPreview(hasPicture, color, name, projectId) {
    const preview = document.getElementById('edit-project-preview');
    if (hasPicture) {
        // Fetch current picture URL from the card via the data attribute selector
        const editBtn = document.querySelector(`[data-project-id="${projectId}"]`);
        const img = editBtn ? editBtn.closest('.bg-card').querySelector('a img') : null;
        if (img) {
            preview.innerHTML = `<img src="${img.src}" class="w-full h-full object-cover">`;
            preview.style.backgroundColor = '';
        } else {
            preview.style.backgroundColor = color;
            preview.textContent = name.substring(0, 2).toUpperCase();
        }
    } else {
        preview.innerHTML = '';
        preview.style.backgroundColor = color;
        preview.textContent = name.substring(0, 2).toUpperCase();
    }
}

function closeEditProjectModal() {
    document.getElementById('edit-project-modal').classList.add('hidden');
    document.getElementById('edit-project-modal').classList.remove('flex');
    document.getElementById('edit-project-pic-input').value = '';
    document.getElementById('edit-project-pic-base64').value = '';
}
document.getElementById('edit-project-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditProjectModal();
});

function pickEditColor(color) {
    editCurrentColor = color;
    document.getElementById('edit-project-color').value = color;
    document.querySelectorAll('.edit-color-swatch').forEach(sw => {
        sw.style.outline = sw.dataset.color === color ? `2px solid ${sw.dataset.color}` : 'none';
        sw.style.outlineOffset = '2px';
    });
    const preview = document.getElementById('edit-project-preview');
    if (!preview.querySelector('img')) {
        preview.style.backgroundColor = color;
    }
}

function previewEditProjectPic(input) {
    if (!input.files || !input.files[0]) return;
    if (input.files[0].size > 2 * 1024 * 1024) {
        input.value = '';
        document.getElementById('edit-project-pic-base64').value = '';
        alert('Image must be under 2 MB.');
        return;
    }
    const reader = new FileReader();
    const preview = document.getElementById('edit-project-preview');
    reader.onload = e => {
        preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        preview.style.backgroundColor = '';
        document.getElementById('edit-remove-picture').value = '0';
        document.getElementById('edit-remove-pic-btn').classList.remove('hidden');
        document.getElementById('edit-project-pic-base64').value = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

function removeEditProjectPic() {
    document.getElementById('edit-remove-picture').value = '1';
    document.getElementById('edit-project-pic-input').value = '';
    document.getElementById('edit-project-pic-base64').value = '';
    const preview = document.getElementById('edit-project-preview');
    preview.innerHTML = '';
    preview.style.backgroundColor = editCurrentColor;
    preview.textContent = document.getElementById('edit-project-name').value.substring(0, 2).toUpperCase();
    document.getElementById('edit-remove-pic-btn').classList.add('hidden');
}
</script>
@endpush
@endsection
