<?php

namespace App\Livewire;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InvoiceHeader;
use App\Models\InvoiceLine;
use App\Models\ItemLedgerEntry;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\SaleOrderHeader;
use App\Models\SaleOrderLine;
use App\Models\Serial_No;
use App\Models\TableProduct;
use App\Models\TableQueue;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\PendingDispatch;
use Livewire\Component;

class Cart extends Component
{





    public $cart = [];
    public $cart_queue_no = 0;
    public $new_cart = true;
    public $old_queue_no = 0;
    public $qty = 0;
    public $count_cart = 0;
    public $currency = 'USD';
    public $currency_name = 'US Dollar';
    public $factor = 1; // Conversion factor
    public $all_currency;


    public $customer_name = 'Walk-in Customer';
    public $customer_id = null;
    public $customer_phone = '';
    public $customer_address1 = '';
    public $customer_address2 = '';
    public $customer_contact_name = '';
    public $customer_contact_phone = '';
    public $customer_city = '';
    public $customer_discount_percent = 0;

    public $vat_status = 0;
    public $cart_mode = 'normal';
    public $openIndex = null;

    // Sale Order Info 
    public $document_no = 'NA';
    public $document_id = 0;
    public $document_type = 'NA';

    public function toggleItem($index)
    {
        $this->openIndex = $this->openIndex === $index ? null : $index;
    }

    #[\Livewire\Attributes\On('set-item-lots')]
    public function setItemLots($index = null, $lots = [])
    {
        $this->cart[$index]['lots'] = $lots;
    }
    public function viewLots($index)
    {
        $lotsInCart = $this->cart[$index]['lots'] ?? [];
        $productId = $this->cart[$index]['id'] ?? null; // ✅ get product id

        // Get all warehouse products for these lot IDs
        $lotIds = collect($lotsInCart)->pluck('id')->toArray();
        $warehouseProducts = WarehouseProduct::with('warehouse')->whereIn('id', $lotIds)->get()->keyBy('id');

        // Merge qty from cart with lot details
        $lotsToShow = collect($lotsInCart)->map(function ($lot) use ($warehouseProducts) {
            $wp = $warehouseProducts[$lot['id']] ?? null;

            return [
                'id' => $lot['id'],
                'qty' => $lot['qty'],
                'lot' => $wp->lot ?? 'NO LOT',
                'expire' => $wp->expire
                    ? \Carbon\Carbon::parse($wp->expire)->format('d-M-Y')
                    : '-',
                'stock' => $wp->qty ?? 0,

                // ✅ ADD THIS
                'warehouse' => $wp?->warehouse?->name ?? 'NA',
            ];
        })->toArray();

        // Dispatch to JS modal, include product_id
        $this->dispatch('view-cart-lots', [
            'lots' => $lotsToShow,
            'product_name' => $this->cart[$index]['name'],
            'product_id' => $productId, // ✅ added
        ]);
    }

    private function generateExpenseCode()
    {
        $year = now()->format('y'); // 26

        $lastExpense = Expense::where('expense_code', 'like', "EXP{$year}-%")
            ->latest('id')
            ->first();

        if ($lastExpense) {
            $lastNumber = (int) substr($lastExpense->expense_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'EXP' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    #[\Livewire\Attributes\On('payment-expenses')]
    public function paymentExpenses($payload = [])
    {
        if (empty($this->cart)) {
            $this->dispatch('payment-error', ['message' => 'Cart is empty']);
            return;
        }

        DB::transaction(function () use ($payload) {
            $expenseCode = $this->generateExpenseCode();

            foreach ($this->cart as $cartItem) {
                $qty = (float) ($cartItem['qty'] ?? 1);
                $unitPrice = (float) ($cartItem['price'] ?? 0);
                $amount = $qty * $unitPrice;

                Expense::create([
                    'expense_date'   => $payload['document_date'] ?? now()->toDateString(),
                    'product_id'     => $cartItem['id'] ?? null,
                    'expense_code'   => $expenseCode,
                    'expense_name'   => $cartItem['name'] ?? 'General Expense',
                    'qty'            => $qty,
                    'unit_price'     => $unitPrice,
                    'amount'         => $amount,
                    'payment_method' => $payload['paymentMethod'] ?? null,
                    'note'           => $cartItem['expence_for'] ?? null,
                    'status'         => 1,
                ]);
            }

            $this->cart_queue_no = 0;
            $this->getDocument($this->cart_queue_no);
        });

        $this->new_cart = true;
        $this->cart = [];
        $this->count_cart = 0;

        $this->dispatch('payment-success', [
            'message' => 'Expense saved successfully'
        ]);
    }
    #[\Livewire\Attributes\On('paymentConfirmed')]
    public function paymentConfirmed($payload)
    {

        if (empty($this->cart)) {
            $this->dispatch('payment-error', ['message' => 'Cart is empty']);
            return;
        }


        $invoiceNumber = '';
        $totalAmount = 0;
        $totalDiscount = 0;
        $totalVAT = 0;




        DB::transaction(function () use ($payload, &$totalAmount, &$totalDiscount, &$totalVAT) {


            $customer_id = !empty($payload['customer_id']) ? (int) $payload['customer_id'] : null;
            $customer_name = $customer->name ?? ($payload['customer_name'] ?? 'Walk-in Customer');
            $customer_phone = $customer->phone ?? ($payload['customer_phone'] ?? 'NA');
            $customer_address = $customer->address ?? ($payload['customer_address'] ?? null);

            // 1️⃣ Create Invoice Header
            $invoice = InvoiceHeader::create([
                'invoice_number'   => $this->generateInvoiceNumber(),
                'invoice_date'     => $payload['document_date'] ?? now(),
                'customer_id'      =>  $customer_id,

                'contact_name'     => $customer_name,
                'phone'            => $customer_phone,
                'address'          => $customer_address,

                'total_amount'     => 0,
                'vat_amount'       => 0,
                'discount_percent' => $payload['discount_percent'] ?? 0,
                'discount_amount'  => $payload['discount_amount'] ?? 0,
                'grand_total'      => 0,

                'payment_method'   => $payload['paymentMethod'] ?? null,
                'customer_type'    => $payload['customer_type'] ?? 'walk-in',

                'currency_name'    => $this->currency_name,
                'factor'           => $this->factor,
                'created_by'       => Auth::user()->name ?? 'System',
                'remarks'          => $payload['remark'] ?? null,
            ]);
            // Assign **immediately** to external variable
            $invoiceNumber = $invoice->invoice_number;
            $this->dispatch('get-reciept-no', ['invoice_number' => $invoice->invoice_number]);

            // 2️⃣ Create Invoice Lines
            foreach ($this->cart as $cartItem) {
                $product = Product::find($cartItem['id']);
                if (!$product) {
                    continue;
                }

                $qty = (float) ($cartItem['qty'] ?? 1);
                $sellPrice = (float) ($cartItem['price'] ?? 0);   // actual selling price from cart
                $vatRate = (float) ($cartItem['vat'] ?? 0);
                $discountPercent = (float) ($cartItem['discount_percent'] ?? 0);
                $unitCost = (float) ($product->cost ?? 0);
                $unitPrice = (float) ($product->sell_price ?? 0); // product base/default price

                $lineAmount = round($sellPrice * $qty, 4); // gross before discount
                $discountAmount = round(($lineAmount * $discountPercent) / 100, 4);
                $netAmount = round($lineAmount - $discountAmount, 4); // after discount, before VAT
                $vatAmount = round(($netAmount * $vatRate) / 100, 4);
                $grandTotal = round($netAmount + $vatAmount, 4);

                // accumulate invoice totals
                $totalAmount += $lineAmount;
                $totalDiscount += $discountAmount;
                $totalVAT += $vatAmount;

                // =========================
                // 1) CREATE INVOICE LINE
                // =========================
                InvoiceLine::create([
                    'sale_invoice_id'    => $invoice->id,
                    'product_id'         => $product->id,

                    'barcode'            => $product->bar_code,
                    'item_code'          => $product->code,
                    'name'               => $product->name,
                    'variant'            => $product->variant,
                    'description'        => $product->description,

                    'quantity'           => $qty,
                    'unit'               => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                    'category_name'      => optional($product->category)->name,

                    'cost'               => $unitCost,
                    'unit_price'         => $unitPrice,
                    'sell_price'         => $sellPrice,

                    'discount_percent'   => $discountPercent,
                    'discount_amount'    => $discountAmount,

                    'line_amount'        => $lineAmount,
                    'vat'        => $vatRate,   // ✅ correct for InvoiceLine model
                    'vat_amount'         => $vatAmount,
                    'net_amount'         => $netAmount,
                    'grand_total_amount' => $grandTotal,

                    'created_by'         => Auth::user()->name ?? 'System',
                    'remarks'            => null,
                ]);

                // =========================
                // 2) ITEM LEDGER FOR STOCK ITEMS
                // =========================
                if ($product->track_stock == 1) {

                    $cartLots = $cartItem['lots'] ?? [];
                    $qtyNeeded = $qty;
                    $saleQty = $qty;

                    $ledgerRows = [];

                    // CASE 1: User manually selected lots
                    if (!empty($cartLots)) {
                        foreach ($cartLots as $lot) {
                            $warehouseProduct = WarehouseProduct::lockForUpdate()->find($lot['id']);

                            if (!$warehouseProduct) {
                                $this->dispatch('error', ['message' => "Lot ID {$lot['id']} not found"]);
                                throw new \Exception("Lot ID {$lot['id']} not found");
                            }

                            $lotQty = (float) ($lot['qty'] ?? 0);

                            if ($lotQty <= 0) {
                                continue;
                            }

                            if ((float) $warehouseProduct->qty < $lotQty) {
                                $this->dispatch('error', [
                                    'message' => "Not enough stock in Lot " . $warehouseProduct->lot
                                ]);
                                throw new \Exception("Not enough stock in Lot {$warehouseProduct->lot}");
                            }

                            $warehouseProduct->decrement('qty', $lotQty);

                            $ledgerRows[] = [
                                'warehouse_product_id' => $warehouseProduct->id,
                                'warehouse_id'         => $warehouseProduct->warehouse_id,
                                'lot'                  => $warehouseProduct->lot,
                                'expire_date'          => $warehouseProduct->expire,
                                'qty'                  => $lotQty,
                            ];

                            $this->syncPurchaseRemainingQty(
                                $product->id,
                                $warehouseProduct->lot,
                                $warehouseProduct->warehouse_id
                            );
                        }

                        $selectedQty = collect($ledgerRows)->sum('qty');
                        if (round($selectedQty, 4) != round($saleQty, 4)) {


                            $this->dispatch('no-stock', [
                                'message' => "{$product->name}  មិនមានស្តុកទេ"
                            ]);

                            DB::rollBack();
                            return;
                        }
                    } else {
                        // CASE 2: AUTO PICK FEFO
                        $warehouseProducts = WarehouseProduct::where('product_id', $product->id)
                            ->where('qty', '>', 0)
                            ->orderByRaw('CASE WHEN expire IS NULL THEN 1 ELSE 0 END')
                            ->orderBy('expire', 'asc')
                            ->lockForUpdate()
                            ->get();

                        $remaining = $qtyNeeded;

                        foreach ($warehouseProducts as $wp) {
                            if ($remaining <= 0) {
                                break;
                            }

                            $deductQty = min((float) $wp->qty, $remaining);

                            if ($deductQty <= 0) {
                                continue;
                            }

                            $wp->decrement('qty', $deductQty);

                            $ledgerRows[] = [
                                'warehouse_product_id' => $wp->id,
                                'warehouse_id'         => $wp->warehouse_id,
                                'lot'                  => $wp->lot,
                                'expire_date'          => $wp->expire,
                                'qty'                  => $deductQty,
                            ];

                            $remaining -= $deductQty;

                            $this->syncPurchaseRemainingQty(
                                $product->id,
                                $wp->lot,
                                $wp->warehouse_id
                            );
                        }

                        if ($remaining > 0) {
                            $this->dispatch('no-stock', [
                                'message' => "{$product->name} មិនមានស្តុកទេ"
                            ]);

                            DB::rollBack();
                            return;
                        }
                    }

                    // =========================
                    // 3) CREATE ITEM LEDGER ENTRIES
                    // =========================
                    if (!empty($ledgerRows)) {
                        foreach ($ledgerRows as $row) {
                            $rowQty = (float) $row['qty'];
                            $rowUnitCost = $this->getLotUnitCost(
                                $product->id,
                                $row['lot'] ?? null,
                                $row['warehouse_id'] ?? null
                            );
                            $warehouseName = Warehouse::where('id', $row['warehouse_id'])->value('name');

                            // distribute amounts proportionally by deducted lot qty
                            $rowLineAmount = $saleQty > 0 ? round(($lineAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowDiscountAmount = $saleQty > 0 ? round(($discountAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowNetAmount = $saleQty > 0 ? round(($netAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowVatAmount = $saleQty > 0 ? round(($vatAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowGrandTotal = $saleQty > 0 ? round(($grandTotal / $saleQty) * $rowQty, 4) : 0;

                            $ledger = $this->createLedgerEntry([
                                'posting_date'        => $payload['document_date'] ?? now()->toDateString(),
                                'document_type'       => 'Sales Order',
                                'document_no'         => $invoice->invoice_number,

                                'source_id'           => $invoice->id,
                                'source_table'        => 'sale_invoice_headers',

                                'product_id'          => $product->id,
                                'barcode'             => $product->bar_code,
                                'item_code'           => $product->code,
                                'name'                => $product->name,
                                'variant'             => $product->variant,
                                'description'         => $product->description,
                                'unit'                => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                                'category_name'       => optional($product->category)->name,
                                'type'                =>  'product',

                                'warehouse_id'        => $row['warehouse_id'] ?? null,
                                'warehouse_name'      => $warehouseName ?? null,
                                'lot'                 => $row['lot'] ?? null,
                                'expire_date'         => $row['expire_date'] ?? null,

                                'quantity'            => -1 * $rowQty,
                                'remaining_quantity'  => 0,
                                'entry_type'          => 'negative',

                                'unit_cost'           => $rowUnitCost,
                                'unit_price'          => $unitPrice,
                                'sell_price'          => $sellPrice,

                                'discount_percent'    => $discountPercent,
                                'discount_amount'     => $rowDiscountAmount,

                                'vat'                 => $vatRate,
                                'vat_amount'          => $rowVatAmount,

                                'line_amount'         => $rowLineAmount,
                                'net_amount'          => $rowNetAmount,
                                'grand_total_amount'  => $rowGrandTotal,

                                'customer_id'         =>  $customer_id,
                                'customer_name'       => $customer_name ?? null,
                                'customer_phone'      => $customer_phone ?? null,
                                'customer_address'    => $customer_address ?? null,

                                'payment_method'      => $payload['paymentMethod'] ?? null,
                                'remark'              => '',
                                'created_by'          => Auth::user()->name ?? 'System',
                            ]);
                            $ledger->update([
                                'entry_no' => $ledger->id
                            ]);
                        }
                    }
                } else {

                    // Non-stock / service item still creates ledger entry without warehouse/lot
                    $ledger = ItemLedgerEntry::create([
                        'entry_no'            => null,

                        'posting_date'        => $payload['document_date'] ?? now()->toDateString(),
                        'document_type'       => 'Sales Order',
                        'document_no'         => $invoice->invoice_number,

                        'source_id'           => $invoice->id,
                        'source_table'        => 'sale_invoice_headers',

                        'product_id'          => $product->id,
                        'barcode'             => $product->bar_code,
                        'item_code'           => $product->code,
                        'name'                => $product->name,
                        'variant'             => $product->variant,
                        'description'         => $product->description,
                        'unit'                => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                        'category_name'       => optional($product->category)->name,
                        'type'                => $product->type ?? 'product',

                        'warehouse_id'        => null,
                        'warehouse_name'      => null,
                        'lot'                 => null,
                        'expire_date'         => null,

                        'quantity'            => -1 * $qty,
                        'remaining_quantity'  => 0,
                        'entry_type'          => 'negative',

                        'unit_cost'           => $unitCost,
                        'unit_price'          => $unitPrice,
                        'sell_price'          => $sellPrice,

                        'discount_percent'    => $discountPercent,
                        'discount_amount'     => $discountAmount,

                        'vat'                 => $vatRate,
                        'vat_amount'          => $vatAmount,

                        'line_amount'         => $lineAmount,
                        'net_amount'          => $netAmount,
                        'grand_total_amount'  => $grandTotal,

                        'customer_id'         =>  $customer_id,
                        'customer_name'       => $customer_name ?? null,
                        'customer_phone'      => $customer_phone ?? null,
                        'customer_address'    => $customer_address ?? null,

                        'vendor_id'           => null,
                        'payment_method'      => $payload['paymentMethod'] ?? null,
                        'remark'              => null,
                        'created_by'          => Auth::user()->name ?? 'System',
                    ]);
                    $ledger->update([
                        'entry_no' => $ledger->id
                    ]);
                }
            }
            // 3️⃣ Update invoice totals
            $invoice->update([
                'total_amount'     => $totalAmount,
                'discount_amount'  => $totalDiscount,
                'vat_amount'       => $totalVAT,
                'grand_total'      => $totalAmount - $totalDiscount + $totalVAT,
            ]);
            $this->new_cart = true;
            $this->dispatch('payment-success', [
                'message' => 'Invoice Payment ' . $invoiceNumber . ' is successfully'
            ]);
            $this->cart_queue_no = 0;
            $this->getDocument($this->cart_queue_no); // send queue to front
        });
    }
    private function createLedgerEntry(array $data)
    {
        $ledger = ItemLedgerEntry::create($data);

        $ledger->update([
            'entry_no' => 'ILE-' . str_pad($ledger->id, 8, '0', STR_PAD_LEFT)
        ]);

        return $ledger;
    }
    public function syncPurchaseRemainingQty($productId, $lot = null, $warehouseId = null)
    {
        $lot = !is_null($lot) && trim($lot) !== '' ? trim($lot) : null;

        $remainingQty = WarehouseProduct::where('product_id', $productId)
            ->when(!is_null($lot), fn($q) => $q->where('lot', $lot), fn($q) => $q->whereNull('lot'))
            ->when(!is_null($warehouseId), fn($q) => $q->where('warehouse_id', $warehouseId))
            ->sum('qty');

        $lastPurchaseEntry = ItemLedgerEntry::where('product_id', $productId)
            ->where('entry_type', 'positive')
            ->where('document_type', 'Purchase')
            ->when(!is_null($lot), fn($q) => $q->where('lot', $lot), fn($q) => $q->whereNull('lot'))
            ->when(!is_null($warehouseId), fn($q) => $q->where('warehouse_id', $warehouseId))
            ->latest('id')
            ->first();

        if ($lastPurchaseEntry) {
            $lastPurchaseEntry->update([
                'remaining_quantity' => $remainingQty,
            ]);
        }

        return $remainingQty;
    }
    private function getLotUnitCost($productId, $lot = null, $warehouseId = null)
    {
        $lot = !is_null($lot) && trim($lot) !== '' ? trim($lot) : null;

        $purchaseEntry = ItemLedgerEntry::where('product_id', $productId)
            ->where('document_type', 'Purchase')
            ->where('entry_type', 'positive')
            ->when(!is_null($lot), fn($q) => $q->where('lot', $lot), fn($q) => $q->whereNull('lot'))
            ->when(!is_null($warehouseId), fn($q) => $q->where('warehouse_id', $warehouseId))
            ->latest('id')
            ->first();

        return (float) ($purchaseEntry->unit_cost ?? 0);
    }
    #[\Livewire\Attributes\On('clearAll_after_payment')]
    public function clearAll_after_payment()
    {
        // 4️⃣ Clear current table products

        // Clear cart/session
        $this->clearCustomer();
        $this->clearCart_no_message();
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
                'type' => 'product',
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

        $this->clearCart();
        $this->clearCustomer();
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
            $this->currency_name = $default->code;
            $this->factor   = $default->factor;
        }
    }


    #[\Livewire\Attributes\On('add-product')]
    public function addProduct($productJson)
    {
        if ($this->new_cart && $this->cart_queue_no == 0) {
            $this->cart_queue_no = $this->incrementQueueTable();
            $this->getDocument($this->cart_queue_no);
            $this->new_cart = false;
        }

        $product = json_decode($productJson, true);

        if (empty($this->cart)) {
            $this->cart_mode = ($product['type'] === 'expence') ? 'expence' : 'sale';
        }

        if ($this->cart_mode === 'expence' && $product['type'] !== 'expence') {
            $this->dispatch('product_item_prevented', [
                'message' => 'ចំណាយ មិនអាចបន្ថែមបានទេ'
            ]);
            return;
        }

        if ($this->cart_mode === 'sale' && $product['type'] === 'expence') {
            $this->dispatch('expense_item_prevented', [
                'message' => 'ទំនិញលក់ មិនអាចបន្ថែមចំណាយបានទេ'
            ]);
            return;
        }

        $vat = (float) ($product['vat'] ?? 0);
        $price = (float) ($product['sell_price'] ?? 0);
        $type = strtolower($product['type'] ?? '');

        if ($this->customer_discount_percent > 0 && $type === 'product') {
            $discountPercent = (float) $this->customer_discount_percent;
        } else {
            $discountPercent = (float) ($product['discount_percent'] ?? 0);
        }
        $stock = (float) ($product['stock'] ?? 0);
        $unit = $product['unit'] ?? 'NA';
        $trackStock = (int) ($product['track_stock'] ?? 0);

        // Per unit calculation
        $discountAmount = ($price * $discountPercent) / 100;
        $netPrice = $price - $discountAmount;          // before VAT
        $vatAmount = ($netPrice * $vat) / 100;         // VAT on net price
        $grandPrice = $netPrice + $vatAmount;          // optional, if needed

        // if ($trackStock && $stock <= 0) {
        //     $this->dispatch('out-of-stock', name: $product['name']);
        //     return;
        // }

        static $lastAddedId = null;
        static $lastClickTime = 0;

        $now = microtime(true) * 1000;

        if ($lastAddedId === $product['id'] && $now - $lastClickTime < 300) {
            return;
        }

        $lastAddedId = $product['id'];
        $lastClickTime = $now;

        foreach ($this->cart as $index => $item) {
            if ($item['id'] === $product['id']) {
                // if ($trackStock) {
                //     if ($item['qty'] < $stock) {
                //         $this->cart[$index]['qty']++;
                //     }
                // } else {
                $this->cart[$index]['qty']++;
                // }

                $qty = $this->cart[$index]['qty'];

                $this->cart[$index]['discount_percent'] = $discountPercent;
                $this->cart[$index]['discount_price'] = $netPrice;
                $this->cart[$index]['amount_line'] = $qty * $price;
                $this->cart[$index]['discount_amount_line'] = $qty * $discountAmount;
                $this->cart[$index]['net_amount_line'] = $qty * $netPrice;      // before VAT
                $this->cart[$index]['vat_amount_line'] = $qty * $vatAmount;     // each line VAT
                $this->cart[$index]['vat'] = $vat;
                // optional:
                // $this->cart[$index]['grand_amount_line'] = ($qty * $netPrice) + ($qty * $vatAmount);

                $this->count_cart = count($this->cart);
                return;
            }
        }

        $this->cart[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'type' => $product['type'],
            'price' => $price,
            'qty' => 1,
            'discount_percent' => $discountPercent,
            'discount_price' => $netPrice,
            'order_no' => count($this->cart) + 1,
            'amount_line' => $price,
            'discount_amount_line' => $discountAmount,
            'net_amount_line' => $netPrice,         // before VAT
            'vat_amount_line' => $vatAmount,        // VAT for this line
            'vat' => $vat,
            'stock' => $stock,
            'unit' => $unit,
            'track_stock' => $trackStock,
            // optional:
            // 'grand_amount_line' => $grandPrice,
        ];

        $this->count_cart = count($this->cart);
    }

    public function clearTable()
    {
        $this->customer_id = null;
        $this->customer_discount_percent = 0;
    }
    #[\Livewire\Attributes\On('clearCart')]
    public function clearCart()
    {

        $this->new_cart = true;
        $this->cart = [];
        $this->qty = 0;
        $this->count_cart = 0;
        $this->cart_mode = 'normal';
        $this->customer_discount_percent = 0;
        $this->document_no = 'NA';
        $this->document_id = 0;
        $this->document_type = 'NA';
        $this->dispatch('cart-cleared', [
            'message' => 'Cart has been cleared'
        ]);
    }
    public function clearCart_no_message()
    {
        $this->document_no = 'NA';
        $this->new_cart = true;
        $this->cart = [];
        $this->qty = 0;
        $this->count_cart = 0;
        $this->cart_mode = 'normal';
        $this->customer_discount_percent = 0;
        $this->document_id = 0;
        $this->document_type = 'NA';
    }
    public function getTotalsProperty()
    {
        $totalOriginal = 0;
        $totalDiscount = 0;
        $totalNet = 0;          // before VAT
        $totalVatStatus = 0;    // biggest VAT for display only
        $totalVatAmount = 0;    // sum of each line VAT

        foreach ($this->cart as $item) {
            $lineOriginal  = (float) ($item['amount_line'] ?? 0);
            $lineDiscount  = (float) ($item['discount_amount_line'] ?? 0);
            $lineNet       = (float) ($item['net_amount_line'] ?? 0);
            $lineVat       = (float) ($item['vat'] ?? 0);
            $lineVatAmount = (float) ($item['vat_amount_line'] ?? 0);

            $totalOriginal += $lineOriginal;
            $totalDiscount += $lineDiscount;
            $totalNet += $lineNet;

            // real VAT logic = sum each line VAT
            $totalVatAmount += $lineVatAmount;

            // display only = biggest VAT
            if ($lineVat > $totalVatStatus) {
                $totalVatStatus = $lineVat;
            }
        }
        $gran_total = $totalNet + $totalVatAmount;
        if ($this->cart_mode === 'expence') {
            $gran_total = $totalNet; // no VAT for expence
        }
        return [
            'total_original'   => $totalOriginal,
            'total_discount'   => $totalDiscount,
            'total_net'        => $totalNet,                  // before VAT
            'vat_status'       => $totalVatStatus,
            'total_vat_amount' => $totalVatAmount,            // VAT money
            'grand_total'      => $gran_total // final total
        ];
    }

    #[\Livewire\Attributes\On('applyCustomerDiscountEvent')]
    public function applyCustomerDiscountEvent($discount = 0)
    {
        $discount = (float) $discount;

        $this->customer_discount_percent = $discount;
        $this->applyCustomerDiscountToCart($discount);
    }
    public function applyCustomerDiscountToCart($discountPercent = null)
    {
        $customerDiscountPercent = (float) ($discountPercent ?? $this->customer_discount_percent ?? 0);

        foreach ($this->cart as $index => $item) {
            $price = (float) ($item['price'] ?? 0);
            $qty   = (float) ($item['qty'] ?? 0);
            $vat   = (float) ($item['vat'] ?? 0);
            $type  = $item['type'] ?? 'sale';

            $originalDiscountPercent = (float) ($item['original_discount_percent'] ?? 0);

            if ($type === 'expence' || $type === 'service') {
                $appliedDiscountPercent = 0;
                $lineOriginal = $price * $qty;
                $lineDiscountAmount = 0;
                $lineNet = $lineOriginal;
                $lineVatAmount = 0;
                $vat = 0;
            } else {
                // if customer has discount, use it
                // otherwise restore original product discount only
                $appliedDiscountPercent = $customerDiscountPercent > 0
                    ? $customerDiscountPercent
                    : $originalDiscountPercent;

                $lineOriginal = $price * $qty;
                $lineDiscountAmount = ($lineOriginal * $appliedDiscountPercent) / 100;
                $lineNet = $lineOriginal - $lineDiscountAmount;
                $lineVatAmount = ($lineNet * $vat) / 100;
            }

            $this->cart[$index]['discount_percent'] = $appliedDiscountPercent;
            $this->cart[$index]['amount_line'] = $lineOriginal;
            $this->cart[$index]['discount_amount_line'] = $lineDiscountAmount;
            $this->cart[$index]['net_amount_line'] = $lineNet;
            $this->cart[$index]['vat_amount_line'] = $lineVatAmount;
            $this->cart[$index]['vat'] = $vat;
        }
    }
    public function recalcLine($index, $field, $inputValue = null)
    {
        $qty = floatval($this->cart[$index]['qty'] ?? 1);
        $discount = floatval($this->cart[$index]['discount_percent'] ?? 0);
        $vat = floatval($this->cart[$index]['vat'] ?? 0);

        // Price
        if ($field === 'price' && $inputValue !== null) {
            $price = $this->factor != 1 ? floatval($inputValue) / $this->factor : floatval($inputValue);
            $this->cart[$index]['price'] = $price;
        }

        $price = floatval($this->cart[$index]['price'] ?? 0);

        // Qty changed
        if ($field === 'qty') {
            $qty = max(1, $qty);
            $this->cart[$index]['qty'] = $qty;
        }

        // Discount percent changed
        if ($field === 'discount_percent') {
            $discount = min(max(0, $discount), 100);
            $this->cart[$index]['discount_percent'] = $discount;
        }

        // Recalculate amounts
        $discountAmount = ($price * $discount) / 100;
        $discountPrice = $price - $discountAmount;
        $vatAmount = ($discountPrice * $vat) / 100;

        $this->cart[$index]['discount_price'] = $discountPrice;
        $this->cart[$index]['amount_line'] = $price * $qty;
        $this->cart[$index]['discount_amount_line'] = $discountAmount * $qty;
        $this->cart[$index]['net_amount_line'] = $discountPrice * $qty;
        $this->cart[$index]['vat_amount_line'] = $vatAmount * $qty;

        // optional if you want line total including VAT
        // $this->cart[$index]['grand_amount_line'] = ($discountPrice + $vatAmount) * $qty;

        $this->cart = array_values($this->cart);
    }
    // Set currency and get factor
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




    #[\Livewire\Attributes\On('confirmSaleOrder')]
    public function confirmSaleOrder($payload)
    {
        if (empty($this->cart)) {
            $this->dispatch('payment-error', ['message' => 'Cart is empty']);
            return;
        }

        $saleOrderNo = '';

        try {
            DB::transaction(function () use ($payload, &$saleOrderNo) {

                $totalAmount = 0;
                $totalDiscount = 0;
                $totalVAT = 0;

                $customer_id = !empty($payload['customer_id']) ? (int) $payload['customer_id'] : null;
                $customer_name = $payload['customer_name'] ?? 'Walk-in Customer';
                $customer_phone = $payload['customer_phone'] ?? 'NA';
                $customer_address = $payload['customer_address'] ?? null;

                $saleOrderNo = $this->generateSaleOrderNo();

                $requestedStatus = $payload['status'] ?? 'Ordered';

                $saleOrder = SaleOrderHeader::create([
                    'document_no'      => $saleOrderNo,

                    'customer_id'      => $customer_id,
                    'contact_name'     => $customer_name,
                    'phone'            => $customer_phone,
                    'address'          => $customer_address,

                    'posting_date'     => $payload['document_date'] ?? now()->toDateString(),
                    'order_date'       => $payload['order_date'] ?? null,
                    'delivery_date'    => $payload['delivery_date'] ?? null,

                    'delivery_status'  => $payload['delivery_status'] ?? 'N/A',
                    'delivery_info'    => $payload['delivery_info'] ?? null,
                    'driver_name'      => $payload['driver_name'] ?? null,
                    'driver_phone'     => $payload['driver_phone'] ?? null,

                    'total_amount'     => 0,
                    'vat_amount'       => 0,
                    'discount_percent' => $payload['discount_percent'] ?? 0,
                    'discount_amount'  => 0,
                    'grand_total'      => 0,

                    'deposit_amount'   => 0,
                    'paid_amount'      => 0,
                    'balance_amount'   => 0,

                    'status'           => $requestedStatus,
                    'payment_status'   => 'Unpaid',

                    'customer_type'    => $payload['customer_type'] ?? null,
                    'payment_method'   => $payload['paymentMethod'] ?? null,
                    'currency_name'    => $this->currency_name ?? 'USD',
                    'factor'           => $this->factor ?? 1,

                    'remarks'          => $payload['remark'] ?? null,
                    'created_by'       => Auth::user()->name ?? 'System',
                ]);

                foreach ($this->cart as $cartItem) {
                    $product = Product::find($cartItem['id']);

                    if (!$product) {
                        continue;
                    }

                    $qty = (float) ($cartItem['qty'] ?? 1);
                    $sellPrice = (float) ($cartItem['price'] ?? $cartItem['sell_price'] ?? 0);
                    $vatRate = (float) ($cartItem['vat'] ?? 0);
                    $discountPercent = (float) ($cartItem['discount_percent'] ?? 0);

                    $unitCost = (float) ($product->cost ?? 0);
                    $unitPrice = (float) ($product->sell_price ?? 0);

                    $lineAmount = round($sellPrice * $qty, 4);
                    $discountAmount = round(($lineAmount * $discountPercent) / 100, 4);
                    $netAmount = round($lineAmount - $discountAmount, 4);
                    $vatAmount = round(($netAmount * $vatRate) / 100, 4);
                    $lineGrandTotal = round($netAmount + $vatAmount, 4);

                    $totalAmount += $lineAmount;
                    $totalDiscount += $discountAmount;
                    $totalVAT += $vatAmount;

                    SaleOrderLine::create([
                        'sale_order_id'      => $saleOrder->id,
                        'product_id'         => $product->id,

                        'barcode'            => $product->bar_code,
                        'item_code'          => $product->code,
                        'name'               => $product->name,
                        'variant'            => $product->variant,
                        'description'        => $product->description,

                        'quantity'           => $qty,
                        'unit'               => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                        'category_name'      => optional($product->category)->name,

                        'cost'               => $unitCost,
                        'unit_price'         => $unitPrice,
                        'sell_price'         => $sellPrice,

                        'discount_percent'   => $discountPercent,
                        'discount_amount'    => $discountAmount,

                        'line_amount'        => $lineAmount,
                        'vat'                => $vatRate,
                        'vat_amount'         => $vatAmount,
                        'net_amount'         => $netAmount,
                        'grand_total_amount' => $lineGrandTotal,

                        'created_by'         => Auth::user()->name ?? 'System',
                    ]);
                }

                $grandTotal = round($totalAmount - $totalDiscount + $totalVAT, 4);
                $deposit = (float) ($payload['deposit_amount'] ?? 0);

                if ($requestedStatus === 'Quotation') {
                    $deposit = 0;
                    $paymentStatus = 'N/A';
                    $finalStatus = 'Quotation';
                    $delivery = 'N/A';
                } elseif ($requestedStatus === 'Draft') {
                    $deposit = 0;
                    $paymentStatus = 'N/A';
                    $finalStatus = 'Draft';
                    $delivery = 'N/A';
                } else {
                    if ($deposit <= 0) {
                        $paymentStatus = 'Unpaid';
                        $finalStatus = 'Ordered';
                    } elseif ($deposit >= $grandTotal) {
                        $deposit = $grandTotal;
                        $paymentStatus = 'Paid';
                        $finalStatus = 'Completed';
                    } else {
                        $paymentStatus = 'Partial';
                        $finalStatus = 'Deposit';
                    }

                    $delivery = $payload['delivery_status'] ?? 'N/A';
                    if($delivery == ''){
                        $delivery = 'NA';
                    }
                }

                $saleOrder->update([
                    'total_amount'     => $totalAmount,
                    'vat_amount'       => $totalVAT,
                    'discount_amount'  => $totalDiscount,
                    'grand_total'      => $grandTotal,

                    'deposit_amount'   => $deposit,
                    'paid_amount'      => $deposit,
                    'balance_amount'   => max($grandTotal - $deposit, 0),

                    'payment_status'   => $paymentStatus,
                    'status'           => $finalStatus,
                    'delivery_status'  => $delivery,
                ]);

                if ($paymentStatus !== 'Paid') {
                    $this->cart_queue_no = 0;
                    $this->getDocument($this->cart_queue_no);
                    return;
                }

                $invoice = InvoiceHeader::create([
                    'sale_order_id'    => $saleOrder->id,
                    'invoice_number'   => $this->generateInvoiceNumber(),
                    'document_no'      => $saleOrder->document_no,
                    'invoice_date'     => $payload['document_date'] ?? now()->toDateString(),

                    'customer_id'      => $customer_id,
                    'contact_name'     => $customer_name,
                    'phone'            => $customer_phone,
                    'address'          => $customer_address,

                    'total_amount'     => 0,
                    'vat_amount'       => 0,
                    'discount_percent' => $payload['discount_percent'] ?? 0,
                    'discount_amount'  => 0,
                    'grand_total'      => 0,

                    'payment_method'   => $payload['paymentMethod'] ?? null,
                    'customer_type'    => $payload['customer_type'] ?? null,
                    'currency_name'    => $this->currency_name ?? 'USD',
                    'factor'           => $this->factor ?? 1,

                    'created_by'       => Auth::user()->name ?? 'System',
                    'remarks'          => $payload['remark'] ?? null,
                ]);

                $invoiceTotal = 0;
                $invoiceDiscount = 0;
                $invoiceVAT = 0;

                foreach ($this->cart as $cartItem) {
                    $product = Product::find($cartItem['id']);

                    if (!$product) {
                        continue;
                    }

                    $qty = (float) ($cartItem['qty'] ?? 1);
                    $sellPrice = (float) ($cartItem['price'] ?? $cartItem['sell_price'] ?? 0);
                    $vatRate = (float) ($cartItem['vat'] ?? 0);
                    $discountPercent = (float) ($cartItem['discount_percent'] ?? 0);

                    $unitCost = (float) ($product->cost ?? 0);
                    $unitPrice = (float) ($product->sell_price ?? 0);

                    $lineAmount = round($sellPrice * $qty, 4);
                    $discountAmount = round(($lineAmount * $discountPercent) / 100, 4);
                    $netAmount = round($lineAmount - $discountAmount, 4);
                    $vatAmount = round(($netAmount * $vatRate) / 100, 4);
                    $grandTotalLine = round($netAmount + $vatAmount, 4);

                    $invoiceTotal += $lineAmount;
                    $invoiceDiscount += $discountAmount;
                    $invoiceVAT += $vatAmount;

                    InvoiceLine::create([
                        'sale_invoice_id'    => $invoice->id,
                        'product_id'         => $product->id,

                        'barcode'            => $product->bar_code,
                        'item_code'          => $product->code,
                        'name'               => $product->name,
                        'variant'            => $product->variant,
                        'description'        => $product->description,

                        'quantity'           => $qty,
                        'unit'               => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                        'category_name'      => optional($product->category)->name,

                        'cost'               => $unitCost,
                        'unit_price'         => $unitPrice,
                        'sell_price'         => $sellPrice,

                        'discount_percent'   => $discountPercent,
                        'discount_amount'    => $discountAmount,

                        'line_amount'        => $lineAmount,
                        'vat'                => $vatRate,
                        'vat_amount'         => $vatAmount,
                        'net_amount'         => $netAmount,
                        'grand_total_amount' => $grandTotalLine,

                        'created_by'         => Auth::user()->name ?? 'System',
                    ]);

                    if ((int) ($product->track_stock ?? 0) === 1) {
                        $ledgerRows = [];
                        $cartLots = $cartItem['lots'] ?? [];
                        $saleQty = $qty;

                        if (!empty($cartLots)) {
                            foreach ($cartLots as $lot) {
                                $warehouseProduct = WarehouseProduct::lockForUpdate()->find($lot['id']);

                                if (!$warehouseProduct) {
                                    throw new \Exception("Lot ID {$lot['id']} not found");
                                }

                                $lotQty = (float) ($lot['qty'] ?? 0);

                                if ($lotQty <= 0) {
                                    continue;
                                }

                                if ((float) $warehouseProduct->qty < $lotQty) {
                                    throw new \Exception("Not enough stock in Lot {$warehouseProduct->lot}");
                                }

                                $warehouseProduct->decrement('qty', $lotQty);

                                $ledgerRows[] = [
                                    'warehouse_id' => $warehouseProduct->warehouse_id,
                                    'lot' => $warehouseProduct->lot,
                                    'expire_date' => $warehouseProduct->expire,
                                    'qty' => $lotQty,
                                ];

                                $this->syncPurchaseRemainingQty(
                                    $product->id,
                                    $warehouseProduct->lot,
                                    $warehouseProduct->warehouse_id
                                );
                            }

                            if (round(collect($ledgerRows)->sum('qty'), 4) != round($saleQty, 4)) {
                                throw new \Exception("Selected lot quantity not equal sale quantity for {$product->name}");
                            }
                        } else {
                            $warehouseProducts = WarehouseProduct::where('product_id', $product->id)
                                ->where('qty', '>', 0)
                                ->orderByRaw('CASE WHEN expire IS NULL THEN 1 ELSE 0 END')
                                ->orderBy('expire', 'asc')
                                ->lockForUpdate()
                                ->get();

                            $remaining = $qty;

                            foreach ($warehouseProducts as $wp) {
                                if ($remaining <= 0) {
                                    break;
                                }

                                $deductQty = min((float) $wp->qty, $remaining);

                                if ($deductQty <= 0) {
                                    continue;
                                }

                                $wp->decrement('qty', $deductQty);

                                $ledgerRows[] = [
                                    'warehouse_id' => $wp->warehouse_id,
                                    'lot' => $wp->lot,
                                    'expire_date' => $wp->expire,
                                    'qty' => $deductQty,
                                ];

                                $remaining -= $deductQty;

                                $this->syncPurchaseRemainingQty(
                                    $product->id,
                                    $wp->lot,
                                    $wp->warehouse_id
                                );
                            }

                            if ($remaining > 0) {
                                throw new \Exception("Not enough stock for {$product->name}");
                            }
                        }

                        foreach ($ledgerRows as $row) {
                            $rowQty = (float) $row['qty'];

                            $rowUnitCost = $this->getLotUnitCost(
                                $product->id,
                                $row['lot'] ?? null,
                                $row['warehouse_id'] ?? null
                            );

                            $warehouseName = Warehouse::where('id', $row['warehouse_id'])->value('name');

                            $rowLineAmount = $saleQty > 0 ? round(($lineAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowDiscountAmount = $saleQty > 0 ? round(($discountAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowNetAmount = $saleQty > 0 ? round(($netAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowVatAmount = $saleQty > 0 ? round(($vatAmount / $saleQty) * $rowQty, 4) : 0;
                            $rowGrandTotal = $saleQty > 0 ? round(($grandTotalLine / $saleQty) * $rowQty, 4) : 0;

                            $ledger = $this->createLedgerEntry([
                                'posting_date'        => $payload['document_date'] ?? now()->toDateString(),
                                'document_type'       => 'Sales Invoice',
                                'document_no'         => $invoice->invoice_number,

                                'source_id'           => $invoice->id,
                                'source_table'        => 'sale_invoice_headers',

                                'product_id'          => $product->id,
                                'barcode'             => $product->bar_code,
                                'item_code'           => $product->code,
                                'name'                => $product->name,
                                'variant'             => $product->variant,
                                'description'         => $product->description,
                                'unit'                => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                                'category_name'       => optional($product->category)->name,
                                'type'                => 'product',

                                'warehouse_id'        => $row['warehouse_id'] ?? null,
                                'warehouse_name'      => $warehouseName,
                                'lot'                 => $row['lot'] ?? null,
                                'expire_date'         => $row['expire_date'] ?? null,

                                'quantity'            => -1 * $rowQty,
                                'remaining_quantity'  => 0,
                                'entry_type'          => 'negative',

                                'unit_cost'           => $rowUnitCost,
                                'unit_price'          => $unitPrice,
                                'sell_price'          => $sellPrice,

                                'discount_percent'    => $discountPercent,
                                'discount_amount'     => $rowDiscountAmount,

                                'vat'                 => $vatRate,
                                'vat_amount'          => $rowVatAmount,

                                'line_amount'         => $rowLineAmount,
                                'net_amount'          => $rowNetAmount,
                                'grand_total_amount'  => $rowGrandTotal,

                                'customer_id'         => $customer_id,
                                'customer_name'       => $customer_name,
                                'customer_phone'      => $customer_phone,
                                'customer_address'    => $customer_address,

                                'payment_method'      => $payload['paymentMethod'] ?? null,
                                'remark'              => '',
                                'created_by'          => Auth::user()->name ?? 'System',
                            ]);

                            $ledger->update([
                                'entry_no' => $ledger->id,
                            ]);
                        }
                    } else {
                        $ledger = ItemLedgerEntry::create([
                            'entry_no'            => null,

                            'posting_date'        => $payload['document_date'] ?? now()->toDateString(),
                            'document_type'       => 'Sales Invoice',
                            'document_no'         => $invoice->invoice_number,

                            'source_id'           => $invoice->id,
                            'source_table'        => 'sale_invoice_headers',

                            'product_id'          => $product->id,
                            'barcode'             => $product->bar_code,
                            'item_code'           => $product->code,
                            'name'                => $product->name,
                            'variant'             => $product->variant,
                            'description'         => $product->description,
                            'unit'                => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                            'category_name'       => optional($product->category)->name,
                            'type'                => $product->type ?? 'product',

                            'warehouse_id'        => null,
                            'warehouse_name'      => null,
                            'lot'                 => null,
                            'expire_date'         => null,

                            'quantity'            => -1 * $qty,
                            'remaining_quantity'  => 0,
                            'entry_type'          => 'negative',

                            'unit_cost'           => $unitCost,
                            'unit_price'          => $unitPrice,
                            'sell_price'          => $sellPrice,

                            'discount_percent'    => $discountPercent,
                            'discount_amount'     => $discountAmount,

                            'vat'                 => $vatRate,
                            'vat_amount'          => $vatAmount,

                            'line_amount'         => $lineAmount,
                            'net_amount'          => $netAmount,
                            'grand_total_amount'  => $grandTotalLine,

                            'customer_id'         => $customer_id,
                            'customer_name'       => $customer_name,
                            'customer_phone'      => $customer_phone,
                            'customer_address'    => $customer_address,

                            'vendor_id'           => null,
                            'payment_method'      => $payload['paymentMethod'] ?? null,
                            'remark'              => null,
                            'created_by'          => Auth::user()->name ?? 'System',
                        ]);

                        $ledger->update([
                            'entry_no' => $ledger->id,
                        ]);
                    }
                }

                $invoice->update([
                    'total_amount'    => $invoiceTotal,
                    'discount_amount' => $invoiceDiscount,
                    'vat_amount'      => $invoiceVAT,
                    'grand_total'     => $invoiceTotal - $invoiceDiscount + $invoiceVAT,
                ]);

                $this->cart_queue_no = 0;
                $this->getDocument($this->cart_queue_no);
            });

            $this->new_cart = true;
            $this->cart = [];
            $this->count_cart = 0;

            $this->dispatch('ordered', [
                'message' => 'ការកម្មង់ បានដាក់រួច : ' . $saleOrderNo
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('payment-error', [
                'message' => $e->getMessage(),
            ]);
        }
    }
    private function generateSaleOrderNo()
    {
        $year = now()->format('y'); // 26, 27
        $prefix = 'SO' . $year . '-';

        $lastOrder = SaleOrderHeader::where('document_no', 'like', $prefix . '%')
            ->orderBy('document_no', 'desc')
            ->first();

        if (!$lastOrder) {
            $nextNo = 1;
        } else {
            $lastNumber = (int) substr($lastOrder->document_no, strlen($prefix));
            $nextNo = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNo, 4, '0', STR_PAD_LEFT);
    }



    public $loaded_sale_order_id = null;

    #[\Livewire\Attributes\On('load-sale-order-to-cart')]
    public function loadSaleOrderToCart($saleOrderId)
    {
        $saleOrder = SaleOrderHeader::with('lines')->find($saleOrderId);

        if (!$saleOrder) {
            $this->dispatch('error', [
                'message' => 'Sale order not found'
            ]);
            return;
        }
        $this->document_id = $saleOrder->id;
        $this->document_type = $saleOrder->status;
        $this->document_no =   $saleOrder->document_no;
        $this->customer_id =    $saleOrder->phone;
        $this->customer_name = $saleOrder->contact_name;
        $this->customer_phone =  $saleOrder->address;

        //  New cart document if needed
        if ($this->new_cart && $this->cart_queue_no == 0) {
            $this->cart_queue_no = $this->incrementQueueTable();
            $this->getDocument($this->cart_queue_no);
            $this->new_cart = false;
        }

        // Clear old cart
        $this->cart = [];
        $this->cart_mode = 'sale';
        $this->loaded_sale_order_id = $saleOrder->id;

        // Customer info
        $this->customer_id = $saleOrder->customer_id;
        $this->customer_name = $saleOrder->contact_name ?? 'Walk-in Customer';
        $this->customer_phone = $saleOrder->phone ?? '';
        $this->customer_address1 = $saleOrder->address ?? '';
        $this->customer_address2 = '';
        $this->customer_contact_name = $saleOrder->contact_name ?? '';
        $this->customer_contact_phone = $saleOrder->phone ?? '';

        // Currency
        // $this->currency_name = $saleOrder->currency_name ?? 'US Dollar';
        // $this->factor = $saleOrder->factor ?? 1;

        foreach ($saleOrder->lines as $line) {
            $qty = (float) ($line->quantity ?? 1);
            $price = (float) ($line->sell_price ?? 0);
            $discountPercent = (float) ($line->discount_percent ?? 0);
            $vat = (float) ($line->vat ?? 0);

            $discountAmount = ($price * $discountPercent) / 100;
            $netPrice = $price - $discountAmount;
            $vatAmount = ($netPrice * $vat) / 100;

            $this->cart[] = [
                'id' => $line->product_id,
                'name' => $line->name,
                'type' => $line->type ?? 'product',
                'price' => $price,
                'qty' => $qty,

                'discount_percent' => $discountPercent,
                'discount_price' => $netPrice,

                'order_no' => count($this->cart) + 1,

                'amount_line' => $qty * $price,
                'discount_amount_line' => $qty * $discountAmount,
                'net_amount_line' => $qty * $netPrice,
                'vat_amount_line' => $qty * $vatAmount,

                'vat' => $vat,

                'stock' => $line->stock ?? 0,
                'unit' => $line->unit ?? 'NA',
                'track_stock' => $line->track_stock ?? 0,
            ];
        }

        $this->count_cart = count($this->cart);

        $this->dispatch('load-sale-order', [
            'message' => 'Sale order loaded to cart successfully',
            'header' =>  $saleOrder
        ]);
    }
    #[On('resetCustomer')]
    public function resetCustomer()
    {
        $this->customer_name = 'Walk-in Customer';
        $this->customer_id = null;
        $this->customer_phone = '';
        $this->customer_address1 = '';
        $this->customer_address2 = '';
        $this->customer_contact_name = '';
        $this->customer_contact_phone = '';
        $this->customer_city = '';
        $this->customer_discount_percent = 0;
    }

    #[\Livewire\Attributes\On('confirmUpdateSaleOrder')]
    public function confirmUpdateSaleOrder($payload)
    {
        DB::transaction(function () use ($payload) {

            $clean = function ($value) {
                return $value === '' ? null : $value;
            };

            $saleOrderId = $clean($payload['sale_order_id'] ?? null);
            $saleOrder = SaleOrderHeader::lockForUpdate()->findOrFail($saleOrderId);

            if (empty($this->cart)) {
                $this->dispatch('error', [
                    'message' => 'Cart is empty. Cannot update sale order.',
                ]);
                return;
            }

            $documentDate = $clean($payload['document_date'] ?? null) ?? now()->toDateString();
            $orderDate = $clean($payload['order_date'] ?? null);
            $deliveryDate = $clean($payload['delivery_date'] ?? null);

            SaleOrderLine::where('sale_order_id', $saleOrder->id)->delete();

            foreach ($this->cart as $item) {
                $qty = (float) ($item['qty'] ?? $item['quantity'] ?? 1);
                $price = (float) ($item['sell_price'] ?? $item['price'] ?? $item['unit_price'] ?? 0);
                $discountPercent = (float) ($item['discount_percent'] ?? 0);

                $lineAmount = round($qty * $price, 4);
                $discountAmount = (float) ($item['discount_amount'] ?? 0);

                if ($discountAmount <= 0 && $discountPercent > 0) {
                    $discountAmount = round($lineAmount * ($discountPercent / 100), 4);
                }

                $netAmount = max($lineAmount - $discountAmount, 0);
                $vat = (float) ($item['vat'] ?? 0);
                $vatAmount = round($netAmount * ($vat / 100), 4);
                $grandTotalAmount = round($netAmount + $vatAmount, 4);

                SaleOrderLine::create([
                    'sale_order_id'      => $saleOrder->id,
                    'product_id'         => $item['product_id'] ?? $item['id'] ?? null,

                    'order_no'           => $saleOrder->document_no,
                    'document_no'        => $saleOrder->document_no,

                    'barcode'            => $item['barcode'] ?? null,
                    'item_code'          => $item['item_code'] ?? $item['code'] ?? null,
                    'name'               => $item['name'] ?? '',
                    'variant'            => $item['variant'] ?? null,
                    'description'        => $item['description'] ?? null,

                    'quantity'           => $qty,
                    'unit'               => $item['unit'] ?? null,
                    'category_name'      => $item['category_name'] ?? null,

                    'cost'               => $item['cost'] ?? $item['cost_price'] ?? 0,
                    'unit_price'         => $price,
                    'sell_price'         => $price,

                    'discount_percent'   => $discountPercent,
                    'discount_amount'    => $discountAmount,

                    'line_amount'        => $lineAmount,
                    'vat'                => $vat,
                    'vat_amount'         => $vatAmount,
                    'net_amount'         => $netAmount,
                    'grand_total_amount' => $grandTotalAmount,
                ]);
            }

            $totalAmount = (float) SaleOrderLine::where('sale_order_id', $saleOrder->id)
                ->sum('grand_total_amount');

            $oldPaidAmount = (float) ($saleOrder->paid_amount ?? 0);
            $newPaidAmount = (float) ($payload['new_paid_amount'] ?? 0);

            if ($newPaidAmount < 0) {
                $newPaidAmount = 0;
            }

            $paidAmount = $oldPaidAmount + $newPaidAmount;

            if ($paidAmount > $totalAmount) {
                $paidAmount = $totalAmount;
            }

            $balanceAmount = max($totalAmount - $paidAmount, 0);

            if ($paidAmount <= 0) {
                $paymentStatus = 'Unpaid';
            } elseif ($balanceAmount > 0) {
                $paymentStatus = 'Partial';
            } else {
                $paymentStatus = 'Paid';
            }

            $status = match ($paymentStatus) {
                'Paid' => 'Completed',
                'Partial' => 'Deposit',
                default => 'Ordered',
            };

            $deliveryStatus = $clean($payload['delivery_status'] ?? null) ?? 'N/A';

            $saleOrder->update([
                'customer_id'     => $clean($payload['customer_id'] ?? null),
                'contact_name'    => $clean($payload['customer_name'] ?? null),
                'phone'           => $clean($payload['customer_phone'] ?? null),
                'address'         => $clean($payload['customer_address'] ?? null),

                'posting_date'    => $documentDate,
                'order_date'      => $orderDate,
                'delivery_date'   => $deliveryDate,

                'total_amount'    => $totalAmount,
                'deposit_amount'  => $newPaidAmount,
                'paid_amount'     => $paidAmount,
                'balance_amount'  => $balanceAmount,

                'payment_method'  => $clean($payload['paymentMethod'] ?? null),
                'payment_status'  => $paymentStatus,
                'status'          => $status,

                'customer_type'   => $clean($payload['customer_type'] ?? null),
                'delivery_status' => $deliveryStatus,
                'delivery_info'   => $clean($payload['delivery_info'] ?? null),
                'driver_name'     => $clean($payload['driver_name'] ?? null),
                'driver_phone'    => $clean($payload['driver_phone'] ?? null),

                'remarks'         => $clean($payload['remark'] ?? null),
            ]);

            if ($paymentStatus !== 'Paid') {
                return;
            }

            if (!empty($saleOrder->invoice_id ?? null)) {
                return;
            }

            $invoice = InvoiceHeader::create([
                'sale_order_id'      => $saleOrder->id,
                'invoice_number'     => $this->generateInvoiceNumber(),
                'document_no'        => $saleOrder->document_no,
                'invoice_date'       => $documentDate,

                'customer_id'        => $saleOrder->customer_id,
                'contact_name'       => $saleOrder->contact_name,
                'phone'              => $saleOrder->phone,
                'address'            => $saleOrder->address,

                'total_amount'       => 0,
                'vat_amount'         => 0,
                'discount_percent'   => 0,
                'discount_amount'    => 0,
                'grand_total'        => 0,

                'customer_type'      => $saleOrder->customer_type,
                'payment_method'     => $saleOrder->payment_method,

                'currency_name'      => $this->currency_name ?? null,
                'factor'             => $this->factor ?? 1,

                'remarks'            => $saleOrder->remarks,
                'created_by'         => Auth::user()->name ?? 'System',
            ]);

            $invoiceTotalAmount = 0;
            $invoiceDiscountAmount = 0;
            $invoiceVatAmount = 0;

            foreach ($this->cart as $cartItem) {
                $productId = $cartItem['product_id'] ?? $cartItem['id'] ?? null;
                $product = Product::find($productId);

                if (!$product) {
                    continue;
                }

                $qty = (float) ($cartItem['qty'] ?? $cartItem['quantity'] ?? 1);
                $sellPrice = (float) ($cartItem['sell_price'] ?? $cartItem['price'] ?? $cartItem['unit_price'] ?? 0);
                $vatRate = (float) ($cartItem['vat'] ?? 0);
                $discountPercent = (float) ($cartItem['discount_percent'] ?? 0);

                $unitCost = (float) ($product->cost ?? $cartItem['cost'] ?? $cartItem['cost_price'] ?? 0);
                $unitPrice = (float) ($product->sell_price ?? $sellPrice);

                $lineAmount = round($sellPrice * $qty, 4);
                $discountAmount = round(($lineAmount * $discountPercent) / 100, 4);
                $netAmount = round($lineAmount - $discountAmount, 4);
                $vatAmount = round(($netAmount * $vatRate) / 100, 4);
                $grandTotal = round($netAmount + $vatAmount, 4);

                $invoiceTotalAmount += $lineAmount;
                $invoiceDiscountAmount += $discountAmount;
                $invoiceVatAmount += $vatAmount;

                InvoiceLine::create([
                    'sale_invoice_id'    => $invoice->id,
                    'product_id'         => $product->id,

                    'barcode'            => $product->bar_code ?? $cartItem['barcode'] ?? null,
                    'item_code'          => $product->code ?? $cartItem['code'] ?? null,
                    'name'               => $product->name ?? $cartItem['name'] ?? '',
                    'variant'            => $product->variant ?? $cartItem['variant'] ?? null,
                    'description'        => $product->description ?? $cartItem['description'] ?? null,

                    'quantity'           => $qty,
                    'unit'               => $cartItem['unit'] ?? ($product->unit ?? null),
                    'category_name'      => optional($product->category)->name ?? ($cartItem['category_name'] ?? null),

                    'cost'               => $unitCost,
                    'unit_price'         => $unitPrice,
                    'sell_price'         => $sellPrice,

                    'discount_percent'   => $discountPercent,
                    'discount_amount'    => $discountAmount,

                    'line_amount'        => $lineAmount,
                    'vat'                => $vatRate,
                    'vat_amount'         => $vatAmount,
                    'net_amount'         => $netAmount,
                    'grand_total_amount' => $grandTotal,

                    'created_by'         => Auth::user()->name ?? 'System',
                    'remarks'            => null,
                ]);

                if ((int) ($product->track_stock ?? 0) === 1) {
                    $cartLots = $cartItem['lots'] ?? [];
                    $saleQty = $qty;
                    $ledgerRows = [];

                    if (!empty($cartLots)) {
                        foreach ($cartLots as $lot) {
                            $warehouseProduct = WarehouseProduct::lockForUpdate()->find($lot['id']);

                            if (!$warehouseProduct) {
                                $this->dispatch('error', [
                                    'message' => "Lot ID {$lot['id']} not found",
                                ]);
                                throw new \Exception("Lot ID {$lot['id']} not found");
                            }

                            $lotQty = (float) ($lot['qty'] ?? 0);

                            if ($lotQty <= 0) {
                                continue;
                            }

                            if ((float) $warehouseProduct->qty < $lotQty) {
                                $this->dispatch('error', [
                                    'message' => "Not enough stock in Lot " . $warehouseProduct->lot,
                                ]);
                                throw new \Exception("Not enough stock in Lot {$warehouseProduct->lot}");
                            }

                            $warehouseProduct->decrement('qty', $lotQty);

                            $ledgerRows[] = [
                                'warehouse_product_id' => $warehouseProduct->id,
                                'warehouse_id'         => $warehouseProduct->warehouse_id,
                                'lot'                  => $warehouseProduct->lot,
                                'expire_date'          => $warehouseProduct->expire,
                                'qty'                  => $lotQty,
                            ];

                            $this->syncPurchaseRemainingQty(
                                $product->id,
                                $warehouseProduct->lot,
                                $warehouseProduct->warehouse_id
                            );
                        }

                        $selectedQty = collect($ledgerRows)->sum('qty');

                        if (round($selectedQty, 4) != round($saleQty, 4)) {
                            $this->dispatch('no-stock', [
                                'message' => "{$product->name} មិនមានស្តុកទេ",
                            ]);
                            throw new \Exception("Selected lot quantity not equal sale quantity");
                        }
                    } else {
                        $warehouseProducts = WarehouseProduct::where('product_id', $product->id)
                            ->where('qty', '>', 0)
                            ->orderByRaw('CASE WHEN expire IS NULL THEN 1 ELSE 0 END')
                            ->orderBy('expire', 'asc')
                            ->lockForUpdate()
                            ->get();

                        $remaining = $qty;

                        foreach ($warehouseProducts as $wp) {
                            if ($remaining <= 0) {
                                break;
                            }

                            $deductQty = min((float) $wp->qty, $remaining);

                            if ($deductQty <= 0) {
                                continue;
                            }

                            $wp->decrement('qty', $deductQty);

                            $ledgerRows[] = [
                                'warehouse_product_id' => $wp->id,
                                'warehouse_id'         => $wp->warehouse_id,
                                'lot'                  => $wp->lot,
                                'expire_date'          => $wp->expire,
                                'qty'                  => $deductQty,
                            ];

                            $remaining -= $deductQty;

                            $this->syncPurchaseRemainingQty(
                                $product->id,
                                $wp->lot,
                                $wp->warehouse_id
                            );
                        }

                        if ($remaining > 0) {
                            $this->dispatch('no-stock', [
                                'message' => "{$product->name} មិនមានស្តុកទេ",
                            ]);
                            throw new \Exception("Not enough stock for {$product->name}");
                        }
                    }

                    foreach ($ledgerRows as $row) {
                        $rowQty = (float) $row['qty'];

                        $rowUnitCost = $this->getLotUnitCost(
                            $product->id,
                            $row['lot'] ?? null,
                            $row['warehouse_id'] ?? null
                        );

                        $warehouseName = Warehouse::where('id', $row['warehouse_id'])->value('name');

                        $rowLineAmount = $saleQty > 0 ? round(($lineAmount / $saleQty) * $rowQty, 4) : 0;
                        $rowDiscountAmount = $saleQty > 0 ? round(($discountAmount / $saleQty) * $rowQty, 4) : 0;
                        $rowNetAmount = $saleQty > 0 ? round(($netAmount / $saleQty) * $rowQty, 4) : 0;
                        $rowVatAmount = $saleQty > 0 ? round(($vatAmount / $saleQty) * $rowQty, 4) : 0;
                        $rowGrandTotal = $saleQty > 0 ? round(($grandTotal / $saleQty) * $rowQty, 4) : 0;

                        $ledger = $this->createLedgerEntry([
                            'posting_date'        => $documentDate,
                            'document_type'       => 'Sales Invoice',
                            'document_no'         => $invoice->invoice_number,

                            'source_id'           => $invoice->id,
                            'source_table'        => 'sale_invoice_headers',

                            'product_id'          => $product->id,
                            'barcode'             => $product->bar_code,
                            'item_code'           => $product->code,
                            'name'                => $product->name,
                            'variant'             => $product->variant,
                            'description'         => $product->description,
                            'unit'                => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                            'category_name'       => optional($product->category)->name,
                            'type'                => 'product',

                            'warehouse_id'        => $row['warehouse_id'] ?? null,
                            'warehouse_name'      => $warehouseName ?? null,
                            'lot'                 => $row['lot'] ?? null,
                            'expire_date'         => $row['expire_date'] ?? null,

                            'quantity'            => -1 * $rowQty,
                            'remaining_quantity'  => 0,
                            'entry_type'          => 'negative',

                            'unit_cost'           => $rowUnitCost,
                            'unit_price'          => $unitPrice,
                            'sell_price'          => $sellPrice,

                            'discount_percent'    => $discountPercent,
                            'discount_amount'     => $rowDiscountAmount,

                            'vat'                 => $vatRate,
                            'vat_amount'          => $rowVatAmount,

                            'line_amount'         => $rowLineAmount,
                            'net_amount'          => $rowNetAmount,
                            'grand_total_amount'  => $rowGrandTotal,

                            'customer_id'         => $saleOrder->customer_id,
                            'customer_name'       => $saleOrder->contact_name,
                            'customer_phone'      => $saleOrder->phone,
                            'customer_address'    => $saleOrder->address,

                            'payment_method'      => $saleOrder->payment_method,
                            'remark'              => '',
                            'created_by'          => Auth::user()->name ?? 'System',
                        ]);

                        $ledger->update([
                            'entry_no' => $ledger->id,
                        ]);
                    }
                } else {
                    $ledger = ItemLedgerEntry::create([
                        'entry_no'            => null,

                        'posting_date'        => $documentDate,
                        'document_type'       => 'Sales Invoice',
                        'document_no'         => $invoice->invoice_number,

                        'source_id'           => $invoice->id,
                        'source_table'        => 'sale_invoice_headers',

                        'product_id'          => $product->id,
                        'barcode'             => $product->bar_code,
                        'item_code'           => $product->code,
                        'name'                => $product->name,
                        'variant'             => $product->variant,
                        'description'         => $product->description,
                        'unit'                => $cartItem['unit'] ?? ($product->unit ?? 'NA'),
                        'category_name'       => optional($product->category)->name,
                        'type'                => $product->type ?? 'product',

                        'warehouse_id'        => null,
                        'warehouse_name'      => null,
                        'lot'                 => null,
                        'expire_date'         => null,

                        'quantity'            => -1 * $qty,
                        'remaining_quantity'  => 0,
                        'entry_type'          => 'negative',

                        'unit_cost'           => $unitCost,
                        'unit_price'          => $unitPrice,
                        'sell_price'          => $sellPrice,

                        'discount_percent'    => $discountPercent,
                        'discount_amount'     => $discountAmount,

                        'vat'                 => $vatRate,
                        'vat_amount'          => $vatAmount,

                        'line_amount'         => $lineAmount,
                        'net_amount'          => $netAmount,
                        'grand_total_amount'  => $grandTotal,

                        'customer_id'         => $saleOrder->customer_id,
                        'customer_name'       => $saleOrder->contact_name,
                        'customer_phone'      => $saleOrder->phone,
                        'customer_address'    => $saleOrder->address,

                        'vendor_id'           => null,
                        'payment_method'      => $saleOrder->payment_method,
                        'remark'              => null,
                        'created_by'          => Auth::user()->name ?? 'System',
                    ]);

                    $ledger->update([
                        'entry_no' => $ledger->id,
                    ]);
                }
            }

            $invoice->update([
                'total_amount'    => $invoiceTotalAmount,
                'discount_amount' => $invoiceDiscountAmount,
                'vat_amount'      => $invoiceVatAmount,
                'grand_total'     => $invoiceTotalAmount - $invoiceDiscountAmount + $invoiceVatAmount,
            ]);

            $saleOrder->update([
                'invoice_id' => $invoice->id,
                'status' => 'Completed',
                'payment_status' => 'Paid',
                'balance_amount' => 0,
            ]);
        });

        $this->dispatch('load-sale-order', [
            'message' => 'Sale order updated successfully',
            'id' => $payload['sale_order_id'] ?? null,
        ]);

        $this->clearCart_no_message();
    }
}
