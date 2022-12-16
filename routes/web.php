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

//Auth::routes(); //with registering

//No registering, by now.
Auth::routes([
    'register' => false
]);


Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index'])->name('admin');

/**
 * TES web routes
 */

//Busca
 Route::get('/', [App\Http\Controllers\SearchPageController::class, 'index'])->name('searchpage');

//Pages for individual tema
Route::get('/tema/{tema?}', [App\Http\Controllers\TemaPageController::class, 'index'])->name('temapage');

//Pages for temas links
Route::get('/temas', [App\Http\Controllers\AllTemasPageController::class, 'index'])->name('alltemaspage');

//Ajax requests admin
Route::post('/admin-ajax-request', [App\Http\Controllers\AjaxController::class, 'adminstore'])->name('adminstore');
Route::post('/admin-ajax-request-del', [App\Http\Controllers\AjaxController::class, 'admindel'])->name('admindel');
Route::post('/admin-ajax-request-similarity', [App\Http\Controllers\AjaxController::class, 'searchByKeywordSimilarity'])->name('searchByKeywordSimilarity');
Route::get('/admin-ajax-request-get-id', [App\Http\Controllers\AjaxController::class, 'getidbykeyword'])->name('getidbykeyword');
