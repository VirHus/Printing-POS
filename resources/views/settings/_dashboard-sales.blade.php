        <a @if(\App\Models\SystemSetting::current()->moduleEnabled('sales', auth()->user())) href="{{ route('sales.index', $storeId ? ['store_id' => $storeId] : []) }}" @endif class="dashboard-card rounded-2xl p-4 sm:p-5 " aria-label="View sales transactions">
            <div class="flex items-start justify-between">
                <div>
                    <p class="dashboard-muted text-sm">{{ ($widget['title'] ?? '') !== '' ? $widget['title'] : $periodLabel.' Sales' }}</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-white">₱{{ number_format($todaySales, 2) }}</p>
                    <div class="mt-2 flex items-center gap-2 text-xs"><span class="rounded-md bg-teal-500/10 px-2 py-1 font-semibold text-teal-300">{{ $salesGrowth !== null ? ($salesGrowth > 0 ? '+' : '').number_format($salesGrowth, 1).'%' : ($todaySales > 0 ? 'First sales' : 'No sales') }}</span><span class="dashboard-muted">{{ $previousSales > 0 ? 'vs previous period' : 'No previous sales to compare' }}</span></div>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-teal-500/10 text-teal-300"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="1.8" stroke-linecap="round"/></svg></span>
            </div>
            <svg class="mt-5 h-20 w-full" viewBox="0 0 440 80" preserveAspectRatio="none" role="img" aria-label="Recorded sales trend">
                <defs><linearGradient id="salesFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--color-teal-400)" stop-opacity=".35"/><stop offset="1" stop-color="var(--color-teal-400)" stop-opacity="0"/></linearGradient></defs>
                <polygon points="0,80 {{ implode(' ', $sparkPoints) }} 440,80" fill="url(#salesFill)"/><polyline points="{{ implode(' ', $sparkPoints) }}" fill="none" stroke="var(--color-teal-400)" stroke-width="3"/>
            </svg>
        </a>
