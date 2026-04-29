<div>
    <div class="screen-only">
        <div id="header_invoice"
            class="border-b bg-white border-default pb-2 p-2 flex items-center justify-between sticky top-0">

            <h1 id="chasier" class="mb-2 font-bold flex items-center gap-2">


                <span id="tittle_span" class="text-transparent bg-clip-text bg-gradient-to-r to-amber-600 from-amber-400">

                    @if ($cart_mode == 'expence')
                        Expense Order
                    @else
                        Sales Order
                        @if ($this->document_no != 'NA')
                            {{ $this->document_no }}
                        @endif
                    @endif

                </span>

                {{-- MODE BADGE --}}
                @if ($cart_mode == 'expence')
                    <span class="text-xs bg-red-500 text-white px-2 py-1 rounded-full">
                        Expense Mode
                    </span>
                @elseif($cart_mode == 'sale')
                    <span class="text-xs bg-green-500 text-white px-2 py-1 rounded-full">
                        Sale Mode
                    </span>
                @endif

            </h1>

            <div class="px-4 cursor-pointer" id="refreshBtn" data-popover-target="popover-user-profile">
                <i id="refresh-icon" class="fa-solid fa-arrows-rotate"></i>
            </div>

            <div data-popover id="popover-user-profile" role="tooltip"
                class="absolute z-10 invisible inline-block w-64 text-sm text-body transition-opacity duration-300 bg-neutral-primary-soft border border-default rounded-base shadow-xs opacity-0">

                <div class="p-3">
                    <p class="text-sm text-gray-500">
                        Tip: Click on the arrows to refresh the Page.
                    </p>
                </div>

                <div data-popper-arrow></div>
            </div>

        </div>

        @forelse ($cart as $item)
            <div class="w-full mx-auto animate-add">
                <!-- Item Card -->
                <div
                    class="card bg-white shadow border-b-amber-600 focus-within:bg-yellow-50 transition-colors duration-200 ">
                    <!-- Header (clickable) -->
                    <div class="btn_sale_invoice w-full flex items-center justify-between p-2">
                        <div class="flex items-start gap-3">
                            <div class="flex flex-col items-center justify-center">
                                @if ($this->document_type == 'Deposit' || $this->document_type == 'Completed' ||  $this->document_type == 'Cancelled' || $this->document_type == 'Returned')
                                @else
                                    <span style="font-size:20px" wire:click="toggleItem({{ $loop->index }})"
                                        class="text-green-500 text-lg transition-transform duration-300 hover:cursor-pointer
                                                {{ $openIndex === $loop->index ? 'rotate-180' : '' }}">
                                        ▾
                                    </span>
                                    <button wire:click.stop="removeItem({{ $item['id'] }})" title="Remove item"><span
                                            class="text-red-500 text-lg transition-transform duration-300 hover:cursor-pointer arrow"><i
                                                class="fa-solid fa-delete-left fa-flip-horizontal"></i></span></button>
                                @endif


                            </div>
                            <div class="text-left">
                                <p class="font-semibold number-change">{{ $item['order_no'] }}. {{ $item['name'] }} x
                                    {{ $item['qty'] }} {{ $item['unit'] }}

                                </p>
                                @if ($item['stock'] < $item['qty'] && strtolower($item['type']) == 'product')
                                    <span
                                        class="inline-flex items-center bg-rose-400 border border-brand-subtle text-white text-xs font-medium px-1.5 py-0.5 rounded-sm">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                        ស្តុកមិនគ្រប់
                                    </span>
                                @endif
                                <p class="text-sm text-gray-400 number-change">
                                    តម្លៃ:
                                    @if ($item['discount_percent'] != 0)
                                        <del>
                                            {{ number_format((float) $item['price'] * $this->factor, $this->factor == 1 ? 2 : 0) }}
                                        </del>
                                        {{ $this->currency_name }}
                                        -
                                        {{ number_format((float) $item['discount_price'] * $this->factor, $this->factor == 1 ? 2 : 0) }}
                                        {{ $this->currency_name }}
                                    @else
                                        {{ number_format((float) $item['price'] * $this->factor, $this->factor == 1 ? 2 : 0) }}
                                        {{ $this->currency_name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold number-change">
                                @if ($item['discount_percent'] != 0)
                                    <del>
                                        {{ number_format((float) $item['amount_line'] * $this->factor, $this->factor == 1 ? 2 : 0) }}
                                        {{ $this->currency_name }}
                                    </del>
                                    -
                                    {{ number_format((float) $item['net_amount_line'] * $this->factor, $this->factor == 1 ? 2 : 0) }}
                                    {{ $this->currency_name }}
                                @else
                                    {{ number_format((float) $item['amount_line'] * $this->factor, $this->factor == 1 ? 2 : 0) }}
                                    {{ $this->currency_name }}
                                @endif


                            </p>
                        </div>
                    </div>

                    <!-- Dropdown Content -->
                    <div class="{{ $openIndex === $loop->index ? '' : 'hidden' }} bonus border-b p-2">
                        <div class="grid grid-cols-7 gap-4">
                            @if ($item['type'] == 'product' || $item['type'] == 'service')
                                <div class="col-span-2">
                                    <label class="text-sm text-gray-500">ចំនួន</label>
                                    <input type="number" min="1" max="{{ $item['stock'] }}"
                                        id="qty_order_{{ $loop->index }}" step="1"
                                        wire:model.lazy="cart.{{ $loop->index }}.qty"
                                        wire:change="recalcLine({{ $loop->index }}, 'qty')"
                                        class="w-full mt-1 border rounded px-3 py-2 focus:outline-none focus:ring" />
                                </div>

                                <!-- Unit Price -->
                                <div class="col-span-2">
                                    <label class="text-sm text-gray-500">តម្លៃ</label>
                                    <input type="number" min="0" step="0.01"
                                        wire:key="price-{{ $loop->index }}-{{ $this->cart[$loop->index]['price'] }}-{{ $this->factor }}"
                                        value="{{ rtrim(rtrim(number_format($item['price'] * $this->factor, 3, '.', ''), '0'), '.') }}"
                                        wire:change="recalcLine({{ $loop->index }}, 'price', $event.target.value)"
                                        class="w-full mt-1 border rounded px-3 py-2" />
                                </div>

                                <!-- Discount -->
                                <div class="col-span-2">
                                    <label class="text-sm text-gray-500">បញ្ចុះតម្លៃ (%)</label>
                                    <input type="number" min="0" max="100"
                                        wire:model.lazy="cart.{{ $loop->index }}.discount_percent"
                                        wire:change="recalcLine({{ $loop->index }}, 'discount_percent')"
                                        class="w-full mt-1 border rounded px-3 py-2 focus:outline-none focus:ring" />
                                </div>
                            @else
                                <div class="col-span-3">
                                    <label class="text-sm text-gray-500">ចំនួន</label>
                                    <input type="number" min="1" max="{{ $item['stock'] }}"
                                        id="qty_order_{{ $loop->index }}" step="1"
                                        wire:model.lazy="cart.{{ $loop->index }}.qty"
                                        wire:change="recalcLine({{ $loop->index }}, 'qty')"
                                        class="w-full mt-1 border rounded px-3 py-2 focus:outline-none focus:ring" />
                                </div>

                                <!-- Unit Price -->
                                <div class="col-span-4">
                                    <label class="text-sm text-gray-500">តម្លៃ</label>
                                    <input type="number" min="0" step="0.01"
                                        wire:key="price-{{ $loop->index }}-{{ $this->cart[$loop->index]['price'] }}-{{ $this->factor }}"
                                        value="{{ rtrim(rtrim(number_format($item['price'] * $this->factor, 3, '.', ''), '0'), '.') }}"
                                        wire:change="recalcLine({{ $loop->index }}, 'price', $event.target.value)"
                                        class="w-full mt-1 border rounded px-3 py-2" />
                                </div>
                            @endif

                            @if ($item['track_stock'] == 1)
                                <div>

                                    <label class="text-sm text-gray-500">Manage Lot</label>
                                    <div class="flex">
                                        <button
                                            onclick="openLotModal({{ $loop->index }}, {{ $item['id'] }}, '{{ $item['name'] }}', {{ $item['qty'] }})"
                                            class="bg-blue-300 hover:bg-blue-400 text-white font-semibold px-1 py-1 rounded">
                                            +
                                        </button>
                                        &ensp;
                                        <button wire:click="viewLots({{ $loop->index }})"
                                            class="bg-blue-300 hover:bg-blue-400 text-white font-semibold px-1 py-1 rounded">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                            @if ($item['type'] == 'expence')
                                <div class="col-span-7">
                                    <label class="text-sm text-gray-500">សម្រាប់</label>
                                    <input type="text" wire:model.lazy="cart.{{ $loop->index }}.expence_for"
                                        class="w-full mt-1 border rounded px-3 py-2 focus:outline-none focus:ring" />
                                </div>
                            @endif

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
                @if ($cart_mode == 'expence')

                    <p class="font-semibold">
                        តម្លៃសរុប : {{ number_format($this->totals['grand_total'] * $this->factor, 0) }}
                        {{ $this->currency_name }}
                    </p>

                    @if ($this->currency_name != '$')
                        <p class="font-semibold">
                            តម្លៃសរុប ជា USD : {{ number_format($this->totals['grand_total'], 2) }} $
                        </p>
                    @endif
                @else
                    @php
                        $decimal = $this->factor == 1 ? 2 : 0;
                    @endphp

                    <p class="text-sm">
                        សរុបរង:
                        {{ number_format($this->totals['total_original'] * $this->factor, $decimal) }}
                        {{ $this->currency_name }}
                    </p>

                    <p class="text-sm">
                        បញ្ចុះតម្លៃ :
                        {{ number_format($this->totals['total_discount'] * $this->factor, $decimal) }}
                        {{ $this->currency_name }}
                    </p>

                    @if ($this->totals['vat_status'] > 0)
                        <p class="text-sm">
                            VAT {{ (int) $this->totals['vat_status'] }} % :
                            {{ number_format($this->totals['total_vat_amount'] * $this->factor, $decimal) }}
                            {{ $this->currency_name }}
                        </p>
                    @endif

                    <p class="font-semibold">
                        តម្លៃសរុប :
                        {{ number_format($this->totals['grand_total'] * $this->factor, $decimal) }}
                        {{ $this->currency_name }}
                    </p>

                    @if ($this->factor != 1)
                        <p class="font-semibold">
                            តម្លៃសរុប USD :
                            {{ number_format($this->totals['grand_total'], 2) }} $
                        </p>
                    @endif
                @endif


                <input type="hidden" id="total_amount"
                    value="{{ number_format($this->totals['grand_total'], 2, '.', '') }}">
                <input type="hidden" id="currency_name" value="{{ $currency_name }}">
                <input type="hidden" id="currency_display_symbol" value="{{ $this->currency }}">
                <input type="hidden" id="currency_display_factor" value="{{ $this->factor }}">
                <input type="hidden" id="document_type" value="{{ $this->document_type }}">

                @if ($this->factor != 1)
                    <div class="w-full flex justify-between">

                        <div class="flex items-center">


                            <span
                                class="inline-flex items-center bg-brand-softer border border-brand-subtle text-fg-brand-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">

                                1$ : {{ (float) $factor }}&ensp;{{ $currency }}
                            </span>
                        </div>



                    </div>
                    <input type="hidden" id="converted_total_amount"
                        value="{{ floor($this->totals['grand_total'] * $factor) == $this->totals['grand_total'] * $factor
                            ? number_format($this->totals['grand_total'] * $factor, 0)
                            : number_format($this->totals['grand_total'] * $factor, 2) }}">
                @else
                    <input type="hidden" id="converted_total_amount"
                        value="{{ floor($this->totals['grand_total'] * $factor) == $this->totals['grand_total'] * $factor
                            ? number_format($this->totals['grand_total'] * $factor, 0)
                            : number_format($this->totals['grand_total'] * $factor, 2) }}">
                @endif

            </div>
            <div class="w-full flex  items-end justify-between gap-2">
                <div class="relative col-span-2">

                    <!-- Icon -->
                    <i class="fa-solid fa-dollar-sign absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

                    <!-- Select -->
                    <select wire:change="setCurrency($event.target.value)"
                        class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2
               shadow-sm focus:ring-2 focus:ring-green-300
               focus:outline-none bg-white text-gray-700 min-w-[180px]">

                        @foreach ($all_currency as $currency_symbol)
                            <option value="{{ $currency_symbol->code }}" @selected($currency === $currency_symbol->code)>
                                {{ $currency_symbol->name }}
                            </option>
                        @endforeach
                    </select>

                </div>
                @if ($cart_mode == 'expence')
                @else
                    <div id="list_main" class="relative col-span-2 w-[300px]">

                        <!-- Icon -->
                        <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <!-- Search Input -->
                        <input type="text" id="customerSearch" placeholder="ភ្ញៀវដើរចូល / Search Customer..."
                            autocomplete="off"
                            class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2
                                shadow-sm focus:ring-2 focus:ring-blue-300
                                focus:outline-none text-gray-700">

                        <!-- Hidden Value -->
                        <input type="hidden" id="customerValue" wire:model.live="customer_id">

                        <!-- Dropdown List -->
                        <ul id="customerList"
                            class="hidden absolute z-50 mt-1 bg-white border border-gray-200
               rounded-xl shadow-lg w-full max-h-60 overflow-auto">
                        </ul>

                    </div>
                @endif
            </div>
            <hr>
            <div class="mt-5 grid grid-cols-4 gap-2">




                <!-- Clear -->
                <button wire:click="clearCart"
                    class="bg-red-500 hover:bg-red-600 text-white font-small px-4 py-2 rounded-xl shadow-md transition">
                    <i class="fa-solid fa-trash-can mr-1"></i> Clear
                </button>

                @if ($cart_mode == 'expence')
                    <!-- Expense -->
                    <button onclick="openExpenseModal()"
                        class="bg-orange-500 hover:bg-orange-600 text-white font-small px-4 py-2 rounded-xl shadow-md transition">
                        <i class="fa-solid fa-wallet mr-1"></i> Pay Expense
                    </button>
                @else
                    <!-- Saved Order -->
                    <button onclick="openSaleOrderModal()"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-small px-4 py-2 rounded-xl shadow-md transition">
                       <i class="fa-solid fa-cart-shopping"></i> Orders
                    </button>
                    @if ($this->document_no != 'NA')
                        <!-- Update Sale Order -->
                        <button onclick="update_sale_order()"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-small px-2 py-2 rounded-xl shadow-md transition">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Update
                        </button>
                    @else
                        <!-- Save Order -->
                        <button onclick="Save_Sale_Order()"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-small px-4 py-2 rounded-xl shadow-md transition">
                            <i class="fa-solid fa-hand-holding-dollar"></i> Pay
                        </button>
                    @endif
                    @if ($this->document_id != 0)
                        <button onclick="openSaleLine()"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-small px-4 py-2 rounded-xl shadow-md transition">
                            <i class="fa-solid fa-circle-info"></i> Info
                        </button>
                    @endif

                @endif


            </div>
        </div>
    </div>




    <div id="invoice">
        <div class="print-only">
            <input type="text" id="count_cart_input" value="{{ $count_cart }}" hidden>
            @php
                // CFR
                $logo = 'logo_cfr.png';
                // Panha
                //    $logo = 'logo.jpg';
            @endphp

            <div id="logo" style="flex: 0 0 auto; margin-right:15px;">

                {{-- CFR  --}}
                <img class="logo" style="width: 80px;" src="{{ asset('assets/logo/' . $logo) }}" alt="Logo">
                {{-- Panha --}}
                {{-- <img class="logo" style="width: 250px;" src="{{ asset('assets/logo/' . $logo) }}" alt="Logo"> --}}
            </div>

            <div id="logo_80mm" style="flex: 0 0 auto; margin-right:15px;">

                {{-- CFR  --}}
                <img class="logo" style="width: 80px;" src="{{ asset('assets/logo/' . $logo) }}" alt="Logo">
                {{-- Panha --}}
                {{-- <img class="logo" style="width: 120px;" src="{{ asset('assets/logo/' . $logo) }}" alt="Logo"> --}}
            </div>
            <div id="document_title">
                <h1> </h1>
            </div>
            @php
                $shopInfo_duck = [
                    'company' => 'ឈូកមាស ផ្គត់ផ្គង់សាច់គ្រប់ប្រភេទ',
                    'description' => 'មានលក់ដុំនិងរាយ មាន់ ទា ជើងមាន់ ស្លាបមាន់ សាច់ទ្រូងមាន់ និងគ្រឿងប្រឡាក់សាច់',
                    'address1' => 'ភ្នំពេញ',
                    'address2' => '',
                    'phone' => '011 79 80 87 / 097 779 80 87',
                    'email' => '',
                    'telegram' => '016 79 80 87',
                    'seller' => '016 79 80 87',
                    'name' => 'អតិថិជនទូទៅ',
                ];

                $sellerInfo = $shopInfo_duck;
            @endphp

            <div id="customer_info">
                <div class="text-left">
                    @if ($customer_name != 'Walk-in Customer')
                        @if (filled($this->customer_name) ||
                                filled($this->customer_address1) ||
                                filled($this->customer_address2) ||
                                filled($this->customer_contact_name) ||
                                filled($this->customer_contact_phone))

                            @if (filled($this->customer_name))
                                <div id="sell_to_name" class="bold">
                                    {{ $this->customer_name }}
                                </div>
                            @endif

                            @if (filled($this->customer_address1))
                                <div id="sell_to_address1">
                                    {{ $this->customer_address1 }}
                                </div>
                            @endif

                            @if (filled($this->customer_address2))
                                <div id="sell_to_address2">
                                    {{ $this->customer_address2 }}
                                </div>
                            @endif

                            @if (filled($this->customer_contact_name))
                                <div id="sell_to_contact_name">
                                    ATT To: {{ $this->customer_contact_name }}
                                </div>
                            @endif

                            @if (filled($this->customer_contact_phone))
                                <div id="sell_to_phone">
                                    Mobile: {{ $this->customer_contact_phone }}
                                </div>
                            @endif

                        @endif
                    @else
                        <div id="sell_to_name" class="bold">Walk-in Customer</div>
                    @endif

                </div>
            </div>
            <div id="table_footer">
                <div>
                    <div id="table_footer_description"></div>
                    <!-- CURRENCY RATE -->

                </div>
            </div>

            <div id="invoice-table">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>ល.រ</th>
                            <th>រាយមុខទំនិញ</th>
                            <th>ឯកតា</th>
                            <th>ចំនួន</th>
                            <th>តម្លៃ</th>
                            <th>តម្លៃសរុប</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cart as $item)
                            <tr>
                                <td style="text-align:center;">{{ $item['order_no'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td style="text-align:center;">{{ $item['unit'] }}</td>
                                <td style="text-align:center;">{{ $item['qty'] }}</td>
                                <td style="text-align:right;">
                                    {{ number_format($item['price'] * $factor, $factor == 1 ? 2 : 0) }}
                                </td>
                                <td style="text-align:right;">
                                    {{ number_format($item['net_amount_line'] * $factor, $factor == 1 ? 2 : 0) }}
                                </td>
                            </tr>
                        @endforeach
                        {{-- Sub Total --}}
                        <tr class="total_print">
                            <td colspan="5" style="text-align:right; font-weight:bold;">
                                សរុប/Sub Total ({{ $currency }})
                            </td>
                            <td style="text-align:right; font-weight:bold;">
                                {{ number_format($this->totals['total_original'] * $factor, $factor == 1 ? 2 : 0) }}
                            </td>
                        </tr>
                        {{-- Discount --}}
                        <tr class="total_print">
                            <td colspan="5" style="text-align:right; font-weight:bold;">
                                បញ្ចុះតម្លៃ/Discount ({{ $currency }})
                            </td>
                            <td style="text-align:right; font-weight:bold;">
                                {{ number_format($this->totals['total_discount'] * $factor, $factor == 1 ? 2 : 0) }}
                            </td>
                        </tr>
                        {{-- VAT --}}
                        @if (($this->totals['vat_status'] ?? 0) > 0)
                            <tr class="total_print">
                                <td colspan="5" style="text-align:right; font-weight:bold;">
                                    អាករ/VAT {{ (int) $this->totals['vat_status'] }}% ({{ $currency }})
                                </td>
                                <td style="text-align:right; font-weight:bold;">
                                    {{ number_format($this->totals['total_vat_amount'] * $factor, $factor == 1 ? 2 : 0) }}
                                </td>
                            </tr>
                        @endif
                        {{-- Grand Total --}}
                        <tr class="total_print">
                            <td colspan="5" style="text-align:right; font-weight:bold;">
                                សរុបរួម/Grand Total ({{ $currency }})
                            </td>
                            <td style="text-align:right; font-weight:bold;">
                                {{ number_format($this->totals['grand_total'] * $factor, $factor == 1 ? 2 : 0) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>




    </div>

</div>



</div>




</div>
