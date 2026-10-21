@extends('layouts.app')
@section('page-title', 'Custom Pages')
@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <header><h2 class="text-2xl font-bold text-white">Custom informational pages</h2><p class="mt-2 text-sm text-slate-400">Add instructions, price notes, store policies, or announcements. These are plain-text pages, not new functional modules. Disable a page to remove it from accounts without deleting its content.</p></header>
    <section class="glass-surface rounded-2xl border border-slate-700 p-5 sm:p-6"><h3 class="mb-5 text-lg font-semibold text-white">Add page</h3>@include('settings._page-form', ['page' => null, 'formId' => 'newPage'])</section>
    @foreach($pages as $page)
        <details class="glass-surface rounded-2xl border border-slate-700 p-5 sm:p-6"><summary class="cursor-pointer font-semibold text-white">{{ $page['title'] }} <span class="ml-3 text-xs text-slate-400">{{ $page['enabled'] ? 'Published' : 'Disabled' }}</span></summary><div class="mt-5">@include('settings._page-form', ['page' => $page, 'formId' => 'page'.$loop->index])</div></details>
    @endforeach
</div>
@endsection
