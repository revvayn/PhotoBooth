@extends('layouts.admin')

@section('title', 'Profil - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Profil Admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h2 class="font-heading text-lg font-bold text-slate-800 mb-4">Informasi Akun</h2>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="100"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('name') border-red-300 @enderror">
                @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('email') border-red-300 @enderror">
                @error('email')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="border-t border-slate-100 pt-4">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Ganti Password (opsional)</p>
                <div class="space-y-4">
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password Baru</label>
                        <input type="password" id="password" name="password" minlength="6"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400 @error('password') border-red-300 @enderror">
                        @error('password')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-800 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-400">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Kembali</a>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-600">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <div class="mt-4 flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-6 py-4">
        <div>
            <p class="text-sm font-semibold text-slate-800">Sesi</p>
            <p class="text-xs text-slate-500">Keluar dari panel admin.</p>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST" onsubmit="return confirm('Yakin ingin logout?');">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </div>
</div>
@endsection