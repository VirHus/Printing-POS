@extends('layouts.app')

@section('title', 'Transactions | Printing POS')
@section('page-title', 'Transactions')

@section('content')
<div class="space-y-6">
@if(auth()->user()->isAdministrator())
    <form method="GET" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-700 glass-surface bg-[#0c1828] p-5">
        <div><label for="storeFilter" class="mb-2 block text-sm text-slate-400">Store</label><select id="storeFilter" name="store_id" class="rounded-lg border border-slate-600 bg-slate-900 px-4 py-3 text-white"><option value="">All stores</option>@foreach($stores as $store)<option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>{{ $store->name }}</option>@endforeach</select></div>
        <button class="rounded-lg bg-teal-600 px-4 py-3 font-semibold text-white">Filter transactions</button>
    </form>
@else
    <p class="text-sm text-slate-400">Transactions for {{ auth()->user()->store?->name ?? 'your assigned store' }}</p>
@endif
<div class="grid gap-4 sm:grid-cols-3">
    <article class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 shadow-lg shadow-black/10">
        <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-400">Transactions shown</p><span class="flex size-10 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2ZM9 7h6M9 11h6" stroke-width="1.8" stroke-linecap="round"/></svg></span></div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-white">{{ $sales->count() }}</p><p class="mt-1 text-xs text-slate-500">On this page</p>
    </article>
    <article class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 shadow-lg shadow-black/10">
        <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-400">Page sales</p><span class="flex size-10 items-center justify-center rounded-xl bg-teal-500/10 text-teal-300">₱</span></div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-white">₱{{ number_format($sales->getCollection()->sum('total_amount'), 2) }}</p><p class="mt-1 text-xs text-emerald-400">Completed revenue</p>
    </article>
    <article class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 shadow-lg shadow-black/10">
        <div class="flex items-center justify-between"><p class="text-sm font-medium text-slate-400">Services sold</p><span class="flex size-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" stroke-width="1.8"/></svg></span></div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-white">{{ $sales->getCollection()->sum(fn ($sale) => $sale->items->sum('quantity')) }}</p><p class="mt-1 text-xs text-slate-500">Across listed transactions</p>
    </article>
</div>

<section class="min-h-[520px] overflow-hidden rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] shadow-xl shadow-black/10">
    <div class="flex flex-col gap-4 border-b border-slate-700/70 bg-slate-900/20 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-teal-400">Transaction records</p>
            <h2 class="text-2xl font-bold tracking-tight text-white">Sales History</h2>
            <p class="mt-2 text-sm text-slate-400">Review completed transactions and open their detailed receipts.</p>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-950/20 transition hover:-translate-y-0.5 hover:bg-teal-500">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/></svg>
            New Sale
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px]">
            <thead><tr class="bg-slate-900/50 text-left text-xs uppercase tracking-wider text-slate-500"><th class="px-6 py-4">Transaction</th><th class="px-6 py-4">Date</th><th class="px-6 py-4">Items</th><th class="px-6 py-4">Total</th><th class="px-6 py-4">Cash</th><th class="px-6 py-4">Change</th><th class="px-6 py-4 text-right">Action</th></tr></thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($sales as $sale)
                    <tr class="text-sm transition hover:bg-slate-800/30">
                        <td class="px-6 py-5 font-semibold text-slate-200">#SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}<span class="mt-1 block text-xs font-normal text-slate-500">{{ $sale->store?->name }} · {{ $sale->user?->name }}</span></td>
                        <td class="px-6 py-5 text-slate-400">{{ $sale->created_at->format('M d, Y · h:i A') }}</td>
                        <td class="px-6 py-5"><span class="rounded-full bg-cyan-500/10 px-2.5 py-1 text-xs font-medium text-cyan-300">{{ $sale->items->sum('quantity') }} items</span></td>
                        <td class="px-6 py-5 font-semibold text-white">₱{{ number_format($sale->total_amount, 2) }}</td>
                        <td class="px-6 py-5 text-slate-300">₱{{ number_format($sale->cash_received, 2) }}</td>
                        <td class="px-6 py-5 text-emerald-400">₱{{ number_format($sale->change_amount, 2) }}</td>
                        <td class="px-6 py-5 text-right"><a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-600 bg-slate-800/60 px-3 py-2 text-xs font-medium text-slate-200 transition hover:border-teal-600 hover:text-teal-300">View receipt <span aria-hidden="true">→</span></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-16 text-center"><div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-800 text-slate-500"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Z M9 7h6M9 11h6" stroke-width="1.8" stroke-linecap="round"/></svg></div><p class="mt-3 text-sm text-slate-400">No transactions found.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
        <div class="border-t border-slate-700/70 p-5">{{ $sales->links() }}</div>
    @endif
</section>
</div>
@endsection
