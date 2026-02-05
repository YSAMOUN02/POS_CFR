<?php

namespace App\Livewire;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use Livewire\Component;

class Cart extends Component
{
    public $page = 'Sale Invoice';
    public $prefix = 'Sales';
    public $title = 'Invoice';



    public $cart = [];
    public $qty = 0;
    public $count_cart = 0;
    public $currency = 'USD';
    public $currency_name = 'US Dollar';
    public $factor = 1; // Conversion factor
    public $all_currency = [];

    public $customer_name = 'Walk-in Customer';
    public $customer_id = null;
    public $customer_phone = '';
    public $customer_address = '';
    public $customer_city = '';

    public function updatedCustomerId($value)
    {
        $this->selectcustomer($value);
    }

    public function selectcustomer($customerId)
    {
        $customer = Customer::where('customer_code', $customerId)->first();

        if ($customer) {
            $this->customer_name    = $customer->name;
            $this->customer_phone   = $customer->phone;
            $this->customer_address = $customer->address;
            $this->customer_city    = $customer->city;
        } else {
            $this->customer_name    = 'Walk-in Customer';
            $this->customer_phone   = '';
            $this->customer_address = '';
            $this->customer_city    = '';
        }
    }

    protected $listeners = ['refreshCurrency'];


    public function refreshCurrency()
    {
        $currency = Currency::where('code', $this->currency)->first();

        if ($currency) {
            $this->currency = $currency->code;
            $this->factor   = $currency->factor;
            $this->currency_name = $currency->name;
        }
    }

    public function mount()
    {
        // ✅ Load currencies ONCE
        $this->all_currency = Currency::all();

        // ✅ Set default currency
        $default = $this->all_currency->where('is_default', 1)->first();

        if ($default) {
            $this->currency = $default->code;
            $this->currency_name = $default->name;
            $this->factor   = $default->factor;
        }
    }

#[\Livewire\Attributes\On('add-product')]
public function addProduct($productJson)
{
    $product = json_decode($productJson, true);

    $vat = $product['vat'] ?? 0;
    $price = $product['sell_price'] + ($product['sell_price'] * $vat / 100);
    $discountPercent = $product['discount_percent'] ?? 0;
    $discountAmount = ($price * $discountPercent) / 100;
    $discountPrice = $price - $discountAmount;
    $stock = $product['stock'] ?? 0;
    $unit = $product['unit'] ?? 'NA';
    $trackStock = $product['track_stock'] ?? 0;

    // Out-of-stock check only for tracked items
    if ($trackStock && $stock <= 0) {
        $this->dispatch('out-of-stock', name: $product['name']);
        return;
    }

    // Static variable to prevent accidental double increment for **all items**
    static $lastAddedId = null;
    static $lastClickTime = 0;

    $now = microtime(true) * 1000; // ms

    if ($lastAddedId === $product['id'] && $now - $lastClickTime < 300) {
        return; // block double increment for 300ms
    }

    $lastAddedId = $product['id'];
    $lastClickTime = $now;

    // Check if item exists in cart
    foreach ($this->cart as $index => $item) {
        if ($item['id'] === $product['id']) {
            if ($trackStock) {
                if ($item['qty'] < $stock) $this->cart[$index]['qty']++;
            } else {
                // untracked stock, increment only once per click
                $this->cart[$index]['qty']++;
            }

            $qty = $this->cart[$index]['qty'];
            $this->cart[$index]['discount_percent'] = $discountPercent;
            $this->cart[$index]['discount_price'] = $discountPrice;
            $this->cart[$index]['amount_line'] = $qty * $price;
            $this->cart[$index]['discount_amount_line'] = $qty * $discountAmount;
            $this->cart[$index]['net_amount_line'] = $qty * $discountPrice;
            return;
        }
    }

    // Add new item

    $this->cart[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $price,
        'qty' => 1,
        'discount_percent' => $discountPercent,
        'discount_price' => $discountPrice,
        'order_no' => count($this->cart) + 1,
        'amount_line' => $price,
        'discount_amount_line' => $discountAmount,
        'net_amount_line' => $discountPrice,
        'stock' => $stock,
        'unit' => $unit,
        'track_stock' => $trackStock,
    ];
    $this->count_cart = count($this->cart);

}



    public function clearCart()
    {
        $this->cart = [];
        $this->qty = 0;
        $this->cound_cart = 0;
    }
    // Cart.php (Livewire component)
    public function getTotalsProperty()
    {
        $totalOriginal = 0;
        $totalDiscount = 0;
        $totalNet = 0;

        foreach ($this->cart as $item) {
            $totalOriginal += $item['amount_line'];
            $totalDiscount += $item['discount_amount_line'];
            $totalNet += $item['net_amount_line'];
        }

        return [
            'total_original' => $totalOriginal,
            'total_discount' => $totalDiscount,
            'total_net' => $totalNet,
        ];
    }

    public function updatedCart($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];

        $item = $this->cart[$index];

        if ($field === 'qty') {
            // Only check stock if track_stock is true
            if ($item['qty'] < 1) {
                $this->cart[$index]['qty'] = 1;
            } elseif (($item['track_stock'] ?? 0) && $item['qty'] > $item['stock']) {
                $this->cart[$index]['qty'] = $item['stock'];
            }
        }

        if ($field === 'discount_percent') {
            if ($this->cart[$index]['discount_percent'] < 0) {
                $this->cart[$index]['discount_percent'] = 0;
            } elseif ($this->cart[$index]['discount_percent'] > 100) {
                $this->cart[$index]['discount_percent'] = 100;
            }
        }

        // Recalculate totals
        $price = $item['price'];
        $discountAmount = ($price * $this->cart[$index]['discount_percent']) / 100;
        $discountPrice = $price - $discountAmount;

        $this->cart[$index]['discount_price'] = $discountPrice;
        $this->cart[$index]['amount_line'] = $price * $this->cart[$index]['qty'];
        $this->cart[$index]['discount_amount_line'] = $discountAmount * $this->cart[$index]['qty'];
        $this->cart[$index]['net_amount_line'] = $discountPrice * $this->cart[$index]['qty'];
    }


    // Set currency and get factor
    public function setCurrency($code)
    {
        $currency = Currency::where('code', $code)->first();
        if ($currency) {
            $this->currency = $currency->code;
                     $this->currency_name = $currency->name;
            $this->factor = $currency->factor;
        }
    }
    public function pageSelected($page)
    {
        // map page → full title
        $map = [
            'quote'        => 'Sales Quote',
            'sale-invoice' => 'Sales Invoice',
            'sale-order'   => 'Sales Order',
        ];

        $full = $map[$page] ?? 'Sales Invoice';

        // split
        $parts = explode(' ', $full, 2);

        $this->prefix = $parts[0]; // Sales
        $this->title  = $parts[1]; // Quote / Invoice / Order
        $this->page   = $full;
    }

    public function removeItem($id)
    {
        foreach ($this->cart as $index => $item) {
            if ($item['id'] == $id) {
                unset($this->cart[$index]);
                break;
            }
        }

        // Reindex array (IMPORTANT for Livewire)
        $this->cart = array_values($this->cart);

        // Recalculate order_no
        foreach ($this->cart as $i => $item) {
            $this->cart[$i]['order_no'] = $i + 1;
        }

        $this->cound_cart = count($this->cart);
    }


    public function render()
    {
        return view('livewire.cart');
    }
}
