@extends('layouts.app')
@section('page-title', 'System Settings')
@push('styles')
<style>
    .settings-form input[type="checkbox"] { width: 16px; height: 16px; margin: 0; accent-color: var(--brand-accent); }
    .settings-form .image-remove { display: flex; align-items: center; gap: 8px; margin: 12px 0 0; font-weight: 400; }
    .settings-form input[type="file"] { padding: 12px; font-size: 12px; }
    .settings-form textarea { resize: vertical; }
    .settings-form section { scroll-margin-top: 88px; }
</style>
@endpush
@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-teal-300">Superadmin control panel</p><h2 class="text-2xl font-bold tracking-tight text-white">Make this system yours</h2><p class="mt-2 text-sm text-slate-400">Customize your branding, appearance, and receipts without editing code.</p></div>
        <div class="flex gap-3"><a href="{{ route('users.index') }}" class="rounded-xl border border-slate-600 px-4 py-3 text-sm text-slate-200 hover:border-teal-500">Manage users</a><a href="{{ route('stores.index') }}" class="rounded-xl border border-slate-600 px-4 py-3 text-sm text-slate-200 hover:border-teal-500">Manage stores</a></div>
    </header>
    <nav aria-label="Settings sections" class="flex flex-wrap gap-2"><a href="#branding" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-slate-300">Branding</a><a href="#images" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-slate-300">Images</a><a href="#appearance" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-slate-300">Appearance</a><a href="#receipts" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-slate-300">Receipts</a></nav>
    <a href="{{ route('settings.sidebar.edit') }}" class="inline-flex items-center gap-2 rounded-lg border border-teal-700 px-4 py-3 text-sm font-semibold text-teal-300">Open Sidebar Editor →</a>
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="settings-form space-y-6">
        @csrf @method('PUT')
        <section id="branding" class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 sm:p-7">
            <h3 class="text-lg font-semibold text-white">Branding and names</h3><p class="mt-1 text-sm text-slate-400">Shown in the sidebar, browser title, header, and sign-in screen.</p>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><label for="systemName">System name</label><input id="systemName" name="system_name" value="{{ old('system_name', $branding['system_name']) }}" maxlength="80" required></div>
                <div><label for="systemTagline">Sidebar tagline</label><input id="systemTagline" name="tagline" value="{{ old('tagline', $branding['tagline']) }}" maxlength="100"></div>
                <div class="sm:col-span-2"><label for="headerText">Header subtitle</label><input id="headerText" name="header_text" value="{{ old('header_text', $branding['header_text']) }}" maxlength="120"></div>
                <div class="sm:col-span-2"><label for="loginDescription">Sign-in description</label><textarea id="loginDescription" name="login_description" rows="3" maxlength="500">{{ old('login_description', $branding['login_description']) }}</textarea></div>
            </div>
        </section>
        <section id="images" class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 sm:p-7">
            <h3 class="text-lg font-semibold text-white">Logo and images</h3><p class="mt-1 text-sm text-slate-400">Upload a logo, browser icon, and sign-in image. Store photos are managed in Stores.</p>
            <div class="mt-6 grid gap-5 lg:grid-cols-3">
                @foreach(['logo' => ['System logo', 'PNG, JPG, or WebP · up to 2 MB', 'image/png,image/jpeg,image/webp'], 'favicon' => ['Browser icon', 'PNG · up to 512 KB, 512 × 512 px', 'image/png'], 'login_image' => ['Sign-in image', 'PNG, JPG, or WebP · up to 4 MB', 'image/png,image/jpeg,image/webp']] as $field => $image)
                    <div class="rounded-xl border border-slate-700 bg-slate-900/30 p-4">
                        <div class="mb-4 flex h-32 items-center justify-center overflow-hidden rounded-lg bg-slate-800/70">
                            @if($branding[$field.'_path'])<img src="{{ asset('storage/'.$branding[$field.'_path']) }}" alt="Current {{ strtolower($image[0]) }}" class="max-h-full max-w-full object-contain">@else<span class="text-xs text-slate-500">No image uploaded</span>@endif
                        </div>
                        <label for="{{ $field }}">{{ $image[0] }}</label><input id="{{ $field }}" name="{{ $field }}" type="file" accept="{{ $image[2] }}"><p class="mt-2 text-xs text-slate-500">{{ $image[1] }}</p>
                        @if($branding[$field.'_path'])<label class="image-remove"><input type="checkbox" name="remove_{{ $field }}" value="1"> Remove current image</label>@endif
                    </div>
                @endforeach
            </div>
        </section>
        <section id="appearance" class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 sm:p-7">
            <h3 class="text-lg font-semibold text-white">Colors and layout</h3><p class="mt-1 text-sm text-slate-400">Choose a color palette and layout that adapt to desktop and mobile screens.</p>
            <div class="mt-6 grid gap-5 sm:grid-cols-3">
                <div><label for="accentColor">Accent color</label><select id="accentColor" name="accent_color" required>@foreach(['#0d9488' => 'Teal', '#2563eb' => 'Blue', '#7c3aed' => 'Purple', '#be185d' => 'Rose', '#b45309' => 'Amber'] as $color => $name)<option value="{{ $color }}" @selected(old('accent_color', $branding['accent_color']) === $color)>{{ $name }}</option>@endforeach</select></div>
                <div><label for="sidebarStyle">Sidebar spacing</label><select id="sidebarStyle" name="sidebar_style"><option value="standard" @selected(old('sidebar_style', $branding['sidebar_style']) === 'standard')>Standard</option><option value="compact" @selected(old('sidebar_style', $branding['sidebar_style']) === 'compact')>Compact</option></select></div>
                <div><label for="contentWidth">Page width</label><select id="contentWidth" name="content_width"><option value="contained" @selected(old('content_width', $branding['content_width']) === 'contained')>Centered</option><option value="full" @selected(old('content_width', $branding['content_width']) === 'full')>Full width</option></select></div>
            </div>
            <div class="mt-5 rounded-xl border border-slate-700 bg-slate-900/40 p-5"><p class="text-xs uppercase tracking-wider text-slate-500">Preview</p><p id="brandPreviewName" class="mt-3 text-lg font-semibold text-white">{{ $branding['system_name'] }}</p><span id="brandPreviewButton" class="mt-3 inline-block rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background: {{ $branding['accent_color'] }}">Primary button</span></div>
        </section>
        <section id="receipts" class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 sm:p-7">
            <h3 class="text-lg font-semibold text-white">Business and receipt details</h3><p class="mt-1 text-sm text-slate-400">Receipts use your system logo and the sale’s store location.</p>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><label for="businessName">Business name</label><input id="businessName" name="business_name" value="{{ old('business_name', $branding['business_name']) }}" maxlength="120" required></div>
                <div><label for="receiptHeading">Receipt heading</label><input id="receiptHeading" name="receipt_heading" value="{{ old('receipt_heading', $branding['receipt_heading']) }}" maxlength="80" required></div>
                <div class="sm:col-span-2"><label for="businessAddress">Business address</label><input id="businessAddress" name="business_address" value="{{ old('business_address', $branding['business_address']) }}" maxlength="255"><p class="mt-2 text-xs text-slate-500">Used when a store has no address of its own.</p></div>
                <div class="sm:col-span-2"><label for="receiptFooter">Receipt footer message</label><textarea id="receiptFooter" name="receipt_footer" rows="3" maxlength="500">{{ old('receipt_footer', $branding['receipt_footer']) }}</textarea></div>
            </div>
        </section>
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-700 glass-surface bg-[#0c1828] p-5"><p class="text-sm text-slate-400">Changes apply to all stores after saving.</p><button type="submit" class="rounded-xl bg-teal-600 px-6 py-3 text-sm font-semibold text-white hover:bg-teal-500">Save system settings</button></div>
    </form>
</div>
@endsection
@push('scripts')
<script>
    const systemNameInput = document.getElementById('systemName');
    const accentColorInput = document.getElementById('accentColor');
    function updateBrandPreview() {
        document.getElementById('brandPreviewName').textContent = systemNameInput.value || 'Your system';
        document.getElementById('brandPreviewButton').style.backgroundColor = accentColorInput.value;
    }
    systemNameInput.addEventListener('input', updateBrandPreview);
    accentColorInput.addEventListener('change', updateBrandPreview);
    updateBrandPreview();
</script>
@endpush
