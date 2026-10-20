@props(['title', 'value', 'icon' => null, 'color' => 'blue'])
@php
    $accent = match ($color) {
        'amber' => ['border-t-amber-400', 'bg-amber-50 text-amber-700'],
        'emerald' => ['border-t-emerald-500', 'bg-emerald-50 text-emerald-700'],
        'rose' => ['border-t-rose-400', 'bg-rose-50 text-rose-700'],
        default => ['border-t-primary', 'bg-blue-50 text-primary'],
    };
@endphp
<div class="bg-surface rounded-lg border border-border border-t-2 {{ $accent[0] }} px-5 py-5 min-h-[128px] flex items-center justify-between gap-5">
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-[0.08em] text-slate-500">{{ $title }}</p>
        <h4 class="text-[28px] leading-none font-semibold tracking-tight text-slate-900 mt-2.5 tabular-nums">{{ $value }}</h4>
    </div>
    <div class="w-10 h-10 rounded-md flex-shrink-0 flex items-center justify-center {{ $accent[1] }}">
        {{ $slot }}
    </div>
</div>
