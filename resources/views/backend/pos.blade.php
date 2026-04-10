@extends('backend.master')

@section('content')
    <div id="container" class="w-full grid grid-cols-1 m lg:grid-cols-8 gap-2 h-screen overflow-hidden">
        <div id="mainContent"
            class=" tab_control  lg:col-span-6 md:col-span-4 col-span-2  border-1 border-default border-dashed rounded-base">

            <div id="category_show"
                class=" flex justify-between  mb-2 border-b border-default  mx-5 sticky top-0 bg-amber-400 z-10">
                <div class="flex items-center gap-2 px-4 py-3">
                    @csrf
                    <!-- Field -->
                    <select id="field-select" class="h-10 px-3 border rounded-md text-sm focus:ring-brand focus:border-brand">
                        <option value="bar_code">Barcode</option>
                        <option value="code">Code</option>
                        <option value="name" selected>Name</option>
                        <option value="description">Description</option>
                    </select>

                    <!-- Search -->
                    <div class="relative flex-1">
                        <input type="text" id="search-dropdown"
                            class="w-full h-10 pl-9 pr-3 border rounded-md text-sm
                       focus:ring-brand focus:border-brand"
                            placeholder="Search product">

                    </div>



                </div>


                <ul class="flex overflow-x-auto border-b border-gray-200" id="category-tabs">

                    <li class="me-2">
                        <button data-category="top"
                            class="px-5 py-3 border-b-2 border-transparent text-gray-600 font-semibold transition-all duration-200
                       hover:text-black hover:border-purple-600 text-nowrap
                       focus:outline-none focus:text-black focus:border-purple-600
                       active:text-purple-700">
                            Recently
                        </button>
                    </li>
                    @foreach ($categories as $categoryName => $products)
                        <li class="me-2">
                            <button
                                class="px-5 py-3 border-b-2 border-transparent text-gray-600 font-semibold transition-all duration-200
                       hover:text-black hover:border-purple-600 text-nowrap
                       focus:outline-none focus:text-black focus:border-purple-600
                       active:text-purple-700"
                                data-category="{{ $categoryName }}">
                                {{ $categoryName }}
                            </button>
                        </li>
                    @endforeach
                </ul>


            </div>

            <div id="default-styled-tab-content">
                <div class="hidden rounded-base bg-neutral-secondary-soft" id="styled-profile" role="tabpanel"
                    aria-labelledby="profile-tab">
                    {{-- Tab Control  --}}
                    <div class="w-full grid grid-cols-5 gap-2 p-3 bg-slate-200  mb-12 pb-16">
                        Top
                    </div>
                </div>
            </div>
            <div class="overflow-auto" id="tab-content">


            </div>

        </div>


        {{-- Toggle view  --}}

        <button type="button" id="toggleSidebar">
            <i class="fa-solid fa-caret-right"></i>
        </button>



        <div id="sidebar" class="flex flex-col max-h-full lg:col-span-2">
            <div id="inner-sidebar" class="sticky top-0 bg-slate-100 border-l border-default">

                <div class=" overflow-y-auto bg-white w-full">

                    @livewire('cart')
                </div>
            </div>
        </div>
    </div>

    <script>
        let factor = @json($factor);
        let currency_name = @json($currency_name);

        window.addEventListener("change-currency", (e) => {
            factor = e.detail[0].factor;
            currency_name = e.detail[0].currency_name;

            document.querySelectorAll(".pricing").forEach((element) => {

                const basePrice = parseFloat(element.getAttribute("data-base-price")) || 0;
                console.log(element.getAttribute("data-base-price")); // Debug: Check base price
                const newPrice = basePrice * factor;

                // ✅ Remove trailing .00 if it's a whole number
                const displayPrice = Number.isInteger(newPrice) ? newPrice : newPrice.toFixed(2);

                element.textContent = `${displayPrice} ${currency_name}`;
            });
        });


        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');

            if (collapsed) {
                mainContent.classList.add('expanded');
                toggleBtn.querySelector('i').style.transform = 'rotate(180deg)';
            } else {
                mainContent.classList.remove('expanded');
                toggleBtn.querySelector('i').style.transform = 'rotate(0deg)';
            }

        });



        document.addEventListener('click', function(e) {
            const card = e.target.closest('.card_style');
            if (!card) return;

            const isSuccess = card.classList.contains('card_style_success');

            // ❌ FAIL → FLOAT TEXT ONLY
            if (!isSuccess) {
                const float = document.createElement('div');
                float.className = 'no-stock-float';
                float.textContent = '🚫 No Stock';

                float.style.left = e.pageX + 'px';
                float.style.top = e.pageY + 'px';

                document.body.appendChild(float);
                setTimeout(() => float.remove(), 1000);

                return; // ⛔ STOP here (no icons)
            }

            // ✅ SUCCESS → BURST ICONS
            const count = 8;
            const burst = document.createElement('div');

            burst.className = 'cart-burst';
            burst.style.left = e.pageX + 'px';
            burst.style.top = e.pageY + 'px';

            for (let i = 0; i < count; i++) {
                const icon = document.createElement('span');

                const isCart = Math.random() > 0.5;
                icon.className = `cart-icon ${isCart ? 'cart' : 'plus'}`;
                icon.textContent = isCart ? '🛒' : '✅';

                const angle = Math.random() * Math.PI * 2;
                const distance = 100;

                icon.style.setProperty('--x', `${Math.cos(angle) * distance}px`);
                icon.style.setProperty('--y', `${Math.sin(angle) * distance}px`);
                icon.style.animationDelay = `${i * 0.03}s`;

                burst.appendChild(icon);
            }

            document.body.appendChild(burst);
            setTimeout(() => burst.remove(), 1000);
        });

        // function closeAllItems() {
        //     document.querySelectorAll('.bonus').forEach(b => {
        //         b.classList.add('hidden');
        //     });

        //     document.querySelectorAll('.arrow').forEach(a => {
        //         a.classList.remove('rotate-180');
        //     });
        // }

        function openItem(card) {
            const body = card.querySelector('.bonus');
            const arrow = card.querySelector('.arrow');

            body.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        }

        const tabs = document.querySelectorAll('#category-tabs button');
        const tabContent = document.getElementById('tab-content');

        // Convert Blade categories JSON into JS object
        let productsByCategory = @json($categories);
        async function reloadProducts() {
            try {
                const res = await fetch('/pos/products');
                const data = await res.json();


                // 🔁 Reassign global variable
                productsByCategory = data;

                renderCategoryProducts(current_tab);

            } catch (err) {
                console.error("Failed to reload products:", err);
            }
        }


        // Helper: sort products by total_stock DESC
        function sortByStock(products) {
            return products.sort((a, b) => b.total_stock - a.total_stock);
        }
        let current_tab = 'NA';



        // Event listener for tabs
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const category = tab.dataset.category; // get clicked category
                current_tab = category;
                renderCategoryProducts(category); // render products

                // Update active tab styling
                tabs.forEach(t => {
                    t.classList.remove('border-brand', 'text-black');
                    t.classList.add('text-gray-600', 'border-transparent');
                });
                tab.classList.add('border-brand', 'text-black');
                tab.classList.remove('text-gray-600', 'border-transparent');
            });
        });

        // Load first tab (Top Product)
        if (tabs.length) tabs[0].click();

        function round2(value) {
            return Number(Math.round((value + Number.EPSILON) * 100) / 100);
        }
        // Render Category Products
        async function renderCategoryProducts(category) {
            tabContent.innerHTML = '<p class="p-4">Loading...</p>';
            document.body.style.cursor = 'wait';

            try {
                let products = [];
                if (category === 'top') {
                    products = sortByStock(Object.values(productsByCategory).flat()).slice(0, 30);
                } else {
                    products = sortByStock(productsByCategory[category] || []);
                }

                let html =
                    '<div class="min_heigh_70 w-full grid grid-cols-1 lg:grid-cols-6 md:grid-cols-4 gap-1 p-3 bg-slate-200 mb-12 pb-16">';

                products.forEach(product => {
                    const imageSrc = product.image ?
                        `assets/startic_img/${product.image}` : ``;


                    const price = Number(product.sell_price || 0);
                    const vatRate = Number(product.vat || 0);
                    const finalPrice = round2(price + (price * vatRate / 100));

                    // Discounted price
                    const discountPercent = Number(product.discount_percent || 0);
                    const discountedPrice = round2(finalPrice - (finalPrice * discountPercent / 100));
                    // Stock color logic using percentage
                    let stockColor = 'text-gray-400'; // default out of stock

                    if (product.total_stock > 0) {
                        const stockPercent = (product.total_stock / product.max_stock) * 100;

                        if (product.total_stock > product.max_stock) {
                            stockColor = 'text-green-600'; // overstock
                        } else if (stockPercent < 50) {
                            stockColor = 'text-red-500'; // low stock

                        } else {
                            stockColor = 'text-green-600'; // enough stock
                        }
                    }
                    let style_click = `card_style_fail`;

                    if (product.total_stock > 0 || product.track_stock == 0) {
                        style_click = `card_style_success`;
                    }


                    html += `
                     <div  class="card_style ${style_click} bg-neutral-primary-soft block max-w-sm border border-default shadow-xs relative">
                                <button class="add-to-cart-btn w-full flex flex-col h-full" data-product='${JSON.stringify(product)}'>

                                    <!-- IMAGE -->
                                    <div class="relative w-full">

                                      ${imageSrc
                                        ? `<img class="object-cover w-full" id="product-image${product.id}"
                                                                                                                                                                                                                                                            loading="lazy"
                                                                                                                                                                                                                                                            style="max-height:150px; min-height:150px;"
                                                                                                                                                                                                                                                            src="${imageSrc}"
                                                                                                                                                                                                                                                            onerror="this.outerHTML=\`
                                                    <div class='flex items-center justify-center w-full h-[150px] bg-gray-100'>
                                                        <span class='text-gray-400'>No Image</span>
                                                    </div>\`"
                                                                                                                                                                                                                                                        />`
                                        : `<div class="flex items-center justify-center w-full h-[150px] bg-gray-100">
                                                                                                                                                                                                                                                            <span class="text-gray-400">No Image</span>
                                                                                                                                                                                                                                                    </div>`
                                    }

                                        <i class="info fa-solid fa-circle-info absolute top-1 right-1 text-blue-500 text-sm"></i>

                                        ${product.discount_percent != 0 ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <span class="absolute top-1 left-1 inline-flex items-center bg-red-500 text-white text-[10px] font-semibold px-1.5 py-0.5 rounded-sm shadow-md">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fa-solid fa-tag mr-0.5"></i>${product.discount_percent}% Off
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </span>` : ''}
                                    </div>

                                    <!-- TEXT CONTENT -->
                                    <div class="flex flex-col justify-between p-2 mt-2 h-[130px]">
                                        <!-- h-[130px] = fixed height for bottom content, adjust as needed -->

                                        <div>

                                            <h5 class="text-sm line-clamp-2">
                                                ${product.name}
                                            </h5>
                                        </div>

                                        <div  class="text-center mt-1">
                                            <p class="text-xs">
                                            ${product.track_stock ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="${stockColor} fa-solid fa-boxes-stacked"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <span class="${stockColor}">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${product.total_stock > 0 ? product.total_stock + ' ' + product.unit : 'No stock'}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        &ensp;` : ''}

                                            ${product.discount_percent != 0
                                                ? `<br><del data-base-price="${finalPrice.toFixed(2)}"  class="pricing text-gray-400 text-sm">${finalPrice.toFixed(2) * factor}  ${currency_name}</del> → <span data-base-price="${discountedPrice.toFixed(2)}" class="${stockColor} pricing font-semibold text-sm">${discountedPrice.toFixed(2) *factor} ${currency_name}</span>`
                                                : `<span data-base-price="${finalPrice.toFixed(2)}"  class="pricing font-semibold text-sm">${finalPrice.toFixed(2) * factor} ${currency_name}</span>`
                                            }
                                        </p>
            </div>
        </div>

    </button>
</div>

            `;
                });

                html += '</div>';
                tabContent.innerHTML = html;

                // Initialize buttons (if you have any JS logic for add-to-cart)
                initAddToCartButtons();

            } catch (err) {
                tabContent.innerHTML = '<p class="p-4 text-red-500">Failed to load products.</p>';
                console.error(err);
            } finally {
                document.body.style.cursor = 'default';
            }
        }

        let lastClick = 0;

        tabContent.addEventListener('click', e => {
            const btn = e.target.closest('.add-to-cart-btn');
            if (!btn) return;

            const now = Date.now();
            if (now - lastClick < 300) return; // block fast double clicks
            lastClick = now;

            const productJson = btn.dataset.product;


            Livewire.dispatch('add-product', productJson); // ONLY this
        });

        const searchInput_product = document.getElementById('search-dropdown');
        const fieldSelect = document.getElementById('field-select'); // optional dropdown for barcode/code/name/desc


        searchInput_product.addEventListener('input', async () => {
            const query = searchInput_product.value.trim();
            const field = fieldSelect.value || 'name';


            if (!query) {
                activeTab.click();
                return;
            }

            try {
                tabContent.innerHTML = `
            <div class="min_heigh_70 w-full grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-2 p-3 bg-slate-200 mb-12 pb-16">
                <div class="col-span-full text-center">Loading...</div>
            </div>
        `;

                const response = await fetch('/products/category/search', {
                    method: 'POST',
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                            .value,
                        Accept: "application/json",
                        "Content-Type": "application/json", // 🔥 must have
                    },
                    body: JSON.stringify({
                        field: field,
                        query: query,

                    })
                });

                if (!response.ok) throw new Error(response.status);

                const products = await response.json();

                tabContent.innerHTML = `
            <div class="min_heigh_70 w-full grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-2 p-3 bg-slate-200 mb-12 pb-16">
                ${
                    products.length
                        ? products.map(p => renderProductCard(p)).join('')
                        : `<div class="col-span-full text-center text-gray-500">No products found</div>`
                }
            </div>
        `;

            } catch (err) {
                console.error(err);
                tabContent.innerHTML = `
            <div class="col-span-full p-4 text-red-500">Search failed.</div>
        `;
            }
        });

        function renderProductCard(product) {

            const imageSrc = product.image ?
                `assets/startic_img/${product.image}` :
                'assets/defult/placeholder.jpg';

            const price = Number(product.sell_price || 0);
            const vatRate = Number(product.vat || 0);
            const finalPrice = round2(price + (price * vatRate / 100));

            // Discounted price
            const discountPercent = Number(product.discount_percent || 0);
            const discountedPrice = round2(finalPrice - (finalPrice * discountPercent / 100));

            // Stock color logic
            let stockColor = 'text-gray-400'; // default out of stock
            if (product.total_stock > 0) {
                const stockPercent = (product.total_stock / product.max_stock) * 100;

                if (product.total_stock > product.max_stock) {
                    stockColor = 'text-green-600'; // overstock
                } else if (stockPercent < 50) {
                    stockColor = 'text-red-500'; // low stock

                } else {
                    stockColor = 'text-green-600'; // enough stock
                }
            }
            let style_click = `card_style_fail`;

            if (product.total_stock > 0) {
                style_click = `card_style_success`;
            }
            return `
                            <div class="card_style ${style_click} bg-neutral-primary-soft block max-w-sm border border-default shadow-xs relative">
                                <button class="add-to-cart-btn w-full flex flex-col h-full" data-product='${JSON.stringify(product)}'>

                                    <!-- IMAGE -->
                                    <div class="relative w-full">
                                        <img id="product-image${product.id}" class="object-cover w-full" loading="lazy" style="max-height:150px;min-height:150px;"
                                            src="${imageSrc}" onerror="this.src='assets/defult/placeholder.jpg'" alt="${product.name}" />
                                        <i class="info fa-solid fa-circle-info absolute top-1 right-1 text-blue-500 text-sm"></i>
                                        ${product.discount_percent != 0 ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <span class="absolute top-1 left-1 inline-flex items-center bg-red-500 text-white text-[10px] font-semibold px-1.5 py-0.5 rounded-sm shadow-md">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fa-solid fa-tag mr-0.5"></i>${product.discount_percent}% Off
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </span>` : ''}
                                    </div>

                                    <!-- TEXT CONTENT -->
                                    <div class="flex flex-col justify-between p-2 mt-2 h-[130px]">
                                        <div>

                                            <h5 class="text-sm line-clamp-2">${product.name}</h5>
                                        </div>

                                        <div class="text-center mt-1">
                                            <p class="text-xs">
                                                ${product.track_stock ? `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="${stockColor} fa-solid fa-boxes-stacked"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <span class="${stockColor}">${product.total_stock > 0 ? product.total_stock + ' ' + product.unit : 'No stock'}</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                &ensp;` : ''}

                                       ${product.discount_percent != 0
                                                ? `<del data-base-price="${finalPrice.toFixed(2)}"  class="pricing text-gray-400 text-[10px]">${finalPrice.toFixed(2) * factor}  ${currency_name}</del> → <span data-base-price="${discountedPrice.toFixed(2)}" class="${stockColor} pricing font-semibold text-sm">${discountedPrice.toFixed(2) *factor} ${currency_name}</span>`
                                                : `<span data-base-price="${finalPrice.toFixed(2)}"  class="pricing font-semibold text-sm">${finalPrice.toFixed(2) * factor} ${currency_name}</span>`
                                            }

                                            </p>
                                        </div>
                                    </div>

                                </button>
                            </div>`;
        }








        function initAddToCartButtons() {

            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.removeEventListener('click', btn._addToCartListener); // remove old listener if exists
                btn._addToCartListener = () => {
                    const productJson = btn.dataset.product; // keep JSON string
                    Livewire.dispatch('add-product', productJson);
                };
                btn.addEventListener('click', btn._addToCartListener);
            });
        }

        window.addEventListener('stock-alert', event => {
            alert(event.detail.message);
        });



        function normalizePrice(value) {
            const num = Number(value);

            // Count decimal digits safely
            const decimalPart = num.toString().split('.')[1] || '';

            if (decimalPart.length > 3) {
                return Number(num.toFixed(3));
            }

            return num;
        }






        const input = document.getElementById("customerSearch");
        const list = document.getElementById("customerList");
        const hiddenInput = document.getElementById("customerValue");

        input.addEventListener("input", async () => {
            const value = input.value.trim();

            if (value.length === 0) {
                list.classList.add("hidden");
                return;
            }

            try {
                const res = await fetch(`{{ route('customers.search') }}?q=${encodeURIComponent(value)}`);
                const data = await res.json();

                // Clear previous list
                list.innerHTML = '';

                if (data.length === 0) {
                    list.innerHTML = '<li class="px-3 py-2 text-sm text-gray-500">No results found</li>';
                } else {
                    data.forEach(customer => {

                        const li = document.createElement('li');
                        li.textContent = `${customer.customer_code} - ${customer.name}`;
                        li.dataset.value = customer.customer_code;
                        li.className = 'px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm';
                        li.addEventListener('click', () => {
                            input.value = li.textContent;

                            hiddenInput.value = customer.customer_code;



                            list.classList.add('hidden');
                            hiddenInput.dispatchEvent(new Event('input'));

                        });
                        list.appendChild(li);
                    });
                }

                list.classList.remove("hidden");
            } catch (err) {
                console.error(err);
            }
        });



        // Hide list when clicking outside
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".relative")) {
                list.classList.add("hidden");
            }
        });


        function setPage(page) {
            // send event to Livewire

            Livewire.dispatch('pageSelected', {
                page: page
            });

        }



        function getTotalStock(product) {
            if (!Array.isArray(product.warehouses)) return 0;

            return product.warehouses.reduce(
                (sum, wh) => sum + (Number(wh.stock_qty) || 0),
                0
            );
        }
    </script>
@endsection







@push('modals')

    {{-- <ADD CURRENCY> --}}
    <div id="static-modal-currency-exchange" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <h3 class="text-lg font-medium text-heading">
                        Currency Exchange
                    </h3>
                    <button type="button"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="static-modal-currency-exchange">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form id="currencyForm">
                    @csrf
                    <div id="main_currency_box" class="grid grid-cols-1 gap-2 space-y-4 md:space-y-6 py-4 md:py-6">


                        @foreach ($currency as $item)
                            <div
                                class=" space-x-0 space-y-4 sm:space-y-0 sm:space-x-4 rtl:space-x-reverse flex items-center flex-col sm:flex-row mb-4">
                                <input type="hidden" name="currency[{{ $item->id }}][id]" value="{{ $item->id }}">

                                <div class="flex -space-x-px">

                                    <div class="relative w-full">
                                        <input type="number" value="1" disabled
                                            class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-s-base focus:ring-brand focus:border-brand px-3 py-2.5 placeholder:text-body"
                                            placeholder="1 USD" required />
                                    </div>
                                    <button
                                        class="inline-flex items-center shrink-0 z-10 text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-fg-brand focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded-e-base text-sm px-4 py-2.5 focus:outline-none"
                                        type="button">
                                        USD &ensp;
                                    </button>
                                </div>
                                <svg class="mx-2 w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m16 10 3-3m0 0-3-3m3 3H5v3m3 4-3 3m0 0 3 3m-3-3h14v-3" />
                                </svg>
                                <div class="flex -space-x-px">

                                    <div class="relative w-full">


                                        <input type="number" name="currency[{{ $item->id }}][factor]"
                                            value="{{ (float) $item->factor }}"
                                            class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-l-sm focus:ring-brand focus:border-brand px-3 py-2.5 placeholder:text-body">
                                    </div>
                                    <div>
                                        <input type="text" name="currency[{{ $item->id }}][name]"
                                            class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm  focus:ring-brand focus:border-brand px-3 py-2.5 placeholder:text-body"
                                            value="{{ $item->name }}">
                                    </div>
                                    <div>



                                        <input type="text" name="currency[{{ $item->id }}][code]"
                                            value="{{ $item->code }}"
                                            class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-e-sm focus:ring-brand focus:border-brand px-3 py-2.5 placeholder:text-body">
                                    </div>
                                    <div>


                                        <input type="radio" name="default_currency" value="{{ $item->id }}"
                                            {{ $item->is_default ? 'checked' : '' }}
                                            class="w-4 h-4 text-brand focus:ring-brand">

                                    </div>
                                </div>

                            </div>
                            <br>
                        @endforeach


                    </div>
                    <!-- New Currency Input -->
                    <div
                        class=" space-x-0 space-y-4 sm:space-y-0 sm:space-x-4 rtl:space-x-reverse flex items-center flex-col sm:flex-row mb-4">
                        <div
                            class="space-x-0 space-y-4 sm:space-y-0 sm:space-x-4 rtl:space-x-reverse flex items-center flex-col sm:flex-row mb-4">
                            <div class="flex -space-x-px">

                                <div class="flex -space-x-px">

                                    <div class="relative w-full">
                                        <input type="number" value="1" disabled
                                            class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-s-base focus:ring-brand focus:border-brand px-2 py-2 placeholder:text-body"
                                            required />
                                    </div>
                                    <button
                                        class="inline-flex items-center shrink-0 z-10 text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-fg-brand focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded-e-base text-sm px-2 py-2 focus:outline-none"
                                        type="button">
                                        USD &ensp;
                                    </button>
                                </div>
                                <div class="p-2">
                                    <svg class="mx-2 w-4 h-4 text-body" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m16 10 3-3m0 0-3-3m3 3H5v3m3 4-3 3m0 0 3 3m-3-3h14v-3" />
                                    </svg>
                                </div>

                                <div class="relative w-full">
                                    <input type="number" name="new_currency[factor]" value=""
                                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-l-sm focus:ring-brand focus:border-brand px-2 py-2 placeholder:text-body"
                                        placeholder="Factor">
                                </div>
                                <div class="relative w-full">
                                    <input type="text" name="new_currency[name]" value=""
                                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm  focus:ring-brand focus:border-brand px-3 py-2 placeholder:text-body"
                                        placeholder="Name">
                                </div>
                                <div>
                                    <input type="text" name="new_currency[code]" value=""
                                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-e-sm focus:ring-brand focus:border-brand px-3 py-2 placeholder:text-body"
                                        placeholder="Code">
                                </div>
                                <div>
                                    <input type="radio" name="default_currency" value="new"
                                        class="w-4 h-4 text-brand focus:ring-brand">

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- Modal footer -->
                <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                    <button onclick="saveCurrencies()" {{-- data-modal-hide="static-modal-currency-exchange" --}} type="button"
                        class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">
                        Save</button>
                    &ensp;
                    <button data-modal-hide="static-modal-currency-exchange" type="button"
                        class="text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none mx-2">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- <DELETE CUSTOMER> --}}
    <div id="confirm-delete-cust"
        class=" hight_index fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-96 max-w-sm p-6 text-center">
            <h2 class="text-2xl font-bold mb-3 text-gray-800">Delete Customer</h2>
            <p class="text-gray-600 mb-6">
                ⚠️ This action cannot be undone. Continue?
            </p>
            <br>
            <div class="flex justify-center space-x-4 mt-2">


                <button onclick="confirmDeleteCustomer()" class="px-5 py-2 bg-red-500 text-white rounded-xl">
                    Delete
                </button>
                &ensp;
                <button onclick="closeDeleteCustModal()" class="px-5 py-2 bg-gray-200 rounded-xl">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- <UPDATE CUSTOMER> --}}
    <div id="confirm-update-cust"
        class="hight_index fixed inset-0 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-96 max-w-sm p-6 text-center">
            <h2 class="text-2xl font-bold mb-3 text-gray-800">Update Customer</h2>



            <form id="updateCustomerForm" class="grid gap-2 grid-cols-2 space-y-3 text-left">
                @csrf
                <input type="hidden" id="cust-id" />
                <div>
                    <label>Customer Code</label>
                    <input id="cust-customer_code" type="text" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Name</label>
                    <input id="cust-name" type="text" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Phone</label>
                    <input id="cust-phone" type="text" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Email</label>
                    <input id="cust-email" type="email" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Address 1</label>
                    <input id="cust-address1" type="text" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Address 2</label>
                    <input id="cust-address2" type="text" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>City</label>
                    <input id="cust-city" type="text" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Country</label>
                    <input id="cust-country" type="text" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Type</label>
                    <select id="cust-type" class="w-full border rounded px-3 py-2">
                        <option value="walk_in">Walk-in</option>
                        <option value="member">Member</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>

                <div>
                    <label>Discount (%)</label>
                    <input id="cust-credit" type="number" step="0.01" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Balance</label>
                    <input id="cust-balance" type="number" step="0.01" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Points</label>
                    <input id="cust-point" type="number" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Contact</label>
                    <input id="cust-contact" type="text" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Contact Phone</label>
                    <input id="cust-contact_phone" type="text" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Status</label>
                    <select id="cust-status" class="w-full border rounded px-3 py-2">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </form>

            <br>
            <div class="flex  space-x-4 mt-6">
                <button onclick="confirmUpdateCustomer()" class="mt-2 px-5 py-2 bg-emerald-500 text-white rounded-xl">
                    Update
                </button>
                &ensp;
                <button onclick="closeUpdateCustModal()" class="mt-2 px-5 py-2 bg-gray-200 rounded-xl">
                    Cancel
                </button>
            </div>
        </div>
    </div>


    {{-- <CONFIRM > --}}
    <div id="confirmModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-96 max-w-sm p-6 text-center animate-scaleUp">
            <h2 id="confirmModalTitle" class="text-2xl font-bold mb-3 text-gray-800">Are you sure?</h2>
            <p id="confirmModalMessage" class="text-gray-600 mb-6">This action cannot be undone.</p>
            <div class="flex justify-center space-x-4">
                <button data-modal-close
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">Cancel</button>
                <button id="confirmModalAction"
                    class="px-5 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">Confirm</button>
            </div>
        </div>
    </div>


    {{-- <REFRESH> --}}

    <div id="unsaveModal" class="fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-96 max-w-sm p-6 text-center animate-scaleUp">
            <h2 class="text-2xl font-bold mb-3 text-gray-800">Resfresh Page.</h2>
            <p class="text-gray-600 mb-6">Warning: Unsaved work might be lost. Do you want to continue?</p>
            <div class="flex justify-center space-x-4">
                <button data-modal-close
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">Cancel</button>
                &ensp;
                <button data-modal-action
                    class="px-5 py-2 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition">Continue</button>
            </div>
        </div>
    </div>


    {{-- <LIST CUSTOMER> --}}
    <div id="default-modal-customer-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center bg-black/50 p-4">

        {{-- width Custom  --}}
        <div class=" relative p-4 w-full max-w-10xl max-h-full ">
            <!-- Modal content -->
            <div
                class="min-h-[70vh] max-h-[90vh] respond_laptop relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 flex flex-col">


                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <div class="w-full flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-heading">
                                Customer Information
                            </h3>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <!-- Active checkbox -->
                            <div class="flex items-center gap-2">
                                <label for="customerSearchCheckbox" class="text-sm font-medium">Active</label>
                                <input type="checkbox" checked id="customerSearchCheckbox" class="w-4 h-4">
                            </div>


                            <!-- Type select -->
                            <div class="flex items-center gap-2">
                                <input type="text" id="customerSearchInput"
                                    placeholder="Search by code, name, phone, email..."
                                    class="px-3 py-2 border rounded-md text-sm w-64 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <select id="customerTypeSelect"
                                    class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="">All Types</option>
                                    <option value="walk_in">Walk In</option>
                                    <option value="member">Member</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <button type="button"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="default-modal-customer-list">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="flex-1 overflow-y-auto mt-4">
                    <div class="scroll_content_70 overflow-x-auto">

                        <table id="customer-list" class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Select</th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="id">ID <span
                                            class="sort-icon">↕</span></th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="customer_code">Code <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="name">Name <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="address1">Address 1 <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="phone">Phone <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="email">Email <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="type">Type <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="credit_limit">Credit Limit <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="balance">Balance <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="point">Point <span
                                            class="sort-icon">↕</span></th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="status">Status <span
                                            class="sort-icon">↕</span></th>
                                </tr>
                            </thead>
                            <tbody id="customer-table-body">
                                <!-- async rows -->
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- Modal footer -->

                <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5 mt-4">
                    <div>
                        <button type="button" id="btnEditCustomer"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Edit
                        </button>
                        &ensp;
                        <button type="button" id="btnDeleteCustomer"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Delete
                        </button>


                        <button type="button" data-modal-target="default-modal-customer"
                            data-modal-toggle="default-modal-customer"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            New
                        </button>
                        <button type="button" id="btnPrintCustomer"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Print
                        </button>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center justify-center gap-1 mt-4 mx-2" id="paginationContainer">
                            <!-- JS will render buttons here -->
                        </div>
                        &ensp;
                        <span id="pageInfo" class="text-sm text-gray-600"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- <ADD CUSTOMER> --}}
    <div id="default-modal-customer" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white border border-slate-600 shadow-md rounded-base p-4 md:p-6">


                <form id="AddcustomerForm">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                        <h3 class="text-lg font-medium text-heading">
                            Customer Information
                        </h3>
                        <button type="button"
                            class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="default-modal-customer">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="space-y-4 md:space-y-6 py-4 md:py-6">

                        <div class="grid gap-6 mb-6 md:grid-cols-2">

                            <!-- Customer Code -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Customer Code<span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="customer_code" placeholder="C0001" required
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Customer Name -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Customer Name <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="name" placeholder="John Doe" required
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Phone
                                </label>
                                <input type="tel" name="phone" placeholder="012345678"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Email
                                </label>
                                <input type="email" name="email" placeholder="john@email.com"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Customer Type -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Customer Type <span class="text-rose-600">*</span>
                                </label>
                                <select name="type"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                                    <option value="walk_in">Walk-in</option>
                                    <option value="member">Member</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>

                            <!-- Credit Limit -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Discount (%)
                                </label>
                                <input type="number" name="credit_limit" step="0.01" value="0"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                        </div>
                        <br>
                        <!-- Address -->
                        <div class="mb-6">
                            <label class="block mb-6 text-sm font-medium text-heading">
                                Address <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" name="address1" placeholder="Street / Village"
                                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                        </div>

                        <br>
                        <div class="mb-6">
                            <label class="block mb-6 text-sm font-medium text-heading">
                                Address 2
                            </label>
                            <input type="text" name="address2" placeholder="Street / Village"
                                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                        </div>
                        <br>
                        <!-- City & Country -->
                        <div class="grid gap-6 mb-6 md:grid-cols-2">
                            <input type="text" name="contact_name" placeholder="Contact Name"
                                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">

                            <input type="text" name="contact_phone" placeholder="Contact Phone"
                                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                        </div>
                        <br>
                        <!-- City & Country -->
                        <div class="grid gap-6 mb-6 md:grid-cols-2">
                            <input type="text" name="city" placeholder="City"
                                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">

                            <input type="text" name="country" placeholder="Country"
                                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                        </div>
                        <br>
                        <!-- Status -->
                        <div class="flex items-center mb-6">
                            <input type="checkbox" name="status" checked
                                class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-brand">
                            &ensp;
                            <label class="ms-2 text-sm font-medium text-heading">
                                Active Customer
                            </label>
                        </div>

                        <!-- Submit -->




                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                        <button type="submit"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Save Customer
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <LIST Warehouse > --}}
    <div id="default-modal-warehouse" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        {{-- width Custom  --}}
        <div class="  relative p-4 w-full max-w-10xl max-h-full ">
            <!-- Modal content -->
            <div class="relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <div class="w-full flex flex-col items-center justify-between mb-4">
                        <div class="flex w-full items-center justify-between mb-4">
                            <div>

                                <h3 id="wh_name" class="text-lg font-medium text-heading">
                                    All Warehouse Information
                                </h3>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">



                                <!-- Type select -->
                                <div class="flex items-center gap-2">

                                    <select id="warehouseTypeSelect"
                                        class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="All">All Warehouse</option>

                                    </select>
                                </div>
                                <button type="button"
                                    class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                                    data-modal-hide="default-modal-warehouse">
                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                                    </svg>

                                    <span class="sr-only">Close modal</span>
                                </button>
                            </div>
                        </div>
                        <div class="w-full flex justify-between mb-2 gap-2">




                            <!-- Type select -->

                            <div class="flex">
                                <div class="flex flex-col mr-2">
                                    <label for="Ssearch-stock">Search Name</label>
                                    <input type="text" id="search-stock" placeholder="Search product"
                                        class="px-3 py-2 border rounded-md text-sm w-64 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                &ensp;
                                <div class="flex flex-col  mr-2">
                                    <label for="category-filter2">Category</label>
                                    <select id="category-filter2"
                                        class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">

                                    </select>
                                </div>
                            </div>






                            <div class="flex ">
                                <div class="flex flex-col ">
                                    <label for="limit-filter">Limit</label>
                                    <select id="limit-filter"
                                        class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="All">All</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="25">25</option>
                                        <option value="30">30</option>
                                        <option selected value="50">50</option>

                                        <option value="100">100</option>
                                    </select>

                                </div>
                                &ensp;
                                <div class="flex flex-col  mx-2">
                                    <label for="status-filter">Status</label>
                                    <select id="status-filter"
                                        class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="All">All</option>
                                        <option value="0">Inactive</option>
                                        <option selected value="1">Active</option>

                                    </select>
                                </div>
                                &ensp;
                                <div class="flex flex-col  mx-2">
                                    <label for="stock-filter">Quantity Filter</label>
                                    <select id="stock-filter"
                                        class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="All">All</option>
                                        <option selected value="has">Has Stock</option>
                                        <option value="no">Out of Stock</option>

                                    </select>
                                </div>

                            </div>

                        </div>




                    </div>
                </div>
                <!-- Modal body -->

                <div class="max-h-[70vh] overflow-y-auto">
                    <div class=" scroll_content_70  overflow-x-auto">
                        <table id="wh-product" class=" w-full text-left text-sm table-auto">
                            <thead class="bg-green-50 sticky top-0">
                                <tr class="text-nowrap">
                                    <th data-sort="id" class="sortable px-3 py-2">No.</th>

                                    <th data-sort="code" class="sortable px-3 py-2">Code</th>
                                    <th data-sort="name" class="sortable px-3 py-2">Product Name</th>
                                    <th data-sort="variant" class="sortable px-3 py-2">Variant</th>
                                    <th data-sort="desc" class="sortable px-3 py-2">Description</th>
                                    <th data-sort="lot" class="sortable px-3 py-2">Lot No</th>
                                    <th data-sort="expire" class="sortable px-3 py-2">Expire Date</th>
                                    <th data-sort="qty" class="sortable px-3 py-2">Qty</th>
                                    <th data-sort="unit" class="sortable px-3 py-2">Unit</th>
                                    <th data-sort="cost" class="sortable px-3 py-2">Cost</th>
                                    <th data-sort="vat" class="sortable px-3 py-2">VAT</th>
                                    <th data-sort="sell" class="sortable px-3 py-2">Unit Price</th>
                                    <th data-sort="sellvat" class="sortable px-3 py-2">Sell Price (VAT)</th>
                                    <th data-sort="category" class="sortable px-3 py-2">Category</th>

                                    <th data-sort="status" class="sortable px-3 py-2">Warehouse</th>
                                    <th data-sort="status" class="sortable px-3 py-2">Status</th>
                                    <th data-sort="status" class="sortable px-3 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody id="warehouse-stock-tbody">
                                <!-- Dynamic rows inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                    <div class="flex items-center justify-end border-t border-default space-x-4 pt-4 md:pt-5">

                        <div class="flex items-center justify-center gap-1 mt-4 mx-2" id="paginationContainer_stock">
                            <!-- JS will render buttons here -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- <Update Warehouse > --}}
    <div id="warehouseConfirmModal"
        class=" fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-96 max-w-sm p-6 text-center animate-scaleUp">
            <h2 id="warehouseConfirmTitle" class="text-2xl font-bold mb-3 text-gray-800">Update Warehouse</h2>
            <p id="warehouseConfirmMessage" class="text-gray-600 mb-4">Edit name & location below:</p>

            <div class="mb-6 flex flex-col gap-4">
                <input type="text" id="edit_warehouse_name" placeholder="Warehouse Name"
                    class="w-full px-3 py-2 border rounded" />

                <input type="text" id="edit_warehouse_location" placeholder="Location"
                    class="w-full px-3 py-2 border rounded" />
            </div>
            <br>
            <div class="flex mt-3 justify-center space-x-4">
                <button data-warehouse-close
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">Cancel</button>
                &ensp;
                <button id="warehouseConfirmAction"
                    class="px-5 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition">Confirm</button>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div id="modalContent" class="bg-white rounded-lg p-6 max-w-sm w-full text-center shadow-lg animate-fadeIn">
            <h2 class="text-lg font-semibold mb-4">Are you sure you want to log out?</h2>
            <div class="flex justify-center gap-4">
                <button id="confirmLogout"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-medium px-4 py-2 rounded">Yes</button>
                <button id="cancelLogout"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-medium px-4 py-2 rounded">No</button>
            </div>
        </div>
    </div>
    {{-- Toast  --}}
    <div id="toastMessage"
        class="fixed top-5 right-5 z-50 hidden flex items-center justify-between max-w-sm w-full bg-white rounded-2xl shadow-2xl p-4 animate-scaleUp">

        <div class="flex items-center space-x-3">
            <span id="toastIcon" class="text-green-500 text-xl">✔️</span>
            <p id="toastText" class="text-gray-800 font-medium"></p>
        </div>

        <button onclick="hideToast()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
    </div>




    {{-- <MODAL PRINT > --}}
    <div id="DatePromptModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">

        <div class="bg-white rounded-5xl shadow-2xl  w-1/2 max-w-sm p-6 space-y-5 animate-scaleUp">

            <!-- Header -->
            <div class="flex flex-col items-center text-center">
                <h2 class="text-2xl font-extrabold text-gray-800">Print Dates & Payment</h2>
                <p class="text-sm text-gray-500 mt-1">Set dates and choose your payment method</p>
            </div>


            <div class="space-y-6">

                <!-- Display Total Amounts -->
                <div class="bg-gray-50 p-4 rounded-xl space-y-3">
                    <div>
                        <label for="display_pay_amount" class="block text-gray-600 font-medium mb-1">Total
                            Amount</label>
                        <input type="text" id="display_pay_amount" disabled
                            class="w-full bg-gray-100 border border-gray-300 rounded-xl px-3 py-2 text-gray-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="display_pay_amount_converted" class="block text-gray-600 font-medium mb-1">Total
                            in
                            Other</label>
                        <input type="text" id="display_pay_amount_converted" disabled
                            class="w-full bg-gray-100 border border-gray-300 rounded-xl px-3 py-2 text-gray-700 cursor-not-allowed">
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="space-y-3 px-5">
                    <label for="payment_method" class="block text-gray-700 font-medium mb-1">Payment Method</label>
                    <select id="payment_method"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition">
                        <option value="ABA">ABA</option>
                        <option value="CASH">Cash</option>
                        <option value="CREDIT CARD">Credit Card</option>
                        <option value="BANK TRANSFER">Bank Transfer</option>
                        <option value="Chip Mong">Chip Mong</option>
                        <option value="PAYPAL">PayPal</option>
                        <option value="WISE BANK">WISE BANK</option>
                        <option value="CHEQ">CHEQ</option>
                    </select>
                </div>
                <div class="space-y-3 px-5">
                    <label for="customer_type" class="block text-gray-700 font-medium mb-1">Customer Type</label>
                    <select id="customer_type"
                        class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition">
                        <option value="Dine-In">Dine-In</option>
                        <option value="At-Delivery">At-Delivery</option>
                        <option value="Take-Away">Take Away</option>
                    </select>
                </div>
                <!-- Payment Inputs -->
                <div class="bg-gray-50 p-4 rounded-xl space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Pay USD -->
                        <div>
                            <label for="pay_usd" class="block text-gray-700 font-medium mb-1">Pay as Dollar</label>
                            <input type="text" id="pay_usd" placeholder="$0.00"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition">
                        </div>

                        <!-- Hidden currency factor -->
                        <input type="hidden" id="currency_factor" value="4000"> <!-- example: 1 USD = 4000 KHR -->

                        <!-- Pay other currency -->
                        <div>
                            <label for="pay_other" class="block text-gray-700 font-medium mb-1">
                                Pay as <span id="currency_display_name">៛</span>
                            </label>
                            <input type="text" id="pay_other" placeholder="0 ៛"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition">
                        </div>

                        <!-- Returned USD -->
                        <div>
                            <label for="returned_amount" class="block text-gray-700 font-medium mb-1">លុយអាប់</label>
                            <input type="text" id="returned_amount" readonly
                                class="w-full bg-gray-100 border border-gray-300 rounded-xl px-3 py-2 text-gray-700 cursor-not-allowed">
                        </div>

                        <!-- Returned other currency -->
                        <div>
                            <label for="returned_amount_other" class="block text-gray-700 font-medium mb-1">
                                លុយអាប់ជា <span id="currency_display_name2">៛</span>
                            </label>
                            <input type="text" id="returned_amount_other" readonly
                                class="w-full bg-gray-100 border border-gray-300 rounded-xl px-3 py-2 text-gray-700 cursor-not-allowed">
                        </div>
                    </div>
                </div>

            </div>
            <!-- Date Inputs -->
            <div class="space-y-4 px-5">
                <div>
                    <label for="document_dateInput" class="block text-gray-700 font-medium mb-1">
                        Document Date
                    </label>
                    <input type="date" id="document_dateInput"
                        class="w-full border border-gray-300 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 transition">
                </div>

                <div>
                    <label for="due_date" class="block text-gray-700 font-medium mb-1">
                        Due Date
                    </label>
                    <input type="date" id="due_date"
                        class="w-full border border-gray-300 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 transition">
                    <p class="text-xs text-gray-500 mt-1">Default is <b>+1 month</b>, you can adjust if needed.</p>
                </div>
            </div>

            <br>
            <!-- Buttons -->
            <div class="w-full grid grid-cols-3 gap-2 px-5 ">
                <button data-quotation-cancel class=" bg-rose-200 text-gray-700 rounded-xl hover:bg-rose-300 transition">
                    Cancel
                </button>

                <button id="btn-print-quote" onclick="print_document('Quotation')"
                    class=" bg-amber-400 text-black rounded-xl hover:bg-amber-400 transition shadow-md">
                    Quote
                </button>

                <button onclick="print_document('Order')"
                    class=" bg-amber-400 text-black rounded-xl hover:bg-amber-400 transition shadow-md">
                    Order
                </button>


                <button id="btn-print-delivery" onclick="print_document('Delivery Note')"
                    class=" bg-amber-400 text-black rounded-xl hover:bg-amber-400 transition shadow-md">
                    Delivery
                </button>

                <button id="btn-print-invoice" onclick="print_document('Invoice')"
                    class=" bg-amber-400 text-black rounded-xl hover:bg-amber-400 transition shadow-md">
                    Invoice
                </button>

                <button id="confirmPayBtn" onclick="Final_Payment()"
                    class=" text-nowrap w-24 bg-gray-400 text-black rounded-xl hover:bg-gray-400 transition shadow-md">
                    Enter Amount

                </button>

            </div>
        </div>
    </div>










    {{-- <LIST PRODUCT > --}}
    <div id="default-modal-product-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center bg-black/50 p-4">

        {{-- width Custom  --}}
        <div class="  relative p-4 w-full max-w-10xl max-h-full ">
            <!-- Modal content -->
            <div
                class="min-h-[70vh] max-h-[90vh] respond_laptop relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 flex flex-col">

                @csrf
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <div class="w-full flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-heading">
                                ព័ត៌មានផលិតផល
                            </h3>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <!-- Active checkbox -->
                            <div class="flex items-center gap-2">

                                <select id="productSearchCheckbox">
                                    <option value="">All</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>


                            <!-- Type select -->
                            <div class="flex items-center gap-2">
                                <input type="text" id="ProductSearchInput" placeholder="Search product"
                                    class="px-3 py-2 border rounded-md text-sm w-64 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <select id="productTypeSelect"
                                    class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">


                                </select>
                                <select id="productLimitSelect"
                                    class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option selected value="25">25</option>
                                    <option value="30">30</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <button type="button"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="default-modal-product-list">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="flex-1 overflow-y-auto mt-4">
                    <div class="scroll_content_70 overflow-x-auto">
                        <table id="product_table" class=" w-full text-sm text-left border border-default rounded-base">
                            <thead class="sticky_top text-xs uppercase bg-neutral-secondary">
                                <tr class="text-nowrap">
                                    <th class="px-4 py-3">Select</th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="id">
                                        ID <span class="sort-icon">↕</span>
                                    </th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="image">
                                        Image <span class="sort-icon">↕</span>
                                    </th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="bar_code">
                                        Bar Code <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="code">
                                        Code <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="name">
                                        Name <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="variant">
                                        Variant <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="description">
                                        Description <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="min_stock">
                                        Min Stock <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="max_stock">
                                        Max Stock <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="sell_price">
                                        Unit Price <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="cost">
                                        Cost <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="vat">
                                        VAT <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="discount_percent">
                                        Discount % <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="last_purchase_price">
                                        Last Purchase Price <span class="sort-icon">↕</span>
                                    </th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="category_id">
                                        Category ID <span class="sort-icon">↕</span>
                                    </th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="category_name">
                                        Category <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="unit">
                                        Unit <span class="sort-icon">↕</span>
                                    </th>
                                    <th class="px-4 py-3 cursor-pointer" data-column="category_name">
                                        Display On <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="track_stock">
                                        Track Stock <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="allow_discount">
                                        Allow Discount <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="allow_return">
                                        Allow Return <span class="sort-icon">↕</span>
                                    </th>

                                    <th class="px-4 py-3 cursor-pointer" data-column="status">
                                        Status <span class="sort-icon">↕</span>
                                    </th>
                                </tr>

                            </thead>
                            <tbody id="product-table-body">
                                <!-- async rows -->
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- Modal footer -->

                <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5 mt-4">
                    <div>
                        {{-- <button type="button" data-modal-target="default-modal-customer"
                                data-modal-toggle="default-modal-customer"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                Product Category
                            </button> --}}
                        <button type="button" {{-- id="btnEditCustomer" --}} id="btnEditProduct"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Edit
                        </button>
                        &ensp;
                        {{-- <button type="button"
                             id="btnDeleteCustomer"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                Delete
                            </button> --}}


                        <button type="button" id="btnAddProduct" data-modal-target="default-modal-add-product"
                            data-modal-toggle="default-modal-add-product"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            New
                        </button>

                        <button type="button" id="btnPrintProduct"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Print
                        </button>
                        <button type="button" id="btnPrintProductMenu"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Print Menu
                        </button>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center justify-center gap-1 mt-4 mx-2" id="paginationContainerProduct">
                            <!-- JS will render buttons here -->
                        </div>
                        &ensp;
                        <span id="pageInfo" class="text-sm text-gray-600"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>










    {{-- <ADD PRODUCT > --}}
    <div id="default-modal-add-product" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="  relative p-4 w-full max-w-5xl max-h-full ">
            <!-- Modal content -->
            <!-- Modal content -->
            <div class="relative bg-white border border-slate-600 shadow-md rounded-base p-4 md:p-6">


                <form id="AddProductForm" enctype="multipart/form-data">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                        <h3 class="text-lg font-medium text-heading">
                            Add Product
                        </h3>

                        <button type="button"
                            class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="default-modal-add-product">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>


                    </div>
                    <!-- Modal body -->
                    <div class="space-y-4 md:space-y-6 py-4 md:py-6">

                        <div class="grid gap-6 mb-6 md:grid-cols-2 mt-2">
                            <!-- Codes -->

                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Product Code <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="code" required placeholder="PRD001"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>

                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Barcode
                                </label>
                                <input type="text" name="bar_code" placeholder="123456789"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>
                        </div>

                        <!-- Name & Variant -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Product Name <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="name" id="name" required placeholder="Coca Cola"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>

                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Variant
                                </label>
                                <input type="text" name="variant" placeholder="Can / 330ml"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block mb-2.5 text-sm font-medium text-heading">
                                Description
                            </label>
                            <textarea name="description" rows="3" id="description"
                                class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5"></textarea>
                        </div>

                        <!-- Stock -->
                        <div class="grid gap-6 md:grid-cols-3">
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Min Stock</label>
                                <input type="number" name="min_stock" value="0" id="min_stock"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>

                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Max Stock</label>
                                <input type="number" name="max_stock" value="0"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>


                        </div>

                        <!-- Pricing -->
                        <div class="grid gap-6 md:grid-cols-3">
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Unit Price</label>
                                <input type="number" step="0.01" name="sell_price" value="0"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>

                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Cost</label>
                                <input type="number" step="0.01" name="cost" value="0"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>

                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">VAT (%)</label>
                                <input type="number" step="0.01" name="vat" value="0"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>
                        </div>

                        <!-- Discount -->
                        <div class="grid gap-6 md:grid-cols-3">
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">Discount %</label>
                                <input type="number" step="0.01" name="discount_percent" value="0"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>

                        </div>

                        <br> <!-- Category & Unit -->
                        <div class="grid gap-6 md:grid-cols-3 mt-3">
                            <div>
                                <label>Category</label>
                                <select name="category_id" id="categorySelect"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base px-3 py-2.5 w-full">
                                    <option value="">Loading categories...</option>
                                </select>
                            </div>
                            <div>
                                <label for="category_name">Display on</label>
                                <input name="category_name" id="category_name"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base px-3 py-2.5 w-full">

                            </div>
                            <div>
                                <label for="unit">Unit</label>

                                <input type="text" name="unit" id="unit" placeholder="Unit (pcs, kg, box)"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base px-3 py-2.5 w-full">
                            </div>
                        </div>
                        <!-- Image -->
                        <div>
                            <label class="block mb-2.5 text-sm font-medium text-heading">
                                Product Image
                            </label>

                            <input type="file" name="image" id="productImage" accept="image/*"
                                class="block w-full text-sm text-heading border border-default-medium rounded-base bg-neutral-secondary-medium">


                        </div>

                        <!-- Status -->
                        <div class="grid gap-6 md:grid-cols-4">

                            <div class="flex items-center pt-8">
                                <input type="checkbox" name="status" checked
                                    class="w-4 h-4 rounded-xs border border-default-medium">
                                <label class="ms-2 text-sm font-medium text-heading">Active Product</label>
                            </div>

                            <div class="flex items-center pt-8">
                                <input type="checkbox" name="allow_discount" checked
                                    class="w-4 h-4 rounded-xs border border-default-medium">
                                <label class="ms-2 text-sm font-medium text-heading">Allow Discount</label>
                            </div>

                            <div class="flex items-center pt-8">
                                <input type="checkbox" name="allow_return" checked
                                    class="w-4 h-4 rounded-xs border border-default-medium">
                                <label class="ms-2 text-sm font-medium text-heading">Allow Return</label>
                            </div>
                            <div class="flex items-center pt-8">
                                <input type="checkbox" name="track_stock" checked
                                    class="w-4 h-4 rounded-xs border border-default-medium">
                                <label class="ms-2 text-sm font-medium text-heading">Track Stock</label>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                        <button type="submit"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Save Product
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <UPDATE PRODUCT> --}}
    <div id="confirm-update-product"
        class="hight_index fixed inset-0 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl p-6 text-center">

            <h2 class="text-2xl font-bold mb-3 text-gray-800">Update Product</h2>

            <form id="updateProductForm" class="grid gap-2 grid-cols-4 space-y-3 text-left">

                @csrf

                <!-- Hidden ID -->

                <!-- Hidden ID -->
                <input type="hidden" id="prod-id" />

                <div class="col-span-2">
                    <div class="img_400">
                        <img style="border-radius: 10px" id="preview_img" src="" alt="">
                    </div>



                    <input type="file" id="update_image" accept="image/*">
                </div>

                <div class="grid grid-cols-1 col-span-2">
                    <div>
                        <label>Product Code</label>
                        <input id="prod-code" type="text" class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label>Barcode</label>
                        <input id="prod-barcode" type="text" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label>Product Name</label>
                        <input id="prod-name" type="text" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label>Varaint</label>
                        <input id="prod-variant" type="text" class="w-full border rounded px-3 py-2" />
                    </div>
                </div>

                <div class="col-span-4">
                    <label>Description</label>
                    <input id="prod-description" type="text" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Min Stock</label>
                    <input id="prod-min-stock" type="number" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Max Stock</label>
                    <input id="prod-max-stock" type="number" class="w-full border rounded px-3 py-2" />
                </div>

                <div>
                    <label>Cost Price</label>
                    <input id="prod-cost" type="number" step="0.01" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Unit Price</label>
                    <input id="prod-sell-price" type="number" step="0.01"
                        class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Vat</label>
                    <input id="prod-vat" type="number" step="0.01" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Discount (%)</label>
                    <input id="prod-discount" type="number" step="0.01"
                        class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Sell Price</label>
                    <input id="sell_price-final" disabled type="number" step="0.01"
                        class="w-full border rounded px-3 py-2" />
                </div>
                <div></div>
                <div>
                    <label>Category</label>

                    </input>
                    <select id="prod-category-id" class="w-full border rounded px-3 py-2">
                        <!-- fill dynamically -->
                    </select>
                </div>

                <div>
                    <label>Unit</label>
                    <input id="prod-unit" type="text" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label>Display On</label>
                    <input id="prod-category-name" type="text" class="w-full border rounded px-3 py-2" />
                </div>




                <div class="grid grid-cols-4 gap-2  col-span-4">
                    <!-- Track Stock -->
                    <label class="flex items-center flex-col cursor-pointer gap-3">
                        <span class="text-sm">Track Stock</span>
                        <div class="relative">
                            <input id="prod-track-stock" type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-emerald-500 transition">
                            </div>
                            <div
                                class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5">
                            </div>
                        </div>
                    </label>

                    <!-- Allow Discount -->
                    <label class="flex items-center flex-col cursor-pointer gap-3 mt-2">
                        <span class="text-sm">Allow Discount</span>
                        <div class="relative">
                            <input id="prod-allow-discount" type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-emerald-500 transition">
                            </div>
                            <div
                                class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5">
                            </div>
                        </div>
                    </label>

                    <!-- Allow Return -->
                    <label class="flex items-center flex-col cursor-pointer gap-3 mt-2">
                        <span class="text-sm">Allow Return</span>
                        <div class="relative">
                            <input id="prod-allow-return" type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-emerald-500 transition">
                            </div>
                            <div
                                class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5">
                            </div>
                        </div>
                    </label>

                    <!-- Status -->
                    <label class="flex items-center flex-col cursor-pointer gap-3 mt-2">
                        <span class="text-sm">Status</span>
                        <div class="relative">
                            <input id="prod-status" type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-emerald-500 transition">
                            </div>
                            <div
                                class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5">
                            </div>
                        </div>
                    </label>
                </div>
            </form>

            <br>

            <div class="flex space-x-4 mt-6">
                <button type="button" onclick="confirmUpdateProduct()"
                    class="mt-2 px-5 py-2 bg-emerald-500 text-white rounded-xl">
                    Update
                </button>
                &ensp;
                <button type="button" onclick="closeUpdateProductModal()"
                    class="mt-2 px-5 py-2 bg-gray-200 rounded-xl">
                    Cancel
                </button>
            </div>
        </div>
    </div>






    {{-- <LIST SALE DATA> --}}
    <div id="default-modal-sale-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center bg-black/50 p-4">

        {{-- width Custom  --}}
        <div class="  relative p-4 w-full max-w-10xl max-h-full ">

            <!-- Modal content -->
            <div
                class="respond_laptop relative  bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 ">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <div class="w-full mb-6">
                        <!-- Title -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-heading">
                                បាយការណ៍ ការលក់
                            </h3>
                        </div>

                        <!-- Filters Row 1: Date, Status, Customer -->
                        <!-- Filters -->
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <input type="date" id="from_date" class="px-3 py-2 border rounded-md text-sm">
                            <input type="date" id="to_date" class="px-3 py-2 border rounded-md text-sm">
                            <select id="invoice_paymentMethod" class="px-6 py-2 border rounded-md text-sm">
                                <option value="">All Payment</option>
                            </select>
                            <div class="relative w-52">
                                <input type="text" id="customer_search" placeholder="Customer Name"
                                    autocomplete="off" class="px-3 py-2 border rounded-md text-sm w-full">

                                <input type="hidden" id="customer_filter">

                                <ul id="customer_list"
                                    class="absolute z-50 bg-white border rounded-md w-full mt-1 max-h-60 overflow-y-auto hidden">
                                </ul>
                            </div>

                            <input type="text" id="product_search" list="product_datalist"
                                placeholder="Search product" autocomplete="off"
                                class="px-3 py-2 border rounded-md text-sm w-64">

                            <input type="hidden" id="ProductSearchInput_sale_invoice">

                            <datalist id="product_datalist"></datalist>
                            <select id="category_filter" class="px-3 py-2 border rounded-md text-sm w-44">
                                <option value="">All Categories</option>
                            </select>
                            <select id="sale_view_limit" class="px-5 py-2 border rounded-md text-sm w-36">
                                <option value="10">10 Invoice</option>
                                <option value="20">20 Invoice</option>
                                <option value="30">30 Invoice</option>
                                <option value="50">50 Invoice</option>
                                <option selected value="75">75 Invoice</option>
                                <option value="100">100 Invoice</option>
                                <option value="200">200 Invoice</option>
                                <option value="All">All Invoices</option>
                            </select>
                        </div>
                    </div>
                    <button type="button"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="default-modal-sale-list">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>



                <!-- Modal body -->
                <div class="scroll_content_70 ">
                    <table id="Table-sale-list" class=" text-sm text-left border border-default rounded-base">
                        <thead class="text-xs uppercase bg-neutral-secondary">
                            <tr class="text-nowrap">
                                <!-- ===== Invoice Header ===== -->
                                <th class="px-4 py-3 cursor-pointer" data-column="id">
                                    No<span class="sort-icon">↕</span>
                                </th>
                                <th class="px-4 py-3 cursor-pointer" data-column="invoice_number">
                                    Invoice No <span class="sort-icon">↕</span>
                                </th>
                                <th class="px-4 py-3 cursor-pointer" data-column="created_at">Transaction
                                    Date<span class="sort-icon">↕</span></th>
                                <th class="px-4 py-3 cursor-pointer" data-column="customer_name">
                                    Customer <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="invoice_date">
                                    Invoice Date <span class="sort-icon">↕</span>
                                </th>

                                {{-- <th class="px-4 py-3 cursor-pointer" data-column="due_date">
                                    Due Date <span class="sort-icon">↕</span>
                                </th> --}}
                                <th class="px-4 py-3 cursor-pointer" data-column="payment_method">
                                    Payment Method <span class="sort-icon">↕</span>
                                </th>
                                <th class="px-4 py-3 cursor-pointer" data-column="customer_type">
                                    Customer Type <span class="sort-icon">↕</span>
                                </th>


                                <!-- ===== Line Item Fields ===== -->
                                {{-- <th class="px-4 py-3 cursor-pointer" data-column="item_code">
                                    Item Code <span class="sort-icon">↕</span>
                                </th> --}}

                                <th class="px-4 py-3 cursor-pointer" data-column="product_name">
                                    Name <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="variant">
                                    Variant <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="description">
                                    Description <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="quantity">
                                    Qty <span class="sort-icon">↕</span>
                                </th>
                                <th class="px-4 py-3 cursor-pointer" data-column="unit">
                                    UOM <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="unit_price">
                                    Unit Price <span class="sort-icon">↕</span>
                                </th>
                                {{-- <th class="px-4 py-3 cursor-pointer" data-column="cost">
                                    Cost <span class="sort-icon">↕</span>
                                </th> --}}

                                <th class="px-4 py-3 cursor-pointer" data-column="sell_price">
                                    Price <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="line_amount">
                                    Gross Amount <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="discount_percent">
                                    Discount % <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="discount_percent">
                                    Discount Amount <span class="sort-icon">↕</span>
                                </th>

                                <th class="px-4 py-3 cursor-pointer" data-column="vat_percent">
                                    VAT <span class="sort-icon">↕</span>
                                </th>
                                <th class="px-4 py-3 cursor-pointer" data-column="total_vat_amount">
                                    Total Vat Amount<span class="sort-icon">↕</span>
                                </th>
                                {{-- <th class="px-4 py-3 cursor-pointer" data-column="total_cost_amount">
                                    Total Cost Amount<span class="sort-icon">↕</span>
                                </th> --}}
                                <th class="px-4 py-3 cursor-pointer" data-column="total_line_amount">
                                    Total Amount<span class="sort-icon">↕</span>
                                </th>

                            </tr>

                        </thead>
                        <tbody id="salesTableBody">
                            <!-- async rows -->
                        </tbody>
                    </table>
                </div>


                <!-- Modal footer -->

                <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5">
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center justify-center gap-1 mt-4 mx-2"
                            id="paginationContainer_sale_invoice">
                            <!-- JS will render buttons here -->
                        </div>
                        &ensp;
                        <span id="pageInfo_sale_invoice" class="text-sm text-gray-600"></span>
                    </div>
                    <div class="flex">
                        <button type="button" id="downloadSales" class="px-4 py-2 bg-green-600 text-white rounded">
                            <i class="fa-regular fa-file-excel"></i>
                        </button>



                        &ensp;
                        <button type="button" id="btnPrintSale" class="px-4 py-2 bg-blue-600 text-white rounded">
                            <i class="fa-solid fa-print"></i>
                        </button>
                        &ensp;
                        <button type="button" id="btnReciept" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Print Reciept
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>




    {{-- <LIST TABLE > --}}
    <div id="default-modal-table-select-list" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center p-4">
        <div
            class="min-h-[70vh] max-h-[90vh] respond_laptop relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 flex flex-col">
            <div
                class="relative min-h-[70vh] max-h-[90vh] bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                <div class="flex justify-between w-full p-2">
                    <h3 class="text-lg font-medium text-heading mb-4">Table\Quote</h3>
                    <div>
                        <button onclick="openModal()"
                            class="mt-4 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">New Doc</button>
                        <button onclick="exit_table()"
                            class="mt-4 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Exit
                            Table\Quote</button>
                        <button type="button"
                            onclick="document.getElementById('default-modal-table-select-list').classList.add('hidden')"
                            class="mt-4 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                            Close
                        </button>
                    </div>
                </div>
                <div class=" max-h-[70vh] overflow-y-auto">
                    <div class="scroll_content_70 overflow-x-auto">
                        <table class="w-full text-sm text-left border rounded-base">
                            <thead class="bg-neutral-secondary text-xs uppercase">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Queue No</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="table-modal-body">
                                <!-- JS will fill rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal -->
    <div id="tableModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">

        <div class="bg-white w-96 p-6 rounded-lg shadow-lg">
            <h2 class="text-lg font-semibold mb-4">Create Table\QUOTE</h2>

            <form id="tableForm" class="grid grid-cols-2 gap-2">

                <!-- Table Name -->
                <input type="text" name="name" id="table_name" value="N\A"
                    class="col-span-2 w-full border p-2 rounded" required>
                <!--Buttons -->
                <div class="col-span-2 flex justify-end gap-2 mt-3">
                    <button type="button" onclick="closeModal()" class="bg-gray-400 text-white px-3 py-1 rounded">
                        Cancel
                    </button>

                    <button type="button" onclick="saveTable()" class="bg-green-500 text-white px-3 py-1 rounded">
                        Save
                    </button>
                </div>

            </form>
        </div>
    </div>









    {{-- <LIST User > --}}
    <div id="default-modal-user-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center bg-black/50 p-4">

        {{-- width Custom  --}}
        <div class="  relative p-4 w-full max-w-10xl max-h-full ">
            <!-- Modal content -->
            <div
                class="min-h-[70vh] max-h-[90vh] respond_laptop relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 flex flex-col">


                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <div class="w-full flex flex-col items-center justify-between mb-4">
                        <div class="flex w-full items-center justify-between mb-4">
                            <div>

                                <h3 id="wh_name" class="text-lg font-medium text-heading">
                                    User List
                                </h3>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                <!-- Active checkbox -->
                                <select id="active"
                                    class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="All">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>

                                </select>
                                <select id="role_filter"
                                    class="px-3 py-2 border rounded-md text-sm w-44 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option value="All">All Roles</option>
                                    <option value="Cashier">Cashier</option>
                                    <option value="Supervisor">Supervisor</option>

                                    <option value="Admin">Admin</option>
                                </select>


                                <!-- Type select -->
                                <div class="flex items-center gap-2">
                                    <input type="text" id="userSearchInput" placeholder="Search by name ,email..."
                                        class="px-3 py-2 border rounded-md text-sm w-64 focus:outline-none focus:ring-1 focus:ring-blue-500">

                                </div>
                                <button type="button"
                                    class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                                    data-modal-hide="default-modal-user-list">
                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                                    </svg>

                                    <span class="sr-only">Close modal</span>
                                </button>
                            </div>
                        </div>




                    </div>
                </div>
                <!-- Modal body -->
                <div class="flex-1 overflow-y-auto mt-4">
                    <div class="scroll_content_70 overflow-x-auto">
                        <table id="user-table" class=" w-full text-sm text-left border border-default rounded-base">
                            <thead class="sticky_top text-xs uppercase bg-neutral-secondary">

                                <tr class="text-nowrap">
                                    <th class="px-4 py-3 text-center">Select</th>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3 text-center">Active</th>
                                </tr>
                                </tr>

                            </thead>
                            <tbody id="user-table-body">
                                <!-- async rows -->
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- Modal footer -->

                <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5 mt-4">
                    <div>
                        {{-- <button type="button" data-modal-target="default-modal-customer"
                                data-modal-toggle="default-modal-customer"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                Product Category
                            </button> --}}
                        <button type="button" {{-- id="btnEditCustomer" --}} id="btnEditProduct"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Edit
                        </button>
                        &ensp;
                        {{-- <button type="button"
                             id="btnDeleteCustomer"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                Delete
                            </button> --}}


                        <button type="button" id="btnUser" data-modal-target="default-modal-add-user"
                            data-modal-toggle="default-modal-add-user"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            New User
                        </button>
                    </div>


                </div>

            </div>
        </div>
    </div>
    {{-- <ADD User > --}}
    <div id="default-modal-add-user" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="  relative p-4 w-full max-w-5xl max-h-full ">
            <!-- Modal content -->

            <div class="relative bg-white border border-slate-600 shadow-md rounded-base p-4 md:p-6">


                <form id="AddUserForm" enctype="multipart/form-data">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                        <h3 class="text-lg font-medium text-heading">
                            Add New User <div id="formError" class="text-red-500 text-sm mb-3 hidden"></div>
                        </h3>

                        <button type="button"
                            class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="default-modal-add-user">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>


                    </div>
                    <!-- Modal body -->
                    <div class="space-y-4 md:space-y-6 py-4 md:py-6">



                        <!-- Name & Variant -->
                        <div class="grid gap-6 md:grid-cols-1">
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Display Name <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="display_name" id="display_name" required
                                    placeholder="Candy"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    User login <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="username" id="username" required placeholder="candy"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    User Role<span class="text-rose-600">*</span>
                                </label>
                                <select id="role" name="role" id="role" required
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">

                                    <option selected value="Cashier">Cashier</option>
                                    <option value="Supervisor">Supervisor</option>

                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Email
                                </label>
                                <input type="email" name="email" id="email"
                                    placeholder="jonhdoe@example.com"
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Password
                                </label>
                                <input type="password" name="password" id="password" placeholder="••••••••"
                                    required
                                    class="bg-neutral-secondary-medium border border-default-medium rounded-base w-full px-3 py-2.5">
                            </div>
                        </div>
                        &ensp;
                        <div class="mt-4">
                            <label class="block mb-2.5 text-sm font-medium text-heading">
                                User Can Use Warehouses
                            </label>

                            <div id="warehouseList" class="grid grid-cols-2 gap-2">
                                <!-- JS render here -->
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                        <button type="submit" id="submitBtn" disabled
                            class="bg-gray-400 text-gray-200 cursor-not-allowed font-medium rounded-base text-sm px-4 py-2.5 transition">
                            Required More Info
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>




    <div id="lotModal" class="fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-4xl max-w-7xl p-4 animate-scaleUp">
            <!-- Header / Close -->
            <div class="flex justify-end mb-4">
                <button onclick="closeLotModal()"
                    class="text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
            </div>

            <!-- Main content: image + info + lots -->
            <div class="flex gap-4">
                <!-- Product Image -->
                <div class="flex-shrink-0">
                    <img id="display_img" src="" alt="Product Image"
                        class="w-40 h-40 object-cover rounded-lg border shadow">
                </div>

                <!-- Right side: Name + lots table -->
                <div class="flex-1 flex flex-col">
                    <!-- Product Name / ID / Track Qty -->
                    <h2 id="item-id" class="text-2xl font-bold text-gray-800 mb-4">Loading...</h2>

                    <!-- Lots Table -->
                    <div id="lotModalBody" class="overflow-y-auto max-h-80 border rounded p-4 bg-gray-50 grid gap-2">
                        <!-- JS will inject lots rows here -->
                    </div>

                    <!-- Footer: warning + save -->
                    &ensp;
                    <div class="flex justify-between items-center mt-4 ">
                        <button id="save-lot-btn" onclick="saveLots()"
                            class="px-5 py-2 rounded-xl transition bg-gray-400" disabled>
                            Save
                        </button>
                        <p id="lot-warning" class="text-red-500 hidden text-sm"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div id="viewLotModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-4xl max-w-5xl p-6 animate-scaleUp">

            <!-- Header: Image left, Title right -->
            <div class="flex items-start mb-4 gap-4">
                <!-- Product Image -->
                <div class="flex-shrink-0">
                    <img id="display_img2" src="" alt="Product Image"
                        class="w-32 h-32 object-cover rounded-lg border shadow">
                </div>

                <!-- Title & Info -->
                <div class="flex-1">
                    <h2 id="view-lot-title" class="text-xl font-bold text-gray-800 mb-2">Loading...</h2>
                    <p id="view-lot-info" class="text-gray-600 text-sm">
                        <!-- Optional: show product ID, stock, or other info -->
                    </p>
                </div>

                <!-- Close button -->
                <button onclick="closeViewLotModal()"
                    class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            </div>

            <!-- Table Body -->
            <div id="viewLotModalBody" class=" overflow-y-auto max-h-72 space-y-2">
                <!-- JS injects rows here -->
            </div>

            <!-- Footer -->
            <div class="flex justify-end mt-4">
                <button onclick="closeViewLotModal()"
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">Close</button>
            </div>
        </div>
    </div>


    <div id="transfer_modal"
        class="hidden fixed inset-0 bg-black/50 bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white w-3/4 max-w-4xl p-6 rounded-xl shadow-lg">
            <div class="flex justify-between items-center mb-4">

                    @csrf

                <center>
                    <h2 class="text-xl font-semibold">Transfer Item</h2>
                </center>
                <button onclick="document.getElementById('transfer_modal').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-800">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <!-- From Location -->
                <div class="border p-4 rounded-lg">
                    <h2 class="font-bold mb-2">From Location <span id="location-display"></span></h2>
                    <table class="w-full text-sm">
                        <thead>
                            <tr>

                                <th class="text-left">Product</th>

                                <th class="text-left">Lot</th>
                                <th class="text-right">Qty</th>
                                <th class="text-left">Unit</th>
                            </tr>
                        </thead>
                        <tbody id="from_location_body">
                            <!-- JS will populate this -->
                        </tbody>
                    </table>
                </div>

                <!-- To Location -->
                <div class="border p-4 rounded-lg">
                    <h3 class="font-medium mb-2">To Location</h3>
                    <select id="to_location_select" onchange="validateTransferForm()"
                        class="w-full border rounded-xl p-2">
                        <option value="">Select warehouse</option>
                    </select>


                    <div class="mt-4">
                        <label class="block mb-1 font-medium">Qty to Transfer</label>

                        <input id="transfer_qty" type="number" min="1" oninput="validateTransferForm()"
                            class="w-full border rounded-xl p-2">
                    </div>
                    &ensp;
                    <button type="button" id="confirmTransferBtn" onclick="submitTransfer()" disabled
                        class="mt-4 w-full bg-gray-400 cursor-not-allowed text-white py-2 rounded-xl transition">
                        Confirm Transfer
                    </button>
                </div>

            </div>
        </div>
    </div>
@endpush
