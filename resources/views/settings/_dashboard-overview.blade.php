        <article class="dashboard-card rounded-2xl p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h3 class="font-semibold text-white">{{ ($widget['title'] ?? '') !== '' ? $widget['title'] : 'Sales Overview' }}</h3><p id="chartPeriodLabel" class="dashboard-muted mt-1 text-xs">{{ $periodLabel }} · {{ $period === 'day' ? 'Hourly' : 'Daily' }} recorded revenue</p></div>
                <nav class="flex rounded-lg bg-slate-900/70 p-1 text-xs" aria-label="Sales chart period">
                    @foreach(['day' => 'Day', 'week' => '7 days', 'month' => 'Month'] as $value => $label)
                        <a href="{{ route('dashboard', array_filter(['period' => $value, 'store_id' => $storeId])) }}" @if($period === $value) aria-current="page" @endif class="rounded-md px-3 py-1.5 {{ $period === $value ? 'bg-slate-700 font-medium text-white' : 'text-slate-400 hover:text-white' }}">{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="mt-5 overflow-hidden">
                <svg class="h-44 w-full sm:h-52" viewBox="0 0 720 230" role="img" aria-label="{{ $periodLabel }} sales chart">
                    @foreach([0, 1, 2, 3, 4] as $tick)
                        <line class="dashboard-grid-line" x1="70" y1="{{ 195 - $tick * 42.5 }}" x2="690" y2="{{ 195 - $tick * 42.5 }}"/>
                        <text x="2" y="{{ 199 - $tick * 42.5 }}" font-size="10">₱{{ number_format($chartMaximum * $tick / 4, $chartMaximum < 4 ? 2 : 0) }}</text>
                    @endforeach
                    <defs><linearGradient id="overviewFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--brand-accent)" stop-opacity=".35"/><stop offset="1" stop-color="var(--brand-accent)" stop-opacity="0"/></linearGradient></defs>
                    <polygon points="70,195 {{ implode(' ', $chartPoints) }} 690,195" fill="url(#overviewFill)"/>
                    <polyline points="{{ implode(' ', $chartPoints) }}" fill="none" stroke="var(--brand-accent)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    @foreach($chart as $index => $bucket)
                        <circle cx="{{ 70 + $index / max(1, count($chart) - 1) * 620 }}" cy="{{ 195 - $bucket['revenue'] / $chartMaximum * 170 }}" r="3" fill="var(--brand-accent)"><title>{{ $bucket['label'] }}: ₱{{ number_format($bucket['revenue'], 2) }} · {{ $bucket['transactions'] }} transactions</title></circle>
                        @if($index % max(1, (int) ceil(count($chart) / 7)) === 0 || $loop->last)
                            <text x="{{ 70 + $index / max(1, count($chart) - 1) * 620 }}" y="224" text-anchor="middle" font-size="10">{{ $bucket['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
                @if($todayTransactions === 0)<p class="dashboard-muted mt-3 text-center text-sm">No recorded sales for this period.</p>@endif
            </div>
            @if(\App\Models\SystemSetting::current()->moduleEnabled('reports', auth()->user()) || \App\Models\SystemSetting::current()->moduleEnabled('sales', auth()->user()))
            <div class="mt-3 flex justify-end">
                <a href="{{ \App\Models\SystemSetting::current()->moduleEnabled('reports', auth()->user()) ? route('reports.index', $reportFilters) : route('sales.index', $storeId ? ['store_id' => $storeId] : []) }}" class="text-xs font-semibold text-teal-300">Open detailed report →</a>
            </div>
            @endif
        </article>
