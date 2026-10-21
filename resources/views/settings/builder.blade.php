@extends('layouts.app')
@section('page-title', 'System Builder')
@push('styles')
<style>
    .builder-form label { display: block; margin-bottom: 8px; }
    .builder-form input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--brand-accent); }
    .builder-form .toggle { display: inline-flex; align-items: center; gap: 8px; margin: 0; }
    .builder-form input[type="color"] { height: 42px; padding: 4px; }
</style>
@endpush
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <header><p class="text-xs uppercase tracking-widest text-teal-300">Superadmin control panel</p><h2 class="mt-2 text-2xl font-bold text-white">Appearance, dashboard & modules</h2><p class="mt-2 text-sm text-slate-400">Configure existing features for Admin and Cashier. Changes apply across all stores after saving.</p></header>
    <nav class="flex flex-wrap gap-2" aria-label="Builder sections">@foreach(['appearance' => 'Appearance', 'dashboard' => 'Dashboard builder', 'modules' => 'Module & role controls'] as $anchor => $label)<a href="#builder-{{ $anchor }}" class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-teal-300">{{ $label }}</a>@endforeach<a href="{{ route('settings.sidebar.edit') }}" class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-teal-300">Navigation editor</a><a href="{{ route('settings.pages.index') }}" class="rounded-lg border border-slate-700 px-4 py-2 text-sm text-teal-300">Custom pages</a></nav>
    <form method="POST" action="{{ route('settings.builder.update') }}" id="builderForm" class="builder-form space-y-6">
        @csrf @method('PUT')
        <section id="builder-appearance" class="glass-surface rounded-2xl border border-slate-700 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Appearance</h3><p class="mt-2 text-sm text-slate-400">Brand names, logos, pictures, accent color, and receipts remain in System Settings.</p>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach(['theme' => ['Theme', ['glass' => 'Glass', 'solid' => 'Solid dark']], 'font_family' => ['Font', ['instrument' => 'Instrument Sans', 'system' => 'System sans-serif', 'serif' => 'Georgia serif']], 'font_size' => ['Text size', ['small' => 'Small', 'standard' => 'Standard', 'large' => 'Large']], 'spacing' => ['Spacing & table density', ['compact' => 'Compact', 'comfortable' => 'Comfortable', 'spacious' => 'Spacious']], 'corner_style' => ['Card corners', ['rounded' => 'Rounded', 'square' => 'Squared']]] as $key => $field)
                    <div><label for="builder-{{ $key }}">{{ $field[0] }}</label><select id="builder-{{ $key }}" name="{{ $key }}">@foreach($field[1] as $value => $label)<option value="{{ $value }}" @selected(old($key, $appearance[$key]) === $value)>{{ $label }}</option>@endforeach</select></div>
                @endforeach
                <div><label for="builder-background">Background color</label><input id="builder-background" type="color" name="background_color" value="{{ old('background_color', $appearance['background_color']) }}" required></div>
            </div>
            <div id="appearancePreview" class="mt-5 rounded-xl border border-slate-600 p-5"><p class="text-xs text-slate-300">Appearance preview</p><h4 id="previewTitle" class="mt-2 text-lg font-semibold text-white">{{ $appearance['dashboard_title'] }}</h4><p id="previewText" class="mt-2 text-slate-300">Your business workspace, customized without code.</p></div>
        </section>
        <section id="builder-dashboard" class="glass-surface rounded-2xl border border-slate-700 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Dashboard builder</h3><p class="mt-2 text-sm text-slate-400">Move cards up or down, select desktop widths, and choose which roles can see them. Cards stack on smaller screens.</p>
            <div class="mt-5"><label for="dashboardTitle">Dashboard title</label><input id="dashboardTitle" name="dashboard_title" maxlength="100" value="{{ old('dashboard_title', $appearance['dashboard_title']) }}" required></div>
            <div id="widgetList" class="mt-5 space-y-3">
                @foreach(old('widgets', $widgets) as $index => $widget)
                    <div data-widget-row class="flex flex-wrap items-center gap-4 rounded-xl border border-slate-700 bg-slate-900/20 p-4">
                        <input type="hidden" data-field="key" name="widgets[{{ $index }}][key]" value="{{ $widget['key'] }}">
                        <strong class="min-w-[180px] flex-1 text-sm text-white">{{ \App\Models\SystemSetting::DASHBOARD_WIDGETS[$widget['key']]['label'] ?? 'Widget' }}</strong>
                        <div class="w-full sm:w-52"><label class="sr-only" for="widgetTitle{{ $index }}">Custom card title</label><input id="widgetTitle{{ $index }}" name="widgets[{{ $index }}][title]" data-field="title" value="{{ $widget['title'] ?? '' }}" maxlength="80" placeholder="Custom title (optional)"></div>
                        <div><label for="widgetWidth{{ $index }}" class="sr-only">Card width</label><select id="widgetWidth{{ $index }}" data-field="columns" name="widgets[{{ $index }}][columns]">@foreach([3 => 'Quarter', 4 => 'Third', 6 => 'Half', 8 => 'Two thirds', 12 => 'Full width'] as $columns => $label)<option value="{{ $columns }}" @selected((int) $widget['columns'] === $columns)>{{ $label }}</option>@endforeach</select></div>
                        @foreach(['admin' => 'Admin', 'cashier' => 'Cashier'] as $role => $label)
                            <input type="hidden" data-field="{{ $role }}" name="widgets[{{ $index }}][{{ $role }}]" value="0">
                            <label class="toggle"><input type="checkbox" data-field="{{ $role }}" name="widgets[{{ $index }}][{{ $role }}]" value="1" @checked($widget[$role])>{{ $label }}</label>
                        @endforeach
                        <div class="flex gap-2"><button type="button" data-move="up" class="rounded-lg border border-slate-600 px-3 py-2 text-xs text-slate-200" aria-label="Move {{ \App\Models\SystemSetting::DASHBOARD_WIDGETS[$widget['key']]['label'] ?? 'card' }} up">↑</button><button type="button" data-move="down" class="rounded-lg border border-slate-600 px-3 py-2 text-xs text-slate-200" aria-label="Move {{ \App\Models\SystemSetting::DASHBOARD_WIDGETS[$widget['key']]['label'] ?? 'card' }} down">↓</button></div>
                    </div>
                @endforeach
            </div>
        </section>
        <section id="builder-modules" class="glass-surface rounded-2xl border border-slate-700 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Module & role controls</h3><p class="mt-2 text-sm text-slate-400">Disabling a module hides its navigation and blocks direct URLs. Records are never deleted. Cashiers cannot be granted administrative permissions here.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach($modules as $key => $module)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-700 p-4"><strong class="text-sm text-white">{{ $module['label'] }}</strong><div class="flex gap-4">
                        @foreach(['admin' => 'Admin', 'cashier' => 'Cashier'] as $role => $label)
                            <input type="hidden" name="modules[{{ $key }}][{{ $role }}]" value="0">
                            @if($role === 'admin' || $module['cashier'])<label class="toggle text-sm"><input type="checkbox" name="modules[{{ $key }}][{{ $role }}]" value="1" @checked(old('modules.'.$key.'.'.$role, $moduleSettings[$key][$role] ?? true))>{{ $label }}</label>@endif
                        @endforeach
                    </div></div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-slate-400">Point of Sale requires Transactions for receipt access. Superadmin settings and profile access always remain available. If Dashboard is disabled, sign-in opens the first enabled module.</p>
        </section>
        <div class="flex flex-wrap items-center justify-between gap-4"><p class="text-sm text-slate-400">Save when you are ready to apply changes.</p><button class="rounded-xl bg-teal-600 px-6 py-3 text-sm font-semibold text-white hover:bg-teal-500">Save builder settings</button></div>
    </form>
    <form method="POST" action="{{ route('settings.builder.destroy') }}" onsubmit="return confirm('Restore appearance, dashboard layout and module permissions to defaults? Business data and custom pages will remain unchanged.')">@csrf @method('DELETE')<button class="rounded-xl border border-slate-600 px-4 py-3 text-sm text-slate-300">Restore builder defaults</button></form>
</div>
@endsection
@push('scripts')
<script>
const widgetList = document.getElementById('widgetList');
function reindexWidgets() {
    widgetList.querySelectorAll('[data-widget-row]').forEach((row, index) => {
        row.querySelectorAll('[data-field]').forEach(input => { input.name = `widgets[${index}][${input.dataset.field}]`; });
        row.querySelector('[data-move="up"]').disabled = index === 0;
        row.querySelector('[data-move="down"]').disabled = index === widgetList.children.length - 1;
    });
}
widgetList.addEventListener('click', event => {
    const button = event.target.closest('[data-move]');
    if (!button) return;
    const row = button.closest('[data-widget-row]');
    if (button.dataset.move === 'up' && row.previousElementSibling) widgetList.insertBefore(row, row.previousElementSibling);
    if (button.dataset.move === 'down' && row.nextElementSibling) widgetList.insertBefore(row.nextElementSibling, row);
    reindexWidgets();
    button.focus();
});
reindexWidgets();
document.getElementById('builderForm').addEventListener('submit', reindexWidgets);
function updateAppearancePreview() {
    const preview = document.getElementById('appearancePreview');
    preview.style.backgroundColor = document.getElementById('builder-background').value;
    preview.style.backgroundImage = document.getElementById('builder-theme').value === 'glass' ? 'linear-gradient(135deg, rgba(255,255,255,.12), transparent)' : 'none';
    preview.style.fontFamily = {instrument: 'Instrument Sans, sans-serif', system: 'system-ui, sans-serif', serif: 'Georgia, serif'}[document.getElementById('builder-font_family').value];
    preview.style.padding = {compact: '16px', comfortable: '24px', spacious: '32px'}[document.getElementById('builder-spacing').value];
    preview.style.borderRadius = document.getElementById('builder-corner_style').value === 'square' ? '4px' : '16px';
    document.getElementById('previewText').style.fontSize = {small: '12px', standard: '14px', large: '16px'}[document.getElementById('builder-font_size').value];
    document.getElementById('previewTitle').textContent = document.getElementById('dashboardTitle').value;
}
document.getElementById('builderForm').addEventListener('input', updateAppearancePreview);
updateAppearancePreview();
</script>
@endpush
