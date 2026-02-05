<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public $warehouse_id = 1;
    /**
     * 🔍 Search products (LOT-aware stock)
     */
    public function search(Request $request)
    {
        $allowed = ['name', 'code', 'barcode'];
        $field = in_array($request->query('field'), $allowed)
            ? $request->query('field')
            : 'name';

        $query = $request->query('query', '');

        $products = Product::with(['warehouses' => function ($q) {
            $q->where('warehouse_id', $this->warehouse_id);
        }])
            ->where('status', 1)
            ->where($field, 'like', "%{$query}%")
            ->get();

        // ✅ LOT-aware stock sum
        $products->each(function ($product) {
            $product->total_stock = $product->warehouses->sum(fn($wh) => $wh->pivot->qty ?? 0);
        });

        // Sort: in-stock first, then name
        $products = $products->sort(function ($a, $b) {
            if ($a->total_stock == 0 && $b->total_stock > 0) return 1;
            if ($a->total_stock > 0 && $b->total_stock == 0) return -1;
            return strcmp($a->name, $b->name);
        })->values();

        return response()->json($products);
    }


    public function list_search(Request $request)
{
    $limit = $request->query('limit', 10);

    $query = Product::query()->with('category'); // eager load category

    // Search
    if ($request->filled('search')) {
        $s = $request->search;
        $query->where(function ($q) use ($s) {
            $q->where('code', 'like', "%$s%")
              ->orWhere('name', 'like', "%$s%")
              ->orWhere('variant', 'like', "%$s%")
              ->orWhere('description', 'like', "%$s%")
              ->orWhereHas('category', function ($q2) use ($s) {
                  $q2->where('name', 'like', "%$s%");
              });
        });
    }

    // Filter by category (frontend type = category_id)
    if ($request->filled('type')) {
        $query->where('category_id', $request->type);
    }

    // Filter by status
    if($request->filled('status') != ''){
      if ($request->filled('status') && is_numeric($request->status)) {
            $query->where('status', $request->status);
    }
    }


    // Filter by track_stock
    if ($request->filled('track_stock')) {
        $query->where('track_stock', $request->track_stock);
    }

    // Sorting
    $sortableColumns = [
        'id', 'code', 'name', 'variant', 'sell_price', 'cost',
        'vat', 'discount_percent', 'last_purchase_price', 'min_stock',
        'max_stock', 'status'
    ];

    if ($request->filled('sort_by') && in_array($request->sort_by, $sortableColumns)) {
        $dir = $request->query('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($request->sort_by, $dir);
    } else {
        $query->orderBy('id', 'desc');
    }

    // Return paginated products including category
    $products = $query->paginate($limit);

    // Optional: map to include only fields you want + category name
    $products->getCollection()->transform(function ($product) {
        return [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'variant' => $product->variant,
            'description' => $product->description,
            'sell_price' => $product->sell_price,
            'cost' => $product->cost,
            'vat' => $product->vat,
            'discount_percent' => $product->discount_percent,
            'last_purchase_price' => $product->last_purchase_price,
            'min_stock' => $product->min_stock,
            'max_stock' => $product->max_stock,
            'track_stock' => $product->track_stock,
            'allow_discount' => $product->allow_discount,
            'allow_return' => $product->allow_return,
            'status' => $product->status,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name
            ] : null,
        ];
    });

    return response()->json($products);
}

}
