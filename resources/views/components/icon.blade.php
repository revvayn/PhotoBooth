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
        @case('bag')
            <rect x="5" y="8" width="14" height="13" rx="2"/>
            <path d="M9 8V6a3 3 0 0 1 6 0v2"/>
            @break
        @case('arrow-right')
            <path d="M5 12h14"/>
            <path d="m13 6 6 6-6 6"/>
            @break
        @case('arrow-left')
            <path d="M19 12H5"/>
            <path d="m11 6-6 6 6 6"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/>
            <path d="M8 3v4M16 3v4M3 10h18"/>
            @break
        @case('download')
            <path d="M12 3v12"/>
            <path d="m7 10 5 5 5-5"/>
            <path d="M4 21h16"/>
            @break
        @case('trash')
            <path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7"/>
            <path d="M10 11v6M14 11v6M9 7V4h6v3M4 7h16"/>
            @break
        @case('eye')
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/>
            <circle cx="12" cy="12" r="3"/>
            @break
        @case('search')
            <circle cx="11" cy="11" r="7"/>
            <path d="m21 21-4.3-4.3"/>
            @break
        @case('chevron-left')
            <path d="m15 18-6-6 6-6"/>
            @break
        @case('chevron-right')
            <path d="m9 18 6-6-6-6"/>
            @break
        @case('x')
            <path d="M18 6 6 18M6 6l12 12"/>
            @break
        @case('filter')
            <path d="M4 5h16l-6 7v6l-4 2v-8L4 5z"/>
            @break
        @case('sparkles')
            <path d="m12 3 1.8 4.2L18 9l-4.2 1.8L12 15l-1.8-4.2L6 9l4.2-1.8L12 3z"/>
            <path d="M19 14l.9 2.1L22 17l-2.1.9L19 20l-.9-2.1L16 17l2.1-.9L19 14z"/>
            @break
        @case('sliders')
            <path d="M4 8h10M18 8h2M4 16h4M12 16h8"/>
            <circle cx="16" cy="8" r="2"/>
            <circle cx="10" cy="16" r="2"/>
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('pencil')
            <path d="M12 20h9"/>
            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
            @break
        @case('receipt')
            <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/>
            <path d="M9 8h6M9 12h6M9 16h3"/>
            @break
        @case('ticket')
            <path d="M4 7a2 2 0 0 0 2-2h12a2 2 0 0 0 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 0-2 2H6a2 2 0 0 0-2-2v-3a2 2 0 0 0 0-4V7z"/>
            <path d="M13 5v14" stroke-dasharray="2 2"/>
            @break
        @default
            <circle cx="12" cy="12" r="9"/>
            @break
    @endswitch
</svg>
