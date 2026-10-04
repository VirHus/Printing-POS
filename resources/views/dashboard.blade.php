@extends('layouts.app')

@section('title', 'Dashboard | Printing POS')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .brand { height: 72px; }
    .brand-text h2 { font-size: 18px; }
    .menu-title { font-weight: 700; }
    .topbar { height: 64px; }
    .page-title h1 { color: #f4f8fb; }
    .content { padding: 24px 28px 32px; }
    a.dashboard-card { display: block; transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease; }
    a.dashboard-card:hover { transform: translateY(-2px); }
    .dashboard-grid-line { stroke: rgba(203, 213, 225, .15); stroke-width: 1; stroke-dasharray: 4 5; }
    .dashboard-panel svg text { fill: #a5b7c9; }
    @media (max-width: 750px) { .content { padding: 12px; } .dashboard-panel { min-height: auto; border-radius: 14px; } }
</style>
@endpush

@section('content')
<div class="dashboard-panel min-h-[calc(100vh-96px)] rounded-2xl p-4 sm:p-5 lg:p-6">
    <div class="mb-5 flex min-w-0 flex-col gap-4 md:mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-bold tracking-tight text-white">Business Dashboard</h2>
            <p class="dashboard-muted mt-1 text-sm">Here’s how your printing business is performing today.</p>
        </div>
        <div class="grid w-full min-w-0 grid-cols-1 items-center gap-3 sm:grid-cols-[minmax(0,1fr)_auto] lg:w-auto">
            <label class="sr-only" for="dashboardPeriod">Dashboard period</label>
            <select id="dashboardPeriod" class="dashboard-card min-w-0 w-full cursor-pointer rounded-lg px-4 py-2.5 text-sm text-slate-200 outline-none transition hover:border-teal-500 lg:w-44" onchange="updateDashboardPeriod(this.value)">
                <option value="Today">Today</option>
                <option value="This week" selected>This week</option>
                <option value="This month">This month</option>
            </select>
            @if(auth()->user()->isAdministrator())
                <a href="{{ route('reports.index') }}" class="flex w-full shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-teal-700 bg-teal-950/40 px-4 py-2.5 text-sm font-medium text-teal-300 transition hover:bg-teal-900/50 sm:w-auto">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" stroke-width="2" stroke-linecap="round"/></svg>
                    View Reports
                </a>
            @endif
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('sales.index') }}" class="dashboard-card rounded-2xl p-4 sm:col-span-2 sm:p-5 xl:col-span-2" aria-label="View sales transactions">
            <div class="flex items-start justify-between">
                <div>
                    <p class="dashboard-muted text-sm">Today’s Sales</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-white">₱{{ number_format($todaySales, 2) }}</p>
                    <div class="mt-2 flex items-center gap-2 text-xs"><span class="rounded-md bg-emerald-500/10 px-2 py-1 font-semibold text-emerald-400">↑ 12.5%</span><span class="dashboard-muted">from yesterday</span></div>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-teal-500/10 text-teal-300"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="1.8" stroke-linecap="round"/></svg></span>
            </div>
            <svg class="mt-5 h-20 w-full" viewBox="0 0 440 80" preserveAspectRatio="none" role="img" aria-label="Sales trend rising">
                <defs><linearGradient id="salesFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--color-teal-400)" stop-opacity=".35"/><stop offset="1" stop-color="var(--color-teal-400)" stop-opacity="0"/></linearGradient></defs>
                <path d="M0 64 C45 55,62 57,95 43 S151 52,190 37 S249 45,286 24 S349 33,440 8 V80 H0Z" fill="url(#salesFill)"/><path d="M0 64 C45 55,62 57,95 43 S151 52,190 37 S249 45,286 24 S349 33,440 8" fill="none" stroke="var(--color-teal-400)" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </a>

        <a href="{{ route('sales.index') }}" class="dashboard-card rounded-2xl p-4 sm:p-5" aria-label="View all transactions">
            <div class="flex items-start justify-between"><div><p class="dashboard-muted text-sm">Transactions</p><p class="mt-2 text-3xl font-bold text-white">{{ $todayTransactions }}</p></div><span class="flex size-11 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Z M9 7h6M9 11h6M9 15h3" stroke-width="1.8" stroke-linecap="round"/></svg></span></div>
            <div class="mt-8 flex h-14 items-end gap-2" aria-label="Transaction activity chart">
                @foreach([38, 55, 45, 70, 58, 86, 65] as $barHeight)
                    <span class="flex-1 rounded-t-md bg-gradient-to-t from-cyan-950 to-cyan-500/70" style="height: {{ $barHeight }}%"></span>
                @endforeach
            </div>
            <p class="dashboard-muted mt-3 text-xs">Average order <span class="font-semibold text-slate-200">₱{{ $todayTransactions > 0 ? number_format($todaySales / $todayTransactions, 2) : '0.00' }}</span></p>
        </a>

        <a href="{{ auth()->user()->isAdministrator() ? route('products.index') : route('pos.index') }}" class="dashboard-card rounded-2xl p-4 sm:p-5" aria-label="View active services">
            <div class="flex items-start justify-between"><div><p class="dashboard-muted text-sm">Active Services</p><p class="mt-2 text-3xl font-bold text-white">{{ $activeServices }}</p></div><span class="flex size-11 items-center justify-center rounded-xl bg-violet-500/10 text-violet-300"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" stroke-width="1.8" stroke-linejoin="round"/></svg></span></div>
            <div class="mt-8"><div class="mb-2 flex justify-between text-xs"><span class="dashboard-muted">Service availability</span><span class="text-emerald-400">Online</span></div><div class="h-2 overflow-hidden rounded-full bg-slate-800"><div class="h-full w-[86%] rounded-full bg-gradient-to-r from-violet-500 to-teal-400"></div></div></div>
            <p class="dashboard-muted mt-4 text-xs">All systems are operating normally</p>
        </a>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <article class="dashboard-card rounded-2xl p-4 sm:p-5 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h3 class="font-semibold text-white">Sales Overview</h3><p id="chartPeriodLabel" class="dashboard-muted mt-1 text-xs">Revenue performance over the last 7 days</p></div>
                <div class="flex rounded-lg bg-slate-900/70 p-1 text-xs" aria-label="Sales chart period"><button type="button" class="chart-range rounded-md px-3 py-1.5 text-slate-400 transition hover:text-white" data-range="Day">Day</button><button type="button" class="chart-range rounded-md bg-slate-700 px-3 py-1.5 font-medium text-white" data-range="Week">Week</button><button type="button" class="chart-range rounded-md px-3 py-1.5 text-slate-400 transition hover:text-white" data-range="Month">Month</button></div>
            </div>
            <div class="mt-5 overflow-hidden">
                <svg class="h-44 w-full sm:h-52" viewBox="0 0 720 230" preserveAspectRatio="none" role="img" aria-label="Weekly sales line chart">
                    <line class="dashboard-grid-line" x1="42" y1="25" x2="710" y2="25"/><line class="dashboard-grid-line" x1="42" y1="75" x2="710" y2="75"/><line class="dashboard-grid-line" x1="42" y1="125" x2="710" y2="125"/><line class="dashboard-grid-line" x1="42" y1="175" x2="710" y2="175"/>
                    <text x="0" y="30" fill="#70869b" font-size="11">₱40k</text><text x="0" y="80" fill="#70869b" font-size="11">₱30k</text><text x="0" y="130" fill="#70869b" font-size="11">₱20k</text><text x="0" y="180" fill="#70869b" font-size="11">₱10k</text>
                    <defs><linearGradient id="overviewFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--brand-accent)" stop-opacity=".35"/><stop offset="1" stop-color="var(--brand-accent)" stop-opacity="0"/></linearGradient></defs>
                    <path id="overviewArea" d="M42 171 C98 138 139 157 191 126 S277 143 328 104 S414 121 465 76 S555 112 610 63 S669 48 710 55 V205 H42Z" fill="url(#overviewFill)" class="transition-all duration-300"/><path id="overviewLine" d="M42 171 C98 138 139 157 191 126 S277 143 328 104 S414 121 465 76 S555 112 610 63 S669 48 710 55" fill="none" stroke="var(--brand-accent)" stroke-width="3" stroke-linecap="round" class="transition-all duration-300"/>
                    <text class="chart-x-label" x="42" y="224" text-anchor="middle" fill="#70869b" font-size="11">Mon</text><text class="chart-x-label" x="150" y="224" text-anchor="middle" fill="#70869b" font-size="11">Tue</text><text class="chart-x-label" x="260" y="224" text-anchor="middle" fill="#70869b" font-size="11">Wed</text><text class="chart-x-label" x="370" y="224" text-anchor="middle" fill="#70869b" font-size="11">Thu</text><text class="chart-x-label" x="480" y="224" text-anchor="middle" fill="#70869b" font-size="11">Fri</text><text class="chart-x-label" x="590" y="224" text-anchor="middle" fill="#70869b" font-size="11">Sat</text><text class="chart-x-label" x="695" y="224" text-anchor="middle" fill="#70869b" font-size="11">Sun</text>
                </svg>
            </div>
            <div class="mt-3 flex justify-end">
                <a href="{{ auth()->user()->isAdministrator() ? route('reports.index') : route('sales.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-teal-300 transition hover:text-teal-200">Open detailed report <span aria-hidden="true">→</span></a>
            </div>
        </article>

        <article class="dashboard-card rounded-2xl p-4 sm:p-5">
            <div class="flex items-center justify-between"><div><h3 class="font-semibold text-white">Quick Actions</h3><p class="dashboard-muted mt-1 text-xs">Frequently used functions</p></div><span class="rounded-md bg-teal-500/10 px-2 py-1 text-xs text-teal-300">Shortcuts</span></div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                <a href="{{ route('pos.index') }}" class="glass-shortcut group rounded-xl border border-slate-700 bg-slate-900/40 p-4 transition hover:-translate-y-0.5 hover:border-teal-600"><span class="flex size-9 items-center justify-center rounded-lg bg-teal-500/10 text-teal-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/></svg></span><strong class="mt-3 block text-sm text-white">New Sale</strong><span class="dashboard-muted mt-1 block text-xs">Start transaction</span></a>
                <a href="{{ route('sales.index') }}" class="glass-shortcut group rounded-xl border border-slate-700 bg-slate-900/40 p-4 transition hover:-translate-y-0.5 hover:border-cyan-600"><span class="flex size-9 items-center justify-center rounded-lg bg-cyan-500/10 text-cyan-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" stroke-width="2" stroke-linecap="round"/></svg></span><strong class="mt-3 block text-sm text-white">Transactions</strong><span class="dashboard-muted mt-1 block text-xs">Sales history</span></a>
                @if(auth()->user()->isAdministrator())
                    <a href="{{ route('products.index') }}" class="glass-shortcut group rounded-xl border border-slate-700 bg-slate-900/40 p-4 transition hover:-translate-y-0.5 hover:border-violet-600"><span class="flex size-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 9V3h12v6M6 17H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M6 14h12v7H6v-7Z" stroke-width="1.8"/></svg></span><strong class="mt-3 block text-sm text-white">Services</strong><span class="dashboard-muted mt-1 block text-xs">Manage catalog</span></a>
                    <a href="{{ route('users.index') }}" class="glass-shortcut group rounded-xl border border-slate-700 bg-slate-900/40 p-4 transition hover:-translate-y-0.5 hover:border-amber-600"><span class="flex size-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-width="1.8" stroke-linecap="round"/></svg></span><strong class="mt-3 block text-sm text-white">Users</strong><span class="dashboard-muted mt-1 block text-xs">Manage staff</span></a>
                @endif
            </div>
        </article>
    </div>

    <article class="dashboard-card mt-4 overflow-hidden rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-slate-700/70 p-5 sm:flex-row sm:items-center sm:justify-between"><div><h3 class="font-semibold text-white">Recent Transactions</h3><p class="dashboard-muted mt-1 text-xs">Latest sales recorded in the system</p></div><a href="{{ route('sales.index') }}" class="text-sm font-medium text-teal-300 transition hover:text-teal-200">View all transactions →</a></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px]">
                <thead><tr class="bg-slate-900/50 text-left text-xs uppercase tracking-wider text-slate-500"><th class="px-5 py-3.5">Transaction</th><th class="px-5 py-3.5">Date</th><th class="px-5 py-3.5">Cashier</th><th class="px-5 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($recentSales as $sale)
                        <tr class="transaction-row cursor-pointer text-sm transition hover:bg-slate-800/30" tabindex="0" role="link" data-href="{{ route('sales.show', $sale) }}" aria-label="Open SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}"><td class="px-5 py-4 font-semibold"><a class="text-slate-200 transition hover:text-teal-300" href="{{ route('sales.show', $sale) }}">#SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</a></td><td class="dashboard-muted px-5 py-4">{{ $sale->created_at->format('M d, Y · h:i A') }}</td><td class="px-5 py-4 text-slate-300">{{ $sale->user->name ?? 'Cashier' }}</td><td class="px-5 py-4"><span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">Completed</span></td><td class="px-5 py-4 text-right font-semibold text-white"><a class="hover:text-teal-300" href="{{ route('sales.show', $sale) }}">₱{{ number_format($sale->total_amount, 2) }}</a></td></tr>
                    @empty
                        <tr><td colspan="5" class="dashboard-muted px-5 py-12 text-center text-sm">No transactions yet. Your latest sales will appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</div>
@endsection

@push('scripts')
<script>
function updateDashboardPeriod(period) {
    const rangeMap = { 'Today': 'Day', 'This week': 'Week', 'This month': 'Month' };
    updateSalesChart(rangeMap[period] ?? 'Week');
}

const salesChartData = {
    Day: {
        description: 'Revenue performance throughout today',
        labels: ['8am', '10am', '12pm', '2pm', '4pm', '6pm', '8pm'],
        line: 'M42 178 C95 168 132 142 191 151 S270 105 328 119 S410 79 465 93 S548 54 610 70 S670 44 710 50',
    },
    Week: {
        description: 'Revenue performance over the last 7 days',
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        line: 'M42 171 C98 138 139 157 191 126 S277 143 328 104 S414 121 465 76 S555 112 610 63 S669 48 710 55',
    },
    Month: {
        description: 'Revenue performance over the current month',
        labels: ['W1', 'W2', 'W3', 'W4', 'W5', '', ''],
        line: 'M42 184 C105 173 144 129 218 145 S331 87 402 105 S516 42 592 69 S660 29 710 39',
    },
};

function updateSalesChart(range) {
    const chart = salesChartData[range] ?? salesChartData.Week;
    const line = document.getElementById('overviewLine');
    const area = document.getElementById('overviewArea');

    line.setAttribute('d', chart.line);
    area.setAttribute('d', `${chart.line} V205 H42Z`);
    document.getElementById('chartPeriodLabel').textContent = chart.description;

    document.querySelectorAll('.chart-x-label').forEach((label, index) => {
        label.textContent = chart.labels[index] ?? '';
    });

    document.querySelectorAll('.chart-range').forEach((rangeButton) => {
        const isActive = rangeButton.dataset.range === range;
        rangeButton.classList.toggle('bg-slate-700', isActive);
        rangeButton.classList.toggle('font-medium', isActive);
        rangeButton.classList.toggle('text-white', isActive);
        rangeButton.classList.toggle('text-slate-400', !isActive);
    });

    const periodMap = { Day: 'Today', Week: 'This week', Month: 'This month' };
    document.getElementById('dashboardPeriod').value = periodMap[range];
}

document.querySelectorAll('.chart-range').forEach((button) => {
    button.addEventListener('click', () => updateSalesChart(button.dataset.range));
});

document.querySelectorAll('.transaction-row').forEach((row) => {
    const openTransaction = () => {
        window.location.href = row.dataset.href;
    };

    row.addEventListener('click', (event) => {
        if (!event.target.closest('a')) {
            openTransaction();
        }
    });

    row.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openTransaction();
        }
    });
});
</script>
@endpush
