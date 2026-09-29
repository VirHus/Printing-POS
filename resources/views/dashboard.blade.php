<!DOCTYPE html>
<html>
<head>

    <title>Dashboard - Printing POS</title>

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

        .nav-links a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .welcome h1 {
            margin-bottom: 5px;
        }

        .welcome p {
            color: #666;
        }

        .cards {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, .08);
        }

        .card-title {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 30px;
            font-weight: bold;
        }

        .sales-card {
            color: #16a34a;
        }

        .transaction-card {
            color: #2563eb;
        }

        .service-card {
            color: #9333ea;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, .08);
        }

        .section h2 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .quick-actions {
            margin-top: 25px;

            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 20px;
        }

        @media (max-width: 800px) {

            .cards {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .nav-links a {
                margin-left: 0;
                margin-right: 15px;
            }

            table {
                font-size: 13px;
            }

        }

    </style>

</head>

<body>

<div class="navbar">

    <h2>Printing POS</h2>

    <div class="nav-links">

        <a href="{{ route('dashboard') }}">
            Dashboard
        </a>

        <a href="{{ route('pos.index') }}">
            POS
        </a>

        <a href="{{ route('products.index') }}">
            Services
        </a>

        <a href="{{ route('sales.index') }}">
            Sales History
        </a>

    </div>

</div>


<div class="container">

    <div class="welcome">

        <h1>Dashboard</h1>

        <p>
            Welcome to the Printing Services Point of Sale System.
        </p>

    </div>


    <!-- SUMMARY CARDS -->

    <div class="cards">

        <div class="card">

            <div class="card-title">
                Today's Sales
            </div>

            <div class="card-value sales-card">

                ₱{{ number_format($todaySales, 2) }}

            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Today's Transactions
            </div>

            <div class="card-value transaction-card">

                {{ $todayTransactions }}

            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Active Services
            </div>

            <div class="card-value service-card">

                {{ $activeServices }}

            </div>

        </div>

    </div>


    <!-- RECENT TRANSACTIONS -->

    <div class="section">

        <h2>
            Recent Transactions
        </h2>

        <table>

            <thead>

                <tr>

                    <th>
                        Transaction #
                    </th>

                    <th>
                        Date
                    </th>

                    <th>
                        Total
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse ($recentSales as $sale)

                    <tr>

                        <td>

                            SALE-{{ str_pad(
                                $sale->id,
                                6,
                                '0',
                                STR_PAD_LEFT
                            ) }}

                        </td>

                        <td>

                            {{ $sale->created_at->format(
                                'M d, Y h:i A'
                            ) }}

                        </td>

                        <td>

                            ₱{{ number_format(
                                $sale->total_amount,
                                2
                            ) }}

                        </td>

                        <td>

                            <a
                                href="{{ route(
                                    'sales.show',
                                    $sale
                                ) }}"
                                class="btn"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="4"
                            class="empty"
                        >
                            No transactions yet.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>


        <!-- QUICK ACTIONS -->

        <div class="quick-actions">

            <a
                href="{{ route('pos.index') }}"
                class="btn"
            >
                + New Sale
            </a>

            <a
                href="{{ route('products.index') }}"
                class="btn"
            >
                Manage Services
            </a>

            <a
                href="{{ route('sales.index') }}"
                class="btn"
            >
                View All Sales
            </a>

        </div>

    </div>

</div>

</body>
</html>