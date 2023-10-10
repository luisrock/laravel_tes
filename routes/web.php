<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConceptController;
use SebastianBergmann\Template\Template;
use Spatie\Honeypot\ProtectAgainstSpam;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;


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
    'register' => false,
]);

//enable honeypot
Route::middleware(ProtectAgainstSpam::class)->group(function () {
    Auth::routes();
});

/**
 * TES web routes
 */

//Busca
Route::get('/', [App\Http\Controllers\SearchPageController::class, 'index'])->name('searchpage');

//Pages for individual tema
Route::get('/tema/{tema?}', [App\Http\Controllers\TemaPageController::class, 'index'])->name('temapage');

//Pages for temas links
Route::get('/temas', [App\Http\Controllers\AllTemasPageController::class, 'index'])->name('alltemaspage');

//Page for Atualizações
Route::get('/atualizacoes', [App\Http\Controllers\AtualizacoesPageController::class, 'index'])->name('atualizacoespage');

//Pages for sumulas
Route::get('/index', function () {
    return view('front.tesindex', ['display_pdf' => false]);
})->name('indexsumulaspage');

Route::get('/sumulas/stf', [App\Http\Controllers\AllStfSumulasPageController::class, 'index'])->name('stfallsumulaspage');
Route::get('/sumulas/stj', [App\Http\Controllers\AllStjSumulasPageController::class, 'index'])->name('stjallsumulaspage');
Route::get('/sumulas/tst', [App\Http\Controllers\AllTstSumulasPageController::class, 'index'])->name('tstallsumulaspage');
Route::get('/sumulas/tnu', [App\Http\Controllers\AllTnuSumulasPageController::class, 'index'])->name('tnuallsumulaspage');
//Pages for individual sumula
Route::get('/sumula/stf/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('stfsumulapage');
Route::get('/sumula/stj/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('stjsumulapage');
Route::get('/sumula/tst/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('tstsumulapage');
Route::get('/sumula/tnu/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('tnusumulapage');

//Pages for teses
Route::get('/teses/stf', [App\Http\Controllers\AllStfTesesPageController::class, 'index'])->name('stfalltesespage');
Route::get('/teses/stj', [App\Http\Controllers\AllStjTesesPageController::class, 'index'])->name('stjalltesespage');

//Pages for individual tese
Route::get('/tese/stf/{tese?}', [App\Http\Controllers\TesePageController::class, 'index'])->name('stftesepage');
Route::get('/tese/stj/{tese?}', [App\Http\Controllers\TesePageController::class, 'index'])->name('stjtesepage');


//Ajax requests admin
Route::post('/admin-ajax-request', [App\Http\Controllers\AjaxController::class, 'adminstore'])->name('adminstore');
Route::post('/admin-ajax-request-del', [App\Http\Controllers\AjaxController::class, 'admindel'])->name('admindel');
Route::post('/admin-ajax-request-similarity', [App\Http\Controllers\AjaxController::class, 'searchByKeywordSimilarity'])->name('searchByKeywordSimilarity');
Route::get('/admin-ajax-request-get-id', [App\Http\Controllers\AjaxController::class, 'getidbykeyword'])->name('getidbykeyword');


// Rota AJAX para gerar conceitos
Route::post('/generate-concept', [ConceptController::class, 'generateConcept'])->name('generate-concept');

// Rotas AJAX para validar, editar e remover conceitos
Route::post('/validate-concept', [ConceptController::class, 'validateConcept'])->name('validate-concept');
Route::post('/edit-concept', [ConceptController::class, 'editConcept'])->name('edit-concept');
Route::post('/remove-concept', [ConceptController::class, 'removeConcept'])->name('remove-concept');
//Salvar depois de gerar com OpenAI
Route::post('/save-concept', [ConceptController::class, 'saveConcept'])->name('save-concept');



// Roles and Permissions
// Route::resource('roles', RoleController::class);
// Route::resource('permissions', PermissionController::class);


Route::prefix('admin')->group(function () {
    Route::middleware(['admin_access:manage_all,manage_users'])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('users', UserController::class);
    });

    // Add other routes with different permissions requirements
    Route::middleware(['admin_access:manage_all'])->group(function () {
        Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('admin');
    });
});