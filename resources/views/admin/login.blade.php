<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $brand = \App\Support\Brand::get();
    @endphp
    <title>Login - {{ $brand['name'] }} Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.fonts', ['brand' => $brand])
    @include('partials.branding', ['brand' => $brand])
</head>
<body class="admin-theme min-h-screen flex items-center justify-center p-4 relative overflow-hidden"
      style="background: linear-gradient(180deg, {{ $brand['bg_from'] }} 0%, {{ $brand['bg_via'] }} 48%, {{ $brand['bg_to'] }} 100%);">

    <div aria-hidden="true" class="pointer-events-none fixed inset-0" style="background: radial-gradient(ellipse 80% 55% at 50% 0%, color-mix(in srgb, {{ $brand['primary'] }} 16%, transparent), transparent 72%), radial-gradient(ellipse 80% 55% at 50% 100%, color-mix(in srgb, {{ $brand['secondary'] }} 14%, transparent), transparent 72%);"></div>

    <div class="relative z-10 w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-900/10 border border-slate-200 p-8">
            {{-- Logo & Title --}}
            <div class="text-center mb-8">
                <x-brand-mark size="w-16 h-16" rounded="rounded-2xl" icon="w-8 h-8" class="mx-auto mb-4" />
                <h1 class="font-heading text-2xl font-bold text-slate-800">{{ $brand['name'] }} Admin</h1>
                <p class="text-slate-500 text-sm mt-1">Masuk ke panel administrasi</p>
            </div>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Login Form --}}
            <form action="{{ route('admin.login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200"
                           placeholder="admin@photobooth.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent transition-all duration-200"
                           placeholder="Masukkan password">
                </div>

                <button type="submit"
                        class="w-full py-3.5 text-white font-semibold rounded-xl shadow-lg transition-all duration-200 hover:brightness-105 active:scale-[0.98] focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-100"
                        style="background: linear-gradient(120deg, var(--brand-primary), var(--brand-secondary)); box-shadow: 0 16px 30px -16px var(--brand-primary-shadow);">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">&copy; {{ date('Y') }} {{ $brand['name'] }} Admin Panel</p>
    </div>

    @include('partials.toast')
</body>
</html>
