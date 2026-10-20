@props(['status'])
@php
    $classes = match(strtoupper($status ?? '')) {
        'DISETUJUI' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'DITOLAK' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $classes }}">
    {{ $status }}
</span>
