        <a @if(\App\Models\SystemSetting::current()->moduleEnabled('sales', auth()->user())) href="{{ route('sales.index', $storeId ? ['store_id' => $storeId] : []) }}" @endif class="dashboard-card rounded-2xl p-4 sm:p-5" aria-label="View all transactions">
            <div class="flex items-start justify-between"><div><p class="dashboard-muted text-sm">{{ ($widget['title'] ?? '') !== '' ? $widget['title'] : 'Transactions' }}</p><p class="mt-2 text-3xl font-bold text-white">{{ $todayTransactions }}</p></div><span class="flex size-11 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Z M9 7h6M9 11h6M9 15h3" stroke-width="1.8" stroke-linecap="round"/></svg></span></div>
            <div class="mt-8 flex h-14 items-end gap-2" aria-label="Transaction activity chart">
                @foreach($chart as $bucket)
                    <span class="flex-1 rounded-t-md bg-gradient-to-t from-cyan-950 to-cyan-500/70" style="height: {{ $bucket['transactions'] / $transactionMaximum * 100 }}%" title="{{ $bucket['label'] }}: {{ $bucket['transactions'] }} transactions"></span>
                @endforeach
            </div>
            <p class="dashboard-muted mt-3 text-xs">Average order <span class="font-semibold text-slate-200">₱{{ $todayTransactions > 0 ? number_format($todaySales / $todayTransactions, 2) : '0.00' }}</span></p>
        </a>
