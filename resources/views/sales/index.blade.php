<!DOCTYPE html>
<html>
<head>
    <title>Sales History - Printing POS</title>

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
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        h1 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f1f3f5;
        }

        .btn {
            display: inline-block;
            padding: 8px 13px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .pagination {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="navbar">

    <h2>Printing POS</h2>

    <div class="nav-links">
        <a href="{{ route('pos.index') }}">POS</a>
        <a href="{{ route('products.index') }}">Services</a>
        <a href="{{ route('sales.index') }}">Sales History</a>
    </div>

</div>

<div class="container">

    <div class="card">

        <h1>Sales History</h1>

        <table>

            <thead>
                <tr>
                    <th>Transaction #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Cash</th>
                    <th>Change</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($sales as $sale)

                    <tr>

                        <td>
                            SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                        </td>

                        <td>
                            {{ $sale->created_at->format('M d, Y h:i A') }}
                        </td>

                        <td>
                            {{ $sale->items->sum('quantity') }}
                        </td>

                        <td>
                            ₱{{ number_format($sale->total_amount, 2) }}
                        </td>

                        <td>
                            ₱{{ number_format($sale->cash_received, 2) }}
                        </td>

                        <td>
                            ₱{{ number_format($sale->change_amount, 2) }}
                        </td>

                        <td>
                            <a
                                href="{{ route('sales.show', $sale) }}"
                                class="btn"
                            >
                                View
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7">
                            No transactions found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

        <div class="pagination">
            {{ $sales->links() }}
        </div>

    </div>

</div>

</body>
</html>