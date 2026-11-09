@extends('layouts.admin')

@section('title', 'Edit Voucher - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Edit Voucher')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4 flex items-center gap-2">
        <span class="inline-flex rounded-lg bg-slate-900 text-white px-2.5 py-1 text-xs font-bold tracking-wider">{{ $voucher->code }}</span>
        <span class="text-sm text-slate-500">{{ $voucher->used_count }} dari {{ $voucher->max_uses }} kali terpakai</span>
    </div>
    @include('admin.vouchers._form')
</div>
@endsection
