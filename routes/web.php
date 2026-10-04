<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SidebarSettingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SuperadminSetupController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [
    AuthController::class,
    'showLogin',
])->name('login');

Route::post('/login', [
    AuthController::class,
    'login',
])->name('login.process');

Route::post('/logout', [
    AuthController::class,
    'logout',
])->name('logout');

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {
        return redirect()->route(auth()->user()->isSuperadmin() ? 'settings.edit' : 'dashboard');
    }

    return redirect()->route('login');

});

/*
|--------------------------------------------------------------------------
| Authenticated Users
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Administrator,Cashier')->group(function () {
        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | POS
        |--------------------------------------------------------------------------
        */

        Route::get('/pos', [
            PosController::class,
            'index',
        ])->name('pos.index');

        Route::post('/pos/sale', [
            PosController::class,
            'store',
        ])->name('pos.store');

        /*
        |--------------------------------------------------------------------------
        | Sales
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'sales',
            SaleController::class
        )->only([
            'index',
            'show',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Administrator Only
        |--------------------------------------------------------------------------
        */

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('role:Administrator,Superadmin')->group(function () {
        Route::resource('stores', StoreController::class)->only(['index', 'store', 'update']);

        /*
        | Products / Services
        */

        Route::middleware('role:Administrator')->group(function () {
            Route::resource(
                'products',
                ProductController::class
            );

            Route::patch(
                '/products/{product}/toggle',
                [
                    ProductController::class,
                    'toggle',
                ]
            )->name('products.toggle');
        });

        /*
        | Users
        */

        Route::resource(
            'users',
            UserController::class
        )->except([
            'show',
        ]);

        Route::patch(
            '/users/{user}/toggle',
            [
                UserController::class,
                'toggle',
            ]
        )->name('users.toggle');

        /*
        | Reports
        */

    });

    Route::middleware('role:Superadmin')->group(function () {
        Route::get('/settings/sidebar', [SidebarSettingController::class, 'edit'])->name('settings.sidebar.edit');
        Route::put('/settings/sidebar', [SidebarSettingController::class, 'update'])->name('settings.sidebar.update');
        Route::delete('/settings/sidebar', [SidebarSettingController::class, 'destroy'])->name('settings.sidebar.destroy');
        Route::get('/settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
    });
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/superadmin/setup', [SuperadminSetupController::class, 'create'])->name('superadmin.setup');
        Route::post('/superadmin/setup', [SuperadminSetupController::class, 'store'])->middleware('throttle:5,1')->name('superadmin.setup.store');
    });

});
