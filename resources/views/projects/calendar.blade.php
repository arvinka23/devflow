@extends('layouts.app')

@section('title', $project->name . ' — Calendar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('projects.show', $project) }}" class="p-2 rounded-lg hover:bg-muted transition-colors">
                <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-foreground">{{ $project->name }}</h1>
                <p class="text-muted-foreground mt-1">Calendar — {{ $start->format('F Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- view switcher -->
            <a href="{{ route('projects.show', $project) }}" class="px-3 py-1.5 text-sm rounded-lg text-muted-foreground hover:bg-muted transition-colors">Kanban</a>
            <a href="{{ route('projects.list', $project) }}" class="px-3 py-1.5 text-sm rounded-lg text-muted-foreground hover:bg-muted transition-colors">List</a>
            <span class="px-3 py-1.5 text-sm rounded-lg bg-primary text-primary-foreground">Calendar</span>
        </div>
    </div>

    <!-- Month nav -->
    <div class="flex items-center justify-between bg-card border border-border rounded-2xl px-6 py-4">
        <a href="{{ route('projects.calendar', [$project, 'month' => $prev->month, 'year' => $prev->year]) }}"
           class="p-2 rounded-lg hover:bg-muted transition-colors">
            <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h2 class="text-lg font-semibold text-foreground">{{ $start->format('F Y') }}</h2>
        <a href="{{ route('projects.calendar', [$project, 'month' => $next->month, 'year' => $next->year]) }}"
           class="p-2 rounded-lg hover:bg-muted transition-colors">
            <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Calendar grid -->
    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <div class="grid grid-cols-7 border-b border-border">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
            <div class="py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ $day }}</div>
            @endforeach
        </div>

        @php
            $firstDow = ($start->dayOfWeek === 0 ? 6 : $start->dayOfWeek - 1);
            $daysInMonth = $start->daysInMonth;
            $today = now()->day;
            $isCurrentMonth = $start->month === now()->month && $start->year === now()->year;
        @endphp

        @php $colors = ['todo' => 'bg-slate-500', 'in_progress' => 'bg-amber-500', 'done' => 'bg-green-500']; @endphp
        <div class="grid grid-cols-7">
            {{-- leading empty cells --}}
            @for($i = 0; $i < $firstDow; $i++)
            <div class="min-h-[100px] border-b border-r border-border p-2 bg-muted/30"></div>
            @endfor

            @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $isToday = $isCurrentMonth && $d === $today;
                $dayTasks = $tasks->get($d, collect());
            @endphp
            <div class="min-h-[100px] border-b border-r border-border p-2 {{ $isToday ? 'bg-primary/5' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium {{ $isToday ? 'bg-primary text-primary-foreground w-6 h-6 rounded-full flex items-center justify-center text-xs' : 'text-muted-foreground' }}">{{ $d }}</span>
                </div>
                @foreach($dayTasks as $task)
                <div class="mb-1 px-2 py-0.5 rounded text-xs text-white truncate {{ $colors[$task->status] ?? 'bg-primary' }}"
                     title="{{ $task->title }}">
                    {{ $task->title }}
                </div>
                @endforeach
            </div>
            @endfor

            {{-- trailing empty cells --}}
            @php $trailing = (7 - ($firstDow + $daysInMonth) % 7) % 7; @endphp
            @for($i = 0; $i < $trailing; $i++)
            <div class="min-h-[100px] border-b border-r border-border p-2 bg-muted/30"></div>
            @endfor
        </div>
    </div>
</div>
@endsection
