@extends('layouts.public')

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                <x-icon name="photo" class="h-7 w-7" />
            </div>
            <h1 class="font-heading text-2xl font-extrabold text-slate-900">Unduh Hasil Foto</h1>
            <p class="mt-2 text-sm text-slate-500">
                Masukkan token yang tersedia di layar kiosk, atau pindai QR yang diberikan petugas.
            </p>
        </div>

        <form method="GET" action="{{ route('hasil.index') }}" class="mx-auto mt-7 max-w-md space-y-3">
            <label for="token" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Token download</label>
            <input type="text" id="token" name="token" required autocomplete="off"
                   placeholder="Tempel token di sini"
                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm text-slate-800 outline-none focus:ring-2 focus:ring-rose-400">
            <button type="submit" class="w-full rounded-2xl bg-rose-500 px-6 py-3 text-sm font-bold text-white shadow-md shadow-rose-200 transition hover:bg-rose-600">
                Buka Hasil Saya
            </button>
        </form>

        <p class="mt-6 text-center text-[11px] text-slate-400">
            Token dibuat otomatis setiap foto selesai diambil dan tersimpan pada transaksi pembayaran.
        </p>
    </div>
@endsection
