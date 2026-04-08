<div>
    <div class="screen-only">
        <div id="header_invoice"
            class="border-b bg-white border-default pb-2  p-2 flex items-center justify-between sticky top-0">
            <h1 id="chasier" class="mb-2 font-bold">

                <span id="tittle_span" class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                    Purchase Order
                </span>



            </h1>
            <div class="px-4" id="refreshBtn" data-popover-target="popover-user-profile">
                <i id="refresh-icon" class="fa-solid fa-arrows-rotate"></i>
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


                                <p class="text-sm text-gray-400">
                                    តម្លៃ:
                                    {{-- Display cost --}}
                                    <span>

                                        {{ rtrim(rtrim(number_format((float) $item['cost_price'] * $this->factor, 3, '.', ''), '0'), '.') }}
                                        {{ $this->currency_name }}
                                    </span>


                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">
                                {{ rtrim(rtrim(number_format((float) $item['amount_line'] * $this->factor, 3, '.', ''), '0'), '.') }}

                                {{ $this->currency_name }}



                            </p>
                        </div>
                    </div>

                    <!-- Dropdown Content -->
                    <div class="hidden  bonus border-b p-2">
                        <div class="grid grid-cols-3 gap-1" wire:key="row-{{ $loop->index }}-{{ $this->factor }}">

                            {{-- QTY --}}
                            <div>
                                <label class="text-sm text-gray-500">ចំនួន</label>
                                <input type="number" min="1" step="1"
                                    wire:model.lazy="cart.{{ $loop->index }}.qty"
                                    wire:change="recalcLine({{ $loop->index }}, 'qty')"
                                    class="w-full mt-1 border rounded px-3 py-2 focus:outline-none focus:ring" />
                            </div>
                            {{-- COST --}}
                            <div>
                                <label class="text-sm text-gray-500">ថ្លៃដើម</label>
                                <input type="number" min="0" step="0.01"
                                    wire:key="cost-{{ $loop->index }}-{{ $this->cart[$loop->index]['cost_price'] }}-{{ $this->factor }}"
                                    value="{{ rtrim(rtrim(number_format($item['cost_price'] * $this->factor, 3, '.', ''), '0'), '.') }}"
                                    wire:change="recalcLine({{ $loop->index }}, 'cost_price', $event.target.value)"
                                    class="w-full mt-1 border rounded px-3 py-2" />
                            </div>

                            {{-- AMOUNT --}}
                            <div>
                                <label class="text-sm text-gray-500">សរុប</label>
                                <input type="number" min="0" step="0.01"
                                    wire:key="amount-{{ $loop->index }}-{{ $this->cart[$loop->index]['amount_line'] }}-{{ $this->factor }}"
                                    value="{{ rtrim(rtrim(number_format($item['amount_line'] * $this->factor, 3, '.', ''), '0'), '.') }}"
                                    wire:change="recalcLine({{ $loop->index }}, 'amount_line', $event.target.value)"
                                    class="w-full mt-1 border rounded px-3 py-2" />
                            </div>

                            <div class="col-span-3">
                                <div class="grid grid-cols-2 gap-1">
                                    <div>
                                        <label class="text-sm text-gray-500">Lot Auto (editable)</label>
                                        <input type="text" wire:model.lazy="cart.{{ $loop->index }}.lot"
                                            class="w-full mt-1 border rounded px-3 py-2" />
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-500">Expire</label>
                                        <input type="date" wire:model.lazy="cart.{{ $loop->index }}.expire"
                                            class="w-full mt-1 border rounded px-3 py-2" />
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-span-3">
                            <label class="text-sm text-gray-500">Remark</label>
                            <input type="text" wire:model.lazy="cart.{{ $loop->index }}.remark"
                                class="w-full mt-1 border rounded px-3 py-2" />
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


                <p class="font-semibold">
                    តម្លៃសរុប :
                    {{ rtrim(rtrim(number_format((float) $this->totals['total_amount'] * $this->factor, 3, '.', ''), '0'), '.') }}
                    {{ $currency_name }}
                </p>
                <input type="hidden" id="total_amount"
                    value="{{ number_format($this->totals['total_amount'], 2, '.', '') }}">
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
                            Total USD:{{ $this->totals['total_amount'] }} $

                        </p>


                    </div>
                    <input type="hidden" id="converted_total_amount"
                        value="{{ floor($this->totals['total_amount'] * $factor) == $this->totals['total_amount'] * $factor
                            ? number_format($this->totals['total_amount'] * $factor, 0)
                            : number_format($this->totals['total_amount'] * $factor, 2) }}">
                @else
                    <input type="hidden" id="converted_total_amount"
                        value="{{ floor($this->totals['total_amount'] * $factor) == $this->totals['total_amount'] * $factor
                            ? number_format($this->totals['total_amount'] * $factor, 0)
                            : number_format($this->totals['total_amount'] * $factor, 2) }}">
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
                    <input type="text" id="customerSearch" placeholder="General vendor" autocomplete="off">

                    <input type="hidden" id="customerValue" wire:model.live="customer_id">

                    <ul id="customerList"
                        class="list hidden absolute z-50 bg-white border rounded shadow w-full max-h-60 overflow-auto">
                    </ul>
                </div>
            </div>

            <div class="w-full flex justify-end">

                <input type="text" wire:model.lazy="$this->Remark" placeholder="Remark"
                    class="w-full mt-1 border rounded px-3 py-2" />
            </div>

            <hr>
            <div class="mt-5 grid grid-cols-4 gap-2">




                <button wire:click="clearCart"
                    class="bg-red-300 hover:bg-red-400 text-white font-semibold px-4 py-2 rounded">
                    <i class="fa-solid fa-trash-can"></i>
                </button>

                <button wire:click="post_grn"
                    class="bg-green-600 hover:bg-green-800 text-white font-semibold px-4 py-2 rounded">
                    Purchase
                </button>
                <select wire:model="warehouse_id"
                    class="col-span-2 border rounded px-6 py-2 focus:ring focus:ring-green-300">
                    @foreach ($warehouses as $id => $name)
                        <option value="{{ $id }}" @selected($warehouse_id == $id)>{{ $name }}</option>
                    @endforeach
                </select>
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
                $shopInfo_CFR = [
                    'company' => 'Confirel',
                    'address1' => '#57, Street 178, Songkat Chey Chomneas',
                    'address2' => 'Khan Doun Penh, Phnom Penh, Cambodia.',
                    'phone' => '+855 93 981 724',
                    'email' => 'info@confirel.com',
                    'seller' => 'Cashier',
                    'name' => 'Confirel Co., Ltd.',
                ];
                $sellerInfo_panha = [
                    'address1' => 'PHUM SAMRORNG, SANGKAT KRANG THNUNG,',
                    'address2' => 'KHAN SEN SOK, PHNOM PENH, CAMBODIA',
                    'phone' => '010 712 324 / 070 426 322',
                    'email' => 'spsparep@gmail.com',
                    'name' => 'Mr. Troek Panha',
                ];

                $sellerInfo = $shopInfo_CFR;
            @endphp


            <div id="shop_info">
                <div class="text-left">
                    <div id="seller_address">{{ $sellerInfo['address1'] }}</div>
                    <div id="seller_address2">{{ $sellerInfo['address2'] }}</div>
                    <div id="seller_phone">Mobile: {{ $sellerInfo['phone'] }}</div>
                    <div id="seller_email">Email: {{ $sellerInfo['email'] }}</div>
                    <div id="seller_name">Seller: {{ $sellerInfo['name'] }}</div>
                </div>
            </div>
            <div id="customer_info">
                <div class="text-left">
                    @if ($vendor_name != 'General vendor')
                        @if (filled($this->vendor_name) ||
                                filled($this->vendor_address1) ||
                                filled($this->vendor_address2) ||
                                filled($this->vendor_contact_name) ||
                                filled($this->vendor_contact_phone))

                            @if (filled($this->vendor_name))
                                <div id="sell_to_name" class="bold">
                                    {{ $this->vendor_name }}
                                </div>
                            @endif

                            @if (filled($this->vendor_address1))
                                <div id="sell_to_address1">
                                    {{ $this->vendor_address1 }}
                                </div>
                            @endif

                            @if (filled($this->vendor_address2))
                                <div id="sell_to_address2">
                                    {{ $this->vendor_address2 }}
                                </div>
                            @endif

                            @if (filled($this->vendor_contact_name))
                                <div id="sell_to_contact_name">
                                    ATT To: {{ $this->vendor_contact_name }}
                                </div>
                            @endif

                            @if (filled($this->vendor_contact_phone))
                                <div id="sell_to_phone">
                                    Mobile: {{ $this->vendor_contact_phone }}
                                </div>
                            @endif

                        @endif
                    @else
                        <div id="sell_to_name" class="bold">General vendor</div>
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
                <!-- INVOICE TABLE -->
                <table style="width:100%;">
                    <thead>
                        <tr">
                            <th>No.</th>
                            <th class="text-left">Item</th>
                            <th>Qty</th>
                            <th>Unit</th>


                            <th>Total</th>
                            </tr>
                    </thead>






                    <tbody>
                        @foreach ($cart as $item)
                            <tr style="background-color: {{ $loop->even ? '#ffffff' : '#f9f9f9' }};">
                                <td>{{ $item['order_no'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['qty'] }}
                                </td>
                                <td>{{ $item['unit'] }}
                                </td>


                                <td>
                                    {{ number_format($item['amount_line'], 2) }}$</td>
                            </tr>
                        @endforeach

                        <tr class="total_print">
                            <td colspan="7" style="text-align:end;">
                                Total Amount: {{ number_format(($this->totals['total_amount'] * 100) / 100, 2) }}$

                            </td>
                        </tr>
                        @if ($currency != 'USD')
                            <tr class="total_print">
                                <td colspan="7" style="text-align:end;">
                                    Total Amount in {{ $currency }}:
                                    {{ number_format(floor($this->totals['total_amount'] * $factor * 100) / 100, 0, '.', ' ') }}
                                    {{ $currency }}
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
