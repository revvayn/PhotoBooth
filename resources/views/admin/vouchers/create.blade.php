@extends('layouts.admin')

@section('title', 'Buat Voucher - '.\App\Support\Brand::get()['name'].' Admin')
@section('page-title', 'Buat Voucher')

@section('content')
<div class="max-w-6xl mx-auto">
    @include('admin.vouchers._form', ['type' => $type ?? 'cash'])
</div>
@endsection
