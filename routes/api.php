<?php

use App\Http\Controllers\api\AjustementController;
use App\Http\Controllers\api\AlertController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\DashbordController;
use App\Http\Controllers\api\ExportController;
use App\Http\Controllers\api\ImportController;
use App\Http\Controllers\api\InventaryController;
use App\Http\Controllers\api\ModePayemntController;
use App\Http\Controllers\api\OrderPurchaseController;
use App\Http\Controllers\api\OrderSaleController;
use App\Http\Controllers\api\OwnersController;
use App\Http\Controllers\api\PayemntController;
use App\Http\Controllers\api\PlanController;
use App\Http\Controllers\api\PriceChangeController;
use App\Http\Controllers\api\PrintProfileController;
use App\Http\Controllers\api\ProductComponentController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\ReportController;
use App\Http\Controllers\api\SettingController;
use App\Http\Controllers\api\StoreController;
use App\Http\Controllers\api\StoreProductsController;
use App\Http\Controllers\api\SuppliersController;
use App\Http\Controllers\api\TransfertController;
use App\Http\Controllers\api\UnitController;
use App\Http\Controllers\api\UserController;
use App\Http\Middleware\EnsureTrialIsValid;
use Illuminate\Support\Facades\Route;

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);


// Public plan routes
Route::get('/plans', [PlanController::class, 'index']);
Route::get('/plans/{plan}', [PlanController::class, 'show']);

Route::middleware(['auth:sanctum', EnsureTrialIsValid::class])->group(function (): void {

    Route::get('/dashboard', [DashbordController::class, 'Dashbord']);

    Route::post('/logout', [UserController::class, 'logout']);

    // Plan related routes
    Route::get('/user/plan', [PlanController::class, 'getUserPlan']);

    Route::resource('/users', UserController::class);
    Route::get('/users/{id}/sales', [UserController::class, 'sales']);
    Route::post('/users/{id}/change-password', [UserController::class, 'changePassword']);

    Route::resource('/products', ProductController::class);

    Route::get('/pos', [StoreProductsController::class, 'index']);


    Route::resource('/customers', CustomerController::class);
    Route::resource('/orders', OrderSaleController::class);

    Route::resource('/categories', CategoryController::class);
    Route::resource('/suppliers', SuppliersController::class);

    Route::resource('/purchases', OrderPurchaseController::class);
    Route::post('/purchases/import', [OrderPurchaseController::class, 'import']);
    Route::put('/purchases/{id}/approve', [OrderPurchaseController::class, 'approve']);
    Route::put('/purchases/{id}/cancel', [OrderPurchaseController::class, 'cancel']);

    Route::post('/addPaymentToOrder/{id}', [OrderSaleController::class, 'addPaymentToOrder']);
    Route::post('/updateToInvoice/{id}', [OrderSaleController::class, 'updateToInvoice']);
    Route::put('/orders/{id}/cancel', [OrderSaleController::class, 'cancel']);
    Route::get('/caisse', [PayemntController::class, 'caisse']);

    Route::resource('/paid_methods', ModePayemntController::class);
    Route::apiResource('/stores', StoreController::class, ['index', 'update']);

    // Units
    Route::apiResource('/units', UnitController::class);

    // Print Profiles
    Route::apiResource('/print-profiles', PrintProfileController::class);

    // Product Components (BOM / Composants)
    Route::prefix('products/{product}/components')->group(function () {
        Route::get('/', [ProductComponentController::class, 'index']);
        Route::post('/', [ProductComponentController::class, 'store']);
        Route::put('/{component}', [ProductComponentController::class, 'update']);
        Route::delete('/{component}', [ProductComponentController::class, 'destroy']);
    });

    // Price Changes
    Route::post('/price-changes', [PriceChangeController::class, 'store']);

    // Settings
    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('settings/{id}', [SettingController::class, 'update']);
    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/',         [ReportController::class, 'index']);
        Route::get('/sales-by-annexe',         [ReportController::class, 'salesByAnnexe']);
        Route::get('/orders',         [ReportController::class, 'OrderList']);
        Route::get('/daily-category',         [ReportController::class, 'dailyCategoryReport']);
    });

    // Product Import (upload CSV/XLSX from supplier)
    Route::prefix('imports')->group(function () {
        Route::get('/',              [ImportController::class, 'index']);
        Route::post('/upload',       [ImportController::class, 'upload']);
        Route::get('/{id}',          [ImportController::class, 'show']);
        Route::put('/{id}/commit',   [ImportController::class, 'commit']);
    });


    // Store Products Routes
    Route::prefix('store-products')->group(function () {
        Route::get('/', [StoreProductsController::class, 'index']);
        Route::get('/{id}', [StoreProductsController::class, 'show']);
        Route::post('/', [StoreProductsController::class, 'store']);
        Route::put('/{id}', [StoreProductsController::class, 'update']);
        Route::delete('/{id}', [StoreProductsController::class, 'destroy']);
    });

    // // Stock Management Routes
    Route::prefix('store')->group(function () {
        Route::resource('/transfers', TransfertController::class);
        Route::put('/transfers/{id}/approve', [TransfertController::class, 'approve']);
        Route::put('/transfers/{id}/cancel', [TransfertController::class, 'cancel']);

        Route::resource('/adjustments', AjustementController::class);
        Route::put('/adjustments/{id}/approve', [AjustementController::class, 'approve']);
        Route::put('/adjustments/{id}/cancel', [AjustementController::class, 'cancel']);

        Route::resource('/inventories', InventaryController::class);
        Route::put('/inventories/{id}/start', [InventaryController::class, 'start']);
        Route::put('/inventories/{id}/items/{itemId}', [InventaryController::class, 'updateItem']);
        Route::put('/inventories/{id}/complete', [InventaryController::class, 'complete']);
        Route::put('/inventories/{id}/cancel', [InventaryController::class, 'cancel']);
    });
    // Alert Management Routes
    Route::prefix('alerts')->group(function () {
        Route::get('/', [AlertController::class, 'index']);
        Route::get('/stats', [AlertController::class, 'stats']);
        Route::get('/{alert}', [AlertController::class, 'show']);
        Route::put('/{alert}/read', [AlertController::class, 'markAsRead']);
        Route::put('/{alert}/resolve', [AlertController::class, 'markAsResolved']);
        Route::post('/generate', [AlertController::class, 'generate']);
        Route::post('/bulk-read', [AlertController::class, 'bulkMarkAsRead']);
        Route::post('/bulk-resolve', [AlertController::class, 'bulkMarkAsResolved']);
    });

    // Data Export Route (for owners only)
    Route::post('/export/data', [ExportController::class, 'exportStoreData']);

    // i want to add route but role should have manger to access it
    Route::middleware('role:super_admin')->group(function () {
        Route::prefix('super_admin')->group(function () {
            Route::resource('/owners', OwnersController::class);
            Route::post('/owners/{id}/change-plan', [OwnersController::class, 'changePlan']);
            Route::post('/owners/{id}/suspend', [OwnersController::class, 'suspend']);
            Route::post('/owners/{id}/activate', [OwnersController::class, 'activate']);
        });
    });
});
