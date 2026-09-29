<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
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
| Protected POS System
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

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
    | Products / Services
    |--------------------------------------------------------------------------
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

});