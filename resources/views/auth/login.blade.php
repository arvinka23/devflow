<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — DevFlow</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex items-center justify-center gap-3 mb-8">
            <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-2xl font-semibold text-foreground">DevFlow</span>
        </div>

        <div class="bg-card rounded-2xl border border-border p-8 shadow-xl">
            <h1 class="text-xl font-semibold text-foreground mb-1">Welcome back</h1>
            <p class="text-sm text-muted-foreground mb-6">Sign in to your account</p>

            @if(session('status'))
                <div class="mb-4 p-3 bg-green-500/10 text-green-500 rounded-xl text-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-foreground mb-2">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    @error('email') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-foreground mb-2">Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-4 py-2.5 bg-muted border-0 rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                    @error('password') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded">
                        <span class="text-sm text-muted-foreground">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="w-full py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    Sign in
                </button>
            </form>

            <p class="text-center text-sm text-muted-foreground mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-primary hover:underline">Create one</a>
            </p>
        </div>
    </div>
</body>
</html>
