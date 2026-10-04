@extends('layouts.app')

@section('title', 'Services | Printing POS')
@section('page-title', 'Services')

@push('styles')
<style>
    .services-table th { padding: 16px 24px; background: #111e30; color: #94a3b8; font-size: 11px; letter-spacing: .08em; }
    .services-table td { padding: 20px 24px; border-color: #223247; background: transparent; font-size: 14px; line-height: 1.5; }
    .services-table tr:hover td { background: rgba(30,47,68,.35); }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <article class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5">
            <p class="text-sm font-medium text-slate-400">Total services</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-white">{{ $products->count() }}</p>
            <p class="mt-2 text-xs text-slate-500">Across your service catalog</p>
        </article>
        <article class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5">
            <p class="text-sm font-medium text-slate-400">Active services</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-teal-300">{{ $products->where('is_active', true)->count() }}</p>
            <p class="mt-2 text-xs text-slate-500">Available at the point of sale</p>
        </article>
        <article class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5">
            <p class="text-sm font-medium text-slate-400">Categories in use</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-white">{{ $products->pluck('category_id')->unique()->count() }}</p>
            <p class="mt-2 text-xs text-slate-500">Organized service groups</p>
        </article>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] shadow-xl shadow-black/10">
        <div class="flex flex-col gap-5 border-b border-slate-700/70 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-teal-400">Service catalog</p>
                <h2 class="text-2xl font-bold tracking-tight text-white">Printing Services</h2>
                <p class="mt-2 text-sm leading-6 text-slate-400">Manage service prices, categories, and availability.</p>
            </div>
            <a href="{{ route('products.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-500">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/></svg>
                Add Service
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="services-table w-full min-w-[960px]">
                <caption class="sr-only">Service catalog with pricing, availability, and management actions</caption>
                <thead><tr><th scope="col">Service</th><th scope="col">Category</th><th scope="col">Price / unit</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="max-w-sm">
                                <p class="font-semibold text-slate-100">{{ $product->name }}</p>
                                @if($product->description)
                                    <p class="mt-1.5 max-w-sm whitespace-normal text-xs leading-5 text-slate-400">{{ $product->description }}</p>
                                @endif
                            </td>
                            <td><span class="inline-flex rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-1.5 text-xs font-medium text-slate-300">{{ $product->category?->name ?? 'Uncategorized' }}</span></td>
                            <td class="whitespace-nowrap"><span class="font-semibold tabular-nums text-teal-300">₱{{ number_format($product->price, 2) }}</span><span class="mt-1 block text-xs text-slate-500">per {{ $product->unit }}</span></td>
                            <td>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-500/10 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">
                                    <span class="size-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('products.edit', $product) }}" aria-label="Edit {{ $product->name }}" class="rounded-lg border border-slate-600 bg-slate-800/60 px-3 py-2.5 text-xs font-semibold text-slate-200 transition hover:border-teal-500 hover:text-teal-300">Edit</a>
                                    <form method="POST" action="{{ route('products.toggle', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" aria-label="{{ $product->is_active ? 'Disable' : 'Enable' }} {{ $product->name }}" class="rounded-lg border px-3 py-2.5 text-xs font-semibold transition {{ $product->is_active ? 'border-amber-800/60 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20' : 'border-teal-800/60 bg-teal-500/10 text-teal-300 hover:bg-teal-500/20' }}">{{ $product->is_active ? 'Disable' : 'Enable' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this service?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Delete {{ $product->name }}" class="rounded-lg border border-rose-900/60 bg-rose-500/10 px-3 py-2.5 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="flex flex-col items-center gap-3 py-12 text-center"><h3 class="text-base font-semibold text-white">No services yet</h3><p class="max-w-sm text-sm text-slate-400">Add your first printing service to make it available in the POS.</p><a href="{{ route('products.create') }}" class="mt-2 text-sm font-semibold text-teal-300 hover:text-teal-200">Add a service →</a></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700/70 px-6 py-4 text-xs text-slate-500">Showing {{ $products->count() }} services. Disabled services stay in your catalog but are hidden from the POS.</div>
    </section>
</div>
@endsection
