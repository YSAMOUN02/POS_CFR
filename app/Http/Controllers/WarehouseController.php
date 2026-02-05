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
        // 1️⃣ Load warehouse with products and pivot
        $warehouse = Warehouse::with([
            'products' => function ($q) use ($warehouseId) {
                $q->withPivot(['qty', 'track_lot', 'lot', 'expire'])
                    ->wherePivot('warehouse_id', $warehouseId);
            }
        ])->findOrFail($warehouseId);

        // 2️⃣ Map products to match JS table
        $products = $warehouse->products->map(function ($product) {
            $pivot = $product->pivot;

            return [
                'product_id'     => $product->id,
                'product_name'   => $product->name,
                'code'           => $product->code,
                'variant'        => $product->variant,
                'description'    => $product->description,
                'lot'            => $pivot->track_lot ? $pivot->lot : null,
                'expire'         => $pivot->track_lot ? $pivot->expire : null,
                'qty'            => (int) ($pivot->qty ?? 0),
                'unit'           => $product->unit,
                'cost_price'     => (float) $product->cost,
                'vat'            => (float) $product->vat,
                'sell_price'     => (float) $product->sell_price,
                'sell_price_vat' => $product->sell_price * (1 + $product->vat / 100),
                'status'         => (int) $product->status,
                'min_stock'      => $product->min_stock,
                'max_stock'      => $product->max_stock,
            ];
        });




        // 3️⃣ FILTERS


    // 3️⃣ Apply filters only if values are NOT empty
    if (($search = $request->query('search')) !== null && $search !== '') {
        $search = strtolower($search);
        $products = $products->filter(fn($p) =>
            str_contains(strtolower($p['product_name']), $search) ||
            str_contains(strtolower($p['code']), $search)
        );
    }

    if (($variant = $request->query('variant')) !== null && $variant !== '') {
        $variant = strtolower($variant);
        $products = $products->filter(fn($p) =>
            str_contains(strtolower($p['variant']), $variant)
        );
    }

    if (($status = $request->query('status')) !== null && $status !== '') {
        $status = (int) $status; // 0 or 1
        $products = $products->filter(fn($p) => $p['status'] === $status);
    }

    if (($stock = $request->query('stock')) !== null && $stock !== '') {
        $products = $stock === 'has'
            ? $products->filter(fn($p) => $p['qty'] > 0)
            : $products->filter(fn($p) => $p['qty'] <= 0);
    }
        // 4️⃣ Return JSON
        return response()->json([
            'warehouse' => [
                'id'   => $warehouse->id,
                'name' => $warehouse->name
            ],
            'products' => $products->values()
        ]);
    }
}
