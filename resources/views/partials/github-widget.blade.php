<div class="bg-card rounded-2xl border border-border"
     x-data="githubWidget({{ $project->id }}, '{{ e($project->github_repo ?? '') }}')"
     x-init="init()">

    <!-- Header -->
    <div class="flex items-center justify-between p-5 border-b border-border">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-foreground" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
            <h3 class="font-semibold text-foreground text-sm">GitHub</h3>
        </div>

        <!-- Linked repo name + unlink -->
        <template x-if="repo">
            <div class="flex items-center gap-2">
                <a :href="'https://github.com/' + repo" target="_blank" rel="noopener"
                   class="text-xs text-primary hover:underline font-mono" x-text="repo"></a>
                <button @click="unlinkRepo()"
                        class="text-xs text-muted-foreground hover:text-destructive transition-colors">
                    Unlink
                </button>
            </div>
        </template>
    </div>

    <div class="p-5">

        <!-- ── NO REPO LINKED: repo picker ── -->
        <template x-if="!repo">
            <div class="space-y-3">

                <!-- Token missing -->
                <template x-if="!hasToken">
                    <div class="text-center py-4">
                        <p class="text-sm text-muted-foreground mb-3">Add your GitHub token in Settings first.</p>
                        <a href="/settings" class="text-xs text-primary hover:underline">Go to Settings →</a>
                    </div>
                </template>

                <!-- Has token: show repo list -->
                <template x-if="hasToken">
                    <div>
                        <!-- Search box -->
                        <div class="relative mb-3">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input x-model="search" type="text" placeholder="Search your repos…"
                                   class="w-full pl-9 pr-3 py-2 bg-muted border-0 rounded-xl text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                        </div>

                        <!-- Loading repos -->
                        <template x-if="loadingRepos">
                            <div class="text-center py-6 text-sm text-muted-foreground">
                                <svg class="w-4 h-4 animate-spin mx-auto mb-2 text-muted-foreground" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Loading your repositories…
                            </div>
                        </template>

                        <!-- Error -->
                        <template x-if="repoError">
                            <div class="text-center py-4">
                                <p class="text-xs text-destructive mb-2" x-text="repoError"></p>
                                <button @click="fetchRepos()" class="text-xs text-primary hover:underline">Retry</button>
                            </div>
                        </template>

                        <!-- Repo list -->
                        <template x-if="!loadingRepos && !repoError">
                            <div>
                                <template x-if="filteredRepos.length === 0">
                                    <p class="text-xs text-muted-foreground text-center py-4">No repositories found.</p>
                                </template>
                                <ul class="space-y-1 max-h-56 overflow-y-auto -mx-1 px-1">
                                    <template x-for="r in filteredRepos" :key="r.full_name">
                                        <li>
                                            <button @click="linkRepo(r.full_name)"
                                                    class="w-full text-left px-3 py-2.5 rounded-xl hover:bg-muted transition-colors group/repo">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-3.5 h-3.5 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                    </svg>
                                                    <span class="text-sm font-medium text-foreground group-hover/repo:text-primary transition-colors truncate flex-1" x-text="r.full_name"></span>
                                                    <template x-if="r.private">
                                                        <span class="text-xs px-1.5 py-0.5 bg-muted rounded font-medium text-muted-foreground shrink-0">private</span>
                                                    </template>
                                                </div>
                                                <template x-if="r.description">
                                                    <p class="text-xs text-muted-foreground mt-0.5 ml-5.5 truncate" x-text="r.description"></p>
                                                </template>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                                <p class="text-xs text-muted-foreground mt-2 text-center" x-show="filteredRepos.length > 0">
                                    Click a repo to link it to this project
                                </p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        <!-- ── REPO LINKED: show PRs & branches ── -->
        <template x-if="repo">
            <div>
                <template x-if="loading">
                    <div class="text-center py-4 text-sm text-muted-foreground">
                        <svg class="w-4 h-4 animate-spin mx-auto mb-2 text-muted-foreground" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Loading GitHub data…
                    </div>
                </template>
                <template x-if="error && !loading">
                    <p class="text-sm text-destructive" x-text="error"></p>
                </template>
                <template x-if="!loading && !error">
                    <div class="space-y-4">
                        <!-- Open PRs -->
                        <div>
                            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                Open PRs (<span x-text="prs.length"></span>)
                            </p>
                            <template x-if="prs.length === 0">
                                <p class="text-xs text-muted-foreground">No open pull requests.</p>
                            </template>
                            <template x-for="pr in prs" :key="pr.number">
                                <a :href="pr.url" target="_blank" rel="noopener"
                                   class="flex items-start gap-2 py-1.5 hover:text-primary transition-colors group/pr">
                                    <span class="text-xs text-muted-foreground font-mono shrink-0 mt-0.5" x-text="'#' + pr.number"></span>
                                    <span class="text-sm text-foreground group-hover/pr:text-primary line-clamp-1 flex-1" x-text="pr.title"></span>
                                </a>
                            </template>
                        </div>

                        <!-- Branches -->
                        <div>
                            <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                </svg>
                                Branches (<span x-text="branches.length"></span>)
                            </p>
                            <template x-if="branches.length === 0">
                                <p class="text-xs text-muted-foreground">No branches found.</p>
                            </template>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="branch in branches" :key="branch">
                                    <span class="text-xs px-2 py-0.5 bg-muted rounded-lg font-mono text-muted-foreground" x-text="branch"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

    </div>
</div>

{{-- Alpine component defined in app.js --}}
