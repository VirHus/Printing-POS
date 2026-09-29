<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Services - Printing POS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .navbar {
            background: #1f2937;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            margin: 0;
            font-size: 22px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            background: #dcfce7;
            color: #166534;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        th {
            background: #374151;
            color: white;
            text-align: left;
            padding: 14px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f9fafb;
        }

        .actions {
            display: flex;
            gap: 6px;
        }

        .status-active {
            color: #16a34a;
            font-weight: bold;
        }

        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }

        .price {
            font-weight: bold;
        }

        form {
            display: inline;
        }
    </style>
</head>

<body>

<div class="navbar">
    <h1>Printing POS</h1>
    <span>Service Management</span>
</div>

<div class="container">

    <div class="header">
        <h2>Services</h2>

        <a href="{{ route('products.create') }}"
           class="btn btn-primary">
            + Add Service
        </a>
    </div>

    @if(session('success'))
        <div class="alert">
            {{ session('success') }}
        </div>
    @endif

    <table>

        <thead>
            <tr>
                <th>#</th>
                <th>Service</th>
                <th>Category</th>
                <th>Price</th>
                <th>Unit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

        @forelse($products as $product)

            <tr>

                <td>
                    {{ $product->id }}
                </td>

                <td>
                    <strong>{{ $product->name }}</strong>

                    @if($product->description)
                        <br>
                        <small>
                            {{ $product->description }}
                        </small>
                    @endif
                </td>

                <td>
                    {{ $product->category->name }}
                </td>

                <td class="price">
                    ₱{{ number_format($product->price, 2) }}
                </td>

                <td>
                    {{ $product->unit }}
                </td>

                <td>

                    @if($product->is_active)

                        <span class="status-active">
                            Active
                        </span>

                    @else

                        <span class="status-inactive">
                            Inactive
                        </span>

                    @endif

                </td>

                <td>

                    <div class="actions">

                        <a
                            href="{{ route('products.edit', $product) }}"
                            class="btn btn-warning">
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="{{ route('products.toggle', $product) }}">

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn {{ $product->is_active ? 'btn-danger' : 'btn-success' }}">

                                {{ $product->is_active ? 'Disable' : 'Enable' }}

                            </button>

                        </form>

                        <form
                            method="POST"
                            action="{{ route('products.destroy', $product) }}"
                            onsubmit="return confirm('Delete this service?');">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-danger">

                                Delete

                            </button>

                        </form>

                    </div>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7" style="text-align:center;">
                    No services found.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

</div>

</body>
</html>