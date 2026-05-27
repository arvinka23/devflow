@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-foreground">Dashboard</h1>
            <p class="text-muted-foreground mt-1">Welcome back, {{ auth()->user()->name }}. Here's what's happening.</p>
        </div>
        <button onclick="openDashboardEditor()"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-muted hover:bg-muted/70 text-sm text-muted-foreground transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Customize
        </button>
    </div>

    @php
        $twoColWidgets = ['recent_projects', 'kanban_preview'];
        $oneColWidgets = ['activity_feed', 'time_tracking'];

        // Group consecutive single-column widgets into paired rows
        $rows = [];
        $i = 0;
        while ($i < count($layout)) {
            $w = $layout[$i];
            if (in_array($w, $twoColWidgets)) {
                $rows[] = ['type' => 'full', 'widget' => $w];
                $i++;
            } else {
                // Try to pair with next one-col widget
                $next = $layout[$i + 1] ?? null;
                if ($next && in_array($next, $oneColWidgets)) {
                    $rows[] = ['type' => 'pair', 'left' => $w, 'right' => $next];
                    $i += 2;
                } else {
                    $rows[] = ['type' => 'full', 'widget' => $w];
                    $i++;
                }
            }
        }
    @endphp

    @foreach($rows as $row)
        @if($row['type'] === 'full')
            @include('partials.widgets.' . $row['widget'])
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2">
                    @include('partials.widgets.' . $row['left'])
                </div>
                <div class="lg:col-span-1">
                    @include('partials.widgets.' . $row['right'])
                </div>
            </div>
        @endif
    @endforeach
</div>

@include('partials.dashboard-editor', ['layout' => $layout])
@endsection
