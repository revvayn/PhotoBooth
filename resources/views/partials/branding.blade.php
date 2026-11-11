@php
    $brand ??= \App\Support\Brand::get();
    $radiusMap = [
        'sm' => ['xl' => '0.5rem', '2xl' => '0.65rem', '3xl' => '0.85rem'],
        'md' => ['xl' => '0.75rem', '2xl' => '1rem', '3xl' => '1.5rem'],
        'lg' => ['xl' => '1rem', '2xl' => '1.25rem', '3xl' => '1.75rem'],
        'xl' => ['xl' => '1.25rem', '2xl' => '1.5rem', '3xl' => '2rem'],
    ];
    $radius = $radiusMap[$brand['radius'] ?? 'md'] ?? $radiusMap['md'];
@endphp
<style>
    :root {
        --brand-primary: {{ $brand['primary'] }};
        --brand-secondary: {{ $brand['secondary'] }};
        --brand-accent: {{ $brand['accent'] }};
        --brand-success: {{ $brand['success'] }};
        --brand-warning: {{ $brand['warning'] }};
        --brand-danger: {{ $brand['danger'] }};
        --brand-text: {{ $brand['text'] }};
        --brand-heading: {{ $brand['heading'] }};
        --brand-muted: {{ $brand['muted'] }};
        --brand-bg-from: {{ $brand['bg_from'] }};
        --brand-bg-via: {{ $brand['bg_via'] }};
        --brand-bg-to: {{ $brand['bg_to'] }};
        --brand-heading-font: '{{ $brand['heading_font'] }}', sans-serif;
        --brand-body-font: '{{ $brand['body_font'] }}', sans-serif;
        --brand-radius-xl: {{ $radius['xl'] }};
        --brand-radius-2xl: {{ $radius['2xl'] }};
        --brand-radius-3xl: {{ $radius['3xl'] }};

        --brand-primary-soft: color-mix(in srgb, var(--brand-primary) 10%, white);
        --brand-primary-line: color-mix(in srgb, var(--brand-primary) 28%, white);
        --brand-primary-muted: color-mix(in srgb, var(--brand-primary) 65%, white);
        --brand-primary-dark: color-mix(in srgb, var(--brand-primary) 82%, black);
        --brand-primary-shadow: color-mix(in srgb, var(--brand-primary) 32%, transparent);
        --brand-secondary-soft: color-mix(in srgb, var(--brand-secondary) 10%, white);
        --brand-secondary-line: color-mix(in srgb, var(--brand-secondary) 28%, white);
        --brand-secondary-muted: color-mix(in srgb, var(--brand-secondary) 65%, white);
        --brand-secondary-dark: color-mix(in srgb, var(--brand-secondary) 82%, black);
        --brand-secondary-shadow: color-mix(in srgb, var(--brand-secondary) 32%, transparent);
        --brand-accent-soft: color-mix(in srgb, var(--brand-accent) 12%, white);
        --brand-accent-line: color-mix(in srgb, var(--brand-accent) 28%, white);
        --brand-accent-muted: color-mix(in srgb, var(--brand-accent) 65%, white);
        --brand-accent-dark: color-mix(in srgb, var(--brand-accent) 82%, black);
        --brand-accent-shadow: color-mix(in srgb, var(--brand-accent) 32%, transparent);
        --brand-success-soft: color-mix(in srgb, var(--brand-success) 10%, white);
        --brand-success-line: color-mix(in srgb, var(--brand-success) 28%, white);
        --brand-success-muted: color-mix(in srgb, var(--brand-success) 65%, white);
        --brand-success-dark: color-mix(in srgb, var(--brand-success) 82%, black);
        --brand-success-shadow: color-mix(in srgb, var(--brand-success) 32%, transparent);
        --brand-warning-soft: color-mix(in srgb, var(--brand-warning) 10%, white);
        --brand-warning-line: color-mix(in srgb, var(--brand-warning) 28%, white);
        --brand-warning-muted: color-mix(in srgb, var(--brand-warning) 65%, white);
        --brand-warning-dark: color-mix(in srgb, var(--brand-warning) 82%, black);
        --brand-warning-shadow: color-mix(in srgb, var(--brand-warning) 32%, transparent);
        --brand-danger-soft: color-mix(in srgb, var(--brand-danger) 10%, white);
        --brand-danger-line: color-mix(in srgb, var(--brand-danger) 28%, white);
        --brand-danger-muted: color-mix(in srgb, var(--brand-danger) 65%, white);
        --brand-danger-dark: color-mix(in srgb, var(--brand-danger) 82%, black);
        --brand-danger-shadow: color-mix(in srgb, var(--brand-danger) 32%, transparent);
    }

    @media screen {
        /* ---------- Tipografi ---------- */
        body.kiosk-theme {
            font-family: var(--brand-body-font);
        }
        .kiosk-theme .font-display,
        .kiosk-theme .font-heading,
        .kiosk-theme h1, .kiosk-theme h2, .kiosk-theme h3,
        .kiosk-theme h4, .kiosk-theme h5, .kiosk-theme h6 {
            font-family: var(--brand-heading-font) !important;
        }

        /* ---------- Bentuk sudut ---------- */
        .kiosk-theme .rounded-xl { border-radius: var(--brand-radius-xl) !important; }
        .kiosk-theme .rounded-2xl { border-radius: var(--brand-radius-2xl) !important; }
        .kiosk-theme .rounded-3xl { border-radius: var(--brand-radius-3xl) !important; }
        .kiosk-theme .rounded-\[2rem\] { border-radius: var(--brand-radius-3xl) !important; }

        /* ---------- Latar ---------- */
        .kiosk-theme {
            background: linear-gradient(180deg, var(--brand-bg-from) 0%, var(--brand-bg-via) 48%, var(--brand-bg-to) 100%);
            background-attachment: fixed;
        }
        .kiosk-theme.has-brand-bg {
            background-image: linear-gradient(180deg, color-mix(in srgb, var(--brand-bg-from) 88%, transparent), color-mix(in srgb, var(--brand-bg-via) 82%, transparent), color-mix(in srgb, var(--brand-bg-to) 88%, transparent)), var(--brand-bg-image);
            background-size: cover;
            background-position: center;
        }

        /* ---------- WARNA UTAMA (primary: rose & pink) ---------- */
        .kiosk-theme .bg-rose-500,
        .kiosk-theme .bg-pink-500 { background-color: var(--brand-primary) !important; }
        .kiosk-theme .bg-rose-400,
        .kiosk-theme .bg-pink-400 { background-color: var(--brand-primary-muted) !important; }
        .kiosk-theme .bg-rose-600,
        .kiosk-theme .bg-rose-700,
        .kiosk-theme .bg-pink-600 { background-color: var(--brand-primary-dark) !important; }
        .kiosk-theme .bg-rose-50,
        .kiosk-theme .bg-pink-50 { background-color: var(--brand-primary-soft) !important; }
        .kiosk-theme .bg-rose-100,
        .kiosk-theme .bg-pink-100 { background-color: color-mix(in srgb, var(--brand-primary) 16%, white) !important; }
        .kiosk-theme .bg-rose-200,
        .kiosk-theme .bg-pink-200 { background-color: color-mix(in srgb, var(--brand-primary) 26%, white) !important; }
        .kiosk-theme .bg-rose-300,
        .kiosk-theme .bg-pink-300 { background-color: color-mix(in srgb, var(--brand-primary) 42%, white) !important; }
        .kiosk-theme .text-rose-500,
        .kiosk-theme .text-rose-600,
        .kiosk-theme .text-rose-700,
        .kiosk-theme .text-pink-500,
        .kiosk-theme .text-pink-600 { color: var(--brand-primary) !important; }
        .kiosk-theme .text-rose-300,
        .kiosk-theme .text-rose-400,
        .kiosk-theme .text-pink-300,
        .kiosk-theme .text-pink-400 { color: var(--brand-primary-muted) !important; }
        .kiosk-theme .ring-rose-100,
        .kiosk-theme .ring-pink-100 { --tw-ring-color: var(--brand-primary-soft) !important; }
        .kiosk-theme .ring-rose-200,
        .kiosk-theme .ring-pink-200 { --tw-ring-color: var(--brand-primary-line) !important; }
        .kiosk-theme .ring-rose-300,
        .kiosk-theme .ring-rose-400,
        .kiosk-theme .ring-rose-500,
        .kiosk-theme .ring-pink-300,
        .kiosk-theme .ring-pink-400 { --tw-ring-color: var(--brand-primary) !important; }
        .kiosk-theme .border-rose-100,
        .kiosk-theme .border-pink-100 { border-color: var(--brand-primary-soft) !important; }
        .kiosk-theme .border-rose-200,
        .kiosk-theme .border-pink-200 { border-color: var(--brand-primary-line) !important; }
        .kiosk-theme .border-rose-300,
        .kiosk-theme .border-rose-400,
        .kiosk-theme .border-rose-500,
        .kiosk-theme .border-pink-300,
        .kiosk-theme .border-pink-400 { border-color: var(--brand-primary) !important; }
        .kiosk-theme .shadow-rose-100,
        .kiosk-theme .shadow-pink-100,
        .kiosk-theme .shadow-rose-100\/40,
        .kiosk-theme .shadow-rose-100\/50,
        .kiosk-theme .shadow-rose-100\/60 { --tw-shadow-color: color-mix(in srgb, var(--brand-primary) 14%, transparent) !important; }
        .kiosk-theme .shadow-rose-200,
        .kiosk-theme .shadow-pink-200,
        .kiosk-theme .shadow-rose-200\/50 { --tw-shadow-color: var(--brand-primary-shadow) !important; }
        .kiosk-theme .shadow-rose-300,
        .kiosk-theme .shadow-pink-300 { --tw-shadow-color: color-mix(in srgb, var(--brand-primary) 34%, transparent) !important; }

        /* ---------- WARNA SUKSES (success: emerald & green) ---------- */
        .kiosk-theme .bg-emerald-500,
        .kiosk-theme .bg-green-500 { background-color: var(--brand-success) !important; }
        .kiosk-theme .bg-emerald-400,
        .kiosk-theme .bg-green-400 { background-color: var(--brand-success-muted) !important; }
        .kiosk-theme .bg-emerald-600,
        .kiosk-theme .bg-emerald-700,
        .kiosk-theme .bg-green-600 { background-color: var(--brand-success-dark) !important; }
        .kiosk-theme .bg-emerald-50,
        .kiosk-theme .bg-green-50 { background-color: var(--brand-success-soft) !important; }
        .kiosk-theme .bg-emerald-100,
        .kiosk-theme .bg-green-100 { background-color: color-mix(in srgb, var(--brand-success) 16%, white) !important; }
        .kiosk-theme .bg-emerald-200,
        .kiosk-theme .bg-green-200 { background-color: color-mix(in srgb, var(--brand-success) 26%, white) !important; }
        .kiosk-theme .text-emerald-500,
        .kiosk-theme .text-emerald-600,
        .kiosk-theme .text-emerald-700,
        .kiosk-theme .text-green-500,
        .kiosk-theme .text-green-600 { color: var(--brand-success) !important; }
        .kiosk-theme .text-emerald-300,
        .kiosk-theme .text-emerald-400,
        .kiosk-theme .text-green-300,
        .kiosk-theme .text-green-400 { color: var(--brand-success-muted) !important; }
        .kiosk-theme .ring-emerald-100,
        .kiosk-theme .ring-green-100 { --tw-ring-color: var(--brand-success-soft) !important; }
        .kiosk-theme .ring-emerald-200,
        .kiosk-theme .ring-green-200 { --tw-ring-color: var(--brand-success-line) !important; }
        .kiosk-theme .ring-emerald-300,
        .kiosk-theme .ring-emerald-400,
        .kiosk-theme .ring-emerald-500 { --tw-ring-color: var(--brand-success) !important; }
        .kiosk-theme .border-emerald-100,
        .kiosk-theme .border-green-100 { border-color: var(--brand-success-soft) !important; }
        .kiosk-theme .border-emerald-200,
        .kiosk-theme .border-green-200 { border-color: var(--brand-success-line) !important; }
        .kiosk-theme .border-emerald-300,
        .kiosk-theme .border-emerald-500 { border-color: var(--brand-success) !important; }
        .kiosk-theme .shadow-emerald-100,
        .kiosk-theme .shadow-green-100 { --tw-shadow-color: color-mix(in srgb, var(--brand-success) 18%, transparent) !important; }
        .kiosk-theme .shadow-emerald-200,
        .kiosk-theme .shadow-green-200 { --tw-shadow-color: var(--brand-success-shadow) !important; }
        .kiosk-theme .shadow-emerald-300 { --tw-shadow-color: color-mix(in srgb, var(--brand-success) 40%, transparent) !important; }

        /* ---------- WARNA SEKUNDER (secondary: violet, teal, blue, indigo, sky, cyan) ---------- */
        .kiosk-theme .bg-violet-500,
        .kiosk-theme .bg-teal-500,
        .kiosk-theme .bg-blue-500,
        .kiosk-theme .bg-indigo-500,
        .kiosk-theme .bg-sky-500,
        .kiosk-theme .bg-cyan-500 { background-color: var(--brand-secondary) !important; }
        .kiosk-theme .bg-violet-400,
        .kiosk-theme .bg-teal-400,
        .kiosk-theme .bg-blue-400,
        .kiosk-theme .bg-indigo-400 { background-color: var(--brand-secondary-muted) !important; }
        .kiosk-theme .bg-violet-600,
        .kiosk-theme .bg-violet-700,
        .kiosk-theme .bg-teal-600,
        .kiosk-theme .bg-blue-600,
        .kiosk-theme .bg-indigo-600 { background-color: var(--brand-secondary-dark) !important; }
        .kiosk-theme .bg-violet-50,
        .kiosk-theme .bg-teal-50,
        .kiosk-theme .bg-blue-50,
        .kiosk-theme .bg-indigo-50,
        .kiosk-theme .bg-sky-50,
        .kiosk-theme .bg-cyan-50 { background-color: var(--brand-secondary-soft) !important; }
        .kiosk-theme .bg-violet-100,
        .kiosk-theme .bg-teal-100,
        .kiosk-theme .bg-blue-100,
        .kiosk-theme .bg-indigo-100 { background-color: color-mix(in srgb, var(--brand-secondary) 16%, white) !important; }
        .kiosk-theme .bg-violet-200,
        .kiosk-theme .bg-teal-200,
        .kiosk-theme .bg-blue-200 { background-color: color-mix(in srgb, var(--brand-secondary) 26%, white) !important; }
        .kiosk-theme .text-violet-500,
        .kiosk-theme .text-violet-600,
        .kiosk-theme .text-violet-700,
        .kiosk-theme .text-teal-500,
        .kiosk-theme .text-teal-600,
        .kiosk-theme .text-blue-500,
        .kiosk-theme .text-blue-600,
        .kiosk-theme .text-blue-700,
        .kiosk-theme .text-indigo-500,
        .kiosk-theme .text-indigo-600,
        .kiosk-theme .text-sky-500,
        .kiosk-theme .text-cyan-500 { color: var(--brand-secondary) !important; }
        .kiosk-theme .text-violet-300,
        .kiosk-theme .text-violet-400,
        .kiosk-theme .text-teal-300,
        .kiosk-theme .text-teal-400,
        .kiosk-theme .text-blue-400 { color: var(--brand-secondary-muted) !important; }
        .kiosk-theme .ring-violet-100,
        .kiosk-theme .ring-blue-100 { --tw-ring-color: var(--brand-secondary-soft) !important; }
        .kiosk-theme .ring-violet-200,
        .kiosk-theme .ring-blue-200 { --tw-ring-color: var(--brand-secondary-line) !important; }
        .kiosk-theme .ring-violet-300,
        .kiosk-theme .ring-violet-400,
        .kiosk-theme .ring-violet-500,
        .kiosk-theme .ring-teal-500,
        .kiosk-theme .ring-blue-500 { --tw-ring-color: var(--brand-secondary) !important; }
        .kiosk-theme .border-violet-100,
        .kiosk-theme .border-blue-100 { border-color: var(--brand-secondary-soft) !important; }
        .kiosk-theme .border-violet-200,
        .kiosk-theme .border-blue-200 { border-color: var(--brand-secondary-line) !important; }
        .kiosk-theme .border-violet-300,
        .kiosk-theme .border-blue-300 { border-color: var(--brand-secondary) !important; }
        .kiosk-theme .shadow-violet-100,
        .kiosk-theme .shadow-blue-100 { --tw-shadow-color: color-mix(in srgb, var(--brand-secondary) 18%, transparent) !important; }
        .kiosk-theme .shadow-violet-200,
        .kiosk-theme .shadow-blue-200 { --tw-shadow-color: var(--brand-secondary-shadow) !important; }
        .kiosk-theme .shadow-violet-300 { --tw-shadow-color: color-mix(in srgb, var(--brand-secondary) 40%, transparent) !important; }

        /* ---------- WARNA AKSEN (accent: fuchsia & purple) ---------- */
        .kiosk-theme .bg-fuchsia-500,
        .kiosk-theme .bg-purple-500 { background-color: var(--brand-accent) !important; }
        .kiosk-theme .bg-fuchsia-400,
        .kiosk-theme .bg-purple-400 { background-color: var(--brand-accent-muted) !important; }
        .kiosk-theme .bg-fuchsia-600,
        .kiosk-theme .bg-purple-600 { background-color: var(--brand-accent-dark) !important; }
        .kiosk-theme .bg-fuchsia-50,
        .kiosk-theme .bg-purple-50 { background-color: var(--brand-accent-soft) !important; }
        .kiosk-theme .bg-fuchsia-100,
        .kiosk-theme .bg-purple-100 { background-color: color-mix(in srgb, var(--brand-accent) 16%, white) !important; }
        .kiosk-theme .bg-fuchsia-200,
        .kiosk-theme .bg-purple-200 { background-color: color-mix(in srgb, var(--brand-accent) 26%, white) !important; }
        .kiosk-theme .text-fuchsia-500,
        .kiosk-theme .text-fuchsia-600,
        .kiosk-theme .text-purple-500,
        .kiosk-theme .text-purple-600 { color: var(--brand-accent) !important; }
        .kiosk-theme .text-fuchsia-300,
        .kiosk-theme .text-fuchsia-400,
        .kiosk-theme .text-purple-400 { color: var(--brand-accent-muted) !important; }
        .kiosk-theme .ring-fuchsia-100 { --tw-ring-color: var(--brand-accent-soft) !important; }
        .kiosk-theme .ring-fuchsia-500 { --tw-ring-color: var(--brand-accent) !important; }
        .kiosk-theme .border-fuchsia-100 { border-color: var(--brand-accent-soft) !important; }
        .kiosk-theme .border-fuchsia-200 { border-color: var(--brand-accent-line) !important; }
        .kiosk-theme .border-fuchsia-300 { border-color: var(--brand-accent) !important; }
        .kiosk-theme .shadow-fuchsia-200 { --tw-shadow-color: var(--brand-accent-shadow) !important; }

        /* ---------- WARNA PERINGATAN (warning: amber, orange, yellow) ---------- */
        .kiosk-theme .bg-amber-500,
        .kiosk-theme .bg-orange-500,
        .kiosk-theme .bg-yellow-500 { background-color: var(--brand-warning) !important; }
        .kiosk-theme .bg-amber-400,
        .kiosk-theme .bg-orange-400 { background-color: var(--brand-warning-muted) !important; }
        .kiosk-theme .bg-amber-600,
        .kiosk-theme .bg-orange-600 { background-color: var(--brand-warning-dark) !important; }
        .kiosk-theme .bg-amber-50,
        .kiosk-theme .bg-orange-50,
        .kiosk-theme .bg-yellow-50 { background-color: var(--brand-warning-soft) !important; }
        .kiosk-theme .bg-amber-100,
        .kiosk-theme .bg-orange-100,
        .kiosk-theme .bg-yellow-100 { background-color: color-mix(in srgb, var(--brand-warning) 18%, white) !important; }
        .kiosk-theme .bg-amber-200,
        .kiosk-theme .bg-orange-200 { background-color: color-mix(in srgb, var(--brand-warning) 28%, white) !important; }
        .kiosk-theme .text-amber-500,
        .kiosk-theme .text-amber-600,
        .kiosk-theme .text-amber-700,
        .kiosk-theme .text-amber-800,
        .kiosk-theme .text-orange-500,
        .kiosk-theme .text-orange-600,
        .kiosk-theme .text-yellow-600 { color: var(--brand-warning) !important; }
        .kiosk-theme .text-amber-300,
        .kiosk-theme .text-amber-400 { color: var(--brand-warning-muted) !important; }
        .kiosk-theme .ring-amber-100,
        .kiosk-theme .ring-orange-100 { --tw-ring-color: var(--brand-warning-soft) !important; }
        .kiosk-theme .ring-amber-200,
        .kiosk-theme .ring-orange-200 { --tw-ring-color: var(--brand-warning-line) !important; }
        .kiosk-theme .ring-amber-400,
        .kiosk-theme .ring-amber-500 { --tw-ring-color: var(--brand-warning) !important; }
        .kiosk-theme .border-amber-100,
        .kiosk-theme .border-orange-100 { border-color: var(--brand-warning-soft) !important; }
        .kiosk-theme .border-amber-200,
        .kiosk-theme .border-orange-200 { border-color: var(--brand-warning-line) !important; }
        .kiosk-theme .border-amber-300,
        .kiosk-theme .border-amber-500 { border-color: var(--brand-warning) !important; }
        .kiosk-theme .shadow-amber-200,
        .kiosk-theme .shadow-orange-200 { --tw-shadow-color: var(--brand-warning-shadow) !important; }

        /* ---------- WARNA BAHAYA (danger: red) ---------- */
        .kiosk-theme .bg-red-500 { background-color: var(--brand-danger) !important; }
        .kiosk-theme .bg-red-400 { background-color: var(--brand-danger-muted) !important; }
        .kiosk-theme .bg-red-600,
        .kiosk-theme .bg-red-700 { background-color: var(--brand-danger-dark) !important; }
        .kiosk-theme .bg-red-50 { background-color: var(--brand-danger-soft) !important; }
        .kiosk-theme .bg-red-100 { background-color: color-mix(in srgb, var(--brand-danger) 16%, white) !important; }
        .kiosk-theme .bg-red-200 { background-color: color-mix(in srgb, var(--brand-danger) 26%, white) !important; }
        .kiosk-theme .text-red-400,
        .kiosk-theme .text-red-500,
        .kiosk-theme .text-red-600,
        .kiosk-theme .text-red-700,
        .kiosk-theme .text-red-800 { color: var(--brand-danger) !important; }
        .kiosk-theme .text-red-300 { color: var(--brand-danger-muted) !important; }
        .kiosk-theme .ring-red-100 { --tw-ring-color: var(--brand-danger-soft) !important; }
        .kiosk-theme .ring-red-200 { --tw-ring-color: var(--brand-danger-line) !important; }
        .kiosk-theme .ring-red-300,
        .kiosk-theme .ring-red-400,
        .kiosk-theme .ring-red-500 { --tw-ring-color: var(--brand-danger) !important; }
        .kiosk-theme .border-red-100 { border-color: var(--brand-danger-soft) !important; }
        .kiosk-theme .border-red-200 { border-color: var(--brand-danger-line) !important; }
        .kiosk-theme .border-red-300,
        .kiosk-theme .border-red-400,
        .kiosk-theme .border-red-500 { border-color: var(--brand-danger) !important; }
        .kiosk-theme .shadow-red-200 { --tw-shadow-color: var(--brand-danger-shadow) !important; }

        /* ---------- Tailwind gradient stops ---------- */
        .kiosk-theme .from-rose-400,
        .kiosk-theme .from-rose-500,
        .kiosk-theme .from-rose-600,
        .kiosk-theme .from-pink-400,
        .kiosk-theme .from-pink-500 { --tw-gradient-from: var(--brand-primary) !important; }
        .kiosk-theme .to-rose-500,
        .kiosk-theme .to-rose-600,
        .kiosk-theme .to-pink-400,
        .kiosk-theme .to-pink-500,
        .kiosk-theme .to-pink-600 { --tw-gradient-to: var(--brand-primary) !important; }
        .kiosk-theme .from-emerald-400,
        .kiosk-theme .from-emerald-500,
        .kiosk-theme .from-emerald-600,
        .kiosk-theme .from-green-400,
        .kiosk-theme .from-green-500 { --tw-gradient-from: var(--brand-success) !important; }
        .kiosk-theme .to-emerald-500,
        .kiosk-theme .to-emerald-600,
        .kiosk-theme .to-green-500 { --tw-gradient-to: var(--brand-success) !important; }
        .kiosk-theme .from-violet-400,
        .kiosk-theme .from-violet-500,
        .kiosk-theme .from-teal-400,
        .kiosk-theme .from-teal-500,
        .kiosk-theme .from-blue-400,
        .kiosk-theme .from-blue-500,
        .kiosk-theme .from-indigo-500,
        .kiosk-theme .from-sky-500,
        .kiosk-theme .from-cyan-500 { --tw-gradient-from: var(--brand-secondary) !important; }
        .kiosk-theme .to-violet-500,
        .kiosk-theme .to-violet-600,
        .kiosk-theme .to-teal-500,
        .kiosk-theme .to-teal-600,
        .kiosk-theme .to-blue-500,
        .kiosk-theme .to-blue-600,
        .kiosk-theme .to-indigo-600 { --tw-gradient-to: var(--brand-secondary) !important; }
        .kiosk-theme .from-fuchsia-400,
        .kiosk-theme .from-fuchsia-500,
        .kiosk-theme .from-purple-400,
        .kiosk-theme .from-purple-500 { --tw-gradient-from: var(--brand-accent) !important; }
        .kiosk-theme .via-fuchsia-400,
        .kiosk-theme .via-fuchsia-500,
        .kiosk-theme .via-purple-500 { --tw-gradient-via: var(--brand-accent) !important; }
        .kiosk-theme .to-fuchsia-500,
        .kiosk-theme .to-fuchsia-600,
        .kiosk-theme .to-purple-500 { --tw-gradient-to: var(--brand-accent) !important; }
        .kiosk-theme .from-amber-400,
        .kiosk-theme .from-amber-500,
        .kiosk-theme .from-orange-400,
        .kiosk-theme .from-yellow-400 { --tw-gradient-from: var(--brand-warning) !important; }
        .kiosk-theme .via-amber-400,
        .kiosk-theme .via-amber-500 { --tw-gradient-via: var(--brand-warning) !important; }
        .kiosk-theme .to-amber-500,
        .kiosk-theme .to-amber-600,
        .kiosk-theme .to-orange-500 { --tw-gradient-to: var(--brand-warning) !important; }
        .kiosk-theme .from-red-400,
        .kiosk-theme .from-red-500 { --tw-gradient-from: var(--brand-danger) !important; }
        .kiosk-theme .to-red-500,
        .kiosk-theme .to-red-600 { --tw-gradient-to: var(--brand-danger) !important; }
    }

    /* Gradien khusus yang dipakai tombol utama. */
    @media screen {
        .kiosk-theme .bg-gradient-to-r.from-rose-400.to-violet-500,
        .kiosk-theme .bg-gradient-to-br.from-rose-400.to-violet-500,
        .kiosk-theme .bg-gradient-to-r.from-rose-500.to-violet-600,
        .kiosk-theme .bg-gradient-to-br.from-rose-500.to-violet-600 {
            background-image: linear-gradient(120deg, var(--brand-primary), var(--brand-secondary)) !important;
        }
        .kiosk-theme .bg-gradient-to-r.from-emerald-500.to-teal-500,
        .kiosk-theme .bg-gradient-to-br.from-emerald-500.to-teal-500 {
            background-image: linear-gradient(120deg, var(--brand-primary), var(--brand-secondary)) !important;
        }
        .kiosk-theme .bg-gradient-to-r.from-violet-400.to-fuchsia-500,
        .kiosk-theme .bg-gradient-to-r.from-violet-500.to-fuchsia-600,
        .kiosk-theme .bg-gradient-to-r.from-rose-500.via-fuchsia-500.to-violet-500 {
            background-image: linear-gradient(120deg, var(--brand-secondary), var(--brand-accent)) !important;
        }
    }

    /* ---------- Admin panel ikut warna brand ---------- */
    @media screen {
        body.admin-theme {
            font-family: var(--brand-body-font);
        }
        .admin-theme .font-heading,
        .admin-theme h1, .admin-theme h2, .admin-theme h3,
        .admin-theme h4, .admin-theme h5, .admin-theme h6 {
            font-family: var(--brand-heading-font) !important;
        }
        .admin-theme .rounded-xl { border-radius: var(--brand-radius-xl) !important; }
        .admin-theme .rounded-2xl { border-radius: var(--brand-radius-2xl) !important; }
        .admin-theme .rounded-3xl { border-radius: var(--brand-radius-3xl) !important; }
        .admin-theme .rounded-\[2rem\] { border-radius: var(--brand-radius-3xl) !important; }
        .admin-theme .bg-rose-500 { background-color: var(--brand-primary) !important; }
        .admin-theme .hover\:bg-rose-600:hover { background-color: var(--brand-primary-dark) !important; }
        .admin-theme .bg-rose-50 { background-color: var(--brand-primary-soft) !important; }
        .admin-theme .hover\:bg-rose-100:hover { background-color: color-mix(in srgb, var(--brand-primary) 16%, white) !important; }
        .admin-theme .text-rose-700 { color: var(--brand-primary-dark) !important; }
        .admin-theme .bg-rose-500\/20 { background-color: color-mix(in srgb, var(--brand-primary) 20%, transparent) !important; }
        .admin-theme .bg-rose-500\/15 { background-color: color-mix(in srgb, var(--brand-primary) 15%, transparent) !important; }
        .admin-theme .bg-rose-500\/10 { background-color: color-mix(in srgb, var(--brand-primary) 10%, transparent) !important; }
        .admin-theme .text-rose-500,
        .admin-theme .text-rose-600 { color: var(--brand-primary) !important; }
        .admin-theme .text-rose-400 { color: var(--brand-primary-muted) !important; }
        .admin-theme .border-rose-200 { border-color: var(--brand-primary-line) !important; }
        .admin-theme .ring-rose-100 { --tw-ring-color: var(--brand-primary-soft) !important; }
        .admin-theme .focus\:ring-rose-400:focus { --tw-ring-color: var(--brand-primary) !important; }
        .admin-theme .shadow-rose-200 { --tw-shadow-color: var(--brand-primary-shadow) !important; }

        /* Aksen sekunder di panel admin (biru/cyan) dan aksen ungu. */
        .admin-theme .bg-blue-500,
        .admin-theme .bg-sky-500,
        .admin-theme .bg-cyan-500,
        .admin-theme .bg-teal-500,
        .admin-theme .bg-indigo-500 { background-color: var(--brand-secondary) !important; }
        .admin-theme .bg-blue-50,
        .admin-theme .bg-sky-50,
        .admin-theme .bg-cyan-50,
        .admin-theme .bg-teal-50,
        .admin-theme .bg-indigo-50 { background-color: var(--brand-secondary-soft) !important; }
        .admin-theme .bg-blue-100,
        .admin-theme .bg-sky-100,
        .admin-theme .bg-cyan-100,
        .admin-theme .bg-teal-100,
        .admin-theme .bg-indigo-100 { background-color: color-mix(in srgb, var(--brand-secondary) 16%, white) !important; }
        .admin-theme .text-blue-500,
        .admin-theme .text-blue-600,
        .admin-theme .text-blue-700,
        .admin-theme .text-sky-500,
        .admin-theme .text-sky-600,
        .admin-theme .text-cyan-500,
        .admin-theme .text-cyan-600,
        .admin-theme .text-teal-500,
        .admin-theme .text-teal-600,
        .admin-theme .text-indigo-500,
        .admin-theme .text-indigo-600 { color: var(--brand-secondary) !important; }
        .admin-theme .border-blue-100,
        .admin-theme .border-sky-100,
        .admin-theme .border-cyan-100,
        .admin-theme .border-teal-100,
        .admin-theme .border-indigo-100 { border-color: var(--brand-secondary-soft) !important; }
        .admin-theme .ring-blue-100,
        .admin-theme .ring-sky-100,
        .admin-theme .ring-cyan-100,
        .admin-theme .ring-teal-100,
        .admin-theme .ring-indigo-100 { --tw-ring-color: var(--brand-secondary-soft) !important; }

        .admin-theme .bg-violet-500,
        .admin-theme .bg-purple-500 { background-color: var(--brand-accent) !important; }
        .admin-theme .bg-violet-50,
        .admin-theme .bg-purple-50 { background-color: var(--brand-accent-soft) !important; }
        .admin-theme .bg-violet-100,
        .admin-theme .bg-purple-100 { background-color: color-mix(in srgb, var(--brand-accent) 16%, white) !important; }
        .admin-theme .text-violet-500,
        .admin-theme .text-violet-600,
        .admin-theme .text-violet-700,
        .admin-theme .text-purple-500,
        .admin-theme .text-purple-600,
        .admin-theme .text-purple-700 { color: var(--brand-accent) !important; }
        .admin-theme .border-violet-100,
        .admin-theme .border-purple-100 { border-color: var(--brand-accent-soft) !important; }
        .admin-theme .ring-violet-100,
        .admin-theme .ring-purple-100 { --tw-ring-color: var(--brand-accent-soft) !important; }
        .admin-theme .from-rose-400,
        .admin-theme .from-rose-500 { --tw-gradient-from: var(--brand-primary) !important; }
        .admin-theme .to-pink-400,
        .admin-theme .to-pink-500,
        .admin-theme .to-violet-500,
        .admin-theme .to-violet-600 { --tw-gradient-to: var(--brand-secondary) !important; }
        .admin-theme .via-pink-500 { --tw-gradient-via: var(--brand-accent) !important; }
        .admin-theme .to-fuchsia-500 { --tw-gradient-to: var(--brand-accent) !important; }
        /* Body login juga memakai gradient brand (bukan turunan .admin-theme). */
        .admin-theme.from-rose-500 { --tw-gradient-from: var(--brand-primary) !important; }
        .admin-theme.via-pink-500 { --tw-gradient-via: var(--brand-secondary) !important; }
        .admin-theme.to-fuchsia-500 { --tw-gradient-to: var(--brand-accent) !important; }
        .admin-theme .file\:bg-rose-50::file-selector-button { background-color: var(--brand-primary-soft) !important; }
        .admin-theme .file\:text-rose-600::file-selector-button { color: var(--brand-primary) !important; }
    }
</style>
