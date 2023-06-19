<?php

use App\Http\Controllers\MemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth')->group(function() {
    Route::get('/user', function (Request $request) { return $request->user(); });

    Route::get('/members/{member}', [MemberController::class, 'show']);
    Route::post('/members/{member}/actions/visit', [MemberController::class, 'visit']);
    Route::get('/members/{member}/actions/promptprolongation', [MemberController::class, 'promptprolongation']);
    Route::post('/members/{member}/actions/prolong', [MemberController::class, 'prolong']);
});
