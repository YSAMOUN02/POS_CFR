<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use PHPUnit\Metadata\Test;

class AdminController extends Controller
{
    public $warehouse_id = 1;
    public function index_by_page()
    {


        // 1️⃣ Load products with only the selected warehouse
        $products = Product::with(['warehouses' => function ($q) {
            $q->where('warehouse_id', $this->warehouse_id);
        }])
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

        return view('backend.pos', compact('categories', 'currency'));
    }


    // Async function to get currency by code
    public function getByCode(Request $request, $code)
    {
        $currency = Currency::where('code', $code)->first();

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $currency
        ]);
    }

    public function updateAll(Request $request)
    {
        try {
            $default = $request->input('default_currency'); // ID or 'new'
            $newCurrency = null;

            // 1️⃣ Clear old default FIRST
            Currency::where('is_default', true)
                ->update(['is_default' => false]);

            // 2️⃣ Update existing currencies
            if ($request->has('currency')) {
                foreach ($request->currency as $id => $data) {
                    Currency::where('id', $id)->update([
                        'factor'     => $data['factor'] ?? null,
                        'code'       => $data['code'] ?? null,
                        'name'       => $data['name'] ?? null,
                        'is_default' => ($default == $id), // ✅ key line
                    ]);
                }
            }

            // 3️⃣ Create new currency (if filled)
            $new = $request->input('new_currency');

            if (
                $new &&
                !empty($new['factor']) &&
                !empty($new['code']) &&
                !empty($new['name'])
            ) {
                $newCurrency = \App\Models\Currency::create([
                    'factor'     => $new['factor'],
                    'code'       => $new['code'],
                    'name'       => $new['name'],
                    'is_default' => ($default === 'new'), // ✅ new can be default
                ]);
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Currency saved successfully',
                'new_currency' => $newCurrency,
            ]);
        } catch (\Exception $e) {
            \Log::error('Currency update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
