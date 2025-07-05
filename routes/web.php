<?php

use App\Http\Controllers\PurchaseCartController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\SalesreturnCartController;
use App\Http\Controllers\SalesreturnController;
use App\Http\Controllers\PurchasereturnController;
use App\Http\Controllers\PurchasereturnCartController;
use App\Http\Controllers\DamageController;
use App\Http\Controllers\DamageCartController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();


// For User Routes
Route::prefix('user')->middleware(['auth'])->group(function () {
    Route::get('/dashboards', [UserController::class, 'index'])->name('user-dashboard');
});

Route::middleware(['auth' ])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin-dashboard');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    Route::resource('products', ProductController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('suppliers', SupplierController::class);

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
    Route::delete('/cart/delete', [CartController::class, 'delete']);
    Route::delete('/cart/empty', [CartController::class, 'empty']);


    Route::get('/purchase-index', [PurchaseCartController::class, 'index'])->name('purchases.index');
    Route::get('/purchase-create', [PurchaseCartController::class, 'create'])->name('purchases.create');
    Route::get('/purchasecart', [PurchaseCartController::class, 'purchaseCart'])->name('purchases.cart');
    Route::post('/purchase-cart', [PurchaseCartController::class, 'store'])->name('purchases.store');
    Route::post('/purchase-cart/change-qty', [PurchaseCartController::class, 'changpurchaseeQty']);
    Route::post('/purchase-cart/change-purchaseprice', [PurchaseCartController::class, 'changePurchaseprice']);
    Route::delete('/purchase-cart/delete', [PurchaseCartController::class, 'delete']);
    Route::delete('/purchase-cart/empty', [PurchaseCartController::class, 'empty']);
    Route::get('/purchase/details/{purchase_id}', [PurchaseController::class, 'purchaseDetails']);



    Route::resource('sales', SaleController::class);
    Route::resource('/purchase', PurchaseController::class);
    Route::post('/sales/partial-payment', [SaleController::class, 'partialPayment'])->name('sales.partial-payment');
    
    
    Route::resource('/salesreturn', SalesreturnController::class);
    Route::get('/salesreturn-cart', [SalesreturnCartController::class, 'index'])->name('salesreturns.index');
    Route::post('/salesreturn-cart', [SalesreturnCartController::class, 'store'])->name('salesreturns.store');
    Route::post('/salesreturn-cart/change-qty', [SalesreturnCartController::class, 'changeQty']);
    Route::delete('/salesreturn-cart/delete', [SalesreturnCartController::class, 'delete']);
    Route::delete('/salesreturn-cart/empty', [SalesreturnCartController::class, 'empty']);
    Route::get('/salesreturn/findorderid/{order_id}', [SalesreturnController::class, 'findOrderID']);
    Route::post('/salesreturn/cart', [SalesreturnController::class, 'addProductToCart']);
    Route::post('/salesreturn/changeqnty', [SalesreturnController::class, 'changeQnty']);
    Route::post('/salesreturn/delete', [SalesreturnController::class, 'handleDelete']);
    Route::get('/salesreturn/details/{salesreturn_id}', [SalesreturnController::class, 'salesreturnDetails']);
    Route::post('/salesreturn/finalsave', [SalesreturnController::class, 'finalSave']);

    Route::resource('/purchasereturn', PurchasereturnController::class);
    Route::get('/purchasereturn-cart', [PurchasereturnCartController::class, 'index'])->name('purchasereturns.index');
    Route::post('/purchasereturn-cart', [PurchasereturnCartController::class, 'store'])->name('purchasereturns.store');
    Route::post('/purchasereturn-cart/change-qty', [PurchasereturnCartController::class, 'changeQty']);
    Route::delete('/purchasereturn-cart/delete', [PurchasereturnCartController::class, 'delete']);
    Route::delete('/purchasereturn-cart/empty', [PurchasereturnCartController::class, 'empty']);
    Route::get('/purchasereturn/findpurchaseid/{purchase_id}', [PurchasereturnController::class, 'findPurchaseID']);
    Route::post('/purchasereturn/cart', [PurchasereturnController::class, 'addProductToCart']);
    Route::post('/purchasereturn/changeqnty', [PurchasereturnController::class, 'changeQnty']);
    Route::post('/purchasereturn/delete', [PurchasereturnController::class, 'handleDelete']);
    Route::get('/purchasereturn/details/{salesreturn_id}', [PurchasereturnController::class, 'purchasereturn.details']);
    Route::post('/purchasereturn/finalsave', [PurchasereturnController::class, 'finalSave']);



    Route::resource('/damage', DamageController::class);
    Route::get('/damage-create', [DamageController::class, 'create'])->name('damages.create');
    Route::post('/damage-cart', [DamageController::class, 'store'])->name('damages.store');
 
    Route::get('/damage/findorderid/{order_id}', [DamageController::class,'findOrderID']);
    Route::post('/damage/cart', [DamageController::class,'addProductToCart']);
    Route::post('/damage/changeqnty', [DamageController::class,'changeQnty']);
    Route::post('/damage/delete', [DamageController::class,'handleDelete']);
    Route::get('/damage/details/{salesreturn_id}', [DamageController::class,'damage.details']);
    Route::post('/damage/finalsave', [DamageController::class,'finalSave']);


    Route::get('/expense-head-create', [ExpenseController::class, 'createExpenseHead'])->name('expense.head.create');
    Route::post('/expense-head-store', [ExpenseController::class, 'storeExpenseHead'])->name('expense.head.store');
    Route::delete('/expense-head/{exp_head_id}', [ExpenseController::class, 'deleteExpenseHead'])->name('expense.head.delete');

    // Expense Report Detail Routes
    Route::get('/expense/sales-details', [ExpenseController::class, 'salesDetails'])->name('expense.sales-details');
    Route::get('/expense/purchase-details', [ExpenseController::class, 'purchaseDetails'])->name('expense.purchase-details');
    Route::get('/expense/expense-details', [ExpenseController::class, 'expenseDetails'])->name('expense.expense-details');
    Route::get('/expense/profit-details', [ExpenseController::class, 'profitDetails'])->name('expense.profit-details');
    Route::get('/expense/cash-details', [ExpenseController::class, 'cashDetails'])->name('expense.cash-details');
    Route::resource('/expense', ExpenseController::class);

    // Transaltions route for React component
    Route::get('/locale/{type}', function ($type) {
        $translations = trans($type);
        return response()->json($translations);
    });

    Route::get('/sales/print/{id}', [SaleController::class, 'print'])->name('sales.print');
    Route::get('/purchase/print/{id}', [PurchaseController::class, 'print'])->name('purchase.print');
    Route::get('/branch/list', [SettingController::class, 'branchList'])->name('branch.list');
    Route::post('/branch/store', [SettingController::class, 'branchStore'])->name('branch.store');
    Route::post('/user/store', [SettingController::class, 'userStore'])->name('user.store');
});
