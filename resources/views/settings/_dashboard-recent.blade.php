    <article class="dashboard-card overflow-hidden rounded-2xl">
        <div class="flex flex-col gap-3 border-b border-slate-700/70 p-5 sm:flex-row sm:items-center sm:justify-between"><div><h3 class="font-semibold text-white">{{ ($widget['title'] ?? '') !== '' ? $widget['title'] : 'Recent Transactions' }}</h3><p class="dashboard-muted mt-1 text-xs">Latest sales for {{ strtolower($periodLabel) }}</p></div><a href="{{ route('sales.index', $storeId ? ['store_id' => $storeId] : []) }}" class="text-sm font-medium text-teal-300 transition hover:text-teal-200">View all transactions →</a></div>
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
