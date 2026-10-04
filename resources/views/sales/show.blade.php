@extends('layouts.app')

@section('title', 'Sale Receipt | Printing POS')
@section('page-title', 'Transaction Receipt')

@push('styles')
<style>
    @media print {
        .sidebar, .topbar, .receipt-actions { display: none !important; }
        .main { margin: 0 !important; width: 100% !important; }
        .content { padding: 0 !important; }
        body, .main { background: #fff !important; }
        .print-receipt { border: 0 !important; box-shadow: none !important; color: #111 !important; }
        .print-receipt * { color: #111 !important; border-color: #ddd !important; }
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('pos.index') }}" class="receipt-actions mb-4 inline-flex rounded-lg border border-teal-700 px-4 py-3 text-sm font-semibold text-teal-300">New Sale</a>
    <div class="receipt-actions mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-teal-300"><span aria-hidden="true">←</span> Back to transactions</a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-500"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" stroke-width="1.8" stroke-linejoin="round"/></svg>Print Receipt</button>
    </div>

    <article class="print-receipt overflow-hidden rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] shadow-xl shadow-black/10">
        <header class="border-b border-slate-700/70 bg-gradient-to-br from-teal-950/60 to-slate-900/30 p-7 text-center">
            <p class="mb-2 font-semibold text-teal-300">{{ $sale->store?->name ?? 'Unassigned store' }}</p>
            @if($sale->store?->address || $branding['business_address'])<p class="mb-3 text-xs text-slate-400">{{ $sale->store?->address ?: $branding['business_address'] }}</p>@endif
            @if($branding['logo_path'])<img src="{{ asset('storage/'.$branding['logo_path']) }}" alt="{{ $branding['business_name'] }} logo" class="mx-auto mb-3 max-h-20 max-w-48 object-contain">@endif
            <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-teal-500/15 text-teal-300"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" stroke-width="1.8" stroke-linejoin="round"/></svg></div>
            <h2 class="mt-3 text-xl font-bold text-white">{{ $branding['business_name'] }}</h2><p class="mt-1 text-sm text-slate-400">{{ $branding['receipt_heading'] }}</p><span class="mt-4 inline-block rounded-full bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">SALE-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
        </header>

        <div class="grid gap-4 border-b border-slate-700/70 p-6 text-sm sm:grid-cols-3">
            <div><p class="text-xs uppercase tracking-wider text-slate-500">Date</p><p class="mt-1 font-medium text-slate-200">{{ $sale->created_at->format('M d, Y') }}</p><p class="text-xs text-slate-500">{{ $sale->created_at->format('h:i A') }}</p></div>
            <div><p class="text-xs uppercase tracking-wider text-slate-500">Cashier</p><p class="mt-1 font-medium text-slate-200">{{ $sale->user->name ?? 'Cashier' }}</p></div>
            <div><p class="text-xs uppercase tracking-wider text-slate-500">Payment</p><p class="mt-1 font-medium text-slate-200">{{ $sale->payment_method }}</p>@if($sale->payment_reference)<p class="mt-1 break-all text-xs text-slate-500">Ref: {{ $sale->payment_reference }}</p>@endif</div>
        </div>

        @if($sale->customer_name || $sale->order_notes)
            <div class="space-y-2 border-b border-slate-700 p-6 text-sm text-slate-300">
                @if($sale->customer_name)<p>Customer: {{ $sale->customer_name }}</p>@endif
                @if($sale->order_notes)<p class="whitespace-pre-wrap break-words">Notes: {{ $sale->order_notes }}</p>@endif
            </div>
        @endif
        <div class="overflow-x-auto p-6">
            <table class="w-full min-w-[520px] text-sm"><thead><tr class="text-left text-xs uppercase tracking-wider text-slate-500"><th class="pb-3">Service</th><th class="pb-3 text-center">Qty</th><th class="pb-3 text-right">Price</th><th class="pb-3 text-right">Subtotal</th></tr></thead><tbody class="divide-y divide-slate-800">
                @foreach($sale->items as $item)
                    <tr><td class="py-4 font-medium text-slate-200">{{ $item->product->name }}</td><td class="py-4 text-center text-slate-400">{{ $item->quantity }}</td><td class="py-4 text-right text-slate-400">₱{{ number_format($item->price, 2) }}</td><td class="py-4 text-right font-medium text-white">₱{{ number_format($item->subtotal, 2) }}</td></tr>
                @endforeach
            </tbody></table>
        </div>

        <div class="border-t border-slate-700/70 bg-slate-900/30 p-6">
            <dl class="ml-auto max-w-xs space-y-3 text-sm">@if($sale->payment_method === 'Cash')<div class="flex justify-between"><dt class="text-slate-400">Cash received</dt><dd class="font-medium text-slate-200">₱{{ number_format($sale->cash_received, 2) }}</dd></div><div class="flex justify-between"><dt class="text-slate-400">Change</dt><dd class="font-medium text-emerald-400">₱{{ number_format($sale->change_amount, 2) }}</dd></div>@endif<div class="flex justify-between border-t border-slate-700 pt-3"><dt class="font-semibold text-white">Total</dt><dd class="text-xl font-bold text-teal-300">₱{{ number_format($sale->total_amount, 2) }}</dd></div></dl>
            <p class="mt-8 whitespace-pre-wrap text-center text-xs text-slate-500">{{ $branding['receipt_footer'] }}</p>
        </div>
    </article>
</div>
@endsection
