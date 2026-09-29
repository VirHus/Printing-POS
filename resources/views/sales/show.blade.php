<!DOCTYPE html>
<html>
<head>

    <title>
        Transaction SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
        }

        .receipt {
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin-bottom: 5px;
        }

        .transaction-info {
            margin-bottom: 20px;
        }

        .transaction-info p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 5px;
            border-bottom: 1px solid #ddd;
        }

        th {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
        }

        @media print {

            body {
                background: white;
            }

            .container {
                margin: 0;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
            }

            .actions {
                display: none;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="receipt">

        <div class="header">

            <h1>PRINTING POS</h1>

            <p>Sales Receipt</p>

            <strong>
                SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
            </strong>

        </div>

        <div class="transaction-info">

            <p>
                <strong>Date:</strong>
                {{ $sale->created_at->format('M d, Y h:i A') }}
            </p>

            <p>
                <strong>Cashier:</strong>

                {{ $sale->user->name ?? 'Cashier' }}
            </p>

            <p>
                <strong>Payment:</strong>
                {{ $sale->payment_method }}
            </p>

        </div>

        <table>

            <thead>

                <tr>
                    <th>Service</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th class="right">Subtotal</th>
                </tr>

            </thead>

            <tbody>

                @foreach ($sale->items as $item)

                    <tr>

                        <td>
                            {{ $item->product->name }}
                        </td>

                        <td>
                            {{ $item->quantity }}
                        </td>

                        <td>
                            ₱{{ number_format($item->price, 2) }}
                        </td>

                        <td class="right">
                            ₱{{ number_format($item->subtotal, 2) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

        <br>

        <table>

            <tr>
                <td>Total</td>

                <td class="right total">
                    ₱{{ number_format($sale->total_amount, 2) }}
                </td>
            </tr>

            <tr>
                <td>Cash Received</td>

                <td class="right">
                    ₱{{ number_format($sale->cash_received, 2) }}
                </td>
            </tr>

            <tr>
                <td>Change</td>

                <td class="right">
                    ₱{{ number_format($sale->change_amount, 2) }}
                </td>
            </tr>

        </table>

        <div class="header" style="margin-top: 30px;">

            <p>Thank you for your transaction!</p>

        </div>

    </div>

    <div class="actions">

        <button
            onclick="window.print()"
            class="btn"
        >
            Print Receipt
        </button>

        <a
            href="{{ route('sales.index') }}"
            class="btn btn-secondary"
        >
            Back to Sales
        </a>

    </div>

</div>

</body>
</html>