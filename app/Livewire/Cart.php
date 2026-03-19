<?php

namespace App\Livewire;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\InvoiceHeader;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Serial_No;
use App\Models\TableProduct;
use App\Models\TableQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use Livewire\Component;

class Cart extends Component
{
    public $page = 'Sale Invoice';
    public $prefix = 'Sales';
    public $title = 'Invoice';



    public $cart = [];
    public $cart_queue_no = 0;
    public $new_cart = true;
    public $old_queue_no = 0;
    public $qty = 0;
    public $count_cart = 0;
    public $currency = 'USD';
    public $currency_name = 'US Dollar';
    public $factor = 1; // Conversion factor
    public $all_currency = [];

    public $invoiceNo = null;
    public $DNNo = null;

    public $customer_name = 'Walk-in Customer';
    public $customer_id = null;
    public $customer_phone = '';
    public $customer_address1 = '';
    public $customer_address2 = '';
    public $customer_contact_name = '';
    public $customer_contact_phone = '';
    public $customer_city = '';





    public $tables = [];
    public $Current_table_id = null;
    public $Current_table_name = "";





    #[\Livewire\Attributes\On('paymentConfirmed')]
    public function paymentConfirmed($payload)
    {
        if (empty($this->cart)) {
            $this->dispatch('payment-error', ['message' => 'Cart is empty']);
            return;
        }

        $totalAmount = 0;
        $totalDiscount = 0;
        $totalVAT = 0;

        $customer_id_no = Customer::where('customer_code', $this->customer_id)->value('id');
        DB::transaction(function () use ($payload, &$totalAmount, &$totalDiscount, &$totalVAT, $customer_id_no) {

            // 1️⃣ Create Invoice Header
            $invoice = InvoiceHeader::create([
                'invoice_number'  => $this->generateInvoiceNumber(),
                'invoice_date'    => $payload['document_date'] ?? now(),
                'due_date'        => $payload['due_date'] ?? null,
                'customer_id'     => $customer_id_no,

                'total_amount'    => 0, // will update after lines
                'discount_amount' => 0,
                'vat_amount'      => 0,
                'payment_method'          => $payload['paymentMethod'],
                'customer_type'          => $payload['customer_type'] ?? 'walk-in',
                'return_amount'   => $payload['returnedUSD'] ?? 0,
                'remarks'         => null,
            ]);
            $this->dispatch('get-reciept-no', ['invoice_number' => $invoice->invoice_number]);

            // 2️⃣ Create Invoice Lines
            foreach ($this->cart as $cartItem) {
                $product = Product::find($cartItem['id']);
                if (!$product) continue;


                $qty = $cartItem['qty'] ?? 1;

                // ✅ Use discount from cart (fallback to 0)
                $discountPercent = $cartItem['discount_percent'] ?? 0;

                // Base price (including VAT)
                $price = $product->sell_price + ($product->sell_price * $product->vat / 100);

                // Discount calculations
                $discountAmountPerUnit = ($price * $discountPercent / 100);
                $discountedPrice = $price - $discountAmountPerUnit;

                // Line calculations
                $lineAmount = $price * $qty;
                $discountAmountLine = $discountAmountPerUnit * $qty;
                $netAmount = $discountedPrice * $qty;
                $vatAmount = ($price - $product->sell_price) * $qty;

                $totalAmount += $netAmount;
                $totalDiscount += $discountAmountLine;
                $totalVAT += $vatAmount;


                InvoiceLine::create([
                    'sale_invoice_id'  => $invoice->id,
                    'product_id'       => $product->id,
                    'barcode'          => $product->bar_code,
                    'item_code'        => $product->code,
                    'name'             => $product->name,
                    'variant'          => $product->variant,
                    'description'      => $product->description,
                    'unit'             => $product->unit ?? 'NA',
                    'category_name'    => $product->category ? $product->category->name : null,
                    'cost'             => $product->cost,
                    'unit_price'       => $product->sell_price,
                    'sell_price'       => $discountedPrice,
                    'quantity'         => $qty,
                    'discount_percent' => $discountPercent, // ✅ from cart
                    'discount_amount'  => $discountAmountLine,
                    'line_amount'      => $lineAmount,
                    'vat'              => $product->vat,
                    'vat_amount'       => $vatAmount,
                    'total_amount'     => $netAmount,
                    'remarks'          => null,
                ]);

                if ($product->track_stock && $product->stock >= $qty) {
                    $product->decrement('stock', $qty);
                }
            }

            // 3️⃣ Update invoice totals
            $invoice->update([
                'total_amount'    => $totalAmount,
                'discount_amount' => $totalDiscount,
                'vat_amount'      => $totalVAT,
            ]);
            $this->cart_queue_no = 0;
            $this->getDocument($this->cart_queue_no); // send queue to front
        });



        $this->new_cart = true;
        $this->dispatch('payment-success', ['message' => 'Payment is successfully']);
    }

    #[\Livewire\Attributes\On('clearAll_after_payment')]
    public function clearAll_after_payment()
    {
        // 4️⃣ Clear current table products
        if ($this->Current_table_id) {
            TableProduct::where('table_id', $this->Current_table_id)->delete();
        }
        // Clear cart/session
        $this->clearCustomer();
        $this->clearCart();
        $this->Current_table_id = null;
        $this->Current_table_name = "";
    }


    protected function generateInvoiceNumber()
    {
        $year = date('y'); // e.g. 26

        // Get latest invoice for current year only
        $lastInvoice = InvoiceHeader::where('invoice_number', 'like', 'SIN' . $year . '-%')
            ->orderByDesc('invoice_number')
            ->first();

        if (!$lastInvoice) {
            return 'SIN' . $year . '-0001';
        }

        // Extract last number (e.g. 0165)
        $lastNumber = intval(substr($lastInvoice->invoice_number, -4));

        $nextNumber = $lastNumber + 1;

        return 'SIN' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    #[\Livewire\Attributes\On('transferCartToTable')]

    #[\Livewire\Attributes\On('save-cart-to-table')]
    public function saveCartToTable($payload)
    {

        $newTableId = $payload['table_id'] ?? null;
        $oldTableId = $payload['old_table_id'] ?? 0;
        if (!$newTableId) return;

        DB::beginTransaction();
        try {
            $customerId = null;

            if ($this->customer_id != null) {
                $customerId = is_numeric($this->customer_id)
                    ? $this->customer_id
                    : Customer::where('customer_code', $this->customer_id)->value('id');
            }




            // 1️⃣ Move from another table → keep old items or clear if needed
            $queueNo = $this->cart_queue_no ?? null;

            // 1️⃣ Handle table transfer
            if ($oldTableId && $oldTableId != $newTableId) {

                $oldRow = TableProduct::where('table_id', $oldTableId)->first();
                // Update New Table QUEUE NO only if not set
                $current_table = RestaurantTable::find($newTableId);
                if ($current_table->queue_no == 0) {
                    $current_table->queue_no = $this->cart_queue_no; // keep cart queue
                    $current_table->save();
                }
                // Reset old table queue
                $old_table = RestaurantTable::find($oldTableId);
                $old_table->queue_no = 0;
                $old_table->save();

                // Move old products to new table (if needed)
                if ($oldRow) {
                    TableProduct::where('table_id', $oldTableId)->update([
                        'table_id' => $newTableId,
                        'queue_no' => $this->cart_queue_no
                    ]);
                }
            } else {
                // Not transfer (new order or same table)
                $current_table = RestaurantTable::find($newTableId);

                // Assign queue only if not assigned
                if ($current_table->queue_no == 0) {
                    if ($this->cart_queue_no == 0) {
                        $this->cart_queue_no = $this->incrementQueueTable();
                        $this->getDocument($this->cart_queue_no); // send queue to front
                    }
                    $current_table->queue_no = $this->cart_queue_no;
                    $current_table->save();
                }
            }

            if ($this->invoiceNo == null || $this->DNNo == null) {

                $this->invoiceNo = $this->generateSerial('invoice');
                $this->DNNo = $this->generateSerial('delivery_note');
                $this->GetInvoiceNo($this->invoiceNo);
                $this->GetDeliveryNote($this->DNNo);
            }



            // 3️⃣ Process cart items
            foreach ($this->cart as $item) {
                $existing = TableProduct::where('table_id', $newTableId)
                    ->where('product_id', $item['id'])
                    ->first();

                if ($existing) {

                    $existing->qty = $item['qty'];
                    $existing->order_qty += 1;
                    $existing->customer_id = $customerId;

                    // ✅ IMPORTANT: update discount from cart
                    $existing->price = $item['price'];
                    $existing->discount_percent = $item['discount_percent'];
                    $existing->vat = $item['vat'] ?? 0;

                    // Recalculate amounts
                    $existing->gross_amount = $existing->qty * $existing->price;
                    $existing->discount_amount = ($existing->gross_amount * $existing->discount_percent) / 100;
                    $existing->net_amount = $existing->gross_amount - $existing->discount_amount;

                    $existing->save();
                } else {

                    // Add new item
                    TableProduct::create([
                        'table_id' => $newTableId,
                        'product_id' => $item['id'],
                        'customer_id' => $customerId,
                        'invoice_no' => $this->invoiceNo,         // ✅ add
                        'delivery_note' => $this->DNNo, // ✅ add
                        'queue_no' => $queueNo, // ✅ same queue for this table/order
                        'qty' => $item['qty'],
                        'order_qty' => 1,
                        'printed_qty' => 100,
                        'price' => $item['price'],
                        'discount_percent' => $item['discount_percent'],
                        'vat' => $item['vat'] ?? 0,
                        'gross_amount' => $item['amount_line'],
                        'discount_amount' => $item['discount_amount_line'],
                        'net_amount' => $item['net_amount_line'],
                    ]);
                }
            }

            DB::commit();

            $table = RestaurantTable::find($newTableId);

            $this->cart_queue_no = 0;
            // reset cart
            $this->cart = [];
            $this->count_cart = 0;

            $this->customer_id = null;
            $this->Current_table_id = null;


            $this->Current_table_name = "";
            $this->dispatch('serve-table', [
                'message' => ($oldTableId && $oldTableId != $newTableId) ? 'Table Transfer Success' : 'Place New Order success',
                'name' => $table?->name
            ]);
            $this->dispatch('clear-customer');
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error($e);
        }
    }

    #[\Livewire\Attributes\On('printReciept')]
    public function printReciept($invoice_no = null)
    {
        if (!$invoice_no) {
            $this->dispatch('payment-error', ['message' => 'Invoice number missing']);
            return;
        }

        // Reset cart first
        $this->cart = [];
        $this->count_cart = 0;

        // Get invoice with lines and product info
        $invoice = InvoiceHeader::with(['lines.item'])
            ->where('invoice_number', $invoice_no)
            ->first();

        if (!$invoice) {
            $this->dispatch('payment-error', ['message' => 'Invoice not found']);
            return;
        }
        $this->dispatch('get-date', [
            'invoice_date' => $invoice->invoice_date,
            'due_date' => $invoice->due_date,
            'invoice_no' => $invoice->invoice_number
        ]);


        $order = 1;

        foreach ($invoice->lines as $line) {
            // if (!$line->product) continue;

            $discountPrice = $line->total_amount / max($line->quantity, 1);

            $this->cart[] = [
                'id' => $line->product_id,
                'name' => $line->name,
                'price' => $line->unit_price,
                'qty' => $line->quantity,
                'discount_percent' => $line->discount_percent,
                'discount_price' => $discountPrice,
                'order_no' => $order++,
                'amount_line' => $line->line_amount,
                'discount_amount_line' => $line->discount_amount,
                'net_amount_line' => $line->total_amount,
                'stock' => $line->item->stock ?? 0,
                'unit' => $line->item->unit ?? 'NA',
                'track_stock' => $line->product->track_stock ?? false,
            ];
        }

        $this->count_cart = count($this->cart);

        // Optional: set invoice number in UI
        $this->GetInvoiceNo($invoice->invoice_number);



        $this->dispatch('trigger-print');
    }




    public function assignQueueForOrder($isUpdate = false)
    {
        // 1️⃣ New cart → always new queue
        if ($this->new_cart && $this->cart_queue_no == 0) {
            $this->cart_queue_no = $this->incrementQueueTable();
            $this->getDocument($this->cart_queue_no);
            $this->new_cart = false;
        }

        // 2️⃣ Updating existing order → assign new queue if desired
        else if ($isUpdate) {
            // optional: only assign new queue if old queue exists
            $this->cart_queue_no = $this->incrementQueueTable();
            $this->getDocument($this->cart_queue_no);
        }

        // 3️⃣ Otherwise → keep old queue
    }
    public function GetInvoiceNo($invoice)
    {

        $this->dispatch('get-invoice-no', [
            'document_no' => $invoice
        ]);
    }
    public function GetDeliveryNote($deliveryNote)
    {
        $this->dispatch('get-delivery-note', [
            'document_no' => $deliveryNote,
        ]);
    }
    public function GetQuotationNo($quotationNo)
    {
        $this->dispatch('get-quotation-no', [
            'document_no' => $quotationNo,
        ]);
    }
    public function generateSerial($type)
    {
        return DB::transaction(function () use ($type) {

            $yearShort = Carbon::now()->format('y');  // 26

            // Find existing serial config
            $serial = Serial_No::where('type', $type)->lockForUpdate()->first();

            if (!$serial) {
                // Create new if not exists
                $serial = Serial_No::create([
                    'prefix' => $type === 'invoice' ? 'INV' : 'DN',
                    'type' => $type,
                    'current_no' => 0,
                    'last_reset_date' => now()
                ]);
            }

            // 🔁 Reset yearly for delivery note
            if ($type === 'delivery_note') {
                if (
                    $serial->last_reset_date &&
                    Carbon::parse($serial->last_reset_date)->format('y') != $yearShort
                ) {
                    $serial->current_no = 0;
                }
            }

            // Increment number
            $serial->current_no += 1;
            $serial->last_reset_date = now();
            $serial->save();

            // Format number 0001
            $number = str_pad($serial->current_no, 4, '0', STR_PAD_LEFT);

            // Return formatted result
            if ($type === 'invoice') {
                return "INV{$yearShort}-{$number}";
            }

            if ($type === 'delivery_note') {
                return "DN{$yearShort}-{$number}";
            }

            return null;
        });
    }

    protected function incrementQueueTable()
    {
        $today = now()->toDateString();

        // Get or create today’s queue
        $queue = TableQueue::firstOrCreate(
            ['queue_date' => $today],
            ['last_number' => 0]
        );

        // Increment properly
        $queue->last_number += 1;
        $queue->save();

        return $queue->last_number;
    }


    #[\Livewire\Attributes\On('exit_table')]
    public function exit_table()
    {
        $this->cart = [];
        $this->count_cart = 0;
        $this->Current_table_id = null;
        $this->Current_table_name  = "";
        $this->clearCart();
        $this->clearCustomer();
    }
    #[\Livewire\Attributes\On('loadTableToCart')]
    public function loadTableToCart($table_id)
    {
        // update order
        $this->new_cart = false;
        // Reset cart first
        $this->cart = [];
        $this->count_cart = 0;


        $tableItems = TableProduct::with('product')
            ->where('table_id', $table_id)
            ->get();

        $tableItems_assign = RestaurantTable::where('id', $table_id)
            ->first();

        $this->GetQuotationNo($tableItems_assign->name);

        $order = 1;
        $queue_no = 0;
        $customer_id  = 0;
        foreach ($tableItems as $row) {
            if (!$row->product) continue;
            $customer_id = $row->customer_id;
            $this->cart_queue_no = $tableItems_assign->queue_no;
            $this->getDocument($this->cart_queue_no); // send queue to front
            $this->cart[] = [
                'id' => $row->product->id,
                'name' => $row->product->name,
                'price' => $row->price,
                'qty' => $row->qty,
                'discount_percent' => $row->discount_percent,
                'discount_price' => $row->net_amount / max($row->qty, 1),
                'order_no' => $order++,
                'amount_line' => $row->gross_amount,
                'discount_amount_line' => $row->discount_amount,
                'net_amount_line' => $row->net_amount,
                'stock' => $row->product->stock ?? 0,
                'unit' => $row->product->unit ?? 'NA',
                'track_stock' => $row->product->track_stock ?? false,
            ];
        }

        $this->GetInvoiceNo($row->invoice_no);
        $this->GetDeliveryNote($row->delivery_note);


        $this->Current_table_id = $table_id;
        $this->Current_table_name = $tableItems_assign->name . '  ' . 'Queue No :' . $this->cart_queue_no;


        if ($customer_id != 0) {
            // Insert Customer
            $customer = Customer::where('id', $customer_id)->first();
            $this->customer_name = $customer->name;
            $this->customer_id = $customer->customer_code;
            $this->customer_phone = $customer->phone;
            $this->customer_address1 = $customer->address1;
            $this->customer_address2 = $customer->address2;
            $this->customer_contact_name = $customer->contact_name;
            $this->customer_contact_phone = $customer->contact_phone;
            $this->customer_city = $customer->city;
            $this->customer_contact_name = $customer->contact_name;
            $this->customer_contact_phone = $customer->contact_phone;
            $display_name =  $customer->customer_code . ' - ' . $customer->name;
            $this->dispatch('update-customer-input', [
                'display' => $display_name,
                'code' =>  $customer->customer_code,

            ]);
            $this->getDocument($queue_no);
        } else {
            $this->clearCustomer();
            $this->getDocument($queue_no);
        }






        $this->count_cart = count($this->cart);
    }
    public function getDocument($queueNo)
    {
        $date = now()->format('Y-m-d'); // 2026-02-13

        $document = $date . '-' . str_pad($queueNo, 4, '0', STR_PAD_LEFT);

        $this->dispatch('get-document', [
            'document_no' => $this->cart_queue_no,
        ]);
    }
    public function clearCustomer()
    {
        $this->customer_name = "";
        $this->customer_id = null;
        $this->customer_phone =  "";
        $this->customer_address1 = "";
        $this->customer_address2 =  "";
        $this->customer_city =  "";

        $this->dispatch('update-customer-input', [
            'display' => "",
            'code' =>  ""
        ]);
    }
    #[\Livewire\Attributes\On('loadTableToCartPayment')]
    public function loadTableToCartPayment($table_id)
    {
        // update cart state
        $this->new_cart = false;
        // Reset cart first
        $this->cart = [];
        $this->count_cart = 0;

        $tableItems = TableProduct::with('product')
            ->where('table_id', $table_id)
            ->get();

        $getDocument = TableProduct::with('product')
            ->where('table_id', $table_id)
            ->first();

        $this->invoiceNo = $getDocument->invoice_no ?? 'NA';
        $this->DNNo = $getDocument->delivery_note ?? 'NA';
        $this->GetInvoiceNo($this->invoiceNo);
        $this->GetDeliveryNote($this->DNNo);


        $tableItems_assign = RestaurantTable::where('id', $table_id)
            ->first();


        $this->GetQuotationNo($tableItems_assign->name);
        $this->Current_table_id = $table_id;
        $this->Current_table_name = $tableItems_assign->name;

        $order = 1;
        $customer_id = 0;
        $queue_no = 0;
        $lastRow = $tableItems->last();

        foreach ($tableItems as $index => $row) {
            if (!$row->product) continue;

            $this->cart[] = [
                'id' => $row->product->id,
                'name' => $row->product->name,
                'price' => $row->price,
                'qty' => $row->qty,
                'discount_percent' => $row->discount_percent,
                'discount_price' => $row->net_amount / max($row->qty, 1),
                'order_no' => $index + 1,
                'amount_line' => $row->gross_amount,
                'discount_amount_line' => $row->discount_amount,
                'net_amount_line' => $row->net_amount,
                'stock' => $row->product->stock ?? 0,
                'unit' => $row->product->unit ?? 'NA',
                'track_stock' => $row->product->track_stock ?? false,
            ];
        }



        $this->getDocument($queue_no);
        $this->count_cart = count($this->cart);
        // ✅ Notify frontend

        if ($customer_id != 0) {
            // Insert Customer
            $customer = Customer::where('id', $customer_id)->first();
            $this->customer_name = $customer->name;
            $this->customer_id = $customer->customer_code;
            $this->customer_phone = $customer->phone;
            $this->customer_address1 = $customer->address1;
            $this->customer_address2 = $customer->address2;
            $this->customer_city = $customer->city;
            $this->customer_contact_name = $customer->contact_name;
            $this->customer_contact_phone = $customer->contact_phone;
            $display_name =  $customer->customer_code . ' - ' . $customer->name;
            $this->dispatch('update-customer-input', [
                'display' => $display_name,
                'code' =>  $customer->customer_code,

            ]);
            $this->getDocument($queue_no);
        } else {
            $this->clearCustomer();
            $this->getDocument($queue_no);
        }



        $this->dispatch('cart-loaded', [

            'table_id' => $table_id,
            'count' => $this->count_cart
        ]);
    }



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
            $this->customer_address1 = $customer->address1;
            $this->customer_address2 = $customer->address2;
            $this->customer_city    = $customer->city;
            $this->customer_contact_name = $customer->contact_name;
            $this->customer_contact_phone = $customer->contact_phone;
        } else {
            $this->customer_name    = 'Walk-in Customer';
            $this->customer_phone   = '';
            $this->customer_address1 = '';
            $this->customer_address2 = '';
            $this->customer_city    = '';
            $this->customer_contact_name = '';
            $this->customer_contact_phone  = '';
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

        // 1️⃣ Only assign queue if it's a new cart and no queue yet
        if ($this->new_cart && $this->cart_queue_no == 0) {
            $this->cart_queue_no = $this->incrementQueueTable();
            $this->getDocument($this->cart_queue_no); // send queue to front
            $this->new_cart = false; // lock queue for this cart
        }

        // Create New

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



    public function clearTable()
    {
        $this->customer_id = null;
        $this->Current_table_id = null;
    }
        #[\Livewire\Attributes\On('clearCart')]
    public function clearCart()
    {
        $this->Current_table_id = null;
        $this->Current_table_name = "";
        $this->new_cart = true;
        $this->cart = [];
        $this->qty = 0;
        $this->count_cart = 0;
        $this->invoiceNo = 'NA';
        $this->DNNo = 'NA';
        $this->GetInvoiceNo($this->invoiceNo);
        $this->GetDeliveryNote($this->DNNo);
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

        $this->count_cart = count($this->cart);
    }



    public function render()
    {
        return view('livewire.cart');
    }
}
