@extends('layouts.app')

@section('title', 'Add Service | Printing POS')
@section('page-title', 'Services')

@section('content')
    @include('products._form', ['product' => null, 'categories' => $categories])
@endsection
