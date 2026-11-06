@extends('layouts.admin')

@section('title', 'Buat Voucher - Photobooth Admin')
@section('page-title', 'Buat Voucher')

@section('content')
<div class="max-w-3xl mx-auto">
    @include('admin.vouchers._form')
</div>
@endsection
