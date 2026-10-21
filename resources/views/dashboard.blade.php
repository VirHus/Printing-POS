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
    .dashboard-widget-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 16px; }
    .dashboard-widget { grid-column: span 12; min-width: 0; }
    .dashboard-widget > .dashboard-card { height: 100%; }
    @media (min-width: 640px) { .dashboard-widget[data-widget="transactions"], .dashboard-widget[data-widget="services"] { grid-column: span 6; } }
    @media (min-width: 1280px) { .dashboard-widget, .dashboard-widget[data-widget="transactions"], .dashboard-widget[data-widget="services"] { grid-column: span var(--widget-columns); } }
    .dashboard-panel svg text { fill: #a5b7c9; }
    .dashboard-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; min-width: 0; }
    .dashboard-filters select { width: 150px; min-width: 0; height: 38px; padding: 8px 12px; }
    .dashboard-filters #dashboardStore { width: 180px; }
    .dashboard-header-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; min-width: 0; }
    @media (max-width: 639px) {
        .dashboard-filters { width: 100%; }
        .dashboard-filters select, .dashboard-filters #dashboardStore { flex: 1 1 130px; width: auto; }
    }
    @media (max-width: 750px) { .content { padding: 12px; } .dashboard-panel { min-height: auto; border-radius: 14px; } }
</style>
@endpush

@section('content')
<div class="dashboard-panel min-h-[calc(100vh-96px)] rounded-2xl p-4 sm:p-5 lg:p-6">
    <div class="mb-4 flex min-w-0 flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h2 class="text-2xl font-bold tracking-tight text-white">{{ \App\Models\SystemSetting::current()->settings()['dashboard_title'] }}</h2>
            <p class="dashboard-muted mt-1 text-sm">Actual sales for {{ strtolower($periodLabel) }}. Comparison uses the preceding {{ $period === 'day' ? 'day' : 'equal-length period' }}.</p>
        </div>
        <div class="dashboard-header-actions w-full sm:w-auto">
            <form method="GET" action="{{ route('dashboard') }}" class="dashboard-filters">
                <label class="sr-only" for="dashboardPeriod">Dashboard period</label>
                <select id="dashboardPeriod" name="period" class="dashboard-card rounded-lg px-4 py-2.5 text-sm text-slate-200" onchange="this.form.requestSubmit()">
                    @foreach(['day' => 'Today', 'week' => 'Last 7 days', 'month' => 'This month'] as $value => $label)
                        <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->isAdministrator())
                    <label class="sr-only" for="dashboardStore">Store</label>
                    <select id="dashboardStore" name="store_id" class="dashboard-card max-w-full rounded-lg px-4 py-2.5 text-sm text-slate-200" onchange="this.form.requestSubmit()">
                        <option value="">All stores</option>
                        @foreach($stores as $store)<option value="{{ $store->id }}" @selected($storeId == $store->id)>{{ $store->name }}</option>@endforeach
                    </select>
                @endif
                <button class="h-[38px] rounded-lg border border-teal-700 px-3 py-2 text-sm text-teal-300" type="submit">Apply</button>
            </form>
            @if(\App\Models\SystemSetting::current()->moduleEnabled('reports', auth()->user()))
                <a href="{{ route('reports.index', $reportFilters) }}" class="flex h-[38px] shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-teal-700 bg-teal-950/40 px-4 py-2 text-sm font-medium text-teal-300 transition hover:bg-teal-900/50">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" stroke-width="2" stroke-linecap="round"/></svg>
                    View Reports
                </a>
            @endif
        </div>
    </div>

    <div class="dashboard-widget-grid">
        @forelse(\App\Models\SystemSetting::current()->dashboardWidgets(auth()->user()) as $widget)
            <section class="dashboard-widget" data-widget="{{ $widget['key'] }}" style="--widget-columns: {{ $widget['columns'] }}">
                @if($widget['key'] === 'shortcuts' && ($widget['title'] ?? '') !== '')<h3 class="mb-3 font-semibold text-white">{{ $widget['title'] }}</h3>@endif
                @include('settings._dashboard-'.$widget['key'])
            </section>
        @empty
            <p class="dashboard-muted col-span-12 py-8 text-center text-sm">Your dashboard cards are currently hidden. Ask the Superadmin to configure your dashboard.</p>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
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
