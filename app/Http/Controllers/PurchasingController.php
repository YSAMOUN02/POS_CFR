<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Product;
use App\Models\PurchaseHeader;
use App\Models\UserWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchasingController extends Controller
{



    public function Purchasing()
    {
        $warehouse_ids = Auth::user()->warehouses->pluck('id');


        // 1️⃣ Load products with only the selected warehouse
        $products = Product::with(['warehouses' => function ($q) use ($warehouse_ids) {
            $q->whereIn('warehouse_id', $warehouse_ids);
        }])
            ->where('track_stock', 1)
            ->where('status', 1)
            ->get();

        // 2️⃣ Sum stock per product (only from this warehouse)
        $products->each(function ($product) {
            $product->total_stock = $product->warehouses->sum(function ($wh) {
                return $wh->pivot->qty ?? 0;
            });
        });

        // 3️⃣ Sort: in-stock first, then by name ascending
        $products = $products->sort(function ($a, $b) {
            if ($a->total_stock == 0 && $b->total_stock > 0) return 1;
            if ($a->total_stock > 0 && $b->total_stock == 0) return -1;
            return strcmp($a->name, $b->name);
        })->values();

        // 4️⃣ Group by category (limit 50 per category)
        $categories = [];
        foreach ($products as $product) {
            $category = $product->category_name ?? 'Uncategorized';
            if (!isset($categories[$category])) {
                $categories[$category] = [];
            }
            if (count($categories[$category]) < 50) {
                $categories[$category][] = $product;
            }
        }

        // 5️⃣ Currency
        $currency = Currency::where('code', '<>', 'USD')->get();
        $currency_default = Currency::where('is_default', 1)->first();
        $factor = $currency_default ? $currency_default->factor : 1;
        $currency_name = $currency_default ? $currency_default->code : 'USD';

        return view('backend.purchasing', compact('categories', 'currency', 'factor', 'currency_name'));
    }

    public function search(Request $request)
    {
        // 👈 Use input() instead of $request->query
        $query = $request->input('query', '');
        $field = $request->input('field', 'name');

        $warehouse_ids = Auth::user()->warehouses->pluck('id');

        // Optional: whitelist allowed fields to prevent SQL injection
        $allowedFields = ['name', 'description', 'code', 'barcode'];
        if (!in_array($field, $allowedFields)) {
            $field = 'name';
        }

        $products = Product::with(['warehouses' => function ($q) use ($warehouse_ids) {
            $q->whereIn('warehouse_id', $warehouse_ids);
        }])
            ->where('status', 1)
            ->where('track_stock', 1)
            // ✅ Dynamic search based on field selected
            ->when($query !== '', function ($q) use ($field, $query) {
                $q->where($field, 'LIKE', "%{$query}%");
            })

            ->limit(41)
            ->get();
        // 2️⃣ Sum stock per product (only from this warehouse)
        $products->each(function ($product) {
            $product->total_stock = $product->warehouses->sum(function ($wh) {
                return $wh->pivot->qty ?? 0;
            });
        });

        // 3️⃣ Sort: in-stock first, then by name ascending
        $products = $products->sort(function ($a, $b) {
            if ($a->total_stock == 0 && $b->total_stock > 0) return 1;
            if ($a->total_stock > 0 && $b->total_stock == 0) return -1;
            return strcmp($a->name, $b->name);
        })->values();

        return response()->json($products);
    }

public function fetchPurchase(Request $request)
{
    try {

        $limit = (int) ($request->limit ?? 100);

        if (!in_array($limit, [100, 200, 300])) {
            $limit = 100;
        }

        $query = PurchaseHeader::with([
            'vendor:id,code,name,contact_person',
            'lines:id,document_no,name,quantity,line_amount,category_name'
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('posting_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('posting_date', '<=', $request->to_date);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('product_search')) {
            $search = $request->product_search;

            $query->whereHas('lines', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_name')) {
            $query->whereHas('lines', function ($q) use ($request) {
                $q->where('category_name', $request->category_name);
            });
        }

        $purchases = $query->orderByDesc('id')->paginate($limit);

        if ($purchases->total() == 0) {
            return response()->json([
                'status' => false,
                'message' => 'No purchase found',
                'data' => $purchases
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Purchase loaded successfully',
            'data' => $purchases
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
}
