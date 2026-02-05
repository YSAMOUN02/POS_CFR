<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;


Route::get('/', [AdminController::class, 'index_by_page']);




Route::get('/products/category/{category}', function ($category) {
    $products = Product::all(); // Get all mock products
    $categories = [];

    foreach ($products as $product) {
        $cat = $product->category;

        if (!isset($categories[$cat])) {
            $categories[$cat] = [];
        }

        // Only push if less than 15 items per category
        if (count($categories[$cat]) < 41) {
            $categories[$cat][] = $product;
        }
    }

    // Return only the requested category
    $result = $categories[$category] ?? [];

    return response()->json($result);
});

Route::get('/categories', [CategoryController::class, 'getCategories']);


Route::get('/currency/{code}', [AdminController::class, 'getByCode']);
Route::post('/currency/update-all', [AdminController::class, 'updateAll'])
    ->name('currency.updateAll');

Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');


Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
Route::get('/customers/list', [CustomerController::class, 'list']);
// DELETE customer
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);

// UPDATE customer
Route::put('/customers/{customer}', [CustomerController::class, 'update']);
Route::get('/customers/list_search', [CustomerController::class, 'list_search']);



Route::get('/warehouses/list', [WarehouseController::class, 'list_warehouse']);
Route::post('/warehouses/update/{id}', [WarehouseController::class, 'update']);
// get stock
Route::get('/warehouses/{id}/stock', [WarehouseController::class, 'getStock']);


Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/list_search', [ProductController::class, 'list_search']);
