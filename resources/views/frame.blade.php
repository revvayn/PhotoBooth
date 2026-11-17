@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Pilih Frame — '.$brand['name'])

@section('content')
    @if (($metode ?? session('metode')) === 'voucher' && ! empty($voucherQuotas ?? []))
        @include('partials.voucher-frame', ['step' => 2, 'fromSpa' => false, 'voucherQuotas' => $voucherQuotas ?? []])
    @else
        @include('partials.frame', ['step' => 2, 'fromSpa' => false])
    @endif
@endsection
