@props(['label' => null, 'name' => ''])
<div>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary']) }}>
        {{ $slot }}
    </select>
    @error($name)
        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
</div>
