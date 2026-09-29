<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Printing POS</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
        }

        .navbar {
            background: #1f2937;
            color: white;
            padding: 18px 25px;
            display: flex;
            justify-content: space-between;
        }

        .container {
            padding: 20px;
        }

        .layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .panel {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .category-btn {
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            background: #374151;
            color: white;
            cursor: pointer;
        }

        .category-btn:hover {
            background: #2563eb;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .product-btn {
            background: #f9fafb;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            text-align: left;
        }

        .product-btn:hover {
            background: #eff6ff;
            border-color: #2563eb;
        }

        .product-name {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        .product-price {
            color: #2563eb;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .total {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
            margin: 20px 0;
        }

        .cash-input {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .change {
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
        }

        .complete-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 6px;
            background: #16a34a;
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }

        .complete-btn:hover {
            background: #15803d;
        }

        .remove-btn {
            border: none;
            background: #dc2626;
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
        }

        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            background: #dcfce7;
            color: #166534;
        }

        .error {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            background: #fee2e2;
            color: #991b1b;
        }

        @media(max-width: 900px) {

            .layout {
                grid-template-columns: 1fr;
            }

            .products {
                grid-template-columns: repeat(2, 1fr);
            }

        }

    </style>

</head>

<body>

<div class="navbar">

    <strong>PRINTING POS</strong>

    <span>
        Today's Sales:
        ₱{{ number_format($todaySales, 2) }}
    </span>

</div>

<div class="container">

    @if(session('success'))

        <div class="alert">
            {{ session('success') }}
        </div>

    @endif

    @if($errors->any())

        <div class="error">

            @foreach($errors->all() as $error)

                <div>{{ $error }}</div>

            @endforeach

        </div>

    @endif

    <div class="layout">

        <!-- SERVICES -->

        <div class="panel">

            <h2>Services</h2>

            <div class="categories">

                @foreach($categories as $category)

                    <button
                        class="category-btn"
                        onclick="showCategory({{ $category->id }})">

                        {{ $category->name }}

                    </button>

                @endforeach

            </div>


            @foreach($categories as $category)

                <div
                    class="category-products"
                    id="category-{{ $category->id }}"
                    style="display:none;">

                    <h3>
                        {{ $category->name }}
                    </h3>

                    <div class="products">

                        @foreach($category->products as $product)

                            <button
                                class="product-btn"
                                onclick="addProduct(
                                    {{ $product->id }},
                                    '{{ addslashes($product->name) }}',
                                    {{ $product->price }}
                                )">

                                <span class="product-name">
                                    {{ $product->name }}
                                </span>

                                <span class="product-price">

                                    ₱{{ number_format($product->price, 2) }}

                                    / {{ $product->unit }}

                                </span>

                            </button>

                        @endforeach

                    </div>

                </div>

            @endforeach

        </div>


        <!-- CURRENT SALE -->

        <div class="panel">

            <h2>Current Sale</h2>

            <form
                method="POST"
                action="{{ route('pos.store') }}"
                id="saleForm">

                @csrf

                <table>

                    <thead>

                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>

                    </thead>

                    <tbody id="cart">

                    </tbody>

                </table>

                <div class="total">

                    Total:
                    ₱<span id="total">0.00</span>

                </div>

                <label>
                    Cash Received
                </label>

                <input
                    type="number"
                    name="cash_received"
                    id="cash"
                    class="cash-input"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    oninput="calculateChange()"
                    required>

                <div class="change">

                    Change:
                    ₱<span id="change">0.00</span>

                </div>

                <button
                    type="submit"
                    class="complete-btn">

                    COMPLETE SALE

                </button>

            </form>

        </div>

    </div>

</div>


<script>

let cart = [];


function showCategory(categoryId)
{
    document
        .querySelectorAll('.category-products')
        .forEach(element => {

            element.style.display = 'none';

        });

    document
        .getElementById('category-' + categoryId)
        .style.display = 'block';
}


function addProduct(id, name, price)
{
    let existing = cart.find(
        item => item.id === id
    );

    if (existing) {

        existing.quantity++;

    } else {

        cart.push({
            id: id,
            name: name,
            price: price,
            quantity: 1
        });

    }

    renderCart();
}


function removeProduct(id)
{
    cart = cart.filter(
        item => item.id !== id
    );

    renderCart();
}


function changeQuantity(id, amount)
{
    let item = cart.find(
        item => item.id === id
    );

    if (!item) {
        return;
    }

    item.quantity += amount;

    if (item.quantity <= 0) {

        removeProduct(id);

        return;

    }

    renderCart();
}


function renderCart()
{
    const cartElement =
        document.getElementById('cart');

    cartElement.innerHTML = '';

    let total = 0;

    cart.forEach((item, index) => {

        let subtotal =
            item.price * item.quantity;

        total += subtotal;

        cartElement.innerHTML += `

            <tr>

                <td>

                    ${item.name}

                    <input
                        type="hidden"
                        name="items[${index}][product_id]"
                        value="${item.id}">

                </td>

                <td>

                    <button
                        type="button"
                        onclick="changeQuantity(
                            ${item.id},
                            -1
                        )">
                        -
                    </button>

                    ${item.quantity}

                    <button
                        type="button"
                        onclick="changeQuantity(
                            ${item.id},
                            1
                        )">
                        +
                    </button>

                    <input
                        type="hidden"
                        name="items[${index}][quantity]"
                        value="${item.quantity}">

                </td>

                <td>
                    ₱${subtotal.toFixed(2)}
                </td>

                <td>

                    <button
                        type="button"
                        class="remove-btn"
                        onclick="removeProduct(
                            ${item.id}
                        )">

                        X

                    </button>

                </td>

            </tr>

        `;

    });

    document.getElementById('total')
        .innerText = total.toFixed(2);

    calculateChange();
}


function calculateChange()
{
    let total =
        parseFloat(
            document.getElementById('total').innerText
        ) || 0;

    let cash =
        parseFloat(
            document.getElementById('cash').value
        ) || 0;

    let change =
        cash - total;

    if (change < 0) {
        change = 0;
    }

    document.getElementById('change')
        .innerText = change.toFixed(2);
}


showCategory(
    {{ $categories->first()->id ?? 0 }}
);

</script>

</body>

</html>