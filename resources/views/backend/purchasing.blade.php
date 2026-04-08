@extends('backend.master_purchasing')

@section('content')
    <div id="container" class="w-full grid grid-cols-1 m lg:grid-cols-8 gap-2 h-screen overflow-hidden">
        <div id="mainContent"
            class=" tab_control  lg:col-span-6 md:col-span-4 col-span-2  border-1 border-default border-dashed rounded-base">

            <div class=" flex justify-between  mb-2 border-b border-default  mx-5 sticky top-0 bg-blue-400 z-10">
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

                    @livewire('purchase-cart')
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

            document.querySelectorAll(".costs").forEach((element) => {
                const baseCost = parseFloat(element.getAttribute("data-base-cost")) || 0;
                const newCost = baseCost * factor;

                // ✅ Remove trailing .00 if it's a whole number
                const displayCost = Number.isInteger(newCost) ? newCost : newCost.toFixed(2);

                element.textContent = `${displayCost} ${currency_name}`;
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
                    let style_click = `card_style_success`;


                    // Show
                    html += `
                     <div class="card_style ${style_click} bg-neutral-primary-soft block max-w-sm border border-default shadow-xs relative">
                                <button class="add-to-cart-btn w-full flex flex-col h-full" data-product='${JSON.stringify(product)}'>

                                    <!-- IMAGE -->
                                    <div class="relative w-full">

                                      ${imageSrc
                                        ? `<img class="object-cover w-full"
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


       <span class="costs font-semibold text-sm" data-base-cost="${product.cost}">
    ${Number(product.cost * factor || 0)} ${currency_name}
</span>

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
                const response = await fetch('/purchase/products/search', {
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
            let style_click = `card_style_success`;
            console.log(product);

            // Search
            return `
                     <div class="card_style ${style_click} bg-neutral-primary-soft block max-w-sm border border-default shadow-xs relative">
                                <button class="add-to-cart-btn w-full flex flex-col h-full" data-product='${JSON.stringify(product)}'>

                                    <!-- IMAGE -->
                                    <div class="relative w-full">

                                      ${imageSrc
                                        ? `<img class="object-cover w-full"
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


                                              <span class="costsfont-semibold text-sm">
                                                ${Number(product.cost || 0).toFixed(2)} $
                                            </span>

                                        </p>
            </div>
        </div>

    </button>
</div>

            `;
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
    {{-- Toast  --}}
    <div id="toastMessage"
        class="fixed top-5 right-5 z-50 hidden flex items-center justify-between max-w-sm w-full bg-white rounded-2xl shadow-2xl p-4 animate-scaleUp">

        <div class="flex items-center space-x-3">
            <span id="toastIcon" class="text-green-500 text-xl">✔️</span>
            <p id="toastText" class="text-gray-800 font-medium"></p>
        </div>

        <button onclick="hideToast()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
    </div>
@endpush
