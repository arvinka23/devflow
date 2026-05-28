<!-- AI Assistant Widget -->
<div x-data="aiAssistant({{ $project->id }})" class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">

    <!-- Chat Panel -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="bg-card border border-border rounded-2xl shadow-2xl w-80 flex flex-col overflow-hidden"
         style="max-height: 480px;">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-border bg-card shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m1.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-foreground">DevFlow AI</span>
            </div>
            <button @click="open = false" class="p-1 rounded-lg hover:bg-muted text-muted-foreground transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="messagesEl">
            <!-- Welcome / quick actions -->
            <template x-if="messages.length === 0">
                <div class="space-y-2">
                    <p class="text-xs text-muted-foreground text-center">Ask me anything about this project.</p>
                    <button @click="suggestTasks()"
                            class="w-full text-left px-3 py-2.5 bg-muted rounded-xl text-xs text-foreground hover:bg-muted/70 transition-colors flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Suggest tasks for this project
                    </button>
                </div>
            </template>

            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                            ? 'bg-primary text-primary-foreground rounded-2xl rounded-tr-sm px-3 py-2 max-w-[85%] text-xs'
                            : 'bg-muted text-foreground rounded-2xl rounded-tl-sm px-3 py-2 max-w-[90%] text-xs'">
                        <span x-html="msg.html || escHtml(msg.content)"></span>
                    </div>
                </div>
            </template>

            <!-- Loading indicator -->
            <template x-if="loading">
                <div class="flex justify-start">
                    <div class="bg-muted rounded-2xl rounded-tl-sm px-3 py-2.5 flex gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground/60 animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground/60 animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground/60 animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Input -->
        <div class="p-3 border-t border-border shrink-0">
            <div class="flex gap-2">
                <input x-model="input" @keydown.enter.prevent="send()"
                       type="text" placeholder="Ask DevFlow AI…"
                       class="flex-1 px-3 py-2 bg-muted border-0 rounded-xl text-xs text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring">
                <button @click="send()" :disabled="loading || !input.trim()"
                        class="px-3 py-2 bg-primary text-primary-foreground rounded-xl text-xs font-medium hover:opacity-90 transition-opacity disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- FAB Button -->
    <button @click="open = !open"
            class="w-12 h-12 bg-primary text-primary-foreground rounded-full shadow-lg hover:shadow-xl hover:opacity-90 transition-all flex items-center justify-center">
        <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m1.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
        <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

{{-- Alpine component defined in app.js --}}
