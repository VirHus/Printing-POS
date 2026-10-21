@extends('layouts.app')

@section('title', 'Reports | Printing POS')

@section('page-title', 'Sales Reports')

@section('content')
@if(auth()->user()->role === 'Administrator' && \App\Models\SystemSetting::current()->moduleEnabled('profit', auth()->user()))<a href="{{ route('profit.index', request()->only('store_id', 'from', 'to')) }}" class="mb-5 inline-flex rounded-xl border border-teal-700 px-5 py-3 text-sm text-teal-300">Open profit & expense report →</a>@endif

<div class="card" style="margin-bottom:20px;">

    <div class="card-header">

        <div>
            <h2>Sales Report</h2>

            <p>
                Analyze sales performance for a selected period.
            </p>
        </div>

        <button
            onclick="window.print()"
            class="btn btn-secondary"
        >
            🖨 Print Report
        </button>

    </div>


    <form method="GET" action="{{ route('reports.index') }}">
        @if(auth()->user()->isAdministrator())
            <div class="form-group"><label for="reportStore">Store</label><select id="reportStore" name="store_id"><option value="">All stores</option>@foreach($stores as $store)<option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>{{ $store->name }}</option>@endforeach</select></div>
        @else
            <p class="mb-4 text-sm text-slate-400">{{ auth()->user()->store?->name ?? 'No store assigned' }}</p>
        @endif

        <div
            style="
                display:grid;
                grid-template-columns:1fr 1fr auto;
                gap:12px;
                align-items:end;
            "
        >

            <div class="form-group" style="margin:0;">

                <label>
                    From
                </label>

                <input
                    type="date"
                    name="from"
                    value="{{ $from }}"
                >

            </div>


            <div class="form-group" style="margin:0;">

                <label>
                    To
                </label>

                <input
                    type="date"
                    name="to"
                    value="{{ $to }}"
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary"
            >
                Generate Report
            </button>

        </div>

    </form>

</div>


<div class="stats-grid">

    <div class="stat-card">

        <div>

            <div class="stat-label">
                Total Revenue
            </div>

            <div class="stat-value">
                ₱{{ number_format($totalRevenue, 2) }}
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
                {{ $transactionCount }}
            </div>

        </div>

        <div class="stat-icon">
            ▤
        </div>

    </div>


    <div class="stat-card">

        <div>

            <div class="stat-label">
                Items / Services Sold
            </div>

            <div class="stat-value">
                {{ $totalItems }}
            </div>

        </div>

        <div class="stat-icon">
            ◫
        </div>

    </div>


    <div class="stat-card">

        <div>

            <div class="stat-label">
                Reporting Period
            </div>

            <div
                style="
                    font-size:14px;
                    font-weight:700;
                    margin-top:8px;
                "
            >
                {{ \Carbon\Carbon::parse($from)->format('M d, Y') }}
                -
                {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
            </div>

        </div>

        <div class="stat-icon">
            📅
        </div>

    </div>

</div>


<div
    style="
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:20px;
        margin-bottom:20px;
    "
>

    <div class="card">

        <div class="card-header">

            <div>

                <h2>Sales by Service</h2>

                <p>
                    Services generating revenue.
                </p>

            </div>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Service</th>
                        <th>Qty</th>
                        <th>Revenue</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($serviceSales as $service => $data)

                        <tr>

                            <td>
                                {{ $service }}
                            </td>

                            <td>
                                {{ $data['quantity'] }}
                            </td>

                            <td>
                                <strong>
                                    ₱{{ number_format($data['revenue'], 2) }}
                                </strong>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="3"
                                style="
                                    text-align:center;
                                    color:#9ca3af;
                                "
                            >
                                No sales found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    <div class="card">

        <div class="card-header">

            <div>

                <h2>Sales by Cashier</h2>

                <p>
                    Transactions handled by each cashier.
                </p>

            </div>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Cashier</th>
                        <th>Transactions</th>
                        <th>Revenue</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($cashierSales as $cashier => $data)

                        <tr>

                            <td>
                                {{ $cashier }}
                            </td>

                            <td>
                                {{ $data['transactions'] }}
                            </td>

                            <td>
                                <strong>
                                    ₱{{ number_format($data['revenue'], 2) }}
                                </strong>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="3"
                                style="
                                    text-align:center;
                                    color:#9ca3af;
                                "
                            >
                                No sales found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


<div class="card">

    <div class="card-header">

        <div>

            <h2>Transactions</h2>

            <p>
                All transactions within the selected period.
            </p>

        </div>

    </div>


    <div class="table-wrapper">

        <table>

            <thead>

                <tr>
                    <th>Store</th>
                    <th>Transaction</th>
                    <th>Date</th>
                    <th>Cashier</th>
                    <th>Payment</th>
                    <th>Total</th>
                </tr>

            </thead>

            <tbody>

                @forelse($sales as $sale)

                    <tr>

                        <td>{{ $sale->store?->name }}</td>
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
                            {{ $sale->user->name ?? 'Unknown' }}
                        </td>

                        <td>
                            <span class="badge badge-success">
                                {{ $sale->payment_method }}
                            </span>
                        </td>

                        <td>
                            <strong>
                                ₱{{ number_format(
                                    $sale->total_amount,
                                    2
                                ) }}
                            </strong>
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            style="
                                text-align:center;
                                padding:35px;
                                color:#9ca3af;
                            "
                        >
                            No transactions found
                            for this period.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


<style>

@media print {

    .sidebar,
    .topbar,
    .btn,
    form {
        display: none !important;
    }

    .main {
        margin-left: 0 !important;
        width: 100% !important;
    }

    .content {
        padding: 0 !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }

}

</style>

@endsection
