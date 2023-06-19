<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

// Route::get('/test', function(Request $request) {
//     return rand(0,20) . ' ' . rand(0,4);
// });

Auth::routes(['register' => false]);


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members/store', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}/update', [MemberController::class, 'update'])->name('members.update');

    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    Route::post('/visits/store', [VisitController::class, 'store'])->name('visits.store');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}/update', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}/delete', [ProductController::class, 'delete'])->name('products.delete');

    Route::get('/sales', [PaymentController::class, 'index'])->name('sales.index');
    Route::post('/sales/store', [PaymentController::class, 'store'])->name('sales.store');
    Route::put('/sales/{payment}/update', [PaymentController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{payment}/delete', [PaymentController::class, 'delete'])->name('sales.delete');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/{group}/update', [SettingsController::class, 'update'])->name('settings.update');

    Route::prefix('/session_api')->name('webapi.')->group(function () {
        Route::get('/members', [MemberController::class, 'apiIndex'])->name('members.index');
    });
});
