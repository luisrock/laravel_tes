<?php

use App\Models\ContentView;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\MySQLTestCase;

$FIXTURE_NUMERO = 30199;

beforeEach(function () use ($FIXTURE_NUMERO) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Permission::findOrCreate('view_ai_analysis', 'web');
    \Spatie\Permission\Models\Permission::findOrCreate('download_acordaos', 'web');
    \Spatie\Permission\Models\Permission::findOrCreate('search', 'web');
    \Spatie\Permission\Models\Permission::findOrCreate('ad_free', 'web');

    $registered = \Spatie\Permission\Models\Role::findOrCreate('registered', 'web');
    $registered->syncPermissions(['view_ai_analysis', 'download_acordaos', 'search', 'ad_free']);

    $admin = \Spatie\Permission\Models\Role::findOrCreate('admin', 'web');
    $admin->syncPermissions(['view_ai_analysis', 'download_acordaos', 'search', 'ad_free']);

    DB::table('content_views')->truncate();
    SiteSetting::set('metered_wall_enabled', '1');
    SiteSetting::set('metered_wall_daily_limit', '3');
    config(['subscription.enabled' => true]);

    DB::table('tese_analysis_sections')->where('tese_id', '>=', $FIXTURE_NUMERO)->delete();
    DB::table('stf_teses')->where('numero', '>=', $FIXTURE_NUMERO)->delete();

    // Criar fixtures: 5 teses STF com conteúdo IA
    $aiModel = \App\Models\AiModel::firstOrCreate(
        ['model_id' => 'gpt-4o-test-metered'],
        [
            'provider' => 'openai',
            'name' => 'GPT-4o Test Metered',
            'price_input_per_million' => 5.0,
            'price_output_per_million' => 15.0,
            'is_active' => true,
        ]
    );

    for ($i = 0; $i < 6; $i++) {
        $num = $FIXTURE_NUMERO + $i;
        DB::table('stf_teses')->insert([
            'id' => $num,
            'numero' => $num,
            'tema_texto' => "[FIXTURE] Tema STF metered wall $num",
            'tese_texto' => "[FIXTURE] Tese STF metered wall $num",
            'situacao' => 'Ativo',
            'relator' => '[FIXTURE]',
            'aprovadaEm' => '2024-06-01',
        ]);

        DB::table('tese_analysis_sections')->insert([
            [
                'tese_id' => $num,
                'tribunal' => 'STF',
                'section_type' => 'teaser',
                'content' => "Teaser da tese $num.",
                'status' => 'published',
                'is_active' => true,
                'ai_model_id' => $aiModel->id,
                'generated_at' => now(),
            ],
            [
                'tese_id' => $num,
                'tribunal' => 'STF',
                'section_type' => 'caso_fatico',
                'content' => "Caso fático da tese $num. Conteúdo premium protegido.",
                'status' => 'published',
                'is_active' => true,
                'ai_model_id' => $aiModel->id,
                'generated_at' => now(),
            ],
        ]);
    }
});

afterEach(function () use ($FIXTURE_NUMERO) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    DB::table('content_views')->truncate();
    DB::table('site_settings')->whereIn('key', ['metered_wall_enabled', 'metered_wall_daily_limit'])->delete();

    for ($i = 0; $i < 6; $i++) {
        $num = $FIXTURE_NUMERO + $i;
        DB::table('tese_analysis_sections')->where('tese_id', $num)->delete();
        DB::table('stf_teses')->where('numero', $num)->delete();
    }
});

// ==========================================
// Visitante (não logado)
// ==========================================

it('visitante nao logado ve registerwall normal sem contagem', function () use ($FIXTURE_NUMERO) {
    $response = $this->get("/tese/stf/{$FIXTURE_NUMERO}");

    $response->assertSuccessful();
    expect(ContentView::count())->toBe(0);
});

// ==========================================
// Usuário Registrado — Metered Wall
// ==========================================

it('usuario registered ve conteudo na primeira view e contagem e registrada', function () use ($FIXTURE_NUMERO) {
    $user = User::factory()->create();
    $user->assignRole('registered');

    $response = $this->actingAs($user)->get("/tese/stf/{$FIXTURE_NUMERO}");

    $response->assertSuccessful();
    expect(ContentView::count())->toBe(1);
    // Não deve ter blur (primeira view, dentro do limite)
    expect($response->getContent())->not->toContain('Limite de');
});

it('revisita da mesma tese nao incrementa contagem', function () use ($FIXTURE_NUMERO) {
    $user = User::factory()->create();
    $user->assignRole('registered');

    $this->actingAs($user)->get("/tese/stf/{$FIXTURE_NUMERO}");
    $this->actingAs($user)->get("/tese/stf/{$FIXTURE_NUMERO}");

    expect(ContentView::count())->toBe(1);
});

it('usuario registered ve CTA com views restantes em cada visualizacao', function () use ($FIXTURE_NUMERO) {
    $user = User::factory()->create();
    $user->assignRole('registered');

    $response = $this->actingAs($user)->get("/tese/stf/{$FIXTURE_NUMERO}");

    $response->assertSuccessful();
    // Deve conter indicação de views restantes
    expect($response->getContent())->toContain('visualiza');
});

it('usuario registered e bloqueado a partir da 4a tese distinta com limite 3', function () use ($FIXTURE_NUMERO) {
    $user = User::factory()->create();
    $user->assignRole('registered');

    // Limite 3: 1ª–3ª teses com conteúdo completo; na 4ª, após recordView, contagem > 3
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 1));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 2));

    expect(ContentView::where('user_id', $user->id)->count())->toBe(3);

    $response = $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 3));

    $response->assertSuccessful();
    expect($response->getContent())->toContain('premium-content-blur');
    expect($response->getContent())->toContain('Limite de');

    expect(ContentView::where('user_id', $user->id)->count())->toBe(4);
});

// ==========================================
// Subscriber — Acesso ilimitado com histórico
// ==========================================

it('subscriber ve conteudo sem limite e sem CTA de metered wall', function () use ($FIXTURE_NUMERO) {
    $user = createSubscribedUser('prod_test');

    // Ver 4 teses
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 1));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 2));
    $response = $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 3));

    $response->assertSuccessful();
    // Views registradas para histórico
    expect(ContentView::where('user_id', $user->id)->count())->toBe(4);
    // Mas sem blur
    expect($response->getContent())->not->toContain('Limite de an\u00e1lises atingido');
});

// ==========================================
// Admin — Bypass total
// ==========================================

it('admin ve conteudo sem limite e sem contagem', function () use ($FIXTURE_NUMERO) {
    $user = createAdminUser();

    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 1));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 2));
    $response = $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 3));

    $response->assertSuccessful();
    // Admin nunca vê card de bloqueio
    expect($response->getContent())->not->toContain('Assinar o T&amp;S');
    expect(ContentView::where('user_id', $user->id)->count())->toBe(0);
});

// ==========================================
// Janela de 24h rolling
// ==========================================

it('views com mais de 24h nao contam no limite', function () use ($FIXTURE_NUMERO) {
    $user = User::factory()->create();
    $user->assignRole('registered');

    // Criar 3 views antigas (25h atrás)
    for ($i = 0; $i < 3; $i++) {
        ContentView::create([
            'user_id' => $user->id,
            'content_type' => 'tese',
            'content_id' => $FIXTURE_NUMERO + $i,
            'tribunal' => 'stf',
            'viewed_at' => now()->subHours(25),
        ]);
    }

    // 4a tese (recente) — deve ter acesso pois as antigas expiraram
    $response = $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 3));

    $response->assertSuccessful();
    expect($response->getContent())->not->toContain('Limite de');
});

// ==========================================
// SiteSetting — Limite configurável
// ==========================================

it('alteracao do limite via SiteSetting reflete imediatamente', function () use ($FIXTURE_NUMERO) {
    SiteSetting::set('metered_wall_daily_limit', '1');
    $user = User::factory()->create();
    $user->assignRole('registered');

    // 1a tese — dentro do limite
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO));

    // 2a tese — excede limite de 1
    $response = $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 1));

    expect($response->getContent())->toContain('premium-content-blur');
    expect($response->getContent())->toContain('Limite de');
});

it('metered wall desativado libera acesso para todos', function () use ($FIXTURE_NUMERO) {
    SiteSetting::set('metered_wall_enabled', '0');
    $user = User::factory()->create();
    $user->assignRole('registered');

    // Ver 4 teses — todas liberadas
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 1));
    $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 2));
    $response = $this->actingAs($user)->get('/tese/stf/'.($FIXTURE_NUMERO + 3));

    $response->assertSuccessful();
    // Metered wall desativado: sem card de bloqueio
    expect($response->getContent())->not->toContain('Assinar o T&amp;S');
    expect($response->getContent())->not->toContain('Limite de');
});
