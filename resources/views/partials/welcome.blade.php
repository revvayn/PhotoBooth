<div class="flex-1 flex flex-col items-center justify-center text-center py-10">
    <div class="animate-bounce-slow w-28 h-28 rounded-[2rem] bg-gradient-to-br from-rose-400 via-fuchsia-500 to-violet-500 flex items-center justify-center shadow-xl shadow-rose-300 mb-6">
        <span class="text-6xl"></span>
    </div>

    <h1 class="font-display font-extrabold text-5xl sm:text-6xl bg-gradient-to-r from-rose-500 to-violet-600 bg-clip-text text-transparent mb-3">
        Photobooth
    </h1>
    <p class="text-slate-500 max-w-md mb-10">
        Abadikan momen-momen terbaikmu bareng teman dan keluarga. Foto langsung jadi, tinggal cetak!
    </p>

    <form method="POST" action="{{ route('mulai') }}">
        @csrf
        <button type="submit" class="group relative px-16 py-5 rounded-full bg-gradient-to-r from-rose-500 to-violet-600 text-white text-2xl font-display font-bold shadow-xl shadow-rose-300 hover:shadow-2xl hover:scale-105 transition-all">
            Mulai
            <span class="absolute inset-0 rounded-full bg-white/20 opacity-0 group-hover:animate-ping-slow group-hover:opacity-100 transition-opacity"></span>
        </button>
    </form>

    <div class="grid grid-cols-3 gap-6 mt-14">
        <div class="flex flex-col items-center gap-2">
            <span class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600"></span>
            <span class="text-xs font-medium text-slate-500">Cek Pembayaran</span>
        </div>
        <div class="flex flex-col items-center gap-2">
            <span class="w-12 h-12 rounded-2xl bg-rose-100 flex items-center justify-center text-rose-600"></span>
            <span class="text-xs font-medium text-slate-500">Sesi Foto</span>
        </div>
        <div class="flex flex-col items-center gap-2">
            <span class="w-12 h-12 rounded-2xl bg-violet-100 flex items-center justify-center text-violet-600"></span>
            <span class="text-xs font-medium text-slate-500">Print & Download</span>
        </div>
    </div>
</div>
