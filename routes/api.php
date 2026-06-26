<?php

use App\Http\Controllers\api\AjustementController;
use App\Http\Controllers\api\AlertController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\DashbordController;
use App\Http\Controllers\api\ExportController;
use App\Http\Controllers\api\ImportController;
use App\Http\Controllers\api\InventaryController;
use App\Http\Controllers\api\MenuCategoryController;
use App\Http\Controllers\api\MenuController;
use App\Http\Controllers\api\MenuItemController;
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
use App\Http\Controllers\api\PurchaseDeliveryController;
use App\Http\Controllers\api\RecipeController;
use App\Http\Controllers\api\ReportController;
use App\Http\Controllers\api\SettingController;
use App\Http\Controllers\api\StoreController;
use App\Http\Controllers\api\StoreProductsController;
use App\Http\Controllers\api\SuppliersController;
use App\Http\Controllers\api\TransfertController;
use App\Http\Controllers\api\UnitController;
use App\Http\Controllers\api\UnitConversionController;
use App\Http\Controllers\api\StockDeductionController;
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

    // Order-specific routes must come before resource route
    Route::get('/orders/cancelled', [OrderSaleController::class, 'getCancelled']);
    Route::get('/orders/canceled', [OrderSaleController::class, 'getCancelled']); // Alias
    Route::put('/orders/{id}/cancel', [OrderSaleController::class, 'cancel']);
    Route::post('/orders/{id}/payment', [OrderSaleController::class, 'addPaymentToOrder']);
    Route::post('/orders/{id}/invoice', [OrderSaleController::class, 'updateToInvoice']);
    Route::post('/restaurant-orders', [OrderSaleController::class, 'createRestaurantOrder']); // Restaurant-specific order creation
    Route::post('/sell-menu-items', [OrderSaleController::class, 'sellMenuItems']); // Sell menu items from POS/restaurant frontend
    Route::resource('/orders', OrderSaleController::class);

    Route::resource('/categories', CategoryController::class);
    Route::resource('/suppliers', SuppliersController::class);

    Route::resource('/purchases', OrderPurchaseController::class);
    Route::post('/purchases/import', [OrderPurchaseController::class, 'import']);
    Route::put('/purchases/{id}/approve', [OrderPurchaseController::class, 'approve']);
    Route::put('/purchases/{id}/cancel', [OrderPurchaseController::class, 'cancel']);

    // Purchase Delivery Routes (Bon de Livraison)
    Route::get('/purchases/{purchaseOrderId}/deliveries', [PurchaseDeliveryController::class, 'indexByPurchaseOrder']);
    Route::post('/purchase-deliveries', [PurchaseDeliveryController::class, 'store']);
    Route::get('/purchase-deliveries/{id}', [PurchaseDeliveryController::class, 'show']);
    Route::post('/purchase-deliveries/{id}/validate', [PurchaseDeliveryController::class, 'validate']);
    Route::post('/purchase-deliveries/{id}/cancel', [PurchaseDeliveryController::class, 'cancel']);

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
        Route::post('/import', [StoreProductsController::class, 'import']);
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

    // Menu Management Routes (Phase 2)
    Route::prefix('menus')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::get('/currently-available', [MenuController::class, 'currentlyAvailable']);
        Route::get('/statistics', [MenuController::class, 'statistics']);
        Route::post('/', [MenuController::class, 'store']);
        Route::get('/{id}', [MenuController::class, 'show']);
        Route::put('/{id}', [MenuController::class, 'update']);
        Route::delete('/{id}', [MenuController::class, 'destroy']);
    });

    Route::prefix('menu-categories')->group(function () {
        Route::get('/', [MenuCategoryController::class, 'index']);
        Route::post('/', [MenuCategoryController::class, 'store']);
        Route::get('/{id}', [MenuCategoryController::class, 'show']);
        Route::put('/{id}', [MenuCategoryController::class, 'update']);
        Route::delete('/{id}', [MenuCategoryController::class, 'destroy']);
    });

    Route::prefix('menu-items')->group(function () {
        Route::get('/', [MenuItemController::class, 'index']);
        Route::get('/by-profitability', [MenuItemController::class, 'byProfitability']);
        Route::post('/', [MenuItemController::class, 'store']);
        Route::get('/{id}', [MenuItemController::class, 'show']);
        Route::put('/{id}', [MenuItemController::class, 'update']);
        Route::delete('/{id}', [MenuItemController::class, 'destroy']);
        Route::get('/{id}/profitability', [MenuItemController::class, 'profitability']);
        Route::post('/{id}/toggle-availability', [MenuItemController::class, 'toggleAvailability']);
    });

    // Recipe Management Routes (Phase 2)
    Route::prefix('recipes')->group(function () {
        Route::get('/', [RecipeController::class, 'index']);
        Route::post('/', [RecipeController::class, 'store']);
        Route::get('/{id}', [RecipeController::class, 'show']);
        Route::put('/{id}', [RecipeController::class, 'update']);
        Route::delete('/{id}', [RecipeController::class, 'destroy']);
        Route::post('/{id}/recalculate-cost', [RecipeController::class, 'recalculateCost']);
        Route::post('/{id}/clone', [RecipeController::class, 'clone']);
        Route::post('/{id}/profitability', [RecipeController::class, 'calculateProfitability']);
        Route::post('/{id}/ingredients', [RecipeController::class, 'addIngredient']);
        Route::put('/{recipeId}/ingredients/{ingredientId}', [RecipeController::class, 'updateIngredient']);
        Route::delete('/{recipeId}/ingredients/{ingredientId}', [RecipeController::class, 'removeIngredient']);
    });
    Route::post('/recipes-recalculate-all', [RecipeController::class, 'recalculateAllCosts']);

    // Unit Conversions (Phase 3)
    Route::prefix('unit-conversions')->group(function () {
        Route::get('/', [UnitConversionController::class, 'index']);
        Route::post('/', [UnitConversionController::class, 'store']);
        Route::put('/{id}', [UnitConversionController::class, 'update']);
        Route::delete('/{id}', [UnitConversionController::class, 'destroy']);
        Route::post('/convert', [UnitConversionController::class, 'convert']);
        Route::post('/create-standard', [UnitConversionController::class, 'createStandard']);
    });

    // Stock Deduction (Phase 3)
    Route::prefix('stock')->group(function () {
        Route::post('/deduct', [StockDeductionController::class, 'deduct']);
        Route::post('/check-availability', [StockDeductionController::class, 'checkAvailability']);
        Route::post('/simulate', [StockDeductionController::class, 'simulate']);
        Route::get('/theoretical-consumption', [StockDeductionController::class, 'getTheoreticalConsumption']);
        Route::get('/variance-report', [StockDeductionController::class, 'getVarianceReport']);
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
