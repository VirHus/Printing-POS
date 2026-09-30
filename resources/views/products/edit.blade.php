@extends('layouts.app')

@section('title', 'Services | Printing POS')

@section('page-title', 'Services')

@section('content')

<!-- your existing Products page content here -->

@endsection

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Service - Printing POS</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .navbar {
            background: #1f2937;
            color: white;
            padding: 18px 30px;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            min-height: 100px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .error {
            color: #dc2626;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>

<div class="navbar">
    <strong>Printing POS</strong>
</div>

<div class="container">

    <h2>Edit Service</h2>

    @if($errors->any())

        <div class="error">

            <ul>

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form
        method="POST"
        action="{{ route('products.update', $product) }}">

        @csrf
        @method('PUT')

        <label>
            Category
        </label>

        <select name="category_id" required>

            @foreach($categories as $category)

                <option
                    value="{{ $category->id }}"
                    {{ $product->category_id == $category->id ? 'selected' : '' }}>

                    {{ $category->name }}

                </option>

            @endforeach

        </select>


        <label>
            Service Name
        </label>

        <input
            type="text"
            name="name"
            value="{{ old('name', $product->name) }}"
            required>


        <label>
            Description
        </label>

        <textarea name="description">{{ old('description', $product->description) }}</textarea>


        <label>
            Price
        </label>

        <input
            type="number"
            name="price"
            value="{{ old('price', $product->price) }}"
            step="0.01"
            min="0"
            required>


        <label>
            Unit
        </label>

        <select name="unit" required>

            <option value="page"
                {{ $product->unit == 'page' ? 'selected' : '' }}>
                Page
            </option>

            <option value="piece"
                {{ $product->unit == 'piece' ? 'selected' : '' }}>
                Piece
            </option>

            <option value="set"
                {{ $product->unit == 'set' ? 'selected' : '' }}>
                Set
            </option>

            <option value="copy"
                {{ $product->unit == 'copy' ? 'selected' : '' }}>
                Copy
            </option>

        </select>


        <button
            type="submit"
            class="btn btn-primary">

            Update Service

        </button>

        <a
            href="{{ route('products.index') }}"
            class="btn btn-secondary">

            Cancel

        </a>

    </form>

</div>

</body>

</html>