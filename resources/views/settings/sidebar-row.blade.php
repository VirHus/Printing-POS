<article data-menu-row class="rounded-xl border border-slate-700 glass-surface bg-[#0c1828] p-5">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h3 class="font-semibold text-white">Menu button <span data-row-number>{{ is_numeric($index) ? $index + 1 : '' }}</span></h3>
        <div class="flex gap-2">
            <button type="button" data-action="up" aria-label="Move button up" class="rounded-lg border border-slate-600 px-3 py-2 text-sm text-slate-300">↑ Up</button>
            <button type="button" data-action="down" aria-label="Move button down" class="rounded-lg border border-slate-600 px-3 py-2 text-sm text-slate-300">↓ Down</button>
            <button type="button" data-action="remove" class="rounded-lg bg-rose-500/10 px-3 py-2 text-sm text-rose-300">Remove</button>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div><label>Button label<input data-field="label" name="items[{{ $index }}][label]" value="{{ $item['label'] }}" maxlength="60" required></label></div>
        <div><label>Section / category<input data-field="section" name="items[{{ $index }}][section]" value="{{ $item['section'] }}" maxlength="60" placeholder="e.g. Main or Management"></label></div>
        <div><label>Destination<select data-field="destination" name="items[{{ $index }}][destination]">@foreach($destinations as $value => $label)<option value="{{ $value }}" @selected($item['destination'] === $value)>{{ $label }}</option>@endforeach</select></label></div>
        <div><label>Icon<select data-field="icon" name="items[{{ $index }}][icon]">@foreach($icons as $value => $path)<option value="{{ $value }}" @selected($item['icon'] === $value)>{{ ucfirst($value) }}</option>@endforeach</select></label></div>
        <div data-url-field class="sm:col-span-2 lg:col-span-4" @if($item['destination'] !== 'external') hidden @endif><label>Web address<input type="url" data-field="url" name="items[{{ $index }}][url]" value="{{ $item['url'] }}" maxlength="2048" placeholder="https://example.com" @required($item['destination'] === 'external')></label><p class="mt-2 text-xs text-slate-400">External links open in a new tab. Use an HTTP or HTTPS address.</p></div>
    </div>
    <div class="mt-4 flex flex-wrap gap-5">
        @foreach(['admin' => 'Show for administrators', 'cashier' => 'Show for cashiers'] as $role => $label)
            <input type="hidden" data-field="{{ $role }}" name="items[{{ $index }}][{{ $role }}]" value="0">
            <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" data-field="{{ $role }}" name="items[{{ $index }}][{{ $role }}]" value="1" @checked($item[$role])> {{ $label }}</label>
        @endforeach
    </div>
</article>
