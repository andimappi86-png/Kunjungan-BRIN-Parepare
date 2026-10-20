@props(['type' => 'submit', 'variant' => 'primary'])
@php
    $base = 'inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2';
    $styles = match($variant) {
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500',
        default => 'bg-primary text-white hover:bg-blue-700 focus:ring-blue-500',
    };
@endphp
<button type="{{ $type }}" {{ $attributes->merge(['class' => "{$base} {$styles}"]) }}>
    {{ $slot }}
</button>
