<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
            ->orderByDesc('warehouse_product.warehouse_id')
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
        // Only track lots with qty > 0
        $lots = WarehouseProduct::where('product_id', $product_id)
            ->where('qty', '>', 0)
            ->orderBy('expire', 'asc') // earliest expire first
            ->get(['id', 'lot', 'qty', 'expire']);

        return response()->json($lots);
    }
}
