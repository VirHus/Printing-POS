@php
    $packUnit = $item?->purchase_unit ?? ($item?->unit === 'ml' ? 'bottle' : ($item?->unit === 'sheet' || !$item ? 'ream' : 'pack'));
    $packSize = $item?->units_per_pack ?? ($item?->unit === 'ml' ? 100 : ($item?->unit === 'sheet' || !$item ? 500 : 1));
    $mode = !$item || $item->purchase_unit ? 'pack' : 'unit';
@endphp
<div class="sm:col-span-2 lg:col-span-full space-y-4" data-stock-entry>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label for="{{ $prefix }}Mode">Enter stock by</label><select id="{{ $prefix }}Mode" name="entry_mode" data-entry-mode><option value="pack" @selected($mode === 'pack')>Purchase packs (ream / bottle / box)</option><option value="unit" @selected($mode === 'unit')>Individual stock units</option></select></div>
        @if($opening)<div><label for="{{ $prefix }}Preset">Material shortcut</label><select id="{{ $prefix }}Preset" data-material-preset><option value="paper">Paper — ream / 500 sheets</option><option value="ink">Bottled ink — bottle / 100 ml</option><option value="toner">Toner — cartridge / 1 cartridge</option><option value="pouches">Lamination — pack / 100 pouches</option><option value="cards">ID cards — pack / 100 cards</option><option value="pieces">Other supplies — box / 100 pieces</option><option value="custom">Custom pack size</option></select></div>@endif
    </div>
    <div data-pack-fields class="pack-fields grid gap-4 sm:grid-cols-2 lg:grid-cols-4" @if($mode !== 'pack') hidden @endif>
        <div><label for="{{ $prefix }}PackUnit">Purchase unit</label><input id="{{ $prefix }}PackUnit" name="purchase_unit" data-pack-unit value="{{ $packUnit }}" placeholder="ream, bottle, pack" maxlength="40" required></div>
        <div><label for="{{ $prefix }}PackSize">Stock units per pack</label><input id="{{ $prefix }}PackSize" name="units_per_pack" data-pack-size type="number" step="0.001" min="0.001" value="{{ $packSize }}" required></div>
        <div><label for="{{ $prefix }}PackCount">Number of packs</label><input id="{{ $prefix }}PackCount" name="pack_count" data-pack-count type="number" step="1" min="{{ $opening ? 0 : 1 }}" value="{{ $opening ? 0 : 1 }}" required></div>
        <div><label for="{{ $prefix }}PackPrice">Price per pack (₱)</label><input id="{{ $prefix }}PackPrice" name="pack_price" data-pack-price type="number" step="0.01" min="0" placeholder="e.g. 178 per ream" required></div>
        @if($opening)<div><label for="{{ $prefix }}Loose">Extra loose stock units</label><input id="{{ $prefix }}Loose" name="loose_quantity" data-loose-quantity type="number" step="0.001" min="0" value="0"><p class="mt-2 text-xs text-slate-400">For partial reams or bottles; valued at the same unit rate.</p></div>@endif
    </div>
    <div data-unit-fields class="pack-fields grid gap-4 sm:grid-cols-2" @if($mode !== 'unit') hidden @endif>
        <div><label for="{{ $prefix }}Quantity">{{ $opening ? 'Opening quantity' : 'Quantity' }} (stock units)</label><input id="{{ $prefix }}Quantity" name="quantity" data-unit-quantity type="number" step="0.001" min="{{ $opening ? 0 : 0.001 }}" @if($opening) value="0" @endif required></div>
        <div data-unit-cost-field><label for="{{ $prefix }}UnitCost">Cost per stock unit (₱)</label><input id="{{ $prefix }}UnitCost" name="unit_cost" data-unit-cost type="number" step="0.0001" min="0" placeholder="Only for direct-unit purchases" required></div>
    </div>
    <p data-conversion-preview class="rounded-xl border border-teal-800/50 bg-teal-950/20 p-4 text-sm text-teal-200" role="status" aria-live="polite">Enter quantity and price to preview converted stock.</p>
</div>
