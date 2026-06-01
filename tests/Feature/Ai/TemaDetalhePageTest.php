<?php

use App\Filament\Pages\TemaDetalhe;
use App\Jobs\AnalisarAcordaoJob;
use App\Models\AiModel;
use App\Models\TeseAnalysisJob;
use App\Models\TeseAnalysisSection;
use App\Models\User;
use App\Services\Ai\AcordaoTemaDetailService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    temaDetalheSetupTables();
    DB::table('tese_analysis_jobs')->delete();
    DB::table('tese_analysis_sections')->delete();
    DB::table('tese_acordaos')->delete();
});

function temaDetalheSetupTables(): void
{
    Schema::dropIfExists('stf_teses');
    Schema::dropIfExists('stj_teses');

    Schema::create('stf_teses', function ($table) {
        $table->id();
        $table->unsignedInteger('numero');
        $table->text('tema_texto')->nullable();
        $table->text('tese_texto')->nullable();
        $table->string('situacao')->nullable();
    });

    Schema::create('stj_teses', function ($table) {
        $table->id();
        $table->unsignedInteger('numero');
        $table->text('tema')->nullable();
        $table->text('tese_texto')->nullable();
        $table->string('situacao')->nullable();
    });
}

function temaDetalheFakeOpenRouter(): void
{
    config()->set('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    config()->set('services.openrouter.management_key', 'mgmt-key');
    Cache::forget('openrouter:models');
    Cache::forget('openrouter:models:raw');

    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([
            'data' => [[
                'id' => 'anthropic/claude-sonnet-4.6',
                'name' => 'Claude Sonnet 4.6',
                'pricing' => ['prompt' => '0.000003', 'completion' => '0.000015'],
                'architecture' => [
                    'input_modalities' => ['text', 'image', 'file'],
                    'output_modalities' => ['text'],
                ],
            ]],
        ]),
    ]);
}

function temaDetalheCreateStfTese(int $numero = 1069): int
{
    return (int) DB::table('stf_teses')->insertGetId([
        'numero' => $numero,
        'tema_texto' => "Tema {$numero} para detalhe",
        'tese_texto' => 'Texto da tese firmada.',
        'situacao' => 'Trânsito em Julgado',
    ]);
}

function temaDetalheCreateAcordao(int $teseId): void
{
    Storage::fake('s3');

    DB::table('tese_acordaos')->insert([
        'tese_id' => $teseId,
        'tribunal' => 'STF',
        'numero_acordao' => 'RE 123',
        'tipo' => 'Principal',
        'label' => 'Principal',
        's3_key' => "acordaos/stf/{$teseId}/test.pdf",
        'filename_original' => 'acordao.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'checksum' => md5("stf-{$teseId}"),
        'version' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('TemaDetalhe — acesso', function () {

    it('redireciona visitante não autenticado', function () {
        temaDetalheCreateStfTese(1069);

        $this->get('/admin/painel/tema/STF/1069')->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        temaDetalheCreateStfTese(1069);

        $this->actingAs(User::factory()->create())
            ->get('/admin/painel/tema/STF/1069')
            ->assertForbidden();
    });

    it('permite acesso ao admin pela URL com número do tema', function () {
        $teseId = temaDetalheCreateStfTese(1069);
        temaDetalheCreateAcordao($teseId);

        $response = $this->actingAs(createAdminUser())
            ->get('/admin/painel/tema/STF/1069');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

    it('retorna 404 para número de tema inexistente', function () {
        $this->actingAs(createAdminUser())
            ->get('/admin/painel/tema/STF/999999')
            ->assertNotFound();
    });

    it('não resolve tema pelo id interno da base', function () {
        $teseId = temaDetalheCreateStfTese(1069);

        $this->actingAs(createAdminUser())
            ->get("/admin/painel/tema/STF/{$teseId}")
            ->assertNotFound();
    });

});

describe('TemaDetalhe — dados', function () {

    it('carrega tema pelo número público no serviço de detalhe', function () {
        $teseId = temaDetalheCreateStfTese(1069);
        temaDetalheCreateAcordao($teseId);

        $aiModel = AiModel::create([
            'provider' => 'openrouter',
            'name' => 'Test',
            'model_id' => 'anthropic/claude-sonnet-4.6',
            'price_input_per_million' => 3.0,
            'price_output_per_million' => 15.0,
            'is_active' => true,
        ]);

        TeseAnalysisSection::create([
            'tese_id' => $teseId,
            'tribunal' => 'STF',
            'section_type' => 'teaser',
            'content' => str_repeat('x', 250),
            'status' => 'published',
            'is_active' => false,
            'ai_model_id' => $aiModel->id,
            'generated_at' => now(),
        ]);

        $detail = app(AcordaoTemaDetailService::class)->loadByNumero('STF', 1069);

        expect($detail)->not->toBeNull()
            ->and($detail['tese_id'])->toBe($teseId)
            ->and($detail['numero'])->toBe(1069)
            ->and($detail['acordaos'])->toHaveCount(1)
            ->and($detail['sections'])->toHaveCount(1)
            ->and($detail['is_eligible'])->toBeFalse();
    });

    it('exibe aba Seções IA sem rótulos internos tipo Section {id}', function () {
        $teseId = temaDetalheCreateStfTese(1069);

        $aiModel = AiModel::create([
            'provider' => 'openrouter',
            'name' => 'Claude Sonnet 4.6',
            'model_id' => 'anthropic/claude-sonnet-4.6',
            'price_input_per_million' => 3.0,
            'price_output_per_million' => 15.0,
            'is_active' => true,
        ]);

        TeseAnalysisSection::create([
            'tese_id' => $teseId,
            'tribunal' => 'STF',
            'section_type' => 'caso_fatico',
            'content' => str_repeat('Fatos do tema. ', 80),
            'status' => 'draft',
            'is_active' => false,
            'ai_model_id' => $aiModel->id,
            'cost_usd' => 0.01,
            'tokens_input' => 100,
            'tokens_output' => 50,
            'generated_at' => now(),
        ]);

        Livewire::actingAs(createAdminUser())
            ->test(TemaDetalhe::class, ['tribunal' => 'STF', 'numero' => 1069])
            ->assertSee('Metadados da análise')
            ->assertSee('Claude Sonnet 4.6')
            ->assertSee('Caso fático')
            ->assertDontSee('Section 1');
    });

});

describe('TemaDetalhe — polling', function () {

    it('detecta job ativo para polling', function () {
        $teseId = temaDetalheCreateStfTese(1069);

        $aiModel = AiModel::create([
            'provider' => 'openrouter',
            'name' => 'Test',
            'model_id' => 'anthropic/claude-sonnet-4.6',
            'price_input_per_million' => 3.0,
            'price_output_per_million' => 15.0,
            'is_active' => true,
        ]);

        TeseAnalysisJob::create([
            'tese_id' => $teseId,
            'tribunal' => 'STF',
            'section_type' => 'all',
            'ai_model_id' => $aiModel->id,
            'status' => 'running',
        ]);

        $component = Livewire::actingAs(createAdminUser())
            ->test(TemaDetalhe::class, ['tribunal' => 'STF', 'numero' => 1069]);

        expect($component->instance()->hasActiveAnalysisJob())->toBeTrue();
    });

    it('não faz polling quando o último job terminou', function () {
        $teseId = temaDetalheCreateStfTese(1069);

        $aiModel = AiModel::create([
            'provider' => 'openrouter',
            'name' => 'Test',
            'model_id' => 'anthropic/claude-sonnet-4.6',
            'price_input_per_million' => 3.0,
            'price_output_per_million' => 15.0,
            'is_active' => true,
        ]);

        TeseAnalysisJob::create([
            'tese_id' => $teseId,
            'tribunal' => 'STF',
            'section_type' => 'all',
            'ai_model_id' => $aiModel->id,
            'status' => 'done',
        ]);

        $component = Livewire::actingAs(createAdminUser())
            ->test(TemaDetalhe::class, ['tribunal' => 'STF', 'numero' => 1069]);

        expect($component->instance()->hasActiveAnalysisJob())->toBeFalse();
    });

});

describe('TemaDetalhe — enfileiramento', function () {

    it('enfileira via ação do header com modelo escolhido', function () {
        Queue::fake();
        temaDetalheFakeOpenRouter();

        $teseId = temaDetalheCreateStfTese(1069);
        temaDetalheCreateAcordao($teseId);

        Livewire::actingAs(createAdminUser())
            ->test(TemaDetalhe::class, ['tribunal' => 'STF', 'numero' => 1069])
            ->callAction(TestAction::make('enqueue')->arguments([
                'model_slug' => 'anthropic/claude-sonnet-4.6',
                'force' => false,
            ]))
            ->assertNotified();

        $job = TeseAnalysisJob::query()->where('tese_id', $teseId)->first();

        expect($job)->not->toBeNull()
            ->and($job->status)->toBe('queued');

        Queue::assertPushed(AnalisarAcordaoJob::class);
    });

});

describe('TemasElegiveis — link para detalhe', function () {

    it('gera URL do detalhe com número do tema', function () {
        $url = TemaDetalhe::getUrl(['tribunal' => 'STF', 'numero' => 1069]);

        expect($url)->toContain('/admin/painel/tema/STF/1069');
    });

});
