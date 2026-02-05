@extends('backend.master')

@section('content')
    <div id="container" class="w-full grid grid-cols-1 md:grid-cols-4 lg:grid-cols-8 gap-2 h-screen overflow-hidden">
        <div id="mainContent"
            class=" tab_control lg:col-span-6  col-span-2  border-1 border-default border-dashed rounded-base">

            <div class=" flex justify-between  mb-2 border-b border-default  mx-5 sticky top-0 bg-amber-400 z-10">
                <div class="flex items-center gap-2 px-4 py-3">

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
                            ALL Product
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

            const count = 5; // 💥 MORE + BIGGER
            const burst = document.createElement('div');
            burst.className = 'cart-burst';
            burst.style.left = e.clientX + 'px';
            burst.style.top = e.clientY + 'px';

            for (let i = 0; i < count; i++) {
                const icon = document.createElement('span');

                const isCart = Math.random() > 0.5;
                icon.className = `cart-icon ${isCart ? 'cart' : 'plus'}`;
                icon.textContent = isCart ? '🛒' : '✅';

                // 🎯 RANDOM direction + BIG distance
                const angle = Math.random() * Math.PI * 2;
                const distance = 70 + Math.random() * 50;

                icon.style.setProperty('--x', `${Math.cos(angle) * distance}px`);
                icon.style.setProperty('--y', `${Math.sin(angle) * distance}px`);

                burst.appendChild(icon);
            }

            document.body.appendChild(burst);
            setTimeout(() => burst.remove(), 900);
        });

        function toggleItem(button) {
            const allBodies = document.querySelectorAll('.bonus'); // all dropdowns
            const allArrows = document.querySelectorAll('.arrow'); // all arrows
            const allCards = document.querySelectorAll('.btn_sale_invoice'); // parent cards

            const body = button.nextElementSibling; // clicked dropdown
            const arrow = button.querySelector('.arrow'); // clicked arrow
            const card = button; // the parent card button itself

            // Close all other dropdowns
            allBodies.forEach(b => {
                if (b !== body) b.classList.add('hidden');
            });

            allArrows.forEach(a => {
                if (a !== arrow) a.classList.remove('rotate-180');
            });

            allCards.forEach(c => {
                if (c !== card) c.classList.remove('active-card'); // remove focus from others
            });

            // Toggle the clicked one
            body.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
            card.classList.toggle('active-card'); // toggle focus on current
        }


        const tabs = document.querySelectorAll('#category-tabs button');
        const tabContent = document.getElementById('tab-content');

        // Convert Blade categories JSON into JS object
        const productsByCategory = @json($categories);


        console.log(productsByCategory); // Access the array directly

        // Helper: sort products by total_stock DESC
        function sortByStock(products) {
            return products.sort((a, b) => b.total_stock - a.total_stock);
        }

        // Event listener for tabs
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const category = tab.dataset.category; // get clicked category
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

        // Render Category Products
        async function renderCategoryProducts(category) {
            tabContent.innerHTML = '<p class="p-4">Loading...</p>';
            document.body.style.cursor = 'wait';

            try {
                let products = [];
                if (category === 'top') {
                    products = sortByStock(Object.values(productsByCategory).flat()).slice(0, 1000);
                } else {
                    products = sortByStock(productsByCategory[category] || []);
                }

                let html =
                    '<div class="min_heigh_70 w-full grid grid-cols-1 lg:grid-cols-6 md:grid-cols-4 gap-2 p-3 bg-slate-200 mb-12 pb-16">';

                products.forEach(product => {
                    const imageSrc = product.image ?
                        `assets/startic_img/${product.image}` :
                        'assets/startic_img/placeholder.jpg';

                    const price = Number(product.sell_price);
                    const finalPrice = Number(price + (price * Number(product.vat || 0) / 100));
                    const discountedPrice = finalPrice - (finalPrice * Number(product.discount_percent || 0) /
                        100);

                    // Stock color logic using percentage
                    let stockColor = 'text-gray-400'; // default out of stock

                    if (product.total_stock > 0) {
                        const stockPercent = (product.total_stock / product.max_stock) * 100;

                        if (product.total_stock > product.max_stock) {
                            stockColor = 'text-blue-600'; // overstock
                        } else if (stockPercent < 33) {
                            stockColor = 'text-red-500'; // low stock
                        } else if (stockPercent < 66) {
                            stockColor = 'text-yellow-500'; // medium stock warning
                        } else {
                            stockColor = 'text-green-600'; // enough stock
                        }
                    }



                    html += `
                     <div class="card_style bg-neutral-primary-soft block max-w-sm border border-default shadow-xs relative">
                                <button class="add-to-cart-btn w-full flex flex-col h-full" data-product='${JSON.stringify(product)}'>

                                    <!-- IMAGE -->
                                    <div class="relative w-full">
                                        <img class="object-cover w-full"
                                            loading="lazy"
                                            style="max-height: 150px; min-height: 150px;"
                                            src="${imageSrc}"
                                            onerror="this.src='assets/startic_img/placeholder.jpg'"
                                            alt="${product.name}" />

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

                                        <div class="text-center mt-1">
                                            <p class="text-xs">
                                            ${product.track_stock ? `
                                                                                                                                                                                                <i class="${stockColor} fa-solid fa-boxes-stacked"></i>
                                                                                                                                                                                                <span class="${stockColor}">
                                                                                                                                                                                                    ${product.total_stock > 0 ? product.total_stock + ' ' + product.unit : 'No stock'}
                                                                                                                                                                                                </span>
                                                                                                                                                                                                &ensp;` : ''}

                                            ${product.discount_percent != 0
                                                ? `<del class="text-gray-400 text-[10px]">${finalPrice.toFixed(2)} $</del> → <span class="${stockColor} font-semibold text-sm">${discountedPrice.toFixed(2)} $</span>`
                                                : `<span class="font-semibold text-sm">${finalPrice.toFixed(2)} $</span>`
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
            const activeTab = document.querySelector('#category-tabs button.border-brand');
            const category = activeTab ? activeTab.dataset.category : 'top';

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

                const response = await fetch(
                    `/products/search?field=${field}&query=${encodeURIComponent(query)}&category=${category}`
                );

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
                'assets/startic_img/placeholder.jpg';

            const price = Number(product.sell_price);
            const finalPrice = Number(price + (price * Number(product.vat || 0) / 100));
            const discountedPrice = finalPrice - (finalPrice * Number(product.discount_percent || 0) / 100);

            // Stock color logic
            let stockColor = 'text-gray-400'; // default out of stock
            if (product.total_stock > 0) {
                const stockPercent = product.max_stock ? (product.total_stock / product.max_stock) * 100 : 100;
                if (product.total_stock > product.max_stock) stockColor = 'text-blue-600';
                else if (stockPercent < 33) stockColor = 'text-red-500';
                else if (stockPercent < 66) stockColor = 'text-yellow-500';
                else stockColor = 'text-green-600';
            }

            return `
                            <div class="card_style bg-neutral-primary-soft block max-w-sm border border-default shadow-xs relative">
                                <button class="add-to-cart-btn w-full flex flex-col h-full" data-product='${JSON.stringify(product)}'>

                                    <!-- IMAGE -->
                                    <div class="relative w-full">
                                        <img class="object-cover w-full" loading="lazy" style="max-height:150px;min-height:150px;"
                                            src="${imageSrc}" onerror="this.src='assets/startic_img/placeholder.jpg'" alt="${product.name}" />
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
                                                    ? `<del class="text-gray-400 text-[10px]">${finalPrice.toFixed(2)} $</del> → <span class="${stockColor} font-semibold text-sm">${discountedPrice.toFixed(2)} $</span>`
                                                    : `<span class="font-semibold text-sm">${finalPrice.toFixed(2)} $</span>`}
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
            console.log("Setting page to:", page);
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
    <!--Currency Main modal -->
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
    <!-- Reusable Confirmation Modal -->
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
                    <label>Address</label>
                    <input id="cust-address" type="text" class="w-full border rounded px-3 py-2" />
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
                    <label>Credit Limit</label>
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

    <!-- Reusable Confirmation Modal -->
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

    <!-- Unsaved Work Modal (Top Right) -->

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

    <!-- List Customer Main modal -->
    <div id="default-modal-customer-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center bg-black/50 p-4">

        {{-- width Custom  --}}
        <div class="  relative p-4 w-full max-w-10xl max-h-full ">
            <!-- Modal content -->
            <div class=" relative  bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 ">
                <form id="customerFormList">
                    @csrf
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
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div id="customer-list" class="scroll_content_70 max-h-[70vh] overflow-y-auto">
                        <div class="overflow-x-auto">
                            <table class=" w-full text-sm text-left border border-default rounded-base">
                                <thead class="sticky_top text-xs uppercase bg-neutral-secondary">
                                    <tr>
                                        <th class="px-4 py-3">Select</th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="id">
                                            ID <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="customer_code">
                                            Code <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="name">
                                            Name <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="phone">
                                            Phone <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="email">
                                            Email <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="type">
                                            Type <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="credit_limit">
                                            Credit Limit <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="balance">
                                            Balance <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="point">
                                            Point <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="status">
                                            Status <span class="sort-icon">↕</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="customer-table-body">
                                    <!-- async rows -->
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <!-- Modal footer -->

                    <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5">
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
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center justify-center gap-1 mt-4 mx-2" id="paginationContainer">
                                <!-- JS will render buttons here -->
                            </div>
                            &ensp;
                            <span id="pageInfo" class="text-sm text-gray-600"></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add  Customer Main modal -->
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
                                    Credit Limit
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
                            <input type="text" name="address" placeholder="Street / Village"
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
    <div id="default-modal-warehouse" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-[90vh]">
            <!-- Modal content -->
            <div class="relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <h3 class="text-lg font-medium text-heading">
                        Warehouse Information
                    </h3>

                    <button type="button"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="default-modal-warehouse">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>

                        <span class="sr-only">Close modal</span>
                    </button>

                </div>
                <!-- Modal body -->

                <div class="space-y-4 md:space-y-6 py-4 md:py-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border border-default rounded-base">
                            <thead class="sticky_top text-xs uppercase bg-neutral-secondary">
                                <tr>
                                    <th class="px-4 py-3">Select</th>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Warehouse Name</th>
                                    <th class="px-4 py-3">Location</th>
                                    <th class="px-4 py-3 text-center">Total Stock</th>
                                </tr>
                            </thead>
                            <tbody id="warehouse-table-body">
                                <!-- async rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">



                    <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5">
                        <div>
                            <button type="button" id="btnEditWarehouse"
                                class="text-white bg-brand hover:bg-brand-strong rounded-base text-sm px-4 py-2.5">
                                Edit
                            </button>
                            &ensp;
                            <button type="button" id="view-stock-warehouse"
                                class="text-white bg-green-600 hover:bg-green-700 rounded-base text-sm px-4 py-2.5">
                                View Stock
                            </button>
                            <button data-modal-hide="default-modal-warehouse" type="button"
                                class="text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none mx-2">
                                Close
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Warehouse Confirmation + Edit Modal -->
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
    {{-- Toast  --}}
    <div id="toastMessage"
        class="fixed top-5 right-5 z-50 hidden flex items-center justify-between max-w-sm w-full bg-white rounded-2xl shadow-2xl p-4 animate-scaleUp">

        <div class="flex items-center space-x-3">
            <span id="toastIcon" class="text-green-500 text-xl">✔️</span>
            <p id="toastText" class="text-gray-800 font-medium"></p>
        </div>

        <button onclick="hideToast()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
    </div>


    <!-- Modal Overlay -->
    <div id="warehouse-stock-modal"
        class="fixed inset-0 bg-black bg-opacity-60 flex items-start justify-center z-50 hidden transition-opacity duration-300">

        <!-- Modal Box -->
        <div style="margin-top:50px;"
            class="bg-white  shadow-2xl w-[95%] p-4 max-w-6xl mt-5 overflow-hidden transform transition-transform duration-300 scale-100">

            <!-- Modal Header -->
            <div class="flex justify-between items-center border-b border-gray-200 bg-gray-50">
                <h2 id="wh_name" class="text-2xl font-semibold text-gray-800">Warehouse Stock</h2>
                <button id="close-modal"
                    class="text-gray-500 hover:text-gray-800 text-3xl font-bold transition-colors">&times;</button>
            </div>

            <!-- Modal Filters -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 py-3
            border-b border-gray-200 bg-gray-50">

                <!-- Search -->
                <input type="text" id="search-stock" placeholder="Search product..."
                    class="border rounded-sm px-3 py-2 w-full
               focus:outline-none focus:ring-2 focus:ring-green-400 transition text-lg">

                <!-- Variant -->
                <input type="text" id="variant-filter" placeholder="Variant..."
                    class="border rounded-sm px-3 py-2 w-full
               focus:outline-none focus:ring-2 focus:ring-green-400 transition text-lg">

                <!-- Category -->
                <select id="category-filter"
                    class="border rounded-sm px-3 py-2 w-full
               focus:outline-none focus:ring-2 focus:ring-green-400 transition text-lg">
                    <option value="">All Categories</option>
                    <option value="1">Accessory</option>
                </select>

                <!-- Status -->
                <select id="status-filter"
                    class="border rounded-sm px-3 py-2 w-full
               focus:outline-none focus:ring-2 focus:ring-green-400 transition text-lg">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>

                <!-- Stock -->
                <select id="stock-filter"
                    class="border rounded-sm px-3 py-2 w-full
               focus:outline-none focus:ring-2 focus:ring-green-400 transition text-lg">
                    <option value="">All Stock</option>
                    <option value="has">Has Stock</option>
                    <option value="no">No Stock</option>
                </select>

            </div>


            <!-- Table -->
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full text-left text-sm table-auto">
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
                            <th data-sort="sell" class="sortable px-3 py-2">Sell Price</th>
                            <th data-sort="sellvat" class="sortable px-3 py-2">Sell Price (VAT)</th>
                            <th data-sort="category" class="sortable px-3 py-2">Category</th>
                            <th data-sort="status" class="sortable px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody id="warehouse-stock-tbody">
                        <!-- Dynamic rows inserted here -->
                    </tbody>
                </table>
            </div>
            <!-- Modal Footer -->
            <div class="flex justify-end py-2 border-t border-gray-200 bg-gray-50">
                <button id="close-footer-modal"
                    class="px-5 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition text-lg font-semibold">Close</button>
            </div>
        </div>
    </div>
    <!-- Quotation Date Modal -->
    <div id="DatePromptModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center backdrop-blur-sm bg-black/50">

        <div class="bg-white rounded-2xl shadow-2xl w-96 max-w-sm p-6 text-left animate-scaleUp">

            <h2 class="text-2xl font-bold mb-4 text-gray-800">
                Print Dates
            </h2>

            <!-- Quotation Date -->
            <label class="block text-gray-700 mb-2 font-medium">
                Document Date
            </label>

            <input type="date" id="document_dateInput"
                class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">

            <!-- Valid Until -->
            <label class="block text-gray-700 mt-4 mb-2 font-medium">
                Due Date
            </label>

            <input type="date" id="due_date"
                class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">

            <p class="text-sm text-gray-500 mt-3">
                Default is <b>+1 month</b>, you can adjust if needed.
            </p>

            <div class="flex justify-end space-x-3 mt-6">
                <button data-quotation-cancel
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition">
                    Cancel
                </button>

                <button data-quotation-confirm
                    class="px-5 py-2 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 transition">
                    Continue
                </button>
            </div>
        </div>
    </div>










    <!-- List product Main modal -->
    <div id="default-modal-product-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center bg-black/50 p-4">

        {{-- width Custom  --}}
        <div class="  relative p-4 w-full max-w-10xl max-h-full ">
            <!-- Modal content -->
            <div class=" relative  bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 ">
                <form id="customerFormList">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                        <div class="w-full flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-heading">
                                    Product Information
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
                                        <option value="25">25</option>
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
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div id="product-list" class="scroll_content_70 max-h-[70vh] overflow-y-auto">
                        <div class="overflow-x-auto">
                            <table class=" w-full text-sm text-left border border-default rounded-base">
                                <thead class="sticky_top text-xs uppercase bg-neutral-secondary">
                                    <tr class="text-nowrap">
                                        <th class="px-4 py-3">Select</th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="id">
                                            ID <span class="sort-icon">↕</span>
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
                                            Sell Price <span class="sort-icon">↕</span>
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

                                        <th class="px-4 py-3 cursor-pointer" data-column="category_name">
                                            Category <span class="sort-icon">↕</span>
                                        </th>

                                        <th class="px-4 py-3 cursor-pointer" data-column="unit">
                                            Unit <span class="sort-icon">↕</span>
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

                    <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5">
                        <div>
                            <button type="button" data-modal-target="default-modal-customer"
                                data-modal-toggle="default-modal-customer"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                Product Category
                            </button>
                            <button type="button" id="btnEditCustomer"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                Edit
                            </button>
                            &ensp;
                            <button type="button" id="btnDeleteCustomer"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                Delete
                            </button>


                            <button type="button" data-modal-target="default-modal-add-product"
                                data-modal-toggle="default-modal-add-product"
                                class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                                New
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
                </form>
            </div>
        </div>
    </div>









      <!-- Add  Product Main modal -->
    <div id="default-modal-add-product" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white border border-slate-600 shadow-md rounded-base p-4 md:p-6">


                <form id="AddcustomerForm">
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

                        <div class="grid gap-6 mb-6 md:grid-cols-2">
                            123


                        </div>
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
@endpush
