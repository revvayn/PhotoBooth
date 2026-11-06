@props([
    'size' => 'w-10 h-10',
    'rounded' => 'rounded-2xl',
    'icon' => 'w-5 h-5',
])

@php
    $brand = \App\Support\Brand::get();
@endphp

<div {{ $attributes->merge(['class' => $size.' '.$rounded.' flex items-center justify-center shadow-md shrink-0 overflow-hidden']) }}
     style="background: linear-gradient(135deg, {{ $brand['primary'] }}, {{ $brand['secondary'] }}); box-shadow: 0 8px 18px -10px {{ $brand['primary'] }};">
    @if ($brand['logo_url'])
        <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['name'] }}" class="w-full h-full object-contain p-1.5">
    @else
        <x-icon name="camera" :class="$icon.' text-white'" />
    @endif
</div>
