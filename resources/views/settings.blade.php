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
            <p class="text-sm text-muted-foreground mt-1">Click your photo to upload a new one. Max 2 MB.</p>
        </div>
        <div class="p-6 flex items-center gap-6">
            {{-- Single form wraps the label — file input is INSIDE the form so it submits directly --}}
            <form action="{{ route('settings.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="avatar-input" class="relative cursor-pointer group/av shrink-0 block">
                    @if(auth()->user()->profile_picture)
                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                         alt="Profile photo"
                         class="w-20 h-20 rounded-full object-cover ring-2 ring-border group-hover/av:ring-primary transition-all">
                    @else
                    <div class="w-20 h-20 rounded-full bg-primary/20 flex items-center justify-center text-2xl font-medium text-primary ring-2 ring-border group-hover/av:ring-primary transition-all">
                        {{ auth()->user()->initials }}
                    </div>
                    @endif
                    <div class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover/av:opacity-100 transition-opacity">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    {{-- File input INSIDE the form — submits automatically on change --}}
                    <input type="file" name="avatar" id="avatar-input" accept="image/*" class="hidden"
                           onchange="this.form.submit()">
                </label>
            </form>

            <div>
                <p class="text-sm font-medium text-foreground">{{ auth()->user()->name }}</p>
                <p class="text-xs text-muted-foreground mt-0.5">JPG, PNG, WebP or GIF</p>
                @if(auth()->user()->profile_picture)
                <form action="{{ route('settings.avatar.delete') }}" method="POST" class="mt-3">
                    @csrf @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Remove your profile photo?')"
                            class="text-xs text-destructive hover:underline">
                        Remove photo
                    </button>
                </form>
                @endif
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

@endsection
