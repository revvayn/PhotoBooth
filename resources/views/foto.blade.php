@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Sesi Foto — '.$brand['name'])

@section('content')
    @include('partials.foto', ['step' => 4, 'fromSpa' => false])
@endsection
