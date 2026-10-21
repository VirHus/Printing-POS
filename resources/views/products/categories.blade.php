@extends('layouts.app')
@section('page-title', 'Service Categories')
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div><h2 class="text-2xl font-bold text-white">Service categories</h2><p class="mt-2 text-sm text-slate-400">Disabling a category hides its services without deleting receipts.</p></div>
        <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-600 px-4 py-3 text-sm text-teal-300">Back to services</a>
    </header>
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-800 p-4 text-sm text-rose-200">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="glass-surface rounded-2xl border border-slate-700 p-5 sm:p-6">
        <h3 class="mb-5 text-lg font-semibold text-white">Add category</h3>
        <form method="POST" action="{{ route('categories.store') }}" class="grid items-end gap-4 md:grid-cols-[1fr_2fr_auto]">
            @csrf
            <div><label for="categoryName" class="mb-2 block text-sm text-slate-300">Name</label><input id="categoryName" name="name" value="{{ old('name') }}" maxlength="255" required placeholder="Example: Lamination"></div>
            <div><label for="categoryDescription" class="mb-2 block text-sm text-slate-300">Description (optional)</label><input id="categoryDescription" name="description" value="{{ old('description') }}" maxlength="2000"></div>
            <button class="rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-500">Add category</button>
        </form>
    </section>
    <div class="grid gap-5 lg:grid-cols-2">
        @forelse($categories as $category)
            <form method="POST" action="{{ route('categories.update', $category) }}" class="glass-surface space-y-4 rounded-2xl border border-slate-700 p-5 sm:p-6">
                @csrf @method('PUT')
                <div class="flex items-center justify-between gap-3"><span class="text-sm text-slate-400">{{ $category->products_count }} services</span><span class="rounded-full px-3 py-1 text-xs {{ $category->is_active ? 'bg-teal-500/10 text-teal-300' : 'bg-slate-700 text-slate-300' }}">{{ $category->is_active ? 'Active' : 'Disabled' }}</span></div>
                <div><label for="name{{ $category->id }}" class="mb-2 block text-sm text-slate-300">Name</label><input id="name{{ $category->id }}" name="name" value="{{ $category->name }}" maxlength="255" required></div>
                <div><label for="description{{ $category->id }}" class="mb-2 block text-sm text-slate-300">Description</label><textarea id="description{{ $category->id }}" name="description" rows="2" maxlength="2000">{{ $category->description }}</textarea></div>
                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-700 pt-4">
                    <div><label for="status{{ $category->id }}" class="sr-only">Category status</label><select id="status{{ $category->id }}" name="is_active"><option value="1" @selected($category->is_active)>Active in POS</option><option value="0" @selected(!$category->is_active)>Disabled in POS</option></select></div>
                    <button class="rounded-xl bg-teal-600 px-4 py-3 text-sm font-semibold text-white hover:bg-teal-500">Save changes</button>
                </div>
            </form>
        @empty
            <p class="p-6 text-sm text-slate-400">No categories yet. Add one above to organize your services.</p>
        @endforelse
    </div>
</div>
@endsection
