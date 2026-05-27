
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('timerWidget', (taskId, initialEntry) => ({
    taskId,
    running: initialEntry ? initialEntry.running : false,
    startedAt: initialEntry?.started_at ? new Date(initialEntry.started_at) : null,
    elapsed: initialEntry?.duration_seconds ?? 0,
    intervalId: null,
    entries: [],
    loadingEntries: false,

    init() {
        if (this.running && this.startedAt) {
            this.elapsed = Math.floor((Date.now() - this.startedAt.getTime()) / 1000);
            this.intervalId = setInterval(() => {
                this.elapsed = Math.floor((Date.now() - this.startedAt.getTime()) / 1000);
            }, 1000);
        }
        this.fetchEntries();
    },

    destroy() {
        if (this.intervalId) clearInterval(this.intervalId);
    },

    get display() {
        const s = this.elapsed;
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const sec = s % 60;
        return (h > 0 ? String(h).padStart(2,'0') + ':' : '') +
               String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
    },

    async start() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const res = await fetch(`/tasks/${this.taskId}/time/start`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            this.startedAt = new Date(data.entry.started_at);
            this.running   = true;
            this.elapsed   = 0;
            this.intervalId = setInterval(() => {
                this.elapsed = Math.floor((Date.now() - this.startedAt.getTime()) / 1000);
            }, 1000);
        }
    },

    async stop() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const res = await fetch(`/tasks/${this.taskId}/time/stop`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (res.ok) {
            clearInterval(this.intervalId);
            this.intervalId = null;
            this.running    = false;
            this.elapsed    = 0;
            this.startedAt  = null;
            await this.fetchEntries();
        }
    },

    async fetchEntries() {
        this.loadingEntries = true;
        const res = await fetch(`/tasks/${this.taskId}/time`, {
            headers: { 'Accept': 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            this.entries = data.entries;
        }
        this.loadingEntries = false;
    },

    async deleteEntry(entryId) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const res = await fetch(`/time-entries/${entryId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (res.ok) {
            this.entries = this.entries.filter(e => e.id !== entryId);
        }
    },
}));

// ── GitHub Widget ────────────────────────────────────────────────────────────
Alpine.data('githubWidget', (projectId, initialRepo) => ({
    projectId,
    repo:         initialRepo || null,
    hasToken:     true,
    allRepos:     [],
    search:       '',
    loadingRepos: false,
    repoError:    null,
    prs:          [],
    branches:     [],
    loading:      false,
    error:        null,

    get filteredRepos() {
        if (!this.search.trim()) return this.allRepos;
        const q = this.search.toLowerCase();
        return this.allRepos.filter(r =>
            r.full_name.toLowerCase().includes(q) ||
            (r.description && r.description.toLowerCase().includes(q))
        );
    },

    init() {
        if (this.repo) { this.fetchData(); } else { this.fetchRepos(); }
    },

    async fetchRepos() {
        this.loadingRepos = true;
        this.repoError    = null;
        try {
            const res  = await fetch('/github/repos', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.error) {
                if (data.error.includes('token')) this.hasToken = false;
                this.repoError = data.error;
            } else {
                this.allRepos = data.repos ?? [];
            }
        } catch { this.repoError = 'Could not load repositories.'; }
        finally  { this.loadingRepos = false; }
    },

    async fetchData() {
        this.loading = true; this.error = null;
        try {
            const res  = await fetch(`/projects/${this.projectId}/github`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.error) { this.error = data.error; }
            else { this.prs = data.prs ?? []; this.branches = data.branches ?? []; }
        } catch { this.error = 'Failed to load GitHub data.'; }
        finally  { this.loading = false; }
    },

    async linkRepo(fullName) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const res  = await fetch(`/projects/${this.projectId}/github/link`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body:   JSON.stringify({ github_repo: fullName }),
        });
        const data = await res.json();
        if (data.ok) { this.repo = data.github_repo; this.allRepos = []; this.search = ''; this.fetchData(); }
        else { this.repoError = data.message ?? 'Could not link repo.'; }
    },

    async unlinkRepo() {
        if (!confirm('Unlink this GitHub repository?')) return;
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        await fetch(`/projects/${this.projectId}/github/unlink`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        this.repo = null; this.prs = []; this.branches = []; this.fetchRepos();
    },
}));

// ── AI Assistant ─────────────────────────────────────────────────────────────
Alpine.data('aiAssistant', (projectId) => ({
    open: false, input: '', messages: [], loading: false, msgCounter: 0,

    escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\n/g,'<br>');
    },

    scrollToBottom() {
        this.$nextTick(() => { const el = this.$refs.messagesEl; if (el) el.scrollTop = el.scrollHeight; });
    },

    pushMsg(role, content, html = null) {
        this.messages.push({ id: ++this.msgCounter, role, content, html });
        this.scrollToBottom();
    },

    async send() {
        const text = this.input.trim();
        if (!text || this.loading) return;
        this.input = '';
        this.pushMsg('user', text);
        this.loading = true;
        const history = this.messages.slice(0, -1).map(m => ({ role: m.role, content: m.content }));
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res  = await fetch('/ai/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body:   JSON.stringify({ message: text, history, project_id: projectId }),
            });
            const data = await res.json();
            this.pushMsg('assistant', data.message ?? data.error ?? 'No response.');
        } catch { this.pushMsg('assistant', 'Connection error. Please try again.'); }
        finally  { this.loading = false; }
    },

    async suggestTasks() {
        this.loading = true;
        this.pushMsg('assistant', 'Thinking of task suggestions…');
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const res  = await fetch('/ai/suggest-tasks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body:   JSON.stringify({ project_id: projectId }),
            });
            const data = await res.json();
            if (data.error) {
                this.messages.at(-1).content = data.error;
                this.messages.at(-1).html    = this.escHtml(data.error);
            } else if (data.suggestions?.length > 0) {
                const html = data.suggestions.map(s => `
                    <div class="mb-2 last:mb-0">
                        <div class="font-medium">${this.escHtml(s.title)}</div>
                        <div class="text-muted-foreground text-xs mt-0.5">${this.escHtml(s.description)}</div>
                        <span class="inline-block mt-1 text-xs px-1.5 py-0.5 rounded-md bg-card/50 opacity-70">${this.escHtml(s.priority)} priority</span>
                    </div>`).join('<hr class="border-border/50 my-2">');
                this.messages.at(-1).content = 'Here are 5 task suggestions:';
                this.messages.at(-1).html    = html;
            } else {
                this.messages.at(-1).content = 'No suggestions returned. Try describing your project.';
            }
        } catch { this.messages.at(-1).content = 'Connection error. Please try again.'; }
        finally  { this.loading = false; this.scrollToBottom(); }
    },
}));

Alpine.start();

