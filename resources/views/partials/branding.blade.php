@php
    $brand ??= \App\Support\Brand::get();
@endphp
<style>
    :root {
        --brand-primary: {{ $brand['primary'] }};
        --brand-secondary: {{ $brand['secondary'] }};
        --brand-accent: {{ $brand['accent'] }};
        --brand-bg-from: {{ $brand['bg_from'] }};
        --brand-bg-via: {{ $brand['bg_via'] }};
        --brand-bg-to: {{ $brand['bg_to'] }};
        --brand-primary-soft: color-mix(in srgb, var(--brand-primary) 10%, white);
        --brand-primary-line: color-mix(in srgb, var(--brand-primary) 28%, white);
        --brand-primary-muted: color-mix(in srgb, var(--brand-primary) 65%, white);
        --brand-secondary-soft: color-mix(in srgb, var(--brand-secondary) 10%, white);
        --brand-accent-soft: color-mix(in srgb, var(--brand-accent) 12%, white);
        --brand-primary-shadow: color-mix(in srgb, var(--brand-primary) 32%, transparent);
    }

    @media screen {
        .kiosk-theme {
            background: linear-gradient(135deg, var(--brand-bg-from), var(--brand-bg-via), var(--brand-bg-to));
            background-attachment: fixed;
        }

        .kiosk-theme.has-brand-bg {
            background-image: linear-gradient(135deg, color-mix(in srgb, var(--brand-bg-from) 88%, transparent), color-mix(in srgb, var(--brand-bg-via) 80%, transparent), color-mix(in srgb, var(--brand-bg-to) 88%, transparent)), var(--brand-bg-image);
            background-size: cover;
            background-position: center;
        }
    }

    /* Warna brand utama */
    .kiosk-theme .bg-rose-500 { background-color: var(--brand-primary) !important; }
    .kiosk-theme .bg-rose-400 { background-color: var(--brand-primary-muted) !important; }
    .kiosk-theme .bg-rose-600 { background-color: color-mix(in srgb, var(--brand-primary) 82%, black) !important; }
    .kiosk-theme .text-rose-500,
    .kiosk-theme .text-rose-600 { color: var(--brand-primary) !important; }
    .kiosk-theme .text-rose-400 { color: var(--brand-primary-muted) !important; }
    .kiosk-theme .bg-rose-50 { background-color: var(--brand-primary-soft) !important; }
    .kiosk-theme .bg-rose-100 { background-color: color-mix(in srgb, var(--brand-primary) 16%, white) !important; }
    .kiosk-theme .bg-rose-200 { background-color: color-mix(in srgb, var(--brand-primary) 26%, white) !important; }
    .kiosk-theme .ring-rose-100 { --tw-ring-color: var(--brand-primary-soft) !important; }
    .kiosk-theme .ring-rose-200 { --tw-ring-color: var(--brand-primary-line) !important; }
    .kiosk-theme .ring-rose-300,
    .kiosk-theme .ring-rose-400,
    .kiosk-theme .ring-rose-500 { --tw-ring-color: var(--brand-primary) !important; }
    .kiosk-theme .border-rose-100 { border-color: var(--brand-primary-soft) !important; }
    .kiosk-theme .border-rose-200 { border-color: var(--brand-primary-line) !important; }
    .kiosk-theme .border-rose-300,
    .kiosk-theme .border-rose-500 { border-color: var(--brand-primary) !important; }
    .kiosk-theme .shadow-rose-100 { --tw-shadow-color: color-mix(in srgb, var(--brand-primary) 18%, transparent) !important; }
    .kiosk-theme .shadow-rose-200 { --tw-shadow-color: var(--brand-primary-shadow) !important; }
    .kiosk-theme .shadow-rose-300 { --tw-shadow-color: color-mix(in srgb, var(--brand-primary) 40%, transparent) !important; }

    /* Warna sekunder & aksen */
    .kiosk-theme .bg-violet-500,
    .kiosk-theme .bg-violet-600 { background-color: var(--brand-secondary) !important; }
    .kiosk-theme .text-violet-500,
    .kiosk-theme .text-violet-600 { color: var(--brand-secondary) !important; }
    .kiosk-theme .bg-violet-50 { background-color: var(--brand-secondary-soft) !important; }
    .kiosk-theme .bg-violet-100 { background-color: color-mix(in srgb, var(--brand-secondary) 16%, white) !important; }
    .kiosk-theme .text-fuchsia-500,
    .kiosk-theme .text-fuchsia-600 { color: var(--brand-accent) !important; }
    .kiosk-theme .bg-fuchsia-100 { background-color: var(--brand-accent-soft) !important; }

    /* Tailwind gradient stops */
    .kiosk-theme .from-rose-400,
    .kiosk-theme .from-rose-500,
    .kiosk-theme .from-rose-600 { --tw-gradient-from: var(--brand-primary) !important; }
    .kiosk-theme .to-rose-500,
    .kiosk-theme .to-rose-600 { --tw-gradient-to: var(--brand-primary) !important; }
    .kiosk-theme .from-violet-400,
    .kiosk-theme .from-violet-500,
    .kiosk-theme .to-violet-500,
    .kiosk-theme .to-violet-600 { --tw-gradient-from: var(--brand-secondary) !important; --tw-gradient-to: var(--brand-secondary) !important; }
    .kiosk-theme .via-fuchsia-400,
    .kiosk-theme .via-fuchsia-500 { --tw-gradient-via: var(--brand-accent) !important; }
    .kiosk-theme .to-fuchsia-500 { --tw-gradient-to: var(--brand-accent) !important; }
</style>
