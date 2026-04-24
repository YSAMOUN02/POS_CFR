<?php

namespace App\Livewire;

use App\Models\Currency;
use App\Models\ItemLedgerEntry;
use App\Models\Product;
use App\Models\PurchaseHeader;
use App\Models\PurchaseLine;
use App\Models\UserWarehouse;
use App\Models\Vendor;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PurchaseCart extends Component
{
    public $cart = [];
    public $cart_queue_no = 0;
    public $new_cart = true;


    public $qty = 0;
    public $count_cart = 0;
    public $currency = 'USD';
    public $currency_name = 'US Dollar';
    public $factor = 1; // Conversion factor
    public $all_currency;

    public $vendor_name = 'General vendor';
    public $vendor_id = null;
    public $vendor_phone = '';
    public $vendor_address1 = '';
    public $vendor_address2 = '';
    public $vendor_contact_name = '';
    public $vendor_contact_phone = '';
    public $vendor_city = '';


    public $generatedLots = []; // Livewire property to track lots per product+warehouse
    public $remark = '';


    public $openIndex = null;

    public function toggleItem($index)
    {
        $this->openIndex = $this->openIndex === $index ? null : $index;
    }


    public function mount_wh()
    {
        $warehouse_user = UserWarehouse::with('warehouse')
            ->where('user_id', Auth::user()->id)
            ->get();


        // Get key => value array for <option>


        $this->warehouses = $warehouse_user->pluck('warehouse.name', 'warehouse.id')->toArray();


        // optional: set default
        $this->warehouse_id = key($this->warehouses);
    }
    // Add product to cart
    #[\Livewire\Attributes\On('add-product')]
    public function addProduct($productJson)
    {
        $product = json_decode($productJson, true);

        $cost  = $product['cost'] ?? 0;
        $unit  = $product['unit'] ?? 'NA';
        $stock = $product['stock'] ?? 0;

        static $lastAddedId = null;
        static $lastClickTime = 0;
        $now = microtime(true) * 1000;

        if ($lastAddedId === $product['id'] && $now - $lastClickTime < 300) return;

        $lastAddedId = $product['id'];
        $lastClickTime = $now;

        // ❌ REMOVE merge logic completely

        // ✅ Always add new row
        $this->cart[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'qty' => 1,
            'cost_price' => $cost,
            'amount_line' => $cost,
            'order_no' => count($this->cart) + 1,
            'unit' => $unit,
            'stock' => $stock,
            'lot' => $this->generateLotNumber(), // unique lot
            'expire' => now()->addYears(3)->format('Y-m-d'),  // default 3 years ahead
            'remark' => '',    // user sets from front-end
        ];

        $this->count_cart = count($this->cart);
    }
    public function recalcLine($index, $field, $inputValue = null)
    {
        $factor = (float) ($this->factor ?: 1);

        // precision for backend storage
        if ($factor >= 100) {
            $storeDecimal = 5; // large-rate currencies like KHR
        } elseif ($factor >= 1) {
            $storeDecimal = 3; // USD / EUR / normal currencies
        } else {
            $storeDecimal = 5; // very small rates, keep more precision
        }

        $qty = (float) ($this->cart[$index]['qty'] ?? 1);
        $cost = (float) ($this->cart[$index]['cost_price'] ?? 0);
        $amount = (float) ($this->cart[$index]['amount_line'] ?? 0);

        if ($field === 'cost_price') {
            $displayCost = (float) $inputValue;
            $cost = round($displayCost / $factor, $storeDecimal);

            $this->cart[$index]['cost_price'] = $cost;
            $this->cart[$index]['amount_line'] = round($qty * $cost, $storeDecimal);
        } elseif ($field === 'amount_line') {
            $displayAmount = (float) $inputValue;
            $amount = round($displayAmount / $factor, $storeDecimal);

            $this->cart[$index]['amount_line'] = $amount;
            $this->cart[$index]['cost_price'] = $qty > 0
                ? round($amount / $qty, $storeDecimal)
                : 0;
        } elseif ($field === 'qty') {
            $qty = max(1, (float) $qty);
            $this->cart[$index]['qty'] = $qty;
            $this->cart[$index]['amount_line'] = round($qty * $cost, $storeDecimal);
        }

        $this->cart = array_values($this->cart);
    }


    public $warehouses = [];
    public $warehouse_id; // selected warehouse

    public function post_grn()
    {
        if (empty($this->cart)) {
            $this->dispatch('error', ['message' => 'Cart is empty!']);
            return;
        }

        if (!$this->warehouse_id) {
            $this->dispatch('error', ['message' => 'Please select warehouse!']);
            return;
        }



        DB::beginTransaction();

        try {
            $documentNo = $this->generateGrnNo();

            // ✅ Create Header
            $header = PurchaseHeader::create([
                'no' => $documentNo,
                'vendor_id' => $this->vendor_id,
                'posting_date' => now(),
                'due_date' => null,
                'payment_method' => null,
                'remark' => $this->remark,
                'created_by' => Auth::user()->name ?? 'NA',
            ]);

            // ✅ Preload products (FAST)
            $productIds = collect($this->cart)->pluck('id');

            $products = Product::with('category')
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');
            $warehouse = Warehouse::find($this->warehouse_id);
            $vendor = $this->vendor_id ? Vendor::find($this->vendor_id) : null;
            foreach ($this->cart as $item) {

                if (($item['qty'] ?? 0) <= 0) {
                    continue;
                }

                $product = $products[$item['id']] ?? null;

                if (!$product) {
                    throw new \Exception("Product not found ID: " . $item['id']);
                }
                $unitCost   = (float) ($item['cost_price'] ?? 0);
                $qty        = (float) ($item['qty'] ?? 0);
                $lineAmount = $qty * $unitCost;

                // ✅ Create Purchase Line
                $purchaseLine = PurchaseLine::create([
                    'document_no'   => $documentNo,
                    'product_id'    => $product->id,
                    'barcode'       => $product->barcode,
                    'item_code'     => $product->code,
                    'name'          => $product->name,
                    'variant'       => $item['variant'] ?? null, // fixed from varaint
                    'lot'           => $item['lot'] ?? null,
                    'expire_date'   => $item['expire'] ?? null,
                    'description'   => $product->description,
                    'quantity'      => $qty,
                    'unit'          => $product->unit ?? ($item['unit'] ?? null),
                    'category_name' => optional($product->category)->name,
                    'unit_cost'     => $unitCost,
                    'line_amount'   => $lineAmount,
                    'remark'        => $item['remark'] ?? null,
                    'created_by'    => Auth::user()->name ?? 'NA',
                ]);

                // =========================
                // ✅ STOCK UPDATE START
                // =========================

                // ✅ Insert new stock
                DB::table('warehouse_product')->insert([
                    'product_id'   => $product->id,
                    'warehouse_id' => $this->warehouse_id,
                    'qty'          => $qty,
                    'track_lot'    => !empty($item['lot']) ? 1 : 0,
                    'lot'          => $item['lot'] ?? null,
                    'expire'       => $item['expire'] ?? null,
                    'control_exp'  => !empty($item['expire']) ? 1 : 0,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);


                // =========================
                // ✅ STOCK UPDATE END
                // =========================
                // ✅ ITEM LEDGER ENTRY FOR PURCHASE
                $ledger =  ItemLedgerEntry::create([
                    'entry_no'            => 'ILE-' . now()->format('YmdHis') . '-' . $product->id,
                    'posting_date'        => now()->toDateString(),

                    'document_type'       => 'Purchase',
                    'document_no'         => $documentNo,

                    'source_id'           => $purchaseLine->id,
                    'source_table'        => 'Purchase Lines',

                    'product_id'          => $product->id,
                    'barcode'             => $product->barcode,
                    'item_code'           => $product->code,
                    'name'                => $product->name,
                    'variant'             => $product->varian ?? '',
                    'description'         => $product->description,
                    'unit'                => $product->unit ?? ($item['unit'] ?? ''),
                    'category_name'       => optional($product->category)->name,
                    'type'                => $product->type ?? 'product',

                    'warehouse_id'        => $this->warehouse_id,
                    'warehouse_name'      => $warehouse->name ?? '',
                    'lot'                 => $item['lot'] ?? '',
                    'expire_date'         => $item['expire'] ?? null,

                    'quantity'            => $qty,
                    'remaining_quantity'  => $qty, // purchase stock comes in fully available
                    'entry_type'          => 'positive',

                    'unit_cost'           => $unitCost,
                    'sell_price' => $product->sell_price ?? 0,
                    'unit_price' => (($product->sell_price ?? 0) - ($item['discount_amount'] ?? 0)) + ($item['vat_amount'] ?? 0),
                    'discount_percent'    => 0,
                    'discount_amount'     => 0,
                    'vat'         => $product->vat ?? 0,
                    'vat_amount'          => 0,
                    'line_amount'         => ((-1) * $lineAmount),
                    'net_amount'         => ((-1) * $lineAmount),
                    'grand_total_amount'         => ((-1) * $lineAmount),

                    'vendor_id'           => $this->vendor_id,
                    'customer_id'         => null,
                    'customer_name'       =>  '',
                    'customer_phone'      => '',
                    'customer_address'    =>  '',

                    'payment_method'      => '',
                    'remark'              => $item['remark'] ?? $this->remark,
                    'created_by'          => Auth::user()->name ?? 'system',
                ]);
                $ledger->update([
                    'entry_no' => $ledger->id
                ]);
            }

            DB::commit();

            // ✅ Reset cart
            $this->cart = [];
            $this->count_cart = 0;

            $this->dispatch('success', ['message' => 'GRN Posted Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', ['message' => 'Error posting GRN: ' . $e->getMessage()]);
        }
    }
    private function generateGrnNo()
    {
        $year = now()->format('y'); // 24, 25, 26
        $prefix = 'GRN' . $year . '-';

        $lastGrn = PurchaseHeader::where('no', 'like', $prefix . '%')
            ->orderByDesc('no')
            ->lockForUpdate()
            ->first();

        if ($lastGrn) {
            $lastNumber = (int) substr($lastGrn->no, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function generateLotNumber()
    {
        $year = now()->format('y'); // 26

        $key = $year;

        if (isset($this->generatedLots[$key])) {
            $next = $this->generatedLots[$key] + 1;
        } else {

            $lastLot = DB::table('warehouse_product')
                ->where('lot', 'like', $year . 'P%')
                ->select('lot')
                ->orderByRaw("CAST(SUBSTRING(lot, 4) AS UNSIGNED) DESC")
                ->first();

            if ($lastLot) {
                $number = (int) substr($lastLot->lot, 3);
                $next = $number + 1;
            } else {
                $next = 1;
            }
        }

        $this->generatedLots[$key] = $next;

        return $year . 'P' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
    // Calculate total amount
    public function getTotalsProperty()
    {
        $total_amount = 0;
        foreach ($this->cart as $item) {
            $total_amount += $item['amount_line'] ?? 0;
        }

        return [
            'total_amount' => $total_amount,
        ];
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

        $this->count_cart = count($this->cart);
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
    #[\Livewire\Attributes\On('clearCart')]
    public function clearCart()
    {

        $this->cart = [];
        $this->qty = 0;
        $this->count_cart = 0;
        $this->generatedLots = [];
    }
    public function mount()
    {
        $this->all_currency = Currency::all();
        $this->mount_wh();
        $default = $this->all_currency->where('is_default', 1)->first();

        if ($default) {
            $this->currency      = $default->code;
            $this->currency_name = $default->code;
            $this->factor        = $default->factor;
        }
    }
    public function setCurrency($code)
    {
        $currency = Currency::where('code', $code)->first();
        if ($currency) {
            $this->currency = $currency->code;
            $this->currency_name = $currency->code;
            $this->factor = $currency->factor;

            $this->dispatch('change-currency', ['factor' => $this->factor,  'currency_name' => $this->currency_name]);
        }
    }


    public function render()
    {
        return view('livewire.purchase-cart');
    }
}
