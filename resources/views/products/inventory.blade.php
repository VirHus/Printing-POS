@extends('layouts.app')
@section('page-title', 'Inventory')
@push('styles')<style>.pack-fields[hidden] { display: none !important; }</style>@endpush
@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4"><div><h2 class="text-2xl font-bold text-white">Inventory & Service Costs</h2><p class="mt-2 text-sm text-slate-400">Stock is tracked separately for each store. {{ $lowStock }} item(s) need restocking.</p></div>@if(auth()->user()->role === 'Administrator' && \App\Models\SystemSetting::current()->moduleEnabled('profit', auth()->user()))<a href="{{ route('profit.index', ['store_id' => $storeId]) }}" class="rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white">Profit & Expenses →</a>@endif</header>
    @if(auth()->user()->role === 'Administrator')
        <form method="GET" class="glass-surface flex flex-wrap items-end gap-3 rounded-xl border p-5"><div><label for="inventoryStore">Store</label><select id="inventoryStore" name="store_id">@foreach($stores as $store)<option value="{{ $store->id }}" @selected($storeId == $store->id)>{{ $store->name }}</option>@endforeach</select></div><button class="rounded-lg bg-teal-600 px-4 py-3 text-sm text-white">View store</button></form>
        <details class="glass-surface rounded-2xl border p-5 sm:p-6" @if($items->isEmpty()) open @endif><summary class="cursor-pointer text-lg font-semibold text-white">Add inventory item</summary><p class="mt-3 text-sm text-slate-400">Enter the ream, bottle, or pack price. Stock is converted automatically to sheets, ml, or pieces. Pack sizes are editable—use the actual label on your supplies.</p>
            <form method="POST" action="{{ route('inventory.store') }}" data-stock-form class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf<input type="hidden" name="store_id" value="{{ $storeId }}">
                <div><label for="stockName">Item name</label><input id="stockName" name="name" placeholder="A4 bond paper" maxlength="255" required></div>
                <div><label for="stockUnit">Stock / usage unit</label><input id="stockUnit" name="unit" data-stock-unit value="sheet" maxlength="40" required></div>
                <div><label for="stockLow">Low-stock threshold (stock units)</label><input id="stockLow" name="low_stock_threshold" type="number" step="0.001" min="0" value="0" required></div>
                @include('products._stock-entry', ['prefix' => 'opening', 'opening' => true, 'item' => null])
                <div><button class="rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white">Add item</button></div>
            </form>
        </details>
    @endif
    <section class="glass-surface overflow-hidden rounded-2xl border"><div class="border-b border-slate-700 p-5"><h3 class="text-lg font-semibold text-white">Available stock</h3><p class="mt-1 text-xs text-slate-400">Purchase costs use weighted-average valuation. Never enter the same supply purchase again as an operating expense.</p></div><div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm"><thead><tr><th class="p-4 text-left">Item</th><th class="p-4 text-right">Available</th><th class="p-4 text-right">Average unit cost</th><th class="p-4 text-right">Stock value</th><th class="p-4 text-left">Status</th></tr></thead><tbody>@forelse($items as $item)<tr><td class="p-4 font-semibold">{{ $item->name }}</td><td class="p-4 text-right">{{ number_format($item->quantity, 3) }} {{ $item->unit }}</td><td class="p-4 text-right">₱{{ number_format($item->unit_cost, 4) }}</td><td class="p-4 text-right">₱{{ number_format($item->quantity * $item->unit_cost, 2) }}</td><td class="p-4"><span class="{{ $item->quantity <= $item->low_stock_threshold ? 'text-amber-300' : 'text-emerald-300' }}">{{ $item->quantity <= $item->low_stock_threshold ? 'Low stock' : 'In stock' }}</span></td></tr>@empty<tr><td colspan="5" class="p-10 text-center text-slate-400">No stock items for this store. Add your supplies first.</td></tr>@endforelse</tbody></table></div></section>
    @if(auth()->user()->role === 'Administrator' && $items->isNotEmpty())
        <section class="glass-surface rounded-2xl border p-5 sm:p-6"><h3 class="text-lg font-semibold text-white">Restock or adjust stock</h3><p class="mt-2 text-sm text-slate-400">Buy by pack or by unit. Waste, refill usage, and physical-count corrections use stock units. All changes remain in the movement history.</p>
            @foreach($items as $item)
                <details class="mt-4 rounded-xl border border-slate-700 p-4"><summary class="cursor-pointer font-semibold text-slate-200">{{ $item->name }} · {{ $item->unit }}</summary>
                    <form method="POST" action="{{ route('inventory.movements.store', $item) }}" data-stock-form data-stock-unit-label="{{ $item->unit }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                        @csrf
                        <div><label for="movementType{{ $item->id }}">Movement</label><select id="movementType{{ $item->id }}" name="type" data-movement-type><option>Purchase</option><option>Waste</option><option>Refill usage</option><option>Adjustment in</option><option>Adjustment out</option></select></div>
                        <div><label for="movementNote{{ $item->id }}">Reason / supplier reference</label><input id="movementNote{{ $item->id }}" name="notes" maxlength="500" required></div>
                        @include('products._stock-entry', ['prefix' => 'movement'.$item->id, 'opening' => false, 'item' => $item])
                        <button class="justify-self-start rounded-lg bg-teal-600 px-4 py-3 text-sm text-white">Record movement</button>
                    </form>
                </details>
            @endforeach
        </section>
    @endif
    @if(auth()->user()->role === 'Administrator')
        <details class="glass-surface rounded-2xl border p-5 sm:p-6"><summary class="cursor-pointer text-lg font-semibold text-white">Edit item names and low-stock thresholds</summary>
            @foreach($items as $item)<form method="POST" action="{{ route('inventory.update', $item) }}" class="mt-4 grid items-end gap-3 sm:grid-cols-3">@csrf @method('PUT')<div><label for="itemName{{ $item->id }}">Name ({{ $item->unit }})</label><input id="itemName{{ $item->id }}" name="name" value="{{ $item->name }}" maxlength="255" required></div><div><label for="itemThreshold{{ $item->id }}">Low-stock threshold</label><input id="itemThreshold{{ $item->id }}" name="low_stock_threshold" type="number" step="0.001" min="0" value="{{ $item->low_stock_threshold }}" required></div><button class="justify-self-start rounded-lg border border-teal-700 px-4 py-3 text-sm text-teal-300">Save details</button></form>@endforeach
        </details>
        <section class="glass-surface rounded-2xl border p-5 sm:p-6"><h3 class="text-lg font-semibold text-white">Materials per service unit</h3><p class="mt-2 text-sm leading-6 text-slate-400">Example: one photocopy uses 1 sheet plus an estimated ₱0.20 ink cost. Do not include ink in both tracked materials and estimated costs. Save even a zero-cost setup for services that use no supplies. Unconfigured services can still sell, but their profit is flagged as incomplete. For ink, choose either estimated ml deductions per page OR manual refill usage plus an estimated ink cost per page. Refill usage reduces physical stock only; it is not added again to profit expenses. Toner can use cartridge stock with manual refill usage and estimated cost per page.</p>
            @foreach($products as $product)
                @php($profile = $profiles->get($product->id))
                <details class="mt-4 rounded-xl border border-slate-700 p-4"><summary class="cursor-pointer font-semibold text-slate-200">{{ $product->name }} <span class="ml-2 text-xs {{ $profile ? 'text-emerald-300' : 'text-amber-300' }}">{{ $profile ? 'Configured' : 'Not configured' }}</span></summary>
                    <form method="POST" action="{{ route('inventory.materials.update', $product) }}" class="mt-4 space-y-4">@csrf @method('PUT')<input type="hidden" name="store_id" value="{{ $storeId }}"><div><label for="estimate{{ $product->id }}">Estimated untracked material cost per {{ $product->unit }} (₱)</label><input id="estimate{{ $product->id }}" name="estimated_unit_cost" type="number" step="0.0001" min="0" value="{{ $profile?->estimated_unit_cost ?? 0 }}" required></div>
                        @for($row = 0; $row < max(4, $profile?->materials->count() ?? 0); $row++)
                            @php($material = $profile?->materials->get($row))
                            <div class="grid gap-3 sm:grid-cols-2"><div><label for="material{{ $product->id }}_{{ $row }}">Material {{ $row + 1 }}</label><select id="material{{ $product->id }}_{{ $row }}" name="materials[{{ $row }}][inventory_item_id]"><option value="">No material</option>@foreach($items as $item)<option value="{{ $item->id }}" @selected($material?->inventory_item_id == $item->id)>{{ $item->name }} ({{ $item->unit }})</option>@endforeach</select></div><div><label for="usage{{ $product->id }}_{{ $row }}">Quantity used per {{ $product->unit }}</label><input id="usage{{ $product->id }}_{{ $row }}" name="materials[{{ $row }}][quantity]" type="number" step="0.001" min="0.001" value="{{ $material?->quantity }}" placeholder="e.g. 1 sheet or 0.05 ml"></div></div>
                        @endfor
                        <button class="rounded-lg bg-teal-600 px-4 py-3 text-sm text-white">Save service materials</button>
                    </form>
                </details>
            @endforeach
        </section>
    @endif
    <section class="glass-surface overflow-hidden rounded-2xl border"><h3 class="p-5 text-lg font-semibold text-white">Stock movement history</h3><div class="overflow-x-auto"><table class="w-full min-w-[720px] text-sm"><thead><tr><th class="p-4 text-left">Date</th><th class="p-4 text-left">Item</th><th class="p-4 text-left">Type</th><th class="p-4 text-right">Quantity</th><th class="p-4 text-left">Recorded by / notes</th></tr></thead><tbody>@forelse($movements as $movement)<tr><td class="p-4">{{ $movement->created_at->format('M d, Y H:i') }}</td><td class="p-4">{{ $movement->inventoryItem->name }}</td><td class="p-4">{{ $movement->type }}@if($movement->sale_id && \App\Models\SystemSetting::current()->moduleEnabled('sales', auth()->user()))<a href="{{ route('sales.show', $movement->sale_id) }}" class="ml-2 text-teal-300">#{{ $movement->sale_id }}</a>@endif</td><td class="p-4 text-right">{{ number_format($movement->quantity, 3) }}</td><td class="p-4">{{ $movement->user?->name ?? 'System' }}<span class="mt-1 block text-xs text-slate-400">{{ $movement->notes }}</span></td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-400">No movements yet.</td></tr>@endforelse</tbody></table></div><div class="p-5">{{ $movements->withQueryString()->links() }}</div></section>
</div>
@endsection
@push('scripts')
<script>
(() => {
    const presets = {paper: ['ream', 500, 'sheet'], ink: ['bottle', 100, 'ml'], toner: ['cartridge', 1, 'cartridge'], pouches: ['pack', 100, 'pouch'], cards: ['pack', 100, 'card'], pieces: ['box', 100, 'piece']};
    document.querySelectorAll('[data-stock-form]').forEach(form => {
        const mode = form.querySelector('[data-entry-mode]');
        const type = form.querySelector('[data-movement-type]');
        const packs = form.querySelector('[data-pack-fields]');
        const units = form.querySelector('[data-unit-fields]');
        const update = () => {
            const purchase = !type || type.value === 'Purchase';
            if (!purchase) mode.value = 'unit';
            mode.disabled = !purchase;
            const byPack = purchase && mode.value === 'pack';
            packs.hidden = !byPack;
            units.hidden = byPack;
            packs.querySelectorAll('input').forEach(input => input.disabled = !byPack);
            units.querySelectorAll('input').forEach(input => input.disabled = byPack);
            const unitCost = form.querySelector('[data-unit-cost]');
            unitCost.disabled = byPack || !purchase;
            form.querySelector('[data-unit-cost-field]').hidden = !purchase;
            const value = selector => Number(form.querySelector(selector)?.value || 0);
            const size = value('[data-pack-size]');
            const cost = byPack ? (size > 0 ? value('[data-pack-price]') / size : 0) : value('[data-unit-cost]');
            const quantity = byPack ? value('[data-pack-count]') * size + value('[data-loose-quantity]') : value('[data-unit-quantity]');
            const unit = form.querySelector('[data-stock-unit]')?.value || form.dataset.stockUnitLabel || 'units';
            form.querySelector('[data-conversion-preview]').textContent = purchase
                ? `${quantity.toLocaleString(undefined, {maximumFractionDigits: 3})} ${unit} · ₱${cost.toFixed(4)} per ${unit} (calculated automatically)`
                : `${quantity.toLocaleString(undefined, {maximumFractionDigits: 3})} ${unit} ${type.value === 'Adjustment in' ? 'added' : 'deducted'} from stock.`;
        };
        form.addEventListener('input', update);
        form.addEventListener('change', event => {
            if (event.target.matches('[data-material-preset]') && presets[event.target.value]) {
                const [pack, size, unit] = presets[event.target.value];
                form.querySelector('[data-pack-unit]').value = pack;
                form.querySelector('[data-pack-size]').value = size;
                form.querySelector('[data-stock-unit]').value = unit;
                mode.value = 'pack';
            }
            update();
        });
        update();
    });
})();
</script>
@endpush
