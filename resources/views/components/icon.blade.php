@props(['name' => 'camera', 'class' => 'w-6 h-6'])
<svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('camera')
            <rect x="3" y="7" width="18" height="13" rx="2"/>
            <circle cx="12" cy="13" r="3.5"/>
            <path d="M9 7l1.5-2.5h5L17 7"/>
            @break
        @case('film')
            <rect x="3" y="4" width="18" height="16" rx="2"/>
            <path d="M7 4v16M17 4v16M3 9h4M3 15h4M17 9h4M17 15h4"/>
            @break
        @case('cash')
            <rect x="2" y="6" width="20" height="12" rx="2"/>
            <circle cx="12" cy="12" r="2.5"/>
            <path d="M6 12h.01M18 12h.01"/>
            @break
        @case('qr')
            <rect x="4" y="4" width="6" height="6" rx="1"/>
            <rect x="14" y="4" width="6" height="6" rx="1"/>
            <rect x="4" y="14" width="6" height="6" rx="1"/>
            <path d="M14 14h3v3h-3zM20 14v.01M14 20v.01M18 18h.01M20 20v.01"/>
            @break
        @case('printer')
            <path d="M7 8V3h10v5M7 17H4a1 1 0 01-1-1v-7a1 1 0 011-1h16a1 1 0 011 1v7a1 1 0 01-1 1h-3M7 14h10v7H7z"/>
            @break
        @case('envelope')
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m3 7 9 6 9-6"/>
            @break
        @case('card')
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <path d="M2 10h20"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2"/>
            @break
        @case('photo')
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="9" cy="9" r="1.5"/>
            <path d="m21 15-4.5-4.5L6 21"/>
            @break
        @case('check')
            <path d="m5 13 4 4L19 7"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
            @break
    @endswitch
</svg>
