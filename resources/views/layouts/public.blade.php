<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $brand = \App\Support\Brand::get();
    @endphp
    <title>@yield('title', 'Unduh Hasil — '.$brand['name'])</title>
    @include('partials.fonts', ['brand' => $brand])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.branding', ['brand' => $brand])
    @yield('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 font-sans antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse 80% 50% at 50% 0%, color-mix(in srgb, {{ $brand['primary'] }} 12%, transparent), transparent 70%);"></div>
    </div>

    <main class="relative z-10 mx-auto w-full max-w-3xl px-4 py-8 sm:py-12">
        @yield('content')
    </main>
</body>
</html>
