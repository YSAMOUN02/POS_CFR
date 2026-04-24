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
                                            ${Number(product.cost * factor || 0) == 0
                                                ? 'មិនមានតម្លៃ'
                                                : Number(product.cost * factor).toLocaleString() + ' ' + currency_name}
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


                                            <span class="costs font-semibold text-sm" data-base-cost="${product.cost}">
                                            ${Number(product.cost * factor || 0) == 0
                                                ? 'មិនមានតម្លៃ'
                                                : Number(product.cost * factor).toLocaleString() + ' ' + currency_name}
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




        const input = document.getElementById("vendorSearch");
        const list = document.getElementById("vendorList");
        const hiddenInput = document.getElementById("vendorValue");

        input.addEventListener("input", async () => {
            const value = input.value.trim();

            if (value.length === 0) {
                list.classList.add("hidden");
                list.innerHTML = '';
                hiddenInput.value = '';
                return;
            }

            try {
                const res = await fetch(`{{ route('vendor.search') }}`, {
                    method: 'POST',
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]')
                            .value,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        q: value
                    })
                });

                const data = await res.json();

                list.innerHTML = '';
                list.classList.remove("hidden");

                if (!Array.isArray(data) || data.length === 0) {
                    list.innerHTML = `<li class="px-3 py-2 text-sm text-gray-500">No results found</li>`;
                    return;
                }

                data.forEach(vendor => {
                    const li = document.createElement("li");
                    li.textContent = `${vendor.code} - ${vendor.name}`;
                    li.className = "px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm";

                    li.addEventListener("click", () => {
                        input.value = `${vendor.code} - ${vendor.name}`;
                        hiddenInput.value = vendor.id;
                        list.classList.add("hidden");
                        hiddenInput.dispatchEvent(new Event("input"));
                    });

                    list.appendChild(li);
                });

            } catch (error) {
                console.error(error);
                list.innerHTML = `<li class="px-3 py-2 text-sm text-red-500">Error loading vendors</li>`;
                list.classList.remove("hidden");
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


    {{-- <LIST VENDOR> --}}
    <div id="default-modal-vendor-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
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
                                Vendor Information <span id="vendorPageInfo"
                                    class="text-sm text-gray-600 whitespace-nowrap">
                                </span>

                            </h3>
                        </div>
                        <!-- Right Side Filters -->
                        <div class="flex flex-wrap items-center gap-3">

                            <!-- Active Checkbox -->
                            <div class="flex items-center gap-2">
                                <label for="vendorSearchCheckbox" class="text-sm font-medium text-heading">
                                    Active
                                </label>

                                <input type="checkbox" id="vendorSearchCheckbox" checked onchange="loadVendors(1)"
                                    class="w-4 h-4 border border-default-medium rounded-sm">
                            </div>

                            <!-- Search Input -->
                            <div>
                                <input type="text" id="vendorSearchInput"
                                    placeholder="Search code, name, phone, email..." oninput="handleVendorSearchInput()"
                                    class="px-3 py-2 border border-default-medium rounded-md text-sm w-72 focus:outline-none focus:ring-1 focus:ring-brand">
                            </div>

                        </div>

                    </div>
                    <button type="button"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="default-modal-vendor-list">
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

                        <table id="vendor-list" class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Select</th>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Code</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Contact Person</th>
                                    <th class="px-4 py-3">Phone 1</th>
                                    <th class="px-4 py-3">Phone 2</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Country</th>
                                    <th class="px-4 py-3">City</th>
                                    <th class="px-4 py-3">Website</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody id="vendor-table-body">
                                <!-- async rows -->
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- Modal footer -->

                <div class="flex items-center justify-between border-t border-default space-x-4 pt-4 md:pt-5 mt-4">
                    <div>
                        <button type="button" id="btnEditvendor" data-modal-target="default-modal-edit-vendor"
                            data-modal-toggle="default-modal-edit-vendor"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Edit
                        </button>
                        &ensp;
                        {{-- <button type="button" id="btnDeleteCustomer"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Delete
                        </button> --}}
                        <button type="button" data-modal-target="default-modal-vendor"
                            data-modal-toggle="default-modal-vendor"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            New Vendor
                        </button>

                    </div>

                    <div class="flex items-center justify-between mt-4 relative z-50">

                        <div id="vendorPaginationContainer"
                            class="flex items-center justify-center gap-2 mx-2 pointer-events-auto">
                            <!-- JS buttons -->
                        </div>



                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- <ADD Vendor> --}}
    <div id="default-modal-vendor" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white border border-slate-600 shadow-md rounded-base p-4 md:p-6">


                <form id="AddVendorForm">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                        <h3 class="text-lg font-medium text-heading">
                            Vendor Information
                        </h3>
                        <button type="button"
                            class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="default-modal-vendor">
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

                            <!-- Vendor Code -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Vendor Code<span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="code" placeholder="V0001" required disabled
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Vendor Name -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Name <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="name" placeholder="Vendor Name" required
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Contact Person -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Contact Person
                                </label>
                                <input type="text" name="contact_person" placeholder="John Doe"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Email
                                </label>
                                <input type="email" name="email" placeholder="example@mail.com"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Phone 1 -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Phone 1
                                </label>
                                <input type="text" name="phone1" placeholder="012345678"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Phone 2 -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Phone 2
                                </label>
                                <input type="text" name="phone2" placeholder="098765432"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Country
                                </label>
                                <input type="text" name="country" placeholder="Cambodia"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- City -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    City
                                </label>
                                <input type="text" name="city" placeholder="Phnom Penh"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Website -->
                            <div class="md:col-span-2">
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Website
                                </label>
                                <input type="text" name="website" placeholder="https://example.com"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Address 1 -->
                            <div class="md:col-span-2">
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Address 1
                                </label>
                                <textarea name="address1" rows="2"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs"></textarea>
                            </div>

                            <!-- Address 2 -->
                            <div class="md:col-span-2">
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Address 2
                                </label>
                                <textarea name="address2" rows="2"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs"></textarea>
                            </div>

                        </div>
                        <br>
                        <!-- Status -->
                        <div class="flex items-center mb-6">
                            <input type="checkbox" name="status" checked value="1"
                                class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-brand">
                            &ensp;
                            <label class="ms-2 text-sm font-medium text-heading">
                                Active Vendor
                            </label>
                        </div>

                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                        <button type="button" onclick="addVendor()"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Save Vendor
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>






    {{-- <EDIT Vendor> --}}
    <div id="default-modal-edit-vendor" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white border border-slate-600 shadow-md rounded-base p-4 md:p-6">

                <form id="EditVendorForm">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="vendor_id" id="edit_vendor_id">

                    <!-- Modal header -->
                    <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                        <h3 class="text-lg font-medium text-heading">
                            Update Vendor Information
                        </h3>
                        <button type="button"
                            class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="default-modal-edit-vendor">
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

                            <!-- Vendor Code -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Vendor Code<span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="code" id="edit_code" placeholder="V0001" required
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Vendor Name -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Name <span class="text-rose-600">*</span>
                                </label>
                                <input type="text" name="name" id="edit_name" placeholder="Vendor Name" required
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Contact Person -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Contact Person
                                </label>
                                <input type="text" name="contact_person" id="edit_contact_person"
                                    placeholder="John Doe"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Email
                                </label>
                                <input type="email" name="email" id="edit_email" placeholder="example@mail.com"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Phone 1 -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Phone 1
                                </label>
                                <input type="text" name="phone1" id="edit_phone1" placeholder="012345678"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Phone 2 -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Phone 2
                                </label>
                                <input type="text" name="phone2" id="edit_phone2" placeholder="098765432"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Country
                                </label>
                                <input type="text" name="country" id="edit_country" placeholder="Cambodia"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- City -->
                            <div>
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    City
                                </label>
                                <input type="text" name="city" id="edit_city" placeholder="Phnom Penh"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Website -->
                            <div class="md:col-span-2">
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Website
                                </label>
                                <input type="text" name="website" id="edit_website" placeholder="https://example.com"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                            </div>

                            <!-- Address 1 -->
                            <div class="md:col-span-2">
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Address 1
                                </label>
                                <textarea name="address1" id="edit_address1" rows="2"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs"></textarea>
                            </div>

                            <!-- Address 2 -->
                            <div class="md:col-span-2">
                                <label class="block mb-2.5 text-sm font-medium text-heading">
                                    Address 2
                                </label>
                                <textarea name="address2" id="edit_address2" rows="2"
                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs"></textarea>
                            </div>

                        </div>

                        <br>

                        <!-- Status -->
                        <div class="flex items-center mb-6">
                            <input type="checkbox" name="status" id="edit_status" value="1"
                                class="w-4 h-4 border border-default-medium rounded-xs bg-neutral-secondary-medium focus:ring-brand">
                            &ensp;
                            <label class="ms-2 text-sm font-medium text-heading">
                                Active Vendor
                            </label>
                        </div>

                    </div>

                    <!-- Modal footer -->
                    <div class="flex items-center border-t border-default space-x-4 pt-4 md:pt-5">
                        <button type="button" onclick="updateVendor()"
                            class="text-white bg-brand hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium rounded-base text-sm px-4 py-2.5">
                            Update Vendor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>






    {{-- <LIST Prucase DATA> --}}
    <div id="default-modal-purchase-list" tabindex="-1" aria-hidden="true" data-modal-backdrop="static"
        class="hidden fixed inset-0 z-50 flex justify-center items-start md:items-center bg-black/50 p-4">

        {{-- width Custom  --}}
        <div class="  relative p-4 w-full max-w-10xl max-h-full ">

            <!-- Modal content -->
            <div
                class="min-h-[70vh] max-h-[90vh] respond_laptop relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6 flex flex-col">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <div class="w-full mb-6">
                        <!-- Title -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-heading">
                                បាយការណ៍ ការ​ទិញ (Purchasing Report)
                            </h3>
                        </div>

                        <!-- Filters Row 1: Date, Status, Customer -->
                        <!-- Filters -->
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <input type="date" id="from_date" class="px-3 py-2 border rounded-md text-sm" onchange="FetchPurchase(1)">
                            <input type="date" id="to_date" class="px-3 py-2 border rounded-md text-sm" onchange="FetchPurchase(1)">

                            <div class="relative w-52">
                                <input type="text" id="vendor_search" placeholder="vendor name or code"
                                    autocomplete="off" class="px-3 py-2 border rounded-md text-sm w-full">

                                <input type="hidden" id="vendor_filter" onchange="FetchPurchase(1)">

                                <ul id="vendor_list"
                                    class="absolute z-50 bg-white border rounded-md w-full mt-1 max-h-60 overflow-y-auto hidden">
                                </ul>
                            </div>

                            <input type="text" id="product_search" list="product_datalist"
                                placeholder="Search product" autocomplete="off"
                                class="px-3 py-2 border rounded-md text-sm w-64">

                            <input type="hidden" id="ProductSearchInput_sale_invoice" onchange="FetchPurchase(1)">

                            <datalist id="product_datalist"></datalist>
                            <select id="category_filter" onchange="FetchPurchase(1)" class="px-3 py-2 border rounded-md text-sm w-44">
                                <option value="">All Categories</option>
                            </select>
                            <select id="limit_filter" onchange="FetchPurchase(1)" class="px-3 py-2 border rounded-md text-sm w-32">
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="300">300</option>
                            </select>
                        </div>
                    </div>
                    <button type="button"
                        class="text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="default-modal-purchase-list">
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
                        <table id="Table-Purchase-list" class="min-w-full text-sm text-left border-collapse">

                            <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                                <tr class="text-nowrap">
                                    <th class="px-4 py-3 border">No</th>
                                    <th class="px-4 py-3 border">Posting Date</th>
                                    <th class="px-4 py-3 border">Vendor</th>
                                    <th class="px-4 py-3 border">Contact</th>
                                                        <th class="px-4 py-3 border">Items</th>
                                    <th class="px-4 py-3 border">Lot Detail</th>

                                    <th class="px-4 py-3 border">Qty</th>
                                    <th class="px-4 py-3 border">Total</th>
                                    <th class="px-4 py-3 border">Purchase By</th>
                                    <th class="px-4 py-3 border">Action</th>
                                </tr>
                            </thead>

                            <tbody id="PurchaseTableBody">

                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                        Loading...
                                    </td>
                                </tr>

                            </tbody>

                        </table>
                    </div>

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
@endpush
