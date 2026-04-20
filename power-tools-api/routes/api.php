<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsAdmin;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\ProfileController;

use App\Http\Controllers\Api\Client\HomeController as ClientHomeController;
use App\Http\Controllers\Api\Client\ProductController as ClientProductController;
use App\Http\Controllers\Api\Client\PostController as ClientPostController;
use App\Http\Controllers\Api\Client\ContactController as ClientContactController;
use App\Http\Controllers\Api\Client\SearchController as ClientSearchController;
use App\Http\Controllers\Api\Client\CartController as ClientCartController;
use App\Http\Controllers\Api\Client\CheckoutController as ClientCheckoutController;
use App\Http\Controllers\Api\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Api\Client\MemberController as ClientMemberController;
use App\Http\Controllers\Api\Client\ReviewController as ClientReviewController;
use App\Http\Controllers\Api\Client\PageController as ClientPageController;

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Api\Admin\StockReceiptController as AdminStockReceiptController;
use App\Http\Controllers\Api\Admin\InventoryLogController as AdminInventoryLogController;
use App\Http\Controllers\Api\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\PostTopicController as AdminPostTopicController;
use App\Http\Controllers\Api\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Api\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Api\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Api\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\Admin\AttributeController as AdminAttributeController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PUBLIC CLIENT ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/home', [ClientHomeController::class, 'index']);

    Route::prefix('products')->group(function () {
        Route::get('/', [ClientProductController::class, 'index']);
        Route::get('/{slug}', [ClientProductController::class, 'show']);
    });

    Route::prefix('posts')->group(function () {
        Route::get('/', [ClientPostController::class, 'index']);
        Route::get('/{slug}', [ClientPostController::class, 'show']);
    });

    Route::prefix('pages')->group(function () {
        Route::get('/{slug}', [ClientPageController::class, 'show']);
    });

    Route::get('/search', [ClientSearchController::class, 'index']);

    Route::post('/contact', [ClientContactController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | CART / CHECKOUT
    |--------------------------------------------------------------------------
    | Giỏ hàng có thể hoạt động độc lập. Nếu sau này muốn bắt buộc đăng nhập
    | khi checkout thì chuyển route /checkout vào nhóm auth:sanctum bên dưới.
    |--------------------------------------------------------------------------
    */
    Route::prefix('cart')->group(function () {
        Route::get('/', [ClientCartController::class, 'show']);
        Route::post('/items', [ClientCartController::class, 'addItem']);
        Route::put('/items/{cartItem}', [ClientCartController::class, 'updateItem']);
        Route::patch('/items/{cartItem}', [ClientCartController::class, 'updateItem']);
        Route::delete('/items/{cartItem}', [ClientCartController::class, 'removeItem']);
        Route::delete('/clear', [ClientCartController::class, 'clear']);
    });

    Route::post('/checkout', [ClientCheckoutController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | MEMBER / PROFILE ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PROFILE
        |--------------------------------------------------------------------------
        */
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::patch('/', [ProfileController::class, 'update']);
        });

        Route::put('/change-password', [PasswordController::class, 'update']);
        Route::patch('/change-password', [PasswordController::class, 'update']);

        /*
        |--------------------------------------------------------------------------
        | MEMBER CENTER
        |--------------------------------------------------------------------------
        */
        Route::prefix('account')->group(function () {
            Route::get('/', [ClientMemberController::class, 'index']);

            Route::prefix('orders')->group(function () {
                Route::get('/', [ClientOrderController::class, 'index']);
                Route::get('/{code}', [ClientOrderController::class, 'show']);
                Route::patch('/{code}/cancel', [ClientOrderController::class, 'cancel']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | REVIEW
        |--------------------------------------------------------------------------
        */
        Route::post('/reviews', [ClientReviewController::class, 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->middleware(['auth:sanctum', EnsureUserIsAdmin::class])
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | DASHBOARD
            |--------------------------------------------------------------------------
            */
            Route::get('/dashboard', [AdminDashboardController::class, 'index']);

            /*
            |--------------------------------------------------------------------------
            | BRANDS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('brands', AdminBrandController::class);

            /*
            |--------------------------------------------------------------------------
            | CATEGORIES
            |--------------------------------------------------------------------------
            */
            Route::apiResource('categories', AdminCategoryController::class);

            /*
            |--------------------------------------------------------------------------
            | ATTRIBUTES
            |--------------------------------------------------------------------------
            */
            Route::apiResource('attributes', AdminAttributeController::class);

            /*
            |--------------------------------------------------------------------------
            | PRODUCTS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('products', AdminProductController::class);

            Route::prefix('products/{product}')->group(function () {
                Route::post('/images', [AdminProductImageController::class, 'store']);

                // Thuộc tính sản phẩm
                Route::get('/attributes', [AdminAttributeController::class, 'productAttributes']);
                Route::put('/attributes', [AdminAttributeController::class, 'syncProductAttributes']);
                Route::patch('/attributes', [AdminAttributeController::class, 'syncProductAttributes']);
            });

            Route::prefix('product-images')->group(function () {
                Route::delete('/{productImage}', [AdminProductImageController::class, 'destroy']);
                Route::patch('/{productImage}/set-primary', [AdminProductImageController::class, 'setPrimary']);
            });

            /*
            |--------------------------------------------------------------------------
            | STOCK / INVENTORY
            |--------------------------------------------------------------------------
            */
            Route::apiResource('stock-receipts', AdminStockReceiptController::class)
                ->only(['index', 'store', 'show']);

            Route::apiResource('inventory-logs', AdminInventoryLogController::class)
                ->only(['index', 'show']);

            /*
            |--------------------------------------------------------------------------
            | PROMOTIONS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('promotions', AdminPromotionController::class);

            /*
            |--------------------------------------------------------------------------
            | ORDERS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('orders', AdminOrderController::class)
                ->only(['index', 'show', 'destroy']);

            Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

            /*
            |--------------------------------------------------------------------------
            | POSTS / POST TOPICS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('posts', AdminPostController::class);
            Route::apiResource('post-topics', AdminPostTopicController::class);

            /*
            |--------------------------------------------------------------------------
            | CONTACTS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('contacts', AdminContactController::class)
                ->only(['index', 'show', 'destroy']);

            Route::patch('/contacts/{contact}/reply', [AdminContactController::class, 'reply']);

            /*
            |--------------------------------------------------------------------------
            | BANNERS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('banners', AdminBannerController::class);
            Route::patch('/banners/{banner}/sort-order', [AdminBannerController::class, 'updateSortOrder']);

            /*
            |--------------------------------------------------------------------------
            | MEMBERS
            |--------------------------------------------------------------------------
            */
            Route::apiResource('members', AdminMemberController::class);

            /*
            |--------------------------------------------------------------------------
            | SETTINGS
            |--------------------------------------------------------------------------
            */
            Route::get('/settings', [AdminSettingController::class, 'index']);
            Route::put('/settings', [AdminSettingController::class, 'update']);
            Route::patch('/settings', [AdminSettingController::class, 'update']);
        });
});