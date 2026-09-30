@extends('layouts.app')

@section('title', 'Dashboard | Printing POS')

@section('page-title', 'Dashboard')

@section('content')

<div style="margin-bottom:25px;">

    <h2 style="font-size:18px;">
        Welcome, {{ auth()->user()->name }}!
    </h2>

    <p style="color:#6b7280;font-size:13px;margin-top:5px;">
        Here's what's happening with your printing business today.
    </p>

</div>


<div class="stats-grid">

    <div class="stat-card">

        <div>
            <div class="stat-label">
                Today's Sales
            </div>

            <div class="stat-value">
                ₱{{ number_format($todaySales, 2) }}
            </div>
        </div>

        <div class="stat-icon">
            ₱
        </div>

    </div>


    <div class="stat-card">

        <div>
            <div class="stat-label">
                Transactions
            </div>

            <div class="stat-value">
                {{ $todayTransactions }}
            </div>
        </div>

        <div class="stat-icon">
            ▤
        </div>

    </div>


    <div class="stat-card">

        <div>
            <div class="stat-label">
                Active Services
            </div>

            <div class="stat-value">
                {{ $activeServices }}
            </div>
        </div>

        <div class="stat-icon">
            ◫
        </div>

    </div>


    <div class="stat-card">

        <div>
            <div class="stat-label">
                Your Role
            </div>

            <div class="stat-value"
                 style="font-size:18px;">
                {{ auth()->user()->role }}
            </div>
        </div>

        <div class="stat-icon">
            ♙
        </div>

    </div>

</div>


<div class="card" style="margin-bottom:25px;">

    <div class="card-header">

        <div>
            <h2>Quick Actions</h2>

            <p>
                Quickly access frequently used functions.
            </p>
        </div>

    </div>


    <div class="quick-grid">

        <a
            href="{{ route('pos.index') }}"
            class="quick-action"
        >

            <div class="quick-action-icon">
                🛒
            </div>

            <strong>New Sale</strong>

            <span>
                Start a new transaction
            </span>

        </a>


        <a
            href="{{ route('sales.index') }}"
            class="quick-action"
        >

            <div class="quick-action-icon">
                🧾
            </div>

            <strong>Transactions</strong>

            <span>
                View sales history
            </span>

        </a>


        @if(auth()->user()->role === 'Administrator')

            <a
                href="{{ route('products.index') }}"
                class="quick-action"
            >

                <div class="quick-action-icon">
                    ⚙️
                </div>

                <strong>Services</strong>

                <span>
                    Manage prices and services
                </span>

            </a>


            <a
                href="{{ route('reports.index') }}"
                class="quick-action"
            >

                <div class="quick-action-icon">
                    📊
                </div>

                <strong>Reports</strong>

                <span>
                    View business reports
                </span>

            </a>

        @endif

    </div>

</div>


<div class="card">

    <div class="card-header">

        <div>
            <h2>Recent Transactions</h2>

            <p>
                Latest sales recorded in the system.
            </p>
        </div>

        <a
            href="{{ route('sales.index') }}"
            class="btn btn-secondary"
        >
            View All
        </a>

    </div>


    <div class="table-wrapper">

        <table>

            <thead>

                <tr>
                    <th>Transaction</th>
                    <th>Date</th>
                    <th>Cashier</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                @forelse($recentSales as $sale)

                    <tr>

                        <td>
                            <strong>
                                SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                            </strong>
                        </td>

                        <td>
                            {{ $sale->created_at->format('M d, Y h:i A') }}
                        </td>

                        <td>
                            {{ $sale->user->name ?? 'Cashier' }}
                        </td>

                        <td>
                            <strong>
                                ₱{{ number_format($sale->total_amount, 2) }}
                            </strong>
                        </td>

                        <td>

                            <a
                                href="{{ route('sales.show', $sale) }}"
                                class="btn btn-secondary"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            style="
                                text-align:center;
                                padding:35px;
                                color:#9ca3af;
                            "
                        >
                            No transactions yet.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection