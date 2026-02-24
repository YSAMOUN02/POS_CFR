<div>
    <div class="screen-only">
        <div id="header_invoice"
            class="border-b bg-white border-default pb-2  p-2 flex items-center justify-between sticky top-0">
            <h1 id="chasier" class="mb-2 font-bold">
                @if (!empty($this->Current_table_id))
                    <span class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                        Editing Data :
                    </span>
                    {{ $this->Current_table_name }}
                @else
                    <span id="tittle_span"
                        class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                        {{ $prefix }}
                    </span>
                    {{ $title }}
                @endif

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
                                        <del>{{ number_format($item['price'], 2) }}</del>$
                                        -{{ number_format((float) $item['discount_price'], 2) }}$
                                    @else
                                        {{ number_format((float) $item['price'], 2) }} $
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold">
                                @if ($item['discount_percent'] != 0)
                                    <del>{{ number_format($item['amount_line'], 2) }}$</del> -
                                    {{ number_format($item['net_amount_line'], 2) }}$
                                @else
                                    {{ number_format($item['amount_line'], 2) }}$
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
                <p class="text-sm">
                    សរុបរង: {{ number_format($this->totals['total_original'], 2) }}$

                </p>

                <p class="text-sm">
                    បញ្ចុះតម្លៃ : {{ number_format($this->totals['total_discount'], 2) }}$
                </p>
                <p class="font-semibold">
                    តម្លៃសរុប : {{ number_format($this->totals['total_net'], 2) }}$
                </p>
                <input type="hidden" id="total_amount"
                    value="{{ number_format($this->totals['total_net'], 2, '.', '') }}">
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
                            @php
                                $converted = $this->totals['total_net'] * $factor;
                                $truncated = floor($converted * 100) / 100;
                            @endphp
                            {{ $truncated == floor($truncated) ? number_format($truncated, 0) : number_format($truncated, 2) }}
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
                <!-- INVOICE TABLE -->
                <table style="width:100%;">
                    <thead>
                        <tr">
                            <th>No.</th>
                            <th class="text-left">Item</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Price</th>
                            <th>Discount</th>
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
                                    {{ number_format($item['price'], 2) }}$</td>
                                <td>
                                    {{ $item['discount_percent'] }}%</td>
                                <td>
                                    {{ number_format($item['net_amount_line'], 2) }}$</td>
                            </tr>
                        @endforeach
                        <!-- TOTALS -->
                        <tr class="total_print">
                            <td colspan="7" style="text-align:end;">

                                Subtotal: {{ number_format(($this->totals['total_original'] * 100) / 100 , 2) }}$
                            </td>
                        </tr>
                        <tr class="total_print">
                            <td colspan="7" style="text-align:end;">
                                Discount: {{ number_format(($this->totals['total_discount'] * 100) / 100, 2) }}$

                            </td>
                        </tr>
                        <tr class="total_print">
                            <td colspan="7" style="text-align:end;">
                                Total Amount: {{ number_format(($this->totals['total_net'] * 100) / 100, 2) }}$

                            </td>
                        </tr>
                        @if ($currency != 'USD')
                            <tr class="total_print">
                                <td colspan="7" style="text-align:end;">
                                    Total Amount in {{ $currency }}:
                                    {{ number_format(floor($this->totals['total_net'] * $factor * 100) / 100, 0, '.', ' ') }}
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
