<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ItemLedgerEntry;
use App\Models\Product;
use App\Models\PurchaseLine;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function list_warehouse()
    {
        $warehouse_ids = Auth::user()->warehouses->pluck('id');

        return Warehouse::select('id', 'name', 'location')
            ->withSum('products as total_stock', 'warehouse_product.qty')
            ->whereIn('id', $warehouse_ids)
            ->get();
    }
    public function update(Request $request, $id)
    {
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        try {
            $warehouse = Warehouse::findOrFail($id);

            $warehouse->update([
                'name' => $request->name,
                'location' => $request->location,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function getStock(Request $request, $warehouseId)
    {
        $limit = $request->query('limit', 50);



        $query = \DB::table('warehouse_product')
            ->join('product', 'product.id', '=', 'warehouse_product.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_product.warehouse_id')
            ->leftJoin('categories', 'categories.id', '=', 'product.category_id')
            ->orderBy('warehouses.id')                 // priority 1
            ->orderBy('warehouse_product.id')          // priority 2
            ->select(
                'warehouse_product.id as lot_id',
                'warehouses.id as warehouse_id',
                'warehouses.name as warehouse_name',
                'product.id as product_id',
                'product.name as product_name',
                'product.code',
                'product.variant',
                'product.description',
                'warehouse_product.qty',
                'warehouse_product.track_lot',
                'warehouse_product.lot',
                'warehouse_product.expire',
                'product.unit',
                'product.cost as cost_price',
                'product.vat',
                'product.sell_price',
                'product.status',
                'product.min_stock',
                'product.max_stock',
                'categories.name as category_name'
            );
        // 🔥 Apply filters FIRST

        if ($warehouseId != 0) {
            $query->where('warehouse_product.warehouse_id', $warehouseId);
        } else {
            $warehouse_ids = Auth::user()->warehouses->pluck('id');
            $query->whereIn('warehouse_product.warehouse_id', $warehouse_ids);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(product.name) LIKE ?', ["%$search%"])
                    ->orWhereRaw('LOWER(product.code) LIKE ?', ["%$search%"]);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('product.category_id', $request->category_id);
        }

        // 🔥 fix ALL status
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('product.status', (int)$request->status);
        }

        if ($request->filled('stock') && $request->stock !== 'All') {
            if ($request->stock === 'has') {
                $query->where('warehouse_product.qty', '>', 0);
            } else {
                $query->where('warehouse_product.qty', '<=', 0);
            }
        }
        // 🔥 EXECUTE QUERY LAST (VERY IMPORTANT)

        if ($limit === 'All') {
            $products = $query->get();

            $products = $products->map(function ($p) {
                $vat = (float) $p->vat;
                $sellPrice = (float) $p->sell_price;

                return [
                    'lot_id' => $p->lot_id,
                    'warehouse_id'   => $p->warehouse_id,
                    'warehouse_name' => $p->warehouse_name,
                    'product_id'     => $p->product_id,
                    'product_name'   => $p->product_name,
                    'code'           => $p->code,
                    'variant'        => $p->variant,
                    'description'    => $p->description,
                    'lot'            => $p->track_lot ? $p->lot : null,
                    'expire'         => $p->track_lot ? $p->expire : null,
                    'qty'            => (int) $p->qty,
                    'unit'           => $p->unit,
                    'cost_price'     => (float) $p->cost_price,
                    'vat'            => $vat,
                    'sell_price'     => $sellPrice,
                    'sell_price_vat' => $sellPrice * (1 + ($vat / 100)),
                    'status'         => (int) $p->status,
                    'min_stock'      => $p->min_stock,
                    'max_stock'      => $p->max_stock,
                    'category_name'  => $p->category_name
                ];
            });

            return response()->json([
                'data' => $products,
                'current_page' => 1,
                'per_page' => 'All',
                'total' => $products->count()
            ]);
        }

        // 🔥 SAFE paginate
        $perPage = max((int)$limit, 1);
        $products = $query->paginate($perPage);

        $products->getCollection()->transform(function ($p) {
            $vat = (float) $p->vat;
            $sellPrice = (float) $p->sell_price;

            return [
                'lot_id' => $p->lot_id,
                'warehouse_id'   => $p->warehouse_id,
                'warehouse_name' => $p->warehouse_name,
                'product_id'     => $p->product_id,
                'product_name'   => $p->product_name,
                'code'           => $p->code,
                'variant'        => $p->variant,
                'description'    => $p->description,
                'lot'            => $p->track_lot ? $p->lot : null,
                'expire'         => $p->track_lot ? $p->expire : null,
                'qty'            => (int) $p->qty,
                'unit'           => $p->unit,
                'cost_price'     => (float) $p->cost_price,
                'vat'            => $vat,
                'sell_price'     => $sellPrice,
                'sell_price_vat' => $sellPrice * (1 + ($vat / 100)),
                'status'         => (int) $p->status,
                'min_stock'      => $p->min_stock,
                'max_stock'      => $p->max_stock,
                'category_name'  => $p->category_name
            ];
        });

        return response()->json($products);
    }
    public function getCategories()
    {
        $category = Category::orderby('id', 'desc')->select('id', 'name')->get();
        return response()->json($category);
    }

    public function getLotData($product_id)
    {
        $warehouse_ids = Auth::user()->warehouses->pluck('id');

        $lots = WarehouseProduct::with(['warehouse:id,name'])
            ->whereIn('warehouse_id', $warehouse_ids)
            ->where('product_id', $product_id)
            ->where('qty', '>', 0)
            ->orderBy('expire', 'asc')
            ->get(['id', 'warehouse_id', 'lot', 'qty', 'expire'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'lot' => $item->lot,
                    'qty' => $item->qty,
                    'expire' => $item->expire,
                    'warehouse_name' => $item->warehouse->name ?? null,
                ];
            });

        return response()->json($lots);
    }
    public function transfer(Request $request)
    {
        $request->validate([
            'wh_product_id' => 'required|integer|exists:warehouse_product,id',
            'warehouse_id'  => 'required|integer|exists:warehouses,id',
            'qty'           => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $row = WarehouseProduct::where('id', $request->wh_product_id)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Stock row not found'
                ], 404);
            }

            $transferQty   = (int) $request->qty;
            $toWarehouseId = (int) $request->warehouse_id;

            if ($transferQty > $row->qty) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer qty cannot be greater than stock'
                ], 422);
            }

            if ($row->warehouse_id == $toWarehouseId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot transfer to the same warehouse'
                ], 422);
            }

            $product = Product::with('category')->find($row->product_id);
            $fromWarehouse = Warehouse::find($row->warehouse_id);
            $toWarehouse = Warehouse::find($toWarehouseId);

            if (!$product || !$fromWarehouse || !$toWarehouse) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Product or warehouse not found'
                ], 404);
            }

            $year = now()->format('y'); // 26
            $prefix = 'TO' . $year . '-';

            // Find last document no this year
            $lastTransfer = ItemLedgerEntry::where('document_type', 'Transfer')
                ->where('document_no', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->first();

            if ($lastTransfer) {
                $lastNumber = (int) substr($lastTransfer->document_no, strlen($prefix));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $transferNo = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Keep old row data before update/delete
            $oldRowId = $row->id;
            $oldWarehouseId = $row->warehouse_id;
            $oldLot = $row->lot;
            $oldExpire = $row->expire;
            $oldTrackLot = $row->track_lot;
            $oldControlExp = $row->control_exp;

            $targetRow = WarehouseProduct::where('product_id', $row->product_id)
                ->where('warehouse_id', $toWarehouseId)
                ->where('lot', $row->lot)
                ->where(function ($q) use ($row) {
                    if ($row->expire) {
                        $q->whereDate('expire', $row->expire);
                    } else {
                        $q->whereNull('expire');
                    }
                })
                ->lockForUpdate()
                ->first();
            $purchaseLine = PurchaseLine::where('product_id', $product->id)
                ->when(!is_null($oldLot), fn($q) => $q->where('lot', $oldLot), fn($q) => $q->whereNull('lot'))
                ->when(!is_null($oldExpire), fn($q) => $q->whereDate('expire_date', $oldExpire), fn($q) => $q->whereNull('expire_date'))
                ->latest('id')
                ->first();

            $unitCost = $purchaseLine->unit_cost ?? $product->cost ?? 0;
            if ($targetRow) {
                $targetRow->qty += $transferQty;
                $targetRow->original_qty += $transferQty;
                $targetRow->save();

                if ($transferQty == $row->qty) {
                    $row->delete();
                } else {
                    $row->qty -= $transferQty;
                    $row->save();
                }
            } else {
                if ($transferQty == $row->qty) {
                    $row->warehouse_id = $toWarehouseId;
                    $row->save();
                } else {
                    $row->qty -= $transferQty;
                    $row->save();

                    WarehouseProduct::create([
                        'product_id'   => $product->id,
                        'warehouse_id' => $toWarehouseId,
                        'original_qty' => $transferQty,
                        'qty'          => $transferQty,
                        'track_lot'    => $oldTrackLot,
                        'lot'          => $oldLot,
                        'expire'       => $oldExpire,
                        'control_exp'  => $oldControlExp,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            // =========================
            // Item Ledger: Transfer OUT
            // =========================
            $item_ledger = ItemLedgerEntry::create([
                'posting_date'       => now()->toDateString(),
                'document_type'      => 'Transfer Shipment',
                'document_no'        => $transferNo,

                'source_id'          => $oldRowId,
                'source_table'       => 'warehouse_product',

                'product_id'         => $product->id,
                'barcode'            => $product->bar_code,
                'item_code'          => $product->code,
                'name'               => $product->name,
                'variant'            => $product->variant,
                'description'        => $product->description,
                'unit'               => $product->unit ?? 'NA',
                'category_name'      => optional($product->category)->name,
                'type'               => 'product',

                'warehouse_id'       => $oldWarehouseId,
                'warehouse_name'     => $fromWarehouse->name,
                'lot'                => $oldLot,
                'expire_date'        => $oldExpire,

                'quantity'           => -1 * $transferQty,
                'remaining_quantity' => 0,
                'entry_type'         => 'negative',

                'unit_cost'          => $unitCost,
                'unit_price'         => $product->sell_price ?? 0,
                'sell_price'         => $product->sell_price ?? 0,

                'discount_percent'   => 0,
                'discount_amount'    => 0,
                'vat'                => $product->vat ?? 0,
                'vat_amount'         => 0,

                'line_amount'        => 0,
                'net_amount'         => 0,
                'grand_total_amount' => 0,

                'customer_id'        => null,
                'customer_name'      => null,
                'customer_phone'     => null,
                'customer_address'   => null,

                'payment_method'     => null,
                'remark'             => 'Transfer OUT to ' . $toWarehouse->name,
                'created_by'         => Auth::user()->name ?? 'System',
            ]);
            $item_ledger->update([
                'entry_no' => $item_ledger->id
            ]);
            // ========================
            // Item Ledger: Transfer IN
            // ========================
            $item_ledger2 = ItemLedgerEntry::create([
                'posting_date'       => now()->toDateString(),
                'document_type'      => 'Transfer Receipt',
                'document_no'        => $transferNo,

                'source_id'          => $oldRowId,
                'source_table'       => 'warehouse_product',

                'product_id'         => $product->id,
                'barcode'            => $product->bar_code,
                'item_code'          => $product->code,
                'name'               => $product->name,
                'variant'            => $product->variant,
                'description'        => $product->description,
                'unit'               => $product->unit ?? 'NA',
                'category_name'      => optional($product->category)->name,
                'type'               => 'product',

                'warehouse_id'       => $toWarehouse->id,
                'warehouse_name'     => $toWarehouse->name,
                'lot'                => $oldLot,
                'expire_date'        => $oldExpire,

                'quantity'           => $transferQty,
                'remaining_quantity' => $transferQty,
                'entry_type'         => 'positive',

                'unit_cost'          => $unitCost,
                'unit_price'         => $product->sell_price ?? 0,
                'sell_price'         => $product->sell_price ?? 0,

                'discount_percent'   => 0,
                'discount_amount'    => 0,
                'vat'                => $product->vat ?? 0,
                'vat_amount'         => 0,

                'line_amount'        => 0,
                'net_amount'         => 0,
                'grand_total_amount' => 0,

                'customer_id'        => null,
                'customer_name'      => null,
                'customer_phone'     => null,
                'customer_address'   => null,

                'payment_method'     => null,
                'remark'             => 'Transfer IN from ' . $fromWarehouse->name,
                'created_by'         => Auth::user()->name ?? 'System',
            ]);
            $item_ledger2->update([
                'entry_no' =>  $item_ledger2->id
            ]);
            // ✅ Sync purchase remaining qty
            $this->syncPurchaseRemainingQty($product->id, $oldLot, $oldWarehouseId);
            $this->syncPurchaseRemainingQty($product->id, $oldLot, $toWarehouseId);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock transferred successfully'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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
}
