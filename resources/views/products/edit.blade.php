@extends('layouts.app')

@section('title', 'Edit Service | Printing POS')
@section('page-title', 'Services')

@section('content')
    @include('products._form', ['product' => $product, 'categories' => $categories])
@endsection
