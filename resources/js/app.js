
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

// ── GitHub Row (overview page link/unlink) ───────────────────────────────────
Alpine.data('githubRow', (projectId) => ({
    projectId,
    open:    false,
    repos:   [],
    loading: false,
    search:  '',
    error:   null,

    get filtered() {
        if (!this.search.trim()) return this.repos;
        const q = this.search.toLowerCase();
        return this.repos.filter(r =>
            r.full_name.toLowerCase().includes(q) ||
            (r.description && r.description.toLowerCase().includes(q))
        );
    },

    async openPicker() {
        this.open    = true;
        this.loading = true;
        this.error   = null;
        try {
            const res  = await fetch('/github/repos', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.error) { this.error = data.error; }
            else             { this.repos = data.repos ?? []; }
        } catch { this.error = 'Could not load repositories.'; }
        finally  { this.loading = false; }
    },

    async link(fullName) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const res  = await fetch(`/projects/${this.projectId}/github/link`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body:    JSON.stringify({ github_repo: fullName }),
        });
        if ((await res.json()).ok) location.reload();
    },

    async unlink() {
        if (!confirm('Unlink this GitHub repository?')) return;
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        await fetch(`/projects/${this.projectId}/github/unlink`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        location.reload();
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

// ── Project Time Report ──────────────────────────────────────────────────────
Alpine.data('projectTimeReport', (projectId) => ({
    loading: true,
    error:   null,
    data:    null,

    async init() {
        try {
            const res  = await fetch(`/projects/${projectId}/time-report`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (json.error) { this.error = json.error; }
            else            { this.data  = json; }
        } catch { this.error = 'Could not load time report.'; }
        this.loading = false;
    },

    formatSeconds(s) {
        if (!s || s < 60) return (s || 0) + 's';
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        if (h > 0) return h + 'h' + (m > 0 ? ' ' + m + 'm' : '');
        return m + 'm';
    },
}));

Alpine.start();

