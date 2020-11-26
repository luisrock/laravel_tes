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


Route::post('/', [App\Http\Controllers\ApiController::class, 'index'])->name('api');

//Making the laravel endpoint compatible with the old endpoint, called by the chrome extension
//When the extension is updated, we can set the '/' endpoint
Route::post('/stf.php',[App\Http\Controllers\ApiController::class, 'index']);
Route::post('/tst.php',[App\Http\Controllers\ApiController::class, 'index']);
Route::post('/stj.php',[App\Http\Controllers\ApiController::class, 'index']);
Route::post('/tcu.php',[App\Http\Controllers\ApiController::class, 'index']);
Route::post('/tnu.php',[App\Http\Controllers\ApiController::class, 'index']);
Route::post('/carf.php',[App\Http\Controllers\ApiController::class, 'index']);
Route::post('/fonaje.php',[App\Http\Controllers\ApiController::class, 'index']);
Route::post('/cej.php',[App\Http\Controllers\ApiController::class, 'index']);


