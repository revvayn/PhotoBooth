@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Pembayaran — '.$brand['name'])

@section('content')
    @include('partials.metode', ['step' => 1, 'fromSpa' => false])
@endsection
