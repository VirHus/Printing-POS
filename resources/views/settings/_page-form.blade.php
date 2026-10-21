<form method="POST" action="{{ $page ? route('settings.pages.update', $page['slug']) : route('settings.pages.store') }}" class="space-y-5">
    @csrf @if($page) @method('PUT') @endif
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label for="{{ $formId }}Title">Title / sidebar label</label><input id="{{ $formId }}Title" name="title" value="{{ $page['title'] ?? old('title') }}" maxlength="100" required></div>
        <div><label for="{{ $formId }}Slug">Page address</label><input id="{{ $formId }}Slug" name="slug" value="{{ $page['slug'] ?? old('slug') }}" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="80" placeholder="staff-instructions" required><p class="mt-2 text-xs text-slate-400">Lowercase letters, numbers, and hyphens only.</p></div>
        <div><label for="{{ $formId }}Section">Sidebar category</label><input id="{{ $formId }}Section" name="section" value="{{ $page['section'] ?? old('section', 'Information') }}" maxlength="60" required></div>
        <div><label for="{{ $formId }}Enabled">Publication</label><select id="{{ $formId }}Enabled" name="enabled"><option value="1" @selected($page['enabled'] ?? true)>Enabled</option><option value="0" @selected(isset($page) && !$page['enabled'])>Disabled / removed from accounts</option></select></div>
    </div>
    <div><label for="{{ $formId }}Body">Page content (plain text)</label><textarea id="{{ $formId }}Body" name="body" rows="6" maxlength="20000" required>{{ $page['body'] ?? old('body') }}</textarea></div>
    <div class="flex flex-wrap items-end justify-between gap-4"><div class="flex gap-4">@foreach(['admin' => 'Admin', 'cashier' => 'Cashier'] as $role => $label)<div><label for="{{ $formId }}{{ $role }}">{{ $label }} access</label><select id="{{ $formId }}{{ $role }}" name="{{ $role }}"><option value="1" @selected($page[$role] ?? true)>Visible</option><option value="0" @selected(isset($page) && !$page[$role])>Hidden</option></select></div>@endforeach</div><button class="rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-500">{{ $page ? 'Save page' : 'Add page' }}</button></div>
</form>
