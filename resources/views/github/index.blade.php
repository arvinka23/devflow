@extends('layouts.app')

@section('title', 'GitHub')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-foreground">GitHub</h1>
        <p class="text-muted-foreground mt-1">Manage GitHub repository connections for your projects.</p>
    </div>

    @if(! $hasToken)
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-muted-foreground" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-foreground mb-2">No GitHub token configured</h2>
        <p class="text-muted-foreground text-sm mb-6 max-w-sm">Add a personal access token in Settings to link your projects to GitHub repositories.</p>
        <a href="{{ route('settings') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
            Go to Settings
        </a>
    </div>

    @elseif($projects->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-foreground mb-2">No projects yet</h2>
        <p class="text-muted-foreground text-sm mb-6 max-w-sm">Create a project first, then link a GitHub repository to it here.</p>
        <a href="{{ route('projects.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
            View Projects
        </a>
    </div>

    @else
    <div class="bg-card border border-border rounded-2xl overflow-visible">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border">
                    <th class="text-left px-6 py-3.5 text-xs font-medium text-muted-foreground uppercase tracking-wide">Project</th>
                    <th class="text-left px-6 py-3.5 text-xs font-medium text-muted-foreground uppercase tracking-wide">Repository</th>
                    <th class="text-left px-6 py-3.5 text-xs font-medium text-muted-foreground uppercase tracking-wide">Open PRs</th>
                    <th class="px-6 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach($projects as $item)
                <tr x-data="githubRow({{ $item['project']->id }})" class="hover:bg-muted/40 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['project']->color }}"></div>
                            <span class="font-medium text-foreground">{{ $item['project']->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($item['project']->github_repo)
                        <a href="https://github.com/{{ $item['project']->github_repo }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-primary hover:underline font-mono text-xs">
                            {{ $item['project']->github_repo }}
                            <svg class="w-3 h-3 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                        @else
                        <span class="text-muted-foreground text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(! $item['project']->github_repo)
                        <span class="text-muted-foreground">—</span>
                        @elseif($item['pr_count'] === null)
                        <span class="text-muted-foreground">—</span>
                        @elseif($item['pr_count'] === 0)
                        <span class="text-muted-foreground">0</span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                            {{ $item['pr_count'] }}
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($item['project']->github_repo)
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('projects.show', $item['project']) }}"
                               class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                                View
                            </a>
                            <button @click="unlink()"
                                    class="text-sm text-muted-foreground hover:text-destructive transition-colors">
                                Unlink
                            </button>
                        </div>
                        @else
                        <div class="relative inline-flex justify-end">
                            <button @click="openPicker()"
                                    class="inline-flex items-center gap-1.5 text-sm text-primary hover:text-primary/80 font-medium transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Link repo
                            </button>

                            <div x-show="open"
                                 x-transition
                                 @click.outside="open = false"
                                 class="absolute right-0 top-8 w-80 bg-card border border-border rounded-2xl shadow-xl z-20 p-4"
                                 style="display: none;">

                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-medium text-foreground">Link a repository</p>
                                    <button @click="open = false" class="p-1 rounded-lg hover:bg-muted transition-colors">
                                        <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <template x-if="loading">
                                    <div class="text-center py-6 text-sm text-muted-foreground">
                                        <svg class="w-4 h-4 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        Loading repositories…
                                    </div>
                                </template>

                                <template x-if="error">
                                    <p class="text-xs text-destructive text-center py-4" x-text="error"></p>
                                </template>

                                <template x-if="!loading && !error">
                                    <div>
                                        <div class="relative mb-3">
                                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <input x-model="search" type="text" placeholder="Search repos…"
                                                   class="w-full pl-9 pr-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                                        </div>

                                        <template x-if="filtered.length === 0">
                                            <p class="text-xs text-muted-foreground text-center py-3">No repositories found.</p>
                                        </template>

                                        <ul class="space-y-0.5 max-h-52 overflow-y-auto -mx-1 px-1">
                                            <template x-for="r in filtered" :key="r.full_name">
                                                <li>
                                                    <button @click="link(r.full_name)"
                                                            class="w-full text-left px-3 py-2 rounded-xl hover:bg-muted transition-colors flex items-center gap-2 group/r">
                                                        <span class="text-sm text-foreground group-hover/r:text-primary transition-colors truncate flex-1 font-mono" x-text="r.full_name"></span>
                                                        <template x-if="r.private">
                                                            <span class="text-xs px-1.5 py-0.5 bg-muted rounded font-medium text-muted-foreground shrink-0">private</span>
                                                        </template>
                                                    </button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
