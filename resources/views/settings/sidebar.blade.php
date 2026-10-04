@extends('layouts.app')
@section('page-title', 'Sidebar Editor')
@push('styles')
<style>
    .sidebar-editor input[type="checkbox"] { width: 16px; height: 16px; margin: 0; accent-color: var(--brand-accent); }
    .sidebar-editor [hidden] { display: none !important; }
</style>
@endpush
@section('content')
<div class="space-y-6 sidebar-editor">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="mb-2 text-xs uppercase tracking-widest text-teal-300">Superadmin customization</p><h2 class="text-2xl font-bold text-white">Sidebar Editor</h2><p class="mt-2 text-sm text-slate-400">Customize navigation for administrators and cashiers without editing code.</p></div>
        <a href="{{ route('settings.edit') }}" class="rounded-lg border border-slate-600 px-4 py-3 text-sm text-slate-200">System Settings</a>
    </header>
    <div class="rounded-xl border border-teal-900 bg-teal-950/20 p-4 text-sm leading-relaxed text-slate-300">Buttons appear in the order below. Use the same section name to group adjacent buttons. Uncheck both roles to hide a button. Sidebar visibility does not grant page access: cashier-only restrictions remain enforced. These categories organize navigation, not the POS service catalog.</div>
    <form id="sidebarEditorForm" method="POST" action="{{ route('settings.sidebar.update') }}" class="space-y-5">
        @csrf @method('PUT')
        <div id="menuRows" class="space-y-4">
            @foreach(old('items', $items) as $index => $item)
                @include('settings.sidebar-row', ['index' => $index, 'item' => $item])
            @endforeach
        </div>
        <div class="flex flex-wrap gap-3">
            <button id="addMenuButton" type="button" class="rounded-lg border border-teal-700 px-5 py-3 text-sm font-semibold text-teal-300">+ Add button</button>
            <button type="submit" class="rounded-lg bg-teal-600 px-5 py-3 text-sm font-semibold text-white">Save sidebar</button>
        </div>
        <p id="menuEditorStatus" class="text-sm text-slate-400" role="status" aria-live="polite">Changes are applied only when you save. Maximum 30 buttons.</p>
    </form>
    <form method="POST" action="{{ route('settings.sidebar.destroy') }}" onsubmit="return confirm('Restore the original sidebar? Your custom buttons will be removed.');" class="flex flex-wrap items-center gap-4 border-t border-slate-700 pt-5">
        @csrf @method('DELETE')
        <button type="submit" class="rounded-lg border border-slate-600 px-4 py-3 text-sm text-slate-300">Restore defaults</button><p class="text-xs text-slate-400">Branding and other system settings will not change.</p>
    </form>
</div>
<template id="menuRowTemplate">
    @include('settings.sidebar-row', ['index' => '__INDEX__', 'item' => ['label' => 'New button', 'section' => 'Main', 'destination' => 'dashboard', 'url' => '', 'icon' => 'link', 'admin' => true, 'cashier' => false]])
</template>
@endsection
@push('scripts')
<script>
(() => {
    const rows = document.getElementById('menuRows');
    const status = document.getElementById('menuEditorStatus');
    const renumber = () => {
        Array.from(rows.children).forEach((row, index) => {
            row.querySelector('[data-row-number]').textContent = index + 1;
            row.querySelectorAll('[data-field]').forEach(input => input.name = `items[${index}][${input.dataset.field}]`);
            row.querySelector('[data-action="up"]').disabled = index === 0;
            row.querySelector('[data-action="down"]').disabled = index === rows.children.length - 1;
        });
        document.getElementById('addMenuButton').disabled = rows.children.length >= 30;
    };
    rows.addEventListener('click', event => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const row = button.closest('[data-menu-row]');
        if (button.dataset.action === 'up' && row.previousElementSibling) rows.insertBefore(row, row.previousElementSibling);
        if (button.dataset.action === 'down' && row.nextElementSibling) rows.insertBefore(row.nextElementSibling, row);
        if (button.dataset.action === 'remove') {
            if (rows.children.length === 1) { status.textContent = 'Keep at least one button. Uncheck both roles to hide it.'; return; }
            row.remove();
        }
        renumber();
        status.textContent = 'Unsaved sidebar changes. Click Save sidebar to apply.';
    });
    rows.addEventListener('change', event => {
        if (event.target.dataset.field === 'destination') {
            const row = event.target.closest('[data-menu-row]');
            const external = event.target.value === 'external';
            row.querySelector('[data-url-field]').hidden = !external;
            const url = row.querySelector('[data-field="url"]');
            url.required = external;
            if (!external) url.value = '';
        }
        status.textContent = 'Unsaved sidebar changes. Click Save sidebar to apply.';
    });
    document.getElementById('addMenuButton').addEventListener('click', () => {
        if (rows.children.length >= 30) return;
        rows.append(document.getElementById('menuRowTemplate').content.cloneNode(true));
        renumber();
        rows.lastElementChild.querySelector('[data-field="label"]').focus();
        status.textContent = 'New button added. Choose its destination, then save.';
    });
    document.getElementById('sidebarEditorForm').addEventListener('submit', renumber);
    renumber();
})();
</script>
@endpush
