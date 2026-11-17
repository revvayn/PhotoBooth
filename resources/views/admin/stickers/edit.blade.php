@extends('layouts.admin')

@section('title', 'Edit Stiker - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Edit Stiker')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.stickers.index') }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-slate-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Edit: {{ $sticker->name }}</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.stickers.update', $sticker) }}" method="POST" enctype="multipart/form-data" class="space-y-5" data-sticker-form>
                @csrf
                @method('PUT')

                @include('admin.stickers._form', ['sticker' => $sticker])

                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.stickers.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-colors">Batal</a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-rose-500 to-pink-500 text-white text-sm font-semibold rounded-2xl shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:from-rose-600 hover:to-pink-600 transition-all duration-200">
                        Update Stiker
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
