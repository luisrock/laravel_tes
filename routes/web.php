<?php

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

// Route::get('/', function () {
//     return view('welcome');
// });

//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

/**
 * TES web routes
 */

//Home (busca)
 Route::get('/', [App\Http\Controllers\SearchPageController::class, 'index'])->name('searchpage');

//Páginas prontas de temas
Route::get('/tema/{tema?}', [App\Http\Controllers\TemaPageController::class, 'index'])->name('temapage');