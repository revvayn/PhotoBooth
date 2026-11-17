@extends('layouts.admin')

@section('title', 'Edit Ukuran - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Edit Ukuran')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.sizes.index') }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-slate-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Edit: {{ $size->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Frame yang memakai ukuran ini akan otomatis mengikuti harga baru bila tidak punya harga khusus.</p>
            @if ($size->frames()->exists())
                <p class="text-xs text-amber-600 mt-1">Dimensi terkunci karena sudah ada frame yang memakai ukuran ini. Buat ukuran baru bila perlu dimensi berbeda.</p>
            @endif
        </div>
        <div class="p-6">
            @include('admin.sizes._form', [
                'action' => route('admin.sizes.update', $size),
                'method' => 'PUT',
                'submitLabel' => 'Update Ukuran',
            ])
        </div>
    </div>
</div>
@endsection
