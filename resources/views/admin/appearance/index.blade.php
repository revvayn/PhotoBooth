@extends('layouts.admin')

@section('title', 'Tampilan - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Tampilan')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <form method="POST" action="{{ route('admin.appearance.update') }}" enctype="multipart/form-data" class="space-y-6" id="appearance-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
            <div class="space-y-6">
                {{-- Identitas brand --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="font-heading text-lg font-bold text-slate-800">Identitas Brand</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Nama, logo, dan teks sambutan yang tampil di layar pelanggan.</p>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="brand_name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Brand <span class="text-red-500">*</span></label>
                            <input type="text" id="brand_name" name="brand_name" value="{{ old('brand_name', $settings['brand_name']) }}" required maxlength="50"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('brand_name') border-red-300 @enderror"
                                   data-preview-name>
                            @error('brand_name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="brand_tagline" class="block text-sm font-medium text-slate-700 mb-1.5">Tagline</label>
                            <input type="text" id="brand_tagline" name="brand_tagline" value="{{ old('brand_tagline', $settings['brand_tagline']) }}" maxlength="100"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('brand_tagline') border-red-300 @enderror"
                                   data-preview-tagline>
                            @error('brand_tagline')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="brand_welcome" class="block text-sm font-medium text-slate-700 mb-1.5">Teks Sambutan</label>
                            <textarea id="brand_welcome" name="brand_welcome" rows="2" maxlength="500"
                                      class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('brand_welcome') border-red-300 @enderror">{{ old('brand_welcome', $settings['brand_welcome']) }}</textarea>
                            @error('brand_welcome')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="brand_footer" class="block text-sm font-medium text-slate-700 mb-1.5">Teks Footer</label>
                            <input type="text" id="brand_footer" name="brand_footer" value="{{ old('brand_footer', $settings['brand_footer']) }}" maxlength="100"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 @error('brand_footer') border-red-300 @enderror">
                            @error('brand_footer')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Logo</label>
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                                    @if (! empty($settings['brand_logo']))
                                        <img src="{{ asset('storage/'.$settings['brand_logo']) }}" alt="Logo" class="w-full h-full object-contain p-1">
                                    @else
                                        <x-icon name="camera" class="w-6 h-6 text-slate-300" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" id="brand_logo" name="brand_logo" accept="image/png,image/jpeg,image/webp"
                                           class="w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100">
                                    @if (! empty($settings['brand_logo']))
                                        <label class="mt-2 flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
                                            <input type="checkbox" name="remove_brand_logo" value="1" class="rounded border-gray-300 text-rose-500 focus:ring-rose-400">
                                            Hapus logo
                                        </label>
                                    @endif
                                </div>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400">PNG/JPG/WebP, maksimal 4 MB. Disarankan PNG transparan.</p>
                            @error('brand_logo')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Gambar latar --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="font-heading text-lg font-bold text-slate-800">Gambar Latar (opsional)</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Jika diisi, gambar ini menutupi latar kiosk.</p>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-28 h-20 rounded-2xl bg-gray-50 border border-gray-200 overflow-hidden flex items-center justify-center shrink-0">
                                @if (! empty($settings['brand_bg_image']))
                                    <img src="{{ asset('storage/'.$settings['brand_bg_image']) }}" alt="Latar" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[11px] text-slate-300">Tanpa gambar</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" id="brand_bg_image" name="brand_bg_image" accept="image/png,image/jpeg,image/webp"
                                       class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-2xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100">
                                <p class="mt-1.5 text-xs text-slate-400">PNG/JPG/WebP, maksimal 8 MB.</p>
                                @if (! empty($settings['brand_bg_image']))
                                    <label class="mt-2 flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
                                        <input type="checkbox" name="remove_brand_bg_image" value="1" class="rounded border-gray-300 text-rose-500 focus:ring-rose-400">
                                        Hapus gambar latar
                                    </label>
                                @endif
                                @error('brand_bg_image')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Pratinjau --}}
            <div class="lg:sticky lg:top-24">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Pratinjau</p>
                    <div id="appearance-preview" class="rounded-2xl p-4 min-h-[320px] flex flex-col justify-between overflow-hidden relative border border-slate-200"
                         style="background: linear-gradient(180deg, {{ $settings['brand_bg_from'] }}, {{ $settings['brand_bg_via'] }} 50%, {{ $settings['brand_bg_to'] }});">
                        <div class="flex items-center gap-2.5 relative z-10">
                            <div id="preview-logo" class="w-11 h-11 flex items-center justify-center shadow-lg shrink-0" style="background: linear-gradient(135deg, {{ $settings['brand_primary'] }}, {{ $settings['brand_secondary'] }}); border-radius: 0.75rem;">
                                @if (! empty($settings['brand_logo']))
                                    <img src="{{ asset('storage/'.$settings['brand_logo']) }}" alt="" class="w-full h-full object-contain p-1.5" style="border-radius: 0.75rem;">
                                @else
                                    <x-icon name="camera" class="w-5 h-5 text-white" />
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p id="preview-name" class="font-bold leading-none truncate text-slate-900">{{ $settings['brand_name'] }}</p>
                                <p id="preview-tagline" class="text-[10px] text-slate-500 leading-tight truncate">{{ $settings['brand_tagline'] }}</p>
                            </div>
                        </div>

                        <div class="relative z-10 mt-5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1" id="preview-badge">Siap dipakai</p>
                            <p class="text-sm font-semibold text-slate-800 mb-2" id="preview-method-title">Pilih Pembayaran</p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-white ring-1 ring-slate-200 p-2 text-center text-[11px] font-semibold text-slate-700 shadow-sm" style="border-radius: 0.75rem;">QRIS</div>
                                <div class="bg-white ring-1 ring-slate-200 p-2 text-center text-[11px] font-semibold text-slate-700 shadow-sm" style="border-radius: 0.75rem;">Voucher</div>
                            </div>
                            <div id="preview-button" class="mt-3 rounded-full py-2 text-center text-xs font-bold text-white shadow-lg" style="background: linear-gradient(120deg, {{ $settings['brand_primary'] }}, {{ $settings['brand_secondary'] }});">
                                Mulai
                            </div>
                        </div>
                        <div class="absolute -right-8 -bottom-10 w-32 h-32 rounded-full opacity-20 blur-2xl" id="preview-orb" style="background: {{ $settings['brand_accent'] }};"></div>
                    </div>
                    <p class="mt-3 text-[11px] leading-relaxed text-slate-400">Pratinjau mengikuti identitas brand. Perubahan langsung terlihat di layar kiosk setelah disimpan.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 rounded-2xl bg-rose-500 text-white font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
                Simpan Tampilan
            </button>
        </div>
    </form>
</div>

<script>
    (() => {
        const preview = document.getElementById('appearance-preview');
        if (!preview) return;

        const name = document.querySelector('[data-preview-name]');
        const tagline = document.querySelector('[data-preview-tagline]');

        const paint = () => {
            document.getElementById('preview-name').textContent = name?.value || 'Photobooth';
            document.getElementById('preview-tagline').textContent = tagline?.value || '';
        };

        name?.addEventListener('input', paint);
        tagline?.addEventListener('input', paint);

        paint();
    })();
</script>
@endsection
