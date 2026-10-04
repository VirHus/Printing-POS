@extends('layouts.app')

@section('title', 'Point of Sale | Printing POS')
@section('page-title', 'Point of Sale')

@section('content')
<p class="mb-4 text-sm text-slate-400">Selling at <strong class="text-teal-300">{{ auth()->user()->store?->name ?? 'No store assigned' }}</strong></p>
<div class="grid items-start gap-6 pb-20 xl:grid-cols-[minmax(0,1fr)_440px] xl:pb-0">
    <section class="rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-5 shadow-xl shadow-black/10 sm:p-6">
        <div class="flex flex-col gap-5 border-b border-slate-700/70 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-teal-400">Service catalog</p>
                <h2 class="text-2xl font-bold tracking-tight text-white">Printing Services</h2>
                <p class="mt-2 text-sm leading-6 text-slate-400">Choose a category and click a service to add it to the order.</p>
            </div>
            <div class="min-w-44 rounded-xl border border-teal-800/60 bg-teal-950/30 px-5 py-3.5 text-sm text-slate-400">
                <span class="block text-xs uppercase tracking-wider">Today’s sales</span><strong class="mt-1 block text-xl text-teal-300">₱{{ number_format($todaySales, 2) }}</strong>
            </div>
        </div>

        <div class="mt-6">
            <label for="serviceSearch" class="mb-2 block text-sm font-medium text-slate-300">Search services</label>
            <input id="serviceSearch" type="search" placeholder="Search all categories..." oninput="filterServices()" class="w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-sm text-white focus:border-teal-500">
            <p class="mt-2 text-xs text-slate-500">Shortcuts: / search · Alt+C cash · Alt+Enter review sale</p>
        </div>
        <p id="noSearchResults" class="mt-4 hidden text-sm text-slate-400" role="status">No services match your search.</p>
        <div class="mt-6 flex flex-wrap gap-3" aria-label="Service categories">
            @forelse($categories as $category)
                <button type="button" class="category-button rounded-xl border border-slate-700 bg-slate-900/50 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:-translate-y-0.5 hover:border-teal-600 hover:text-white" data-category="{{ $category->id }}" onclick="showCategory({{ $category->id }}, this)">
                    {{ $category->name }}
                </button>
            @empty
                <p class="text-sm text-slate-400">No service categories are available.</p>
            @endforelse
        </div>

        <div class="mt-8">
            @foreach($categories as $category)
                <div class="category-products hidden" id="category-{{ $category->id }}">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">{{ $category->name }}</h3>
                        <span class="rounded-full bg-slate-800 px-3 py-1 text-xs font-medium text-slate-400">{{ $category->products->count() }} services</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3">
                        @forelse($category->products as $product)
                            <button type="button" data-service-name="{{ $product->name }}" class="service-card group flex min-h-28 flex-col justify-between rounded-xl border border-slate-700 bg-slate-900/40 p-5 text-left transition hover:-translate-y-1 hover:border-teal-500 hover:bg-slate-800/70 hover:shadow-lg hover:shadow-teal-950/20" onclick="addProduct({{ $product->id }}, {{ Js::from($product->name) }}, {{ $product->price }})">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="text-base font-semibold leading-6 text-slate-100 group-hover:text-white">{{ $product->name }}</span>
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-slate-500 transition group-hover:bg-teal-500/15 group-hover:text-teal-300"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/></svg></span>
                                </span>
                                <span class="mt-5 block text-lg font-bold text-teal-300">₱{{ number_format($product->price, 2) }} <span class="text-xs font-normal text-slate-500">/ {{ $product->unit }}</span></span>
                            </button>
                        @empty
                            <p class="col-span-full rounded-xl border border-dashed border-slate-700 p-8 text-center text-sm text-slate-500">No active services in this category.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <aside id="orderSummary" class="scroll-mt-20 self-start overflow-hidden rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] shadow-xl shadow-black/10 xl:sticky xl:top-20">
        <div class="flex items-center justify-between border-b border-slate-700/70 bg-slate-900/25 p-6">
            <div><p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-teal-400">Order summary</p><h2 class="text-xl font-bold text-white">Current Sale</h2><p class="mt-1 text-sm text-slate-400"><span id="cartCount">0</span> items selected</p></div>
            <span class="flex size-12 items-center justify-center rounded-xl bg-teal-500/10 text-teal-300"><svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6M10 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM18 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" stroke-width="1.8" stroke-linecap="round"/></svg></span>
        </div>

        <form method="POST" action="{{ route('pos.store') }}" id="saleForm" class="p-6">
            @csrf
            <div class="mb-5 flex justify-end"><button type="button" id="clearOrderButton" onclick="clearOrder()" disabled class="text-sm text-rose-400 disabled:opacity-40">Clear order</button></div>
            <div class="max-h-[300px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead><tr><th class="py-3 pr-2 text-left">Item</th><th class="px-2 py-3 text-center">Qty</th><th class="py-3 pl-2 text-right">Amount</th><th></th></tr></thead>
                    <tbody id="cart"><tr id="emptyCart"><td colspan="4" class="py-10 text-center text-slate-500"><span class="mx-auto mb-3 flex size-10 items-center justify-center rounded-full bg-slate-800/80 text-slate-500">+</span><strong class="block text-sm font-medium text-slate-300">Your cart is empty</strong><span class="mt-1 block text-xs">Select a service to begin.</span></td></tr></tbody>
                </table>
            </div>

            <div class="mt-5 space-y-4 border-t border-slate-700/70 pt-5">
                <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-400">Total amount</span><strong class="text-3xl font-bold tracking-tight text-white">₱<span id="total">0.00</span></strong></div>
                <fieldset>
                    <legend class="mb-3 text-sm font-medium text-slate-300">Payment method</legend>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach(['Cash', 'GCash', 'Maya', 'Bank Transfer', 'Card'] as $method)
                            <label class="payment-method-option cursor-pointer rounded-xl border border-slate-700 bg-slate-900/50 p-3 text-center text-sm font-semibold text-slate-300 transition hover:border-teal-600">
                                <input type="radio" name="payment_method" value="{{ $method }}" class="sr-only" onchange="selectPaymentMethod(this.value)" @checked(old('payment_method', 'Cash') === $method)>
                                {{ $method }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div id="cashPaymentFields" class="space-y-3">
                    <div><label for="cash" class="mb-2 block text-sm font-medium text-slate-300">Cash received</label><div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">₱</span><input type="number" name="cash_received" id="cash" value="{{ old('cash_received') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900/60 py-3 pl-8 pr-3 text-lg text-white outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20" step="0.01" min="0" placeholder="0.00" oninput="calculateChange()" required></div></div>
                    <div class="flex flex-wrap gap-2" aria-label="Quick cash amounts">
                    <button type="button" onclick="setCash(null)" class="rounded-lg border border-teal-800 px-3 py-2 text-xs text-teal-300">Exact</button>
                    @foreach([50, 100, 200, 500] as $amount)
                        <button type="button" onclick="setCash({{ $amount }})" class="rounded-lg border border-slate-700 px-3 py-2 text-xs text-slate-300">₱{{ $amount }}</button>
                    @endforeach
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-900/60 p-4"><span class="text-sm text-slate-400">Change</span><strong class="text-xl text-teal-300">₱<span id="change">0.00</span></strong></div>
                </div>
                <div id="referencePaymentFields" class="hidden">
                    <label for="paymentReference" id="paymentReferenceLabel" class="mb-2 block text-sm font-medium text-slate-300">Payment reference</label>
                    <input type="text" name="payment_reference" id="paymentReference" value="{{ old('payment_reference') }}" maxlength="100" autocomplete="off" class="w-full rounded-lg border border-slate-600 bg-slate-900/60 px-3 py-3 text-white outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20" placeholder="Enter transaction reference">
                    <p class="mt-2 text-xs text-slate-500">Verify the successful payment before completing the sale.</p>
                </div>
                <details class="rounded-xl border border-slate-700 p-4">
                    <summary class="cursor-pointer text-sm text-slate-300">Customer and notes (optional)</summary>
                    <label for="customerName" class="mb-2 mt-4 block text-xs text-slate-400">Customer name</label>
                    <input id="customerName" name="customer_name" maxlength="255" value="{{ old('customer_name') }}" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">
                    <label for="orderNotes" class="mb-2 mt-3 block text-xs text-slate-400">Order notes</label>
                    <textarea id="orderNotes" name="order_notes" maxlength="1000" rows="3" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white">{{ old('order_notes') }}</textarea>
                </details>
                <button type="submit" id="completeSaleButton" class="flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-3.5 font-semibold text-white transition hover:bg-teal-500 disabled:cursor-not-allowed disabled:opacity-40" disabled>
                    Complete Sale
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
        </form>
    </aside>
</div>
<button type="button" onclick="document.getElementById('orderSummary').scrollIntoView({behavior: 'smooth'})" class="fixed bottom-4 left-4 right-4 z-40 flex items-center justify-between rounded-xl bg-teal-600 px-5 py-4 text-sm font-semibold text-white shadow-xl xl:hidden"><span>View order · <span id="mobileCartCount">0</span> items</span><span>₱<span id="mobileTotal">0.00</span></span></button>

<div id="receiptPreviewModal" class="fixed inset-0 z-[2000] hidden items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="receiptPreviewTitle" onclick="handlePreviewBackdrop(event)">
    <div class="max-h-[92vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-slate-600 glass-surface bg-[#0c1828] shadow-2xl shadow-black/50">
        <div class="flex items-start justify-between border-b border-slate-700 p-5 sm:p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-400">Confirm purchase</p>
                <h2 id="receiptPreviewTitle" class="mt-1 text-2xl font-bold tracking-tight text-white">Receipt Preview</h2>
                <p class="mt-1 text-sm text-slate-400">Review the order before completing this sale.</p>
            </div>
            <button type="button" class="flex size-10 shrink-0 items-center justify-center rounded-lg border border-slate-700 bg-slate-900/60 text-xl text-slate-400 transition hover:border-slate-500 hover:text-white" onclick="closeReceiptPreview()" aria-label="Close receipt preview">×</button>
        </div>

        <div class="p-5 sm:p-6">
            <div class="mb-5 flex items-center justify-between rounded-xl border border-slate-700 bg-slate-900/40 p-4">
                <div>@if($branding['logo_path'])<img src="{{ asset('storage/'.$branding['logo_path']) }}" alt="{{ $branding['business_name'] }} logo" class="mb-2 max-h-12 max-w-32 object-contain">@endif<p class="text-xs uppercase tracking-wider text-slate-500">{{ $branding['business_name'] }}</p><p class="mt-1 text-sm font-semibold text-white">{{ $branding['receipt_heading'] }}</p><p class="mt-1 text-xs text-slate-500">{{ auth()->user()->store?->name }}</p></div>
                <div class="text-right"><p class="text-xs uppercase tracking-wider text-slate-500">Payment</p><p id="previewPaymentMethod" class="mt-1 text-sm font-semibold text-teal-300">Cash</p><p id="previewPaymentReference" class="mt-1 hidden max-w-56 break-all text-xs text-slate-500"></p></div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-700">
                <table class="w-full text-sm">
                    <thead><tr><th class="px-4 py-3 text-left">Service</th><th class="px-3 py-3 text-center">Qty</th><th class="px-4 py-3 text-right">Subtotal</th></tr></thead>
                    <tbody id="previewItems" class="divide-y divide-slate-800"></tbody>
                </table>
            </div>

            <p id="previewCustomer" class="mt-4 whitespace-pre-wrap break-words text-sm text-slate-300"></p>
            <dl class="mt-5 space-y-3 rounded-xl bg-slate-900/50 p-4 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-400">Total amount</dt><dd id="previewTotal" class="font-semibold text-white">₱0.00</dd></div>
                <div id="previewCashRow" class="flex justify-between gap-4"><dt class="text-slate-400">Cash received</dt><dd id="previewCash" class="font-semibold text-slate-200">₱0.00</dd></div>
                <div id="previewChangeRow" class="flex justify-between gap-4 border-t border-slate-700 pt-3"><dt class="font-semibold text-white">Change</dt><dd id="previewChange" class="text-xl font-bold text-teal-300">₱0.00</dd></div>
            </dl>

            <p class="mt-5 whitespace-pre-wrap text-center text-xs text-slate-500">{{ $branding['receipt_footer'] }}</p>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" class="rounded-xl border border-slate-600 bg-slate-800 px-5 py-3 text-sm font-semibold text-slate-200 transition hover:bg-slate-700" onclick="closeReceiptPreview()">Return to order</button>
                <button type="button" id="confirmPurchaseButton" class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-500" onclick="confirmPurchase()">
                    Confirm Purchase
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const cart = [];
const saleForm = document.getElementById('saleForm');
const cashInput = document.getElementById('cash');
const paymentReferenceInput = document.getElementById('paymentReference');
const receiptPreviewModal = document.getElementById('receiptPreviewModal');
let isSubmittingSale = false;

function showCategory(categoryId, button) {
    document.getElementById('serviceSearch').value = '';
    document.querySelectorAll('.service-card').forEach((element) => element.classList.remove('hidden'));
    document.getElementById('noSearchResults').classList.add('hidden');
    document.querySelectorAll('.category-products').forEach((element) => element.classList.add('hidden'));
    document.querySelectorAll('.category-button').forEach((element) => {
        element.classList.remove('border-teal-500', 'bg-teal-600', 'text-white');
    });

    const category = document.getElementById(`category-${categoryId}`);
    if (category) {
        category.classList.remove('hidden');
    }
    if (button) {
        button.classList.add('border-teal-500', 'bg-teal-600', 'text-white');
    }
}

function addProduct(id, name, price) {
    const existingItem = cart.find((item) => item.id === id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ id, name, price: Number(price), quantity: 1 });
    }
    renderCart();
}

function removeProduct(id) {
    const itemIndex = cart.findIndex((item) => item.id === id);
    if (itemIndex !== -1) {
        cart.splice(itemIndex, 1);
    }
    renderCart();
}

function changeQuantity(id, amount) {
    const item = cart.find((cartItem) => cartItem.id === id);
    if (!item) return;
    item.quantity += amount;
    if (item.quantity <= 0) {
        removeProduct(id);
        return;
    }
    renderCart();
}

function renderCart() {
    document.getElementById('clearOrderButton').disabled = cart.length === 0;
    const cartElement = document.getElementById('cart');
    const completeSaleButton = document.getElementById('completeSaleButton');
    let total = 0;
    let itemCount = 0;

    if (cart.length === 0) {
        cartElement.innerHTML = '<tr id="emptyCart"><td colspan="4" class="py-10 text-center text-slate-500"><span class="mx-auto mb-3 flex size-10 items-center justify-center rounded-full bg-slate-800/80 text-slate-500">+</span><strong class="block text-sm font-medium text-slate-300">Your cart is empty</strong><span class="mt-1 block text-xs">Select a service to begin.</span></td></tr>';
        completeSaleButton.disabled = true;
    } else {
        cartElement.innerHTML = cart.map((item, index) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            itemCount += item.quantity;
            return `<tr class="border-b border-slate-800">
                <td class="py-3 pr-2 text-slate-200">${escapeHtml(item.name)}<input type="hidden" name="items[${index}][product_id]" value="${item.id}"><input type="hidden" name="items[${index}][quantity]" value="${item.quantity}"></td>
                <td class="px-2 py-3"><div class="flex items-center justify-center gap-1"><button type="button" aria-label="Decrease quantity" class="size-7 shrink-0 rounded-md bg-slate-800 text-slate-300" onclick="changeQuantity(${item.id}, -1)">−</button><input type="number" min="1" step="1" value="${item.quantity}" aria-label="Quantity" class="w-12 rounded border border-slate-600 bg-slate-900 px-1 py-2 text-center text-white" onchange="setQuantity(${item.id}, this.value)"><button type="button" aria-label="Increase quantity" class="size-7 shrink-0 rounded-md bg-slate-800 text-slate-300" onclick="changeQuantity(${item.id}, 1)">+</button></div></td>
                <td class="py-3 pl-2 text-right font-medium text-white">₱${subtotal.toFixed(2)}</td>
                <td class="py-3 pl-2"><button type="button" class="text-rose-400 hover:text-rose-300" onclick="removeProduct(${item.id})" aria-label="Remove ${escapeHtml(item.name)}">×</button></td>
            </tr>`;
        }).join('');
        completeSaleButton.disabled = false;
    }

    document.getElementById('total').innerText = total.toFixed(2);
    document.getElementById('cartCount').innerText = itemCount;
    document.getElementById('mobileCartCount').textContent = itemCount;
    document.getElementById('mobileTotal').textContent = total.toFixed(2);
    calculateChange();
}

function calculateChange() {
    if (getPaymentMethod() !== 'Cash') {
        cashInput.setCustomValidity('');
        document.getElementById('change').innerText = '0.00';
        return;
    }
    const total = Number.parseFloat(document.getElementById('total').innerText) || 0;
    const cash = Number.parseFloat(cashInput.value) || 0;
    document.getElementById('change').innerText = Math.max(cash - total, 0).toFixed(2);

    if (cart.length > 0 && cash < total) {
        cashInput.setCustomValidity('Cash received must be equal to or greater than the total amount.');
    } else {
        cashInput.setCustomValidity('');
    }
}

function escapeHtml(value) {
    const element = document.createElement('div');
    element.textContent = value;
    return element.innerHTML.replaceAll('"', '&quot;').replaceAll("'", '&#39;');
}

const initialCategoryButton = document.querySelector('.category-button');
if (initialCategoryButton) {
    showCategory(Number(initialCategoryButton.dataset.category), initialCategoryButton);
}

selectPaymentMethod(document.querySelector('input[name="payment_method"]:checked')?.value || 'Cash');

saleForm.addEventListener('submit', (event) => {
    if (isSubmittingSale) {
        return;
    }

    event.preventDefault();
    openReceiptPreview();
});

function openReceiptPreview() {
    if (!cart.length || !saleForm.reportValidity()) return;
    const customer = document.getElementById('customerName').value.trim();
    const notes = document.getElementById('orderNotes').value.trim();
    document.getElementById('previewCustomer').textContent = [customer ? `Customer: ${customer}` : '', notes ? `Notes: ${notes}` : ''].filter(Boolean).join('\n');
    const previewItems = document.getElementById('previewItems');
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const cash = Number.parseFloat(cashInput.value) || 0;
    const paymentMethod = getPaymentMethod();
    const isCashPayment = paymentMethod === 'Cash';
    const paymentReference = paymentReferenceInput.value.trim();

    previewItems.innerHTML = cart.map((item) => {
        const subtotal = item.price * item.quantity;
        return `<tr><td class="px-4 py-3 font-medium text-slate-200">${escapeHtml(item.name)}<span class="mt-0.5 block text-xs font-normal text-slate-500">₱${item.price.toFixed(2)} each</span></td><td class="px-3 py-3 text-center text-slate-300">${item.quantity}</td><td class="px-4 py-3 text-right font-semibold text-white">₱${subtotal.toFixed(2)}</td></tr>`;
    }).join('');

    document.getElementById('previewTotal').textContent = `₱${total.toFixed(2)}`;
    document.getElementById('previewPaymentMethod').textContent = paymentMethod;
    document.getElementById('previewPaymentReference').textContent = paymentReference ? `Ref: ${paymentReference}` : '';
    document.getElementById('previewPaymentReference').classList.toggle('hidden', !paymentReference);
    document.getElementById('previewCashRow').classList.toggle('hidden', !isCashPayment);
    document.getElementById('previewChangeRow').classList.toggle('hidden', !isCashPayment);
    document.getElementById('previewCash').textContent = `₱${cash.toFixed(2)}`;
    document.getElementById('previewChange').textContent = `₱${Math.max(cash - total, 0).toFixed(2)}`;
    receiptPreviewModal.classList.remove('hidden');
    receiptPreviewModal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    document.getElementById('confirmPurchaseButton').focus();
}

function closeReceiptPreview() {
    if (isSubmittingSale) {
        return;
    }

    receiptPreviewModal.classList.add('hidden');
    receiptPreviewModal.classList.remove('flex');
    document.body.style.overflow = '';
    document.getElementById('completeSaleButton').focus();
}

function handlePreviewBackdrop(event) {
    if (event.target === receiptPreviewModal) {
        closeReceiptPreview();
    }
}

function confirmPurchase() {
    isSubmittingSale = true;
    const confirmButton = document.getElementById('confirmPurchaseButton');
    confirmButton.disabled = true;
    confirmButton.textContent = 'Completing sale...';
    saleForm.submit();
}

document.addEventListener('keydown', (event) => {
    const previewOpen = !receiptPreviewModal.classList.contains('hidden');
    if (previewOpen && event.key === 'Tab') {
        const buttons = [...receiptPreviewModal.querySelectorAll('button:not([disabled])')];
        const first = buttons[0], last = buttons[buttons.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
    if (!previewOpen) {
        const editing = event.target.matches('input, textarea, select, [contenteditable]');
        if (event.key === '/' && !editing) { event.preventDefault(); document.getElementById('serviceSearch').focus(); }
        if (event.altKey && event.key.toLowerCase() === 'c') { event.preventDefault(); cashInput.focus(); }
        if (event.altKey && event.key === 'Enter') { event.preventDefault(); openReceiptPreview(); }
    }
    if (event.key === 'Escape' && !receiptPreviewModal.classList.contains('hidden')) {
        closeReceiptPreview();
    }
});

function setQuantity(id, value) {
    const item = cart.find((entry) => entry.id === id);
    const quantity = Number(value);
    if (item && Number.isSafeInteger(quantity) && quantity > 0) item.quantity = quantity;
    renderCart();
}

function setCash(amount) {
    cashInput.value = (amount ?? cart.reduce((sum, item) => sum + item.price * item.quantity, 0)).toFixed(2);
    calculateChange();
}

function getPaymentMethod() {
    return document.querySelector('input[name="payment_method"]:checked')?.value || 'Cash';
}

function selectPaymentMethod(method) {
    const isCashPayment = method === 'Cash';
    const selectedInput = document.querySelector(`input[name="payment_method"][value="${method}"]`);
    if (selectedInput) selectedInput.checked = true;
    document.querySelectorAll('.payment-method-option').forEach((option) => {
        const selected = option.querySelector('input').value === method;
        option.classList.toggle('border-teal-500', selected);
        option.classList.toggle('bg-teal-500/10', selected);
        option.classList.toggle('text-teal-200', selected);
    });
    document.getElementById('cashPaymentFields').classList.toggle('hidden', !isCashPayment);
    document.getElementById('referencePaymentFields').classList.toggle('hidden', isCashPayment);
    cashInput.disabled = !isCashPayment;
    cashInput.required = isCashPayment;
    paymentReferenceInput.disabled = isCashPayment;
    paymentReferenceInput.required = !isCashPayment;
    document.getElementById('paymentReferenceLabel').textContent = method === 'Card' ? 'Approval code or last 4 digits' : `${method} reference number`;
    paymentReferenceInput.placeholder = method === 'Card' ? 'Enter approval code or last 4 digits' : 'Enter transaction reference';
    calculateChange();
}

function clearOrder() {
    if (!cart.length || !window.confirm('Clear all items and payment details from this order?')) return;
    cart.length = 0;
    saleForm.reset();
    selectPaymentMethod('Cash');
    renderCart();
}

function filterServices() {
    const query = document.getElementById('serviceSearch').value.trim().toLowerCase();
    if (!query) {
        const active = document.querySelector('.category-button.bg-teal-600') || initialCategoryButton;
        if (active) showCategory(Number(active.dataset.category), active);
        return;
    }
    let matches = 0;
    document.querySelectorAll('.category-products').forEach((category) => {
        let count = 0;
        category.querySelectorAll('.service-card').forEach((card) => {
            const match = card.dataset.serviceName.toLowerCase().includes(query);
            card.classList.toggle('hidden', !match);
            if (match) count++;
        });
        category.classList.toggle('hidden', count === 0);
        matches += count;
    });
    document.getElementById('noSearchResults').classList.toggle('hidden', matches > 0);
}
</script>
@endpush
