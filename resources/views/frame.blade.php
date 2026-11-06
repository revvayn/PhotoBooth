@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Pilih Frame — '.$brand['name'])

@section('content')
    @include('partials.frame', ['step' => 2, 'fromSpa' => false])
@endsection
