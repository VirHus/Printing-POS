<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [
    AuthController::class,
    'showLogin'
])->name('login');

Route::post('/login', [
    AuthController::class,
    'login'
])->name('login.process');

Route::post('/logout', [
    AuthController::class,
    'logout'
])->name('logout');


/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {
        return redirect()->route('dashboard');
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

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | POS
    |--------------------------------------------------------------------------
    */

    Route::get('/pos', [
        PosController::class,
        'index'
    ])->name('pos.index');

    Route::post('/pos/sale', [
        PosController::class,
        'store'
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
        'show'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Administrator Only
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Administrator')->group(function () {

        /*
        | Products / Services
        */

        Route::resource(
            'products',
            ProductController::class
        );

        Route::patch(
            '/products/{product}/toggle',
            [
                ProductController::class,
                'toggle'
            ]
        )->name('products.toggle');


        /*
        | Users
        */

        Route::resource(
            'users',
            UserController::class
        )->except([
            'show'
        ]);

        Route::patch(
            '/users/{user}/toggle',
            [
                UserController::class,
                'toggle'
            ]
        )->name('users.toggle');


        /*
        | Reports
        */

        Route::get('/reports', [
            ReportController::class,
            'index'
        ])->name('reports.index');

    });

});