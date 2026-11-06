@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Konfirmasi QRIS — '.$brand['name'])

@section('content')
    @include('partials.pembayaran', ['step' => 3, 'fromSpa' => false])
@endsection
