@extends('layouts.app')

@php
    $brand = \App\Support\Brand::get();
@endphp

@section('title', 'Review Foto — '.$brand['name'])

@section('content')
    @include('partials.review', ['step' => 6])
@endsection
