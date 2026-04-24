<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Metadata\Test;

class AdminController extends Controller
{

    public function index_by_page()
    {
        $warehouse_ids = Auth::user()->warehouses->pluck('id');


        // 1️⃣ Load products with only the selected warehouse
        $sql = Product::with(['warehouses' => function ($q) use ($warehouse_ids) {
            $q->whereIn('warehouse_id', $warehouse_ids);
        }]);
          if(Auth::user()->role == 'admin') {

        } else {
            $sql->where('type', 'Product');
        }
            $sql->where('status', 1);
          $products =  $sql->get();

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
        return view('backend.pos', compact('categories', 'currency', 'factor', 'currency_name'));
    }



    public function getProducts()
    {
        $warehouse_ids = Auth::user()->warehouses->pluck('id');

           // 1️⃣ Load products with only the selected warehouse
        $sql = Product::with(['warehouses' => function ($q) use ($warehouse_ids) {
            $q->whereIn('warehouse_id', $warehouse_ids);
        }]);
          if(Auth::user()->role == 'admin') {

        } else {
            $sql->where('type', 'Product');
        }
            $sql->where('status', 1);
          $products =  $sql->get();


        $products->each(function ($product) {
            $product->total_stock = $product->warehouses->sum(function ($wh) {
                return $wh->pivot->qty ?? 0;
            });
        });

        $products = $products->sort(function ($a, $b) {
            if ($a->total_stock == 0 && $b->total_stock > 0) return 1;
            if ($a->total_stock > 0 && $b->total_stock == 0) return -1;
            return strcmp($a->name, $b->name);
        })->values();

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

        return response()->json($categories);
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



    public function login()
    {
        return view('backend.login');
    }

    public function login_submit(Request $request)
    {
        // Validate inputs
        $request->validate([
            'name_email' => 'required|string',  // username or email
            'password'   => 'required|string',
        ]);

        $loginInput = $request->input('name_email');
        $password = $request->input('password');
        $remember = $request->has('remember');

        // Try login by username
        if (Auth::attempt(['name' => $loginInput, 'password' => $password], $remember)) {
            if (Auth::user()->status == 0) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Your user has been disabled from the system'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login success ✅',
                'redirect' => 'Sale' // route for /Sale
            ]);
        }

        // Try login by email
        if (Auth::attempt(['email' => $loginInput, 'password' => $password], $remember)) {
            if (Auth::user()->status == 0) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Your user has been disabled from the system'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login success ✅',
                'redirect' => 'Sale' // route for /Sale
            ]);
        }

        // Invalid credentials
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }
    public function logout()
    {
        $auth = Auth::logout();

        if ($auth) {
            return redirect("/login")->with('success', 'Logout Suceess.');
        } else {
            return redirect("/")->with('fail', 'Logout Suceess.');
        }
    }
}
