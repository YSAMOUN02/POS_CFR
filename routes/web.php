<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SaleInvoiceController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ItemLedgerEntryController;
use App\Http\Controllers\SaleOrderController;

Route::get('/', [AdminController::class, 'login'])->name('login');
// Handle login post
Route::post('/login-submit', [AdminController::class, 'login_submit']);



Route::middleware(['auth'])->group(function () {

    Route::get('/Sale', [AdminController::class, 'index_by_page']);
    Route::get('/pos/products', [AdminController::class, 'getProducts']);
    // USER
    Route::get('/users-list-data', [UserController::class, 'userListData'])->name('users.list.data');
    Route::post('/users/store', [UserController::class, 'store_user']);
    // Get Warehouse for User
    Route::get('/warehouse-list-data', [UserController::class, 'get_warehouse_list']);

    Route::post('/purchase/products/search', [PurchasingController::class, 'search']);

    Route::post('/products/category/search', [ProductController::class, 'searchByCategory']);

    Route::get('/categories', [CategoryController::class, 'getCategories']);


    Route::get('/currency/{code}', [AdminController::class, 'getByCode']);
    Route::post('/currency/update-all', [AdminController::class, 'updateAll'])
        ->name('currency.updateAll');

    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');


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
    Route::get('/product/categories', [WarehouseController::class, 'getCategories']);


    // Get lot
    Route::get('/get-lot-data/{product_id}', [WarehouseController::class, 'getLotData']);
    // transfer Lot
    Route::post('/transfer-lot', [WarehouseController::class, 'transfer']);


    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/products/list_search', [ProductController::class, 'list_search']);
    Route::put('/product/{id}', [ProductController::class, 'update'])->name('product.update');



    Route::get('/tables', [TableController::class, 'GetTables']);
    Route::post('/restaurant-tables/store', [TableController::class, 'store'])
        ->name('restaurant-tables.store');

    // Report




    Route::get('/sales-report', [SaleInvoiceController::class, 'salesReport'])->name('sales.report');
    Route::get('/sales/categories', [SaleInvoiceController::class, 'getCategories']);

    Route::get('/sales/customer-search', [SaleInvoiceController::class, 'searchCustomers']);
    Route::get('/sales/product-search', [SaleInvoiceController::class, 'searchProducts']);
    Route::get('/sales/payment-methods', [SaleInvoiceController::class, 'getPaymentMethods']);








    Route::get('/forgot/password', [AdminController::class, 'forgot_password']);


    Route::get('/logout', [AdminController::class, 'logout']);



    Route::get('/Purchasing', [PurchasingController::class, 'Purchasing']);
    Route::get('/purchases/fetch', [PurchasingController::class, 'fetchPurchase'])
        ->name('purchases.fetch');

    Route::post('/vendors', [VendorController::class, 'store']);
    Route::get('/vendors/list', [VendorController::class, 'list'])->name('vendors.list');
    Route::get('/vendors/{id}', [VendorController::class, 'show'])->name('vendors.show');
    Route::put('/vendors/{id}', [VendorController::class, 'update'])->name('vendors.update');
    Route::post('/vendor-search', [VendorController::class, 'search'])
        ->name('vendor.search');




Route::get('/item-ledger-entry', [ItemLedgerEntryController::class, 'latest']);

Route::get('/expenses/latest', [ExpenseController::class, 'latest']);

Route::get('/get-sale-orders', [SaleOrderController::class, 'getSaleOrders']);

Route::get('/sale-order-lines/{id}', [SaleOrderController::class, 'getSaleOrderLines']);
});
