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

// Making the laravel endpoint compatible with the old endpoint, called by the chrome extension
// When the extension is updated, we can set the '/' endpoint
Route::post('/stf.php', [App\Http\Controllers\ApiController::class, 'index']);
Route::post('/tst.php', [App\Http\Controllers\ApiController::class, 'index']);
Route::post('/stj.php', [App\Http\Controllers\ApiController::class, 'index']);
Route::post('/tcu.php', [App\Http\Controllers\ApiController::class, 'index']);
Route::post('/tnu.php', [App\Http\Controllers\ApiController::class, 'index']);
Route::post('/carf.php', [App\Http\Controllers\ApiController::class, 'index']);
Route::post('/fonaje.php', [App\Http\Controllers\ApiController::class, 'index']);
Route::post('/cej.php', [App\Http\Controllers\ApiController::class, 'index']);

// New endpoints for individual sumulas and teses
Route::middleware('bearer.token')->group(function () {
    Route::get('/sumula/{tribunal}/{numero}', [App\Http\Controllers\ApiController::class, 'getSumula']);
    Route::get('/tese/{tribunal}/{numero}', [App\Http\Controllers\ApiController::class, 'getTese']);
    Route::post('/tese/{tribunal}/{numero}', [App\Http\Controllers\ApiController::class, 'updateTese']);
    Route::delete('/tese/{tribunal}/{numero}/tese_texto', [App\Http\Controllers\ApiController::class, 'deleteTeseTexto']);
    Route::get('/random-themes', [App\Http\Controllers\ApiController::class, 'getRandomThemes']);
    Route::get('/random-themes/{limit}', [App\Http\Controllers\ApiController::class, 'getRandomThemes']);
    Route::get('/random-themes/{limit}/{min_judgments}', [App\Http\Controllers\ApiController::class, 'getRandomThemes']);

    // Newsletter API
    Route::get('/newsletters', [App\Http\Controllers\NewsletterApiController::class, 'index']);

    // Quiz API
    Route::prefix('quizzes')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\QuizApiController::class, 'index']);
        Route::get('/categories', [App\Http\Controllers\Api\QuizApiController::class, 'categories']);
        Route::get('/{identifier}', [App\Http\Controllers\Api\QuizApiController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Api\QuizApiController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\QuizApiController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\QuizApiController::class, 'destroy']);
        Route::post('/{quizId}/questions', [App\Http\Controllers\Api\QuizApiController::class, 'addQuestion']);
        Route::delete('/{quizId}/questions/{questionId}', [App\Http\Controllers\Api\QuizApiController::class, 'removeQuestion']);
        Route::put('/{quizId}/questions/reorder', [App\Http\Controllers\Api\QuizApiController::class, 'reorderQuestions']);
    });

    // Questions API
    Route::prefix('questions')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\QuestionApiController::class, 'index']);
        Route::get('/search', [App\Http\Controllers\Api\QuestionApiController::class, 'search']);
        Route::get('/tags', [App\Http\Controllers\Api\QuestionApiController::class, 'tags']);
        Route::post('/tags', [App\Http\Controllers\Api\QuestionApiController::class, 'createTag']);
        Route::get('/{id}', [App\Http\Controllers\Api\QuestionApiController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Api\QuestionApiController::class, 'store']);
        Route::post('/bulk', [App\Http\Controllers\Api\QuestionApiController::class, 'bulkStore']);
        Route::put('/{id}', [App\Http\Controllers\Api\QuestionApiController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\QuestionApiController::class, 'destroy']);
    });
});
