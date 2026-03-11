<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function list_warehouse()
    {
        return Warehouse::select('id', 'name', 'location')
            ->withSum('products as total_stock', 'warehouse_product.qty')
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
    $perPage = $request->query('per_page', 10);

    $query = \DB::table('warehouse_product')
        ->join('products', 'products.id', '=', 'warehouse_product.product_id')
        ->join('warehouses', 'warehouses.id', '=', 'warehouse_product.warehouse_id')
        ->select(
            'warehouses.id as warehouse_id',
            'warehouses.name as warehouse_name',
            'products.id as product_id',
            'products.name as product_name',
            'products.code',
            'products.variant',
            'products.description',
            'warehouse_product.qty',
            'warehouse_product.track_lot',
            'warehouse_product.lot',
            'warehouse_product.expire',
            'products.unit',
            'products.cost as cost_price',
            'products.vat',
            'products.sell_price',
            'products.status',
            'products.min_stock',
            'products.max_stock'
        );

    // 🔥 Warehouse filter
    if ($warehouseId != 0) {
        $query->where('warehouse_product.warehouse_id', $warehouseId);
    }

    // 🔍 Filters
    if ($request->filled('search')) {
        $search = strtolower($request->search);
        $query->where(function ($q) use ($search) {
            $q->whereRaw('LOWER(products.name) LIKE ?', ["%$search%"])
              ->orWhereRaw('LOWER(products.code) LIKE ?', ["%$search%"]);
        });
    }

    if ($request->filled('variant')) {
        $variant = strtolower($request->variant);
        $query->whereRaw('LOWER(products.variant) LIKE ?', ["%$variant%"]);
    }

    if ($request->filled('status')) {
        $query->where('products.status', (int)$request->status);
    }

    if ($request->filled('stock')) {
        if ($request->stock === 'has') {
            $query->where('warehouse_product.qty', '>', 0);
        } else {
            $query->where('warehouse_product.qty', '<=', 0);
        }
    }

    $products = $query->paginate($perPage);

    // Format result
    $products->getCollection()->transform(function ($p) {
        return [
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
            'vat'            => (float) $p->vat,
            'sell_price'     => (float) $p->sell_price,
            'sell_price_vat' => $p->sell_price * (1 + $p->vat / 100),
            'status'         => (int) $p->status,
            'min_stock'      => $p->min_stock,
            'max_stock'      => $p->max_stock,
        ];
    });

    return response()->json($products);
}
}
