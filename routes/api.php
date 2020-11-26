<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * TES api route
 */

//Route::post('/', [App\Http\Controllers\ApiController::class, 'index'])->name('api');

//Making the laravel endpoint compatible with the old endpoint, called by the chrome extension
//When the extension is updated, we can set the '/' endpoint
Route::post('/{what?}', [App\Http\Controllers\ApiController::class, 'index'])->name('api');

