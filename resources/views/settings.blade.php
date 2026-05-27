@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-3xl space-y-8">
    <div>
        <h1 class="text-2xl font-semibold text-foreground">Settings</h1>
        <p class="text-muted-foreground mt-1">Manage your account settings and preferences.</p>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 bg-green-500/10 text-green-500 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="px-4 py-3 bg-destructive/10 text-destructive rounded-xl text-sm font-medium">
        {{ $errors->first() }}
    </div>
    @endif

    <!-- Profile Picture Section -->
    <div class="bg-card rounded-2xl border border-border">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Profile Photo</h2>
            <p class="text-sm text-muted-foreground mt-1">Upload a photo. JPG, PNG, WebP or GIF, max 4 MB.</p>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-6">
                {{-- Avatar preview --}}
                <div class="shrink-0" id="avatar-preview">
                    @if(auth()->user()->profile_picture)
                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                         alt="Profile photo"
                         class="w-20 h-20 rounded-full object-cover ring-2 ring-border">
                    @else
                    <div class="w-20 h-20 rounded-full bg-primary/20 flex items-center justify-center text-2xl font-medium text-primary ring-2 ring-border">
                        {{ auth()->user()->initials }}
                    </div>
                    @endif
                </div>

                <div class="space-y-3">
                    {{-- Same pattern as project image uploads: canvas → hidden base64 input → plain form submit --}}
                    <form id="avatar-upload-form" action="{{ route('settings.avatar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="avatar_base64" id="avatar-base64" value="">
                        <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only">
                        <button type="button" onclick="document.getElementById('avatar-file-input').click()"
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                            {{ auth()->user()->profile_picture ? 'Change Photo' : 'Upload Photo' }}
                        </button>
                    </form>

                    @if(auth()->user()->profile_picture)
                    <form action="{{ route('settings.avatar.delete') }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Remove your profile photo?')"
                                class="text-sm text-destructive hover:underline">
                            Remove photo
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="bg-card rounded-2xl border border-border">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Profile</h2>
            <p class="text-sm text-muted-foreground mt-1">Update your personal information.</p>
        </div>
        <form action="{{ route('settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-foreground mb-2">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}"
                       class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                @error('name') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-foreground mb-2">Email</label>
                <div class="w-full px-4 py-2.5 bg-muted/50 border border-border rounded-xl text-muted-foreground text-sm select-all cursor-default">
                    {{ auth()->user()->email }}
                </div>
                <p class="text-xs text-muted-foreground mt-1">Email address cannot be changed.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Notifications (UI only) -->
    <div class="bg-card rounded-2xl border border-border">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Notifications</h2>
            <p class="text-sm text-muted-foreground mt-1">Choose what updates you want to receive.</p>
        </div>
        <div class="p-6 space-y-6">
            @foreach([
                ['Email Notifications', 'Receive email updates about your projects.', true],
                ['Push Notifications', 'Receive push notifications on your device.', false],
                ['Task Reminders', 'Get reminded about upcoming task deadlines.', true],
                ['Weekly Digest', 'Receive a weekly summary of your activity.', true],
            ] as [$label, $desc, $checked])
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-foreground">{{ $label }}</p>
                    <p class="text-sm text-muted-foreground">{{ $desc }}</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" @if($checked) checked @endif>
                    <div class="w-11 h-6 bg-muted rounded-full peer peer-checked:bg-primary peer-focus:ring-2 peer-focus:ring-ring after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                </label>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Integrations -->
    <div class="bg-card rounded-2xl border border-border">
        <div class="p-6 border-b border-border">
            <h2 class="text-lg font-semibold text-foreground">Integrations</h2>
            <p class="text-sm text-muted-foreground mt-1">Connect external services to enhance DevFlow.</p>
        </div>
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="w-9 h-9 rounded-lg bg-foreground/10 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-foreground" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-foreground">GitHub Personal Access Token</p>
                    <p class="text-sm text-muted-foreground mt-0.5 mb-3">Required to show open PRs and branches on project pages. Create one at <a href="https://github.com/settings/tokens" target="_blank" rel="noopener" class="text-primary hover:underline">github.com/settings/tokens</a> with <code class="text-xs bg-muted px-1 py-0.5 rounded">repo</code> scope.</p>
                    <form action="{{ route('settings.github-token') }}" method="POST" class="flex gap-3">
                        @csrf
                        <input type="password" name="github_token" autocomplete="new-password"
                               placeholder="{{ auth()->user()->github_token ? 'Token saved — paste new token to replace' : 'ghp_… or github_pat_…' }}"
                               class="flex-1 px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring text-sm">
                        <button type="submit" class="px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:opacity-90 transition-opacity shrink-0">
                            Save Token
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="bg-card rounded-2xl border border-destructive/50">
        <div class="p-6 border-b border-destructive/50">
            <h2 class="text-lg font-semibold text-destructive">Danger Zone</h2>
            <p class="text-sm text-muted-foreground mt-1">Irreversible and destructive actions.</p>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-foreground">Delete Account</p>
                    <p class="text-sm text-muted-foreground">Permanently delete your account and all data.</p>
                </div>
                <form action="{{ route('account.delete') }}" method="POST"
                      onsubmit="return confirm('Delete your account permanently? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-destructive text-destructive-foreground rounded-xl text-sm font-medium hover:bg-destructive/90 transition-colors">
                        Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('avatar-file-input').addEventListener('change', function () {
    if (!this.files || !this.files[0]) return;
    if (this.files[0].size > 4 * 1024 * 1024) {
        this.value = '';
        alert('Image must be under 4 MB.');
        return;
    }

    const img = new Image();
    const objectUrl = URL.createObjectURL(this.files[0]);

    img.onload = () => {
        URL.revokeObjectURL(objectUrl);

        // Resize to max 512×512 — keeps the JSON body well under any PHP buffer limit.
        const MAX = 512;
        let w = img.width, h = img.height;
        if (w > MAX || h > MAX) {
            if (w >= h) { h = Math.round(h * MAX / w); w = MAX; }
            else        { w = Math.round(w * MAX / h); h = MAX; }
        }

        const canvas = document.createElement('canvas');
        canvas.width  = w;
        canvas.height = h;
        canvas.getContext('2d').drawImage(img, 0, 0, w, h);

        const base64 = canvas.toDataURL('image/jpeg', 0.85);

        // Show preview immediately so the user sees feedback.
        document.getElementById('avatar-preview').innerHTML =
            `<img src="${base64}" alt="Profile photo" class="w-20 h-20 rounded-full object-cover ring-2 ring-border">`;

        // Plain form submit — identical to how project image uploads work.
        // URL-encoded POST bodies are parsed by PHP directly into $_POST without
        // needing a temp file, so the built-in server handles them reliably.
        document.getElementById('avatar-base64').value = base64;
        document.getElementById('avatar-upload-form').submit();
    };

    img.src = objectUrl;
});
</script>
@endpush

@endsection
