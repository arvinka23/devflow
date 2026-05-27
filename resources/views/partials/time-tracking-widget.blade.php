<div class="bg-card rounded-2xl border border-border p-6">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-foreground">Time This Week</h2>
        @php
            $totalWeekSeconds = array_sum(array_column($weeklyDays, 'seconds'));
            $totalH = intdiv($totalWeekSeconds, 3600);
            $totalM = intdiv($totalWeekSeconds % 3600, 60);
        @endphp
        <span class="text-sm font-mono text-muted-foreground">
            @if($totalWeekSeconds > 0)
                {{ $totalH > 0 ? $totalH . 'h ' : '' }}{{ $totalM }}m total
            @else
                No time logged
            @endif
        </span>
    </div>

    <div class="flex items-end gap-2 h-24">
        @foreach($weeklyDays as $day)
        @php
            $pct = $maxSeconds > 0 ? round($day['seconds'] / $maxSeconds * 100) : 0;
            $isToday = $loop->last;
            $h = intdiv($day['seconds'], 3600);
            $m = intdiv($day['seconds'] % 3600, 60);
            $label = ($h > 0 ? $h . 'h ' : '') . ($m > 0 ? $m . 'm' : ($day['seconds'] > 0 ? '<1m' : '—'));
        @endphp
        <div class="flex-1 flex flex-col items-center gap-1.5 group/bar" title="{{ $day['label'] }}: {{ $label }}">
            <div class="w-full rounded-t-lg transition-all duration-500 relative overflow-hidden"
                 style="height: {{ max($pct, 4) }}%; min-height: 4px;
                        background: {{ $isToday ? 'oklch(0.55 0.2 260 / 0.9)' : 'oklch(0.55 0.2 260 / 0.25)' }}">
                @if($day['seconds'] > 0)
                <div class="absolute inset-0 opacity-0 group-hover/bar:opacity-100 transition-opacity flex items-center justify-center">
                </div>
                @endif
            </div>
            <span class="text-xs text-muted-foreground {{ $isToday ? 'font-semibold text-primary' : '' }}">{{ $day['label'] }}</span>
        </div>
        @endforeach
    </div>

    @if($totalWeekSeconds === 0)
    <p class="text-xs text-center text-muted-foreground mt-3">Start a timer on any task to track time.</p>
    @endif
</div>
