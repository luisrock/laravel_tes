<?php

use App\Http\Controllers\ConceptController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RefundRequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPanelController;
use App\Http\Controllers\WebhookController;
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
| Autenticação: Laravel Fortify (login, registro, reset senha, 2FA, etc.)
|
*/

/**
 * TES web routes
 */

// Busca
Route::get('/', [App\Http\Controllers\SearchPageController::class, 'index'])->name('searchpage');

// Pages for individual tema
Route::get('/tema/{tema?}', [App\Http\Controllers\TemaPageController::class, 'index'])->name('temapage');

// Pages for temas links
Route::get('/temas', [App\Http\Controllers\AllTemasPageController::class, 'index'])->name('alltemaspage');

// Page for Atualizações
// desativada para os visitantes. Por ora, só eu irei usar
Route::get('/atualizacoes', [App\Http\Controllers\AtualizacoesPageController::class, 'index'])->name('atualizacoespage');

// Page to thank the user for sending email for newsletter (proofcourse)
Route::get('/newsletter-obrigado', [App\Http\Controllers\NewsletterPageController::class, 'index'])->name('newsletterobrigadopage');

// Página e envio de contato
Route::get('/contato', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// Pages for sumulas
Route::get('/index', function () {
    return view('front.tesindex', ['display_pdf' => false]);
})->name('indexsumulaspage');

Route::get('/sumulas/stf', [App\Http\Controllers\AllStfSumulasPageController::class, 'index'])->name('stfallsumulaspage');
Route::get('/sumulas/stj', [App\Http\Controllers\AllStjSumulasPageController::class, 'index'])->name('stjallsumulaspage');
Route::get('/sumulas/tst', [App\Http\Controllers\AllTstSumulasPageController::class, 'index'])->name('tstallsumulaspage');
Route::get('/sumulas/tnu', [App\Http\Controllers\AllTnuSumulasPageController::class, 'index'])->name('tnuallsumulaspage');
// Pages for individual sumula
Route::get('/sumula/stf/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('stfsumulapage');
Route::get('/sumula/stj/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('stjsumulapage');
Route::get('/sumula/tst/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('tstsumulapage');
Route::get('/sumula/tnu/{sumula?}', [App\Http\Controllers\SumulaPageController::class, 'index'])->name('tnusumulapage');

// Pages for teses
Route::get('/teses/stf', [App\Http\Controllers\AllStfTesesPageController::class, 'index'])->name('stfalltesespage');
Route::get('/teses/stj', [App\Http\Controllers\AllStjTesesPageController::class, 'index'])->name('stjalltesespage');
Route::get('/teses/tst', [App\Http\Controllers\AllTstTesesPageController::class, 'index'])->name('tstalltesespage');

// Pages for individual tese
Route::get('/tese/stf/{tese?}', [App\Http\Controllers\TesePageController::class, 'index'])->name('stftesepage');
Route::get('/tese/stj/{tese?}', [App\Http\Controllers\TesePageController::class, 'index'])->name('stjtesepage');
Route::get('/tese/tst/{tese?}', [App\Http\Controllers\TesePageController::class, 'index'])->name('tsttesepage');

// Ajax requests admin
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
// Salvar depois de gerar com OpenAI
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
        Route::get('/temas', [App\Http\Controllers\HomeController::class, 'temas'])->name('admin.temas');
        Route::get('/get-temas', [App\Http\Controllers\HomeController::class, 'getTemas'])->name('admin.getTemas');
    });
});

Route::get('/newsletters', [App\Http\Controllers\CampaignsPageController::class, 'index'])->name('newsletterspage');
Route::get('/newsletter/{slug}', [App\Http\Controllers\NewsletterController::class, 'show'])->name('newsletter.show');

// Quiz Public Routes
Route::get('/quizzes', [App\Http\Controllers\QuizController::class, 'index'])->name('quizzes.index');
Route::get('/quizzes/categoria/{categorySlug}', [App\Http\Controllers\QuizController::class, 'byCategory'])->name('quizzes.category');
Route::get('/quiz/{slug}', [App\Http\Controllers\QuizController::class, 'show'])->name('quiz.show');
Route::post('/quiz/{quiz:slug}/answer', [App\Http\Controllers\QuizController::class, 'submitAnswer'])->name('quiz.answer');
Route::get('/quiz/{slug}/resultado', [App\Http\Controllers\QuizController::class, 'results'])->name('quiz.results');
Route::get('/quiz/{slug}/reiniciar', [App\Http\Controllers\QuizController::class, 'restart'])->name('quiz.restart');

// Editable Content Routes
Route::get('/{slug}', [App\Http\Controllers\EditableContentController::class, 'show'])
    ->where('slug', 'precedentes-vinculantes-cpc')
    ->name('content.show');

Route::middleware(['admin_access:manage_all'])->group(function () {
    Route::get('/admin/content/{slug}/edit', [App\Http\Controllers\EditableContentController::class, 'edit'])
        ->name('content.edit');
    Route::put('/admin/content/{slug}', [App\Http\Controllers\EditableContentController::class, 'update'])
        ->name('content.update');

    // Quiz Admin Routes
    Route::prefix('admin/quizzes')->name('admin.quizzes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\QuizAdminController::class, 'index'])->name('index');
        Route::post('/toggle-home', [App\Http\Controllers\Admin\QuizAdminController::class, 'toggleHomeVisibility'])->name('toggle-home');
        Route::get('/create', [App\Http\Controllers\Admin\QuizAdminController::class, 'create'])->name('create');
        Route::get('/stats', [App\Http\Controllers\Admin\QuizStatsController::class, 'index'])->name('stats');
        Route::get('/stats/export', [App\Http\Controllers\Admin\QuizStatsController::class, 'export'])->name('stats.export');
        Route::get('/stats/{quiz}', [App\Http\Controllers\Admin\QuizStatsController::class, 'quiz'])->name('stats.quiz');
        Route::post('/', [App\Http\Controllers\Admin\QuizAdminController::class, 'store'])->name('store');
        Route::get('/{quiz}/edit', [App\Http\Controllers\Admin\QuizAdminController::class, 'edit'])->name('edit');
        Route::put('/{quiz}', [App\Http\Controllers\Admin\QuizAdminController::class, 'update'])->name('update');
        Route::delete('/{quiz}', [App\Http\Controllers\Admin\QuizAdminController::class, 'destroy'])->name('destroy');
        Route::get('/{quiz}/duplicate', [App\Http\Controllers\Admin\QuizAdminController::class, 'duplicate'])->name('duplicate');
        Route::get('/{quiz}/questions', [App\Http\Controllers\Admin\QuizAdminController::class, 'questions'])->name('questions');
        Route::post('/{quiz}/questions', [App\Http\Controllers\Admin\QuizAdminController::class, 'addQuestion'])->name('questions.add');
        Route::delete('/{quiz}/questions/{question}', [App\Http\Controllers\Admin\QuizAdminController::class, 'removeQuestion'])->name('questions.remove');
        Route::post('/{quiz}/questions/reorder', [App\Http\Controllers\Admin\QuizAdminController::class, 'reorderQuestions'])->name('questions.reorder');
        Route::get('/{quiz}/questions/search', [App\Http\Controllers\Admin\QuizAdminController::class, 'searchQuestions'])->name('questions.search');
    });

    // Questions Admin Routes
    Route::prefix('admin/questions')->name('admin.questions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\QuestionAdminController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\QuestionAdminController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\QuestionAdminController::class, 'store'])->name('store');
        Route::post('/inline', [App\Http\Controllers\Admin\QuestionAdminController::class, 'storeInline'])->name('store.inline');
        Route::get('/{question}/edit', [App\Http\Controllers\Admin\QuestionAdminController::class, 'edit'])->name('edit');
        Route::put('/{question}', [App\Http\Controllers\Admin\QuestionAdminController::class, 'update'])->name('update');
        Route::delete('/{question}', [App\Http\Controllers\Admin\QuestionAdminController::class, 'destroy'])->name('destroy');
        Route::get('/{question}/duplicate', [App\Http\Controllers\Admin\QuestionAdminController::class, 'duplicate'])->name('duplicate');
        Route::get('/tags', [App\Http\Controllers\Admin\QuestionAdminController::class, 'tags'])->name('tags');
        Route::post('/tags', [App\Http\Controllers\Admin\QuestionAdminController::class, 'storeTag'])->name('tags.store');
        Route::delete('/tags/{tag}', [App\Http\Controllers\Admin\QuestionAdminController::class, 'destroyTag'])->name('tags.destroy');
    });

    // Acórdãos Admin Routes (Análise do Precedente)
    Route::prefix('admin/acordaos')->name('admin.acordaos.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AcordaoAdminController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\AcordaoAdminController::class, 'store'])->name('store');
        Route::delete('/{acordao}', [App\Http\Controllers\Admin\AcordaoAdminController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Subscription Routes (Sistema de Assinaturas)
|--------------------------------------------------------------------------
*/

// Página de planos (pública)
Route::middleware('subscription.configured')->group(function () {
    Route::get('/assinar', [SubscriptionController::class, 'index'])->name('subscription.plans');

    // Checkout (requer auth)
    Route::middleware('auth')->group(function () {
        Route::post('/assinar/checkout', [SubscriptionController::class, 'checkout'])
            ->name('subscription.checkout');
    });

    // Callbacks do Stripe Checkout (sem auth para evitar perda de sessão)
    Route::get('/assinar/sucesso', [SubscriptionController::class, 'success'])
        ->name('subscription.success');
    Route::get('/assinar/cancelado', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');

    // AJAX para verificar status do checkout
    Route::get('/assinar/status', [SubscriptionController::class, 'checkProcessingStatus'])
        ->name('subscription.check-status');
});

// Webhook Stripe (sem CSRF - configurado em VerifyCsrfToken.php)
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->middleware('stripe.webhook')
    ->name('cashier.webhook');

// Rotas autenticadas do painel do usuário (minha-conta)
Route::middleware(['auth', 'verified', 'subscription.configured'])->prefix('minha-conta')->group(function () {
    Route::get('/', [UserPanelController::class, 'dashboard'])->name('user-panel.dashboard');
    Route::get('/perfil', [UserPanelController::class, 'profile'])->name('user-panel.profile');
    Route::get('/assinatura', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::get('/assinatura/portal', [SubscriptionController::class, 'billingPortal'])->name('subscription.portal');
    Route::get('/estorno', [RefundRequestController::class, 'create'])->name('refund.create');
    Route::post('/estorno', [RefundRequestController::class, 'store'])->name('refund.store');
});
