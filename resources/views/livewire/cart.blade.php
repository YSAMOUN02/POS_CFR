<div>
    <div class="screen-only">
        <div id="header_invoice"
            class="border-b bg-white border-default pb-2  p-2 flex items-center justify-between sticky top-0">
            <h1 style="font-size: 30px;" class="mb-2 font-bold">
                @if (!empty($this->Current_table_id))
                    <span class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                        Editing Data :
                    </span>
                    {{ $this->Current_table_name }}
                @else
                    <span class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                        {{ $prefix }}
                    </span>
                    {{ $title }}
                @endif

            </h1>
            <div class="px-4" id="refreshBtn" data-popover-target="popover-user-profile">
                <i style="font-size: 30px;" class="fa-solid fa-arrows-rotate"></i>
            </div>

            <div data-popover id="popover-user-profile" role="tooltip"
                class="absolute z-10 invisible inline-block w-64 text-sm text-body transition-opacity duration-300 bg-neutral-primary-soft border border-default rounded-base shadow-xs opacity-0">
                <div class="p-3">
                    <p class="text-sm text-gray-500">Tip: Click on the arrows to refresh the Page.</p>
                </div>
                <div data-popper-arrow></div>
            </div>

        </div>

        @forelse ($cart as $item)
            <div class="w-full mx-auto">
                <!-- Item Card -->
                <div
                    class="card bg-white shadow border-b-amber-600 focus-within:bg-yellow-50 transition-colors duration-200 ">
                    <!-- Header (clickable) -->
                    <div onclick="toggleItem(this)"
                        class="btn_sale_invoice w-full flex items-center justify-between p-2">
                        <div class="flex items-start gap-3">
                            <div class="flex flex-col items-center justify-center">
                                <span
                                    class="text-green-500 text-lg transition-transform duration-300 arrow hover:cursor-pointer">▾</span>
                                <button wire:click.stop="removeItem({{ $item['id'] }})" title="Remove item"><span
                                        class="text-red-500 text-lg transition-transform duration-300 hover:cursor-pointer arrow"><i
                                            class="fa-solid fa-delete-left fa-flip-horizontal"></i></span></button>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold">{{ $item['order_no'] }}. {{ $item['name'] }} x
                                    {{ $item['qty'] }} {{ $item['unit'] }}

                                </p>
                                @if ($item['discount_percent'] != 0)
                                    <span
                                        class="inline-flex items-center bg-brand-softer border border-brand-subtle text-fg-brand-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                                        <i class="fa-solid fa-tag"></i>
                                        ចុះ {{ (float) $item['discount_percent'] }}% Off
                                    </span>
                                @endif
                                @if ($item['stock'] == $item['qty'])
                                    <span
                                        class="inline-flex items-center bg-rose-400 border border-brand-subtle text-white text-xs font-medium px-1.5 py-0.5 rounded-sm">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                        អស់់ស្តុក
                                    </span>
                                @endif
                                <p class="text-sm text-gray-400">
                                    តម្លៃ:
                                    @if ($item['discount_percent'] != 0)
                                        <del>{{ $item['price'] }}</del>$ - {{ $item['discount_price'] }}$
                                    @else
                                        {{ (float) $item['price'] }} $
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">
                                @if ($item['discount_percent'] != 0)
                                    <del>{{ $item['amount_line'] }}$</del> - {{ $item['net_amount_line'] }}$
                                @else
                                    {{ $item['amount_line'] }}$
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Dropdown Content -->
                    <div class="hidden  bonus border-b p-2">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-500">ចំនួន</label>
                                <input type="number" min="1" max="{{ $item['stock'] }}"
                                    wire:model.lazy="cart.{{ $loop->index }}.qty"
                                    class="w-full mt-1 border rounded px-3 py-2 focus:outline-none focus:ring" />
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">បញ្ចុះតម្លៃ (%)</label>
                                <input type="number" min="0" max="100"
                                    wire:model.lazy="cart.{{ $loop->index }}.discount_percent"
                                    class="w-full mt-1 border rounded px-3 py-2 focus:outline-none focus:ring" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @empty
            <div class="p-4">
                <p>No items in cart</p>
            </div>
        @endforelse
        {{-- Totals --}}

        <div id="total" class="grid grid-cols-1 gap-1 p-2">
            <div class="flex items-end flex-col justify-between">
                <p class="text-sm">សរុបរង: {{ $this->totals['total_original'] }}$</p>
                <p class="text-sm">បញ្ចុះតម្លៃ : {{ $this->totals['total_discount'] }}$</p>
                <p class="font-semibold">តម្លៃសរុប : {{ $this->totals['total_net'], 0 }} $</p>
                <input type="hidden" id="total_amount" value="{{ $this->totals['total_net'], 0 }}">
                <input type="hidden" id="currency_name" value="{{ $currency_name }}">
                <input type="hidden" id="currency_display_symbol" value="{{ $currency }}">
                <input type="hidden" id="currency_display_factor" value="{{ $factor }}">

                @if ($currency != 'USD')
                    <div class="w-full flex justify-between">

                        <div class="flex items-center">

                            {{-- <p class="font-semibold">1$ : {{ (float) $factor }}{{ $currency }}</p> --}}
                            <span
                                class="inline-flex items-center bg-brand-softer border border-brand-subtle text-fg-brand-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">

                                1$ : {{ (float) $factor }}&ensp;{{ $currency }}
                            </span>
                        </div>
                        <p class="font-semibold">
                            តម្លៃសរុបគិតជា {{ $currency_name }}:
                            {{ floor($this->totals['total_net'] * $factor) == $this->totals['total_net'] * $factor
                                ? number_format($this->totals['total_net'] * $factor, 0)
                                : number_format($this->totals['total_net'] * $factor, 2) }}
                            {{ $currency }}


                        </p>

                    </div>
                    <input type="hidden" id="converted_total_amount"
                        value="{{ floor($this->totals['total_net'] * $factor) == $this->totals['total_net'] * $factor
                            ? number_format($this->totals['total_net'] * $factor, 0)
                            : number_format($this->totals['total_net'] * $factor, 2) }}">
                @else
                    <input type="hidden" id="converted_total_amount"
                        value="{{ floor($this->totals['total_net'] * $factor) == $this->totals['total_net'] * $factor
                            ? number_format($this->totals['total_net'] * $factor, 0)
                            : number_format($this->totals['total_net'] * $factor, 2) }}">
                @endif

            </div>
            <div class="w-full flex  items-end justify-between gap-2">
                <select wire:change="setCurrency($event.target.value)"
                    class="col-span-2 border rounded  px-6 py-2 focus:ring focus:ring-green-300">
                    @foreach ($all_currency as $currency_symbol)
                        <option value="{{ $currency_symbol->code }}" @selected($currency === $currency_symbol->code)>
                            {{ $currency_symbol->name }}
                        </option>
                    @endforeach
                </select>
                <div id="list_main" class="relative col-span-2" style="width:300px;">
                    <input type="text" id="customerSearch" placeholder="ភ្ញៀវដើរចូល" autocomplete="off">

                    <input type="hidden" id="customerValue" wire:model.live="customer_id">

                    <ul id="customerList"
                        class="list hidden absolute z-50 bg-white border rounded shadow w-full max-h-60 overflow-auto">
                    </ul>
                </div>
            </div>
            <hr>
            <div class="mt-5 grid grid-cols-4 gap-2">




                <button wire:click="clearCart"
                    class="bg-red-300 hover:bg-red-400 text-white font-semibold px-4 py-2 rounded">
                    <i class="fa-solid fa-trash-can"></i>
                </button>

                @if (!empty($this->Current_table_id))
                    <button style="font-size: 10px;"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"
                        onclick="showTableModal({{ $count_cart }},'{{ $this->Current_table_id }}')">
                        Table
                    </button>
                @else
                    <button style="font-size: 10px;"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded"
                        onclick="showTableModal({{ $count_cart }},'ALL')">
                        Table
                    </button>
                @endif
                <button onclick="print('Receipt')" style="font-size: 10px;"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
                    Payment
                </button>
                {{-- <button onclick="print('Invoice')" style="font-size: 10px;"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Invoice
                </button> --}}

                {{-- <button onclick="print('Delivery Note')" style="font-size: 10px;"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Delivery Note
                </button> --}}



            </div>
        </div>
    </div>




    <div id="invoice">
        <div class="print-only">
            <input type="text" id="count_cart_input" value="{{ $count_cart }}" hidden>

            <div id="logo" style="flex: 0 0 auto; margin-right:15px;">
                <img id="logo" class="logo" style="width: 100px;" src="{{ asset('assets/logo/logo.png') }}">
            </div>
            <div id="document-header">
                <!-- LOGO -->

                <!-- INVOICE HEADER -->
                <div class="invoice-header" style="display:flex; align-items:flex-start;">

                    <!-- BUYER & SELLER -->
                    <div style="flex:1; display:flex; justify-content:space-between;">

                        <!-- BUYER (LEFT) -->
                        <div style="width:48%; text-align:left;">

                            <strong>Bill To:</strong><br>
                            Name: {{ $customer_name }}<br>
                            Mobile: {{ $customer_phone }}<br>
                            Address: {{ $customer_address }}<br>
                            City: {{ $customer_city }}
                        </div>

                        <!-- SELLER (RIGHT) -->


                    </div>
                </div>



            </div>
            <div id="document_title">
                <h1> </h1>
            </div>

            <div id="shop_info">
                <div class="text-left">
                    <div id="seller_company">Confirel</div>
                    <div id="seller_address">#57 , Street 178, Songkat Chey Chomneas</div>
                    <div id="seller_address2">Khan Doun Penh , Phnom Penh , Cambodia.</div>
                    <div id="seller_phone">Mobile: +855 93 981 724</div>
                    <div id="seller_email">Email: info@confirel.com</div>
                    <div id="seller_name">Seller: Cashier</div>
                </div>
            </div>
            <div id="customer_info">
                <div class="text-left">
                    @if ($customer_name != 'Walk-in Customer')
                        <div id="sell_to_name" class="bold">{{ $customer_name }}</div>
                        <div id="sell_to_company">{{ $customer_name }}</div>
                        <div id="sell_to_address1">{{ $customer_address }}</div>
                        <div id="sell_to_address2">{{ $customer_address }}</div>
                        <div id="sell_to_contact_name">{{ $customer_name }}</div>
                        <div id="sell_to_phone">Mobile: {{ $customer_phone }}</div>
                    @else
                        <div id="sell_to_name" class="bold">Walk-in Customer</div>
                    @endif

                </div>
            </div>
            <div id="table_footer">
                <div style="width:100%; display:flex; justify-content:center ; margin-top:30px;">
                    <div id="table_footer_description"></div>
                    <!-- CURRENCY RATE -->

                </div>
            </div>

            <div id="invoice-table">
                <!-- INVOICE TABLE -->
                <table style="width:100%; border-collapse:collapse; margin-top:15px;">
                    <thead>
                        <tr style="background-color:#f2f2f2;">
                            <th style="border:1px solid #000; padding:8px;">#</th>
                            <th style="border:1px solid #000; padding:8px;">Item</th>
                            <th style="border:1px solid #000; padding:8px;">Qty</th>
                            <th style="border:1px solid #000; padding:8px;">Unit</th>
                            <th style="border:1px solid #000; padding:8px;">Unit Price</th>
                            <th style="border:1px solid #000; padding:8px;">Discount</th>
                            <th style="border:1px solid #000; padding:8px;">Total</th>
                        </tr>
                    </thead>






                    <tbody>
                        @foreach ($cart as $item)
                            <tr style="background-color: {{ $loop->even ? '#ffffff' : '#f9f9f9' }};">
                                <td style="border:1px solid #000; padding:6px;">{{ $item['order_no'] }}</td>
                                <td style="border:1px solid #000; padding:6px;">{{ $item['name'] }}</td>
                                <td style="border:1px solid #000; padding:6px; text-align:center;">{{ $item['qty'] }}
                                </td>
                                <td style="border:1px solid #000; padding:6px; text-align:center;">{{ $item['unit'] }}
                                </td>
                                <td style="border:1px solid #000; padding:6px; text-align:right;">
                                    {{ number_format($item['price'], 2) }}$</td>
                                <td style="border:1px solid #000; padding:6px; text-align:center;">
                                    {{ $item['discount_percent'] }}%</td>
                                <td style="border:1px solid #000; padding:6px; text-align:right;">
                                    {{ number_format($item['net_amount_line'], 2) }}$</td>
                            </tr>
                        @endforeach

                        <!-- TOTALS -->
                        <tr class="total_print">
                            <td colspan="7" style="text-align:end;">Subtotal:
                                {{ number_format($this->totals['total_original'], 2) }}$</td>
                        </tr>
                        <tr class="total_print">
                            <td colspan="7" style="text-align:end;">Discount:
                                {{ number_format($this->totals['total_discount'], 2) }}$</td>
                        </tr>
                        <tr class="total_print">
                            <td colspan="7" style="text-align:end;">Total Amount:
                                {{ number_format($this->totals['total_net'], 2) }}$</td>
                        </tr>

                        @if ($currency != 'USD')
                            <tr class="total_print">
                                <td colspan="7" style="text-align:end;">Total Amount in {{ $currency }}:
                                    {{ number_format($this->totals['total_net'] * $factor, 2) }}{{ $currency }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

            </div>




        </div>

    </div>



</div>




</div>
