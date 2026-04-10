<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
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
            $row = WarehouseProduct::where('id', $request->wh_product_id)->first();

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
                ->first();

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
                    $productId  = $row->product_id;
                    $trackLot   = $row->track_lot;
                    $lot        = $row->lot;
                    $expire     = $row->expire;
                    $controlExp = $row->control_exp;

                    $row->qty -= $transferQty;
                    $row->save();

                    WarehouseProduct::create([
                        'product_id'   => $productId,
                        'warehouse_id' => $toWarehouseId,
                        'original_qty' => $transferQty,
                        'qty'          => $transferQty,
                        'track_lot'    => $trackLot,
                        'lot'          => $lot,
                        'expire'       => $expire,
                        'control_exp'  => $controlExp,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

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
}
