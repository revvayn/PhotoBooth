@php
    $brand ??= \App\Support\Brand::get();
    $weights = [
        'Baloo 2' => '400;500;600;700;800',
        'Poppins' => '300;400;500;600;700;800',
        'Fredoka' => '400;500;600;700',
        'Quicksand' => '400;500;600;700',
        'Nunito' => '400;600;700;800',
        'Inter' => '400;500;600;700;800',
        'DM Sans' => '400;500;700',
        'Space Grotesk' => '400;500;600;700',
    ];
    $fontFamilies = collect([$brand['heading_font'] ?? 'Baloo 2', $brand['body_font'] ?? 'Poppins'])
        ->filter()
        ->unique()
        ->map(fn ($font) => 'family='.str_replace(' ', '+', $font).':wght@'.($weights[$font] ?? '400;500;600;700'))
        ->implode('&');
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?{{ $fontFamilies }}&display=swap" rel="stylesheet">
