@push('styles')
<style>
    .service-form label { margin-bottom: 8px; color: #cbd5e1; font-size: 14px; font-weight: 500; }
    .service-form input, .service-form select, .service-form textarea { padding: 12px 14px; background: #101d2e; border-color: #34465c; color: #f1f5f9; font-size: 14px; line-height: 1.5; }
    .service-form input:focus, .service-form select:focus, .service-form textarea:focus { border-color: #14b8a6; box-shadow: 0 0 0 3px rgba(20,184,166,.12); }
    .service-form [aria-invalid="true"] { border-color: #fb7185; }
</style>
@endpush

<div class="mx-auto max-w-3xl space-y-5">
    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 hover:text-teal-300"><span aria-hidden="true">←</span> Back to services</a>
    <section class="overflow-hidden rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] shadow-xl shadow-black/10">
        <header class="border-b border-slate-700/70 p-5 sm:p-7">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-teal-400">Service catalog</p>
            <h2 class="text-2xl font-bold tracking-tight text-white">{{ $product ? 'Edit Service' : 'Add Service' }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-400">Set the service details and price displayed to your cashiers.</p>
        </header>
        <form method="POST" action="{{ $product ? route('products.update', $product) : route('products.store') }}" class="service-form space-y-6 p-5 sm:p-7">
            @csrf
            @if($product) @method('PUT') @endif

            <div>
                <label for="serviceName">Service name <span class="text-teal-300">*</span></label>
                <input id="serviceName" name="name" type="text" value="{{ old('name', $product?->name) }}" maxlength="255" placeholder="Example: A4 Full Color Printing" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @error('name') aria-describedby="nameError" @enderror>
                @error('name')<p id="nameError" class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="serviceCategory">Category <span class="text-teal-300">*</span></label>
                    <select id="serviceCategory" name="category_id" required aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}" @error('category_id') aria-describedby="categoryError" @enderror>
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p id="categoryError" class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="serviceUnit">Unit <span class="text-teal-300">*</span></label>
                    <select id="serviceUnit" name="unit" required aria-invalid="{{ $errors->has('unit') ? 'true' : 'false' }}" @error('unit') aria-describedby="unitError" @enderror>
                        @foreach(['page' => 'Page', 'piece' => 'Piece', 'set' => 'Set', 'copy' => 'Copy'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('unit', $product?->unit ?? 'page') === $value)>{{ $label }}</option>
                        @endforeach
                        @if($product && !in_array($product->unit, ['page', 'piece', 'set', 'copy']))
                            <option value="{{ $product->unit }}" @selected(old('unit', $product->unit) === $product->unit)>{{ ucfirst($product->unit) }}</option>
                        @endif
                    </select>
                    @error('unit')<p id="unitError" class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="servicePrice">Price per unit (₱) <span class="text-teal-300">*</span></label>
                <input id="servicePrice" name="price" type="number" value="{{ old('price', $product?->price) }}" min="0" step="0.01" placeholder="0.00" required aria-invalid="{{ $errors->has('price') ? 'true' : 'false' }}" @error('price') aria-describedby="priceError" @enderror>
                @error('price')<p id="priceError" class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
                <p class="mt-2 text-xs text-slate-500">The POS multiplies this price by the quantity ordered.</p>
            </div>

            <div>
                <label for="serviceDescription">Description <span class="font-normal text-slate-500">(optional)</span></label>
                <textarea id="serviceDescription" name="description" rows="4" placeholder="Add details to help staff identify this service." aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" @error('description') aria-describedby="descriptionError" @enderror>{{ old('description', $product?->description) }}</textarea>
                @error('description')<p id="descriptionError" class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-700/70 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-600 bg-slate-800/60 px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-slate-700">Cancel</a>
                <button type="submit" class="rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-500">{{ $product ? 'Save Changes' : 'Save Service' }}</button>
            </div>
        </form>
    </section>
</div>
