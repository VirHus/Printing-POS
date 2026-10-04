@extends('layouts.app')
@section('title', 'Stores | Printing POS')
@section('page-title', 'Stores')
@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-6">
        <h2 class="text-xl font-bold text-white">{{ auth()->user()->isSuperadmin() ? 'Store Branding' : 'Store locations' }}</h2>
        <p class="mt-2 text-sm text-slate-400">{{ auth()->user()->isSuperadmin() ? 'Customize store names, addresses, and photos for the main system.' : 'Add a location, then assign its cashiers in Users. Transactions stay with the store where they were recorded.' }}</p>
        <form method="POST" action="{{ route('stores.store') }}" class="mt-6 grid items-end gap-4 sm:grid-cols-3">
            @csrf
            <div><label for="newStoreName" class="mb-2 block text-sm text-slate-300">Store name</label><input id="newStoreName" name="name" value="{{ old('name') }}" maxlength="255" required class="w-full rounded-lg border border-slate-600 bg-slate-900 p-3 text-white"></div>
            <div><label for="newStoreAddress" class="mb-2 block text-sm text-slate-300">Address (optional)</label><input id="newStoreAddress" name="address" maxlength="255" class="w-full rounded-lg border border-slate-600 bg-slate-900 p-3 text-white"></div>
            <button class="rounded-lg bg-teal-600 px-5 py-3 font-semibold text-white">Add store</button>
        </form>
    </section>
    <div class="grid gap-5 lg:grid-cols-2">
        @foreach($stores as $store)
            <section class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-6">
                @if($store->image_path)<img src="{{ asset('storage/'.$store->image_path) }}" alt="{{ $store->name }}" class="mb-5 h-40 w-full rounded-xl object-cover">@endif
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><h3 class="text-lg font-semibold text-white">{{ $store->name }}</h3><span class="text-xs text-slate-400">{{ $store->users_count }} staff @if(! auth()->user()->isSuperadmin()) · {{ $store->sales_count }} transactions @endif</span></div>
                <form method="POST" action="{{ route('stores.update', $store) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf @method('PUT')
                    @if(auth()->user()->isSuperadmin())
                        <div><label for="storeImage{{ $store->id }}">Store photo</label><input id="storeImage{{ $store->id }}" type="file" name="image" accept="image/png,image/jpeg,image/webp"><p class="mt-2 text-xs text-slate-500">PNG, JPG, or WebP · up to 4 MB</p>@if($store->image_path)<label class="mt-3 flex items-center gap-2 text-xs"><input type="checkbox" name="remove_image" value="1" class="size-4!">Remove current photo</label>@endif</div>
                    @endif
                    <div><label for="storeName{{ $store->id }}" class="mb-2 block text-sm text-slate-300">Name</label><input id="storeName{{ $store->id }}" name="name" value="{{ $store->name }}" maxlength="255" required class="w-full rounded-lg border border-slate-600 bg-slate-900 p-3 text-white"></div>
                    <div><label for="storeAddress{{ $store->id }}" class="mb-2 block text-sm text-slate-300">Address</label><input id="storeAddress{{ $store->id }}" name="address" value="{{ $store->address }}" maxlength="255" class="w-full rounded-lg border border-slate-600 bg-slate-900 p-3 text-white"></div>
                    <div><label for="storeStatus{{ $store->id }}" class="mb-2 block text-sm text-slate-300">Status</label><select id="storeStatus{{ $store->id }}" name="is_active" class="w-full rounded-lg border border-slate-600 bg-slate-900 p-3 text-white"><option value="1" @selected($store->is_active)>Active</option><option value="0" @selected(!$store->is_active)>Inactive</option></select><p class="mt-2 text-xs text-slate-500">Inactive stores keep their history and cannot record new sales.</p></div>
                    <div class="flex flex-wrap items-center gap-4"><button class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white">Save changes</button>@if(! auth()->user()->isSuperadmin())<a href="{{ route('sales.index', ['store_id' => $store->id]) }}" class="text-sm text-teal-300">Transactions</a><a href="{{ route('reports.index', ['store_id' => $store->id]) }}" class="text-sm text-teal-300">Reports</a>@endif</div>
                </form>
            </section>
        @endforeach
    </div>
</div>
@endsection
