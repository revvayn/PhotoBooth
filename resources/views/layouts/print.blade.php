<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $brand = \App\Support\Brand::get();
        $radiusMap = [
            'sm' => ['xl' => '0.5rem', '2xl' => '0.65rem', '3xl' => '0.85rem'],
            'md' => ['xl' => '0.75rem', '2xl' => '1rem', '3xl' => '1.5rem'],
            'lg' => ['xl' => '1rem', '2xl' => '1.25rem', '3xl' => '1.75rem'],
            'xl' => ['xl' => '1.25rem', '2xl' => '1.5rem', '3xl' => '2rem'],
        ];
        $radius = $radiusMap[$brand['radius']] ?? $radiusMap['md'];
    @endphp
    <title>@yield('title', 'Print - '.$brand['name'].' Admin')</title>
    @vite(['resources/css/app.css'])
    @include('partials.fonts', ['brand' => $brand])
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
            --brand-radius-xl: {{ $radius['xl'] }};
            --brand-radius-2xl: {{ $radius['2xl'] }};
            --brand-radius-3xl: {{ $radius['3xl'] }};
        }
        body {
            font-family: '{{ $brand['body_font'] }}', sans-serif;
            margin: 0;
            padding: 0;
            background: #f9fafb;
            color: #1f2937;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: '{{ $brand['heading_font'] }}', sans-serif;
        }
        .print-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .rounded-xl { border-radius: var(--brand-radius-xl) !important; }
        .rounded-2xl { border-radius: var(--brand-radius-2xl) !important; }
        .rounded-3xl { border-radius: var(--brand-radius-3xl) !important; }
        .rounded-\[2rem\] { border-radius: var(--brand-radius-3xl) !important; }
        /* app.css kiosk menyembunyikan #print-sheet di layar. Halaman cetak
           memakai id yang sama supaya aturan cetak kiosk tidak menyembunyikan
           konten invoice/voucher; tampilkan kembali secara eksplisit. */
        #print-sheet {
            display: block !important;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .no-print, .no-print * {
                display: none !important;
            }
            .print-container {
                max-width: 100%;
                margin: 0;
                padding: 12mm;
            }
            .no-page-break {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-container" id="print-sheet">
        @yield('content')
    </div>

    <div class="no-print text-center mt-6">
        <button onclick="window.print()" class="px-6 py-2 rounded-lg text-white font-semibold transition" style="background: var(--brand-primary);">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"/></svg>
            Cetak
        </button>
        <a href="{{ route('admin.vouchers.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition ml-2">Kembali</a>
    </div>
</body>
</html>
