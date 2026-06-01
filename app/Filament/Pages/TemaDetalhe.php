<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\TemaDetalheAcordaosTable;
use App\Filament\Widgets\TemaDetalheJobsTable;
use App\Models\SiteSetting;
use App\Models\TeseAnalysisSection;
use App\Services\Ai\AcordaoAnalysisEnqueueService;
use App\Services\Ai\AcordaoTemaDetailService;
use App\Services\Ai\OpenRouterManagementService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Detalhe de um tema STF/STJ: acórdãos, seções de IA e jobs (paridade Flask /tema).
 */
class TemaDetalhe extends Page
{
    protected static ?string $slug = 'tema';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected string $view = 'filament.pages.tema-detalhe';

    public string $tribunal = 'STF';

    public int $numero = 0;

    public int $teseId = 0;

    /** @var array<string, mixed> */
    public array $detail = [];

    public static function routes(Panel $panel): void
    {
        Route::get(
            '/'.static::getSlug($panel).'/{tribunal}/{numero}',
            static::class
        )
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel));
    }

    public function mount(string $tribunal, int $numero): void
    {
        $loaded = app(AcordaoTemaDetailService::class)->loadByNumero($tribunal, $numero);

        abort_if($loaded === null, 404);

        $this->tribunal = $loaded['tribunal'];
        $this->numero = $loaded['numero'];
        $this->teseId = $loaded['tese_id'];
        $this->detail = $loaded;
    }

    public function getTitle(): string|Htmlable
    {
        if ($this->detail === []) {
            return 'Tema';
        }

        return "Tema {$this->detail['numero']} — {$this->detail['tribunal']}";
    }

    public function getSubheading(): string|Htmlable|null
    {
        $descricao = $this->detail['descricao'] ?? '';

        if ($descricao === '') {
            return null;
        }

        return Str::limit($descricao, 160);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Voltar aos temas')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(TemasElegiveis::getUrl()),
            Action::make('public')
                ->label('Ver no site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => $this->detail['public_url'])
                ->openUrlInNewTab(),
            Action::make('enqueue')
                ->label('Enfileirar análise')
                ->icon(Heroicon::OutlinedSparkles)
                ->form([
                    Select::make('model_slug')
                        ->label('Modelo')
                        ->options(fn (): array => $this->acordaoModelOptions())
                        ->default(fn (): string => (string) SiteSetting::get(
                            'acordao_analysis_model',
                            config('ai.acordao_analysis.default_model')
                        ))
                        ->searchable()
                        ->required(),
                    Toggle::make('force')
                        ->label('Forçar reprocesso')
                        ->helperText('Re-enfileira mesmo com seções existentes ou job anterior.'),
                ])
                ->action(function (array $data): void {
                    $job = app(AcordaoAnalysisEnqueueService::class)->enqueue(
                        $this->teseId,
                        $this->tribunal,
                        force: (bool) ($data['force'] ?? false),
                        modelSlug: $data['model_slug'] ?? null,
                    );

                    if ($job === null) {
                        Notification::make()
                            ->warning()
                            ->title('Não enfileirado')
                            ->body('Tema não elegível ou job já ativo (use Forçar reprocesso).')
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Análise enfileirada')
                        ->body("Job #{$job->id} na fila.")
                        ->send();

                    $this->refreshDetail();
                    $this->dispatch('$refresh');
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('TemaDetalheTabs')
                    ->contained(false)
                    ->tabs([
                        Tab::make('resumo')
                            ->label('Resumo')
                            ->schema($this->resumoTabSchema()),
                        Tab::make('acordaos')
                            ->label('Acórdãos')
                            ->badge(fn (): int => $this->detail['acordaos']->count())
                            ->schema([
                                Livewire::make(TemaDetalheAcordaosTable::class, [
                                    'teseId' => $this->teseId,
                                    'tribunal' => $this->tribunal,
                                ]),
                            ]),
                        Tab::make('secoes')
                            ->label('Seções IA')
                            ->badge(fn (): int => $this->detail['sections']->count())
                            ->schema($this->secoesTabSchema()),
                        Tab::make('jobs')
                            ->label('Jobs')
                            ->badge(fn (): int => $this->detail['jobs']->count())
                            ->schema([
                                Livewire::make(TemaDetalheJobsTable::class, [
                                    'teseId' => $this->teseId,
                                    'tribunal' => $this->tribunal,
                                ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, Section|Placeholder>
     */
    protected function resumoTabSchema(): array
    {
        return [
            Section::make('Informações do tema')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2, 'lg' => 3])
                        ->schema([
                            Placeholder::make('tribunal')
                                ->label('Tribunal')
                                ->content(fn (): string => $this->detail['tribunal']),
                            Placeholder::make('numero')
                                ->label('Nº tema')
                                ->content(fn (): string => (string) $this->detail['numero']),
                            Placeholder::make('situacao')
                                ->label('Situação')
                                ->content(fn (): string => $this->detail['situacao'] ?? '—'),
                            Placeholder::make('elegivel')
                                ->label('Elegível para IA')
                                ->content(fn (): string => $this->detail['is_eligible'] ? 'Sim' : 'Não'),
                            Placeholder::make('job_ativo')
                                ->label('Job ativo')
                                ->content(function (): string {
                                    $job = $this->detail['active_job'] ?? null;

                                    return $job !== null ? $job->status : '—';
                                }),
                        ]),
                    Placeholder::make('descricao')
                        ->label('Descrição')
                        ->content(fn (): string => $this->detail['descricao'])
                        ->columnSpanFull(),
                    Placeholder::make('tese_texto')
                        ->label('Tese')
                        ->content(fn (): HtmlString => new HtmlString(
                            '<div class="whitespace-pre-wrap text-sm">'.e((string) ($this->detail['tese_texto'] ?? '—')).'</div>'
                        ))
                        ->columnSpanFull()
                        ->visible(fn (): bool => filled($this->detail['tese_texto'] ?? null)),
                ]),
        ];
    }

    /**
     * @return array<int, Section|Placeholder>
     */
    protected function secoesTabSchema(): array
    {
        /** @var \Illuminate\Support\Collection<int, TeseAnalysisSection> $sections */
        $sections = $this->detail['sections'];

        if ($sections->isEmpty()) {
            return [
                Placeholder::make('sem_secoes')
                    ->hiddenLabel()
                    ->content('Nenhuma seção gerada ainda. Enfileire uma análise para gerar o resumo.'),
            ];
        }

        $components = [
            Section::make('Metadados da análise')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 3])
                        ->schema([
                            Placeholder::make('ia_modelo')
                                ->label('Modelo')
                                ->content(fn (): string => $this->sectionsAnalysisSummary()['model'] ?? '—'),
                            Placeholder::make('ia_custo')
                                ->label('Custo total')
                                ->content(fn (): string => $this->sectionsAnalysisSummary()['cost'] ?? '—'),
                            Placeholder::make('ia_tokens')
                                ->label('Tokens (entrada / saída)')
                                ->content(fn (): string => $this->sectionsAnalysisSummary()['tokens'] ?? '—'),
                        ]),
                ]),
        ];

        foreach ($sections as $section) {
            $label = AcordaoTemaDetailService::SECTION_LABELS[$section->section_type]
                ?? $section->section_type;

            $statusMeta = collect([
                $section->status,
                $section->is_active ? 'ativa' : null,
            ])->filter()->implode(' · ');

            $components[] = Section::make($label)
                ->description($statusMeta !== '' ? $statusMeta : null)
                ->collapsible()
                ->schema([
                    Placeholder::make('conteudo_'.$section->section_type)
                        ->hiddenLabel()
                        ->content(function () use ($section): HtmlString {
                            return new HtmlString(
                                '<div class="max-w-none text-sm leading-relaxed text-gray-700 dark:text-gray-300 whitespace-pre-wrap">'
                                .e(Str::limit($section->content, 4000))
                                .'</div>'
                            );
                        }),
                ]);
        }

        return $components;
    }

    /**
     * Custo, modelo e tokens são iguais em todas as seções de uma mesma execução — exibir uma vez.
     *
     * @return array{model: ?string, cost: ?string, tokens: ?string}
     */
    protected function sectionsAnalysisSummary(): array
    {
        /** @var \Illuminate\Support\Collection<int, TeseAnalysisSection> $sections */
        $sections = $this->detail['sections'] ?? collect();

        if ($sections->isEmpty()) {
            return ['model' => null, 'cost' => null, 'tokens' => null];
        }

        $first = $sections->first();
        $totalCost = $sections->sum(fn (TeseAnalysisSection $s): float => (float) $s->cost_usd);
        $tokensIn = $sections->sum('tokens_input');
        $tokensOut = $sections->sum('tokens_output');

        return [
            'model' => $first->aiModel?->name,
            'cost' => $totalCost > 0 ? '$'.number_format($totalCost, 4) : null,
            'tokens' => ($tokensIn > 0 || $tokensOut > 0)
                ? number_format($tokensIn).' / '.number_format($tokensOut)
                : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function acordaoModelOptions(): array
    {
        $options = app(OpenRouterManagementService::class)->pdfCapableModels();

        $current = SiteSetting::get('acordao_analysis_model', config('ai.acordao_analysis.default_model'));

        if (is_string($current) && $current !== '' && ! isset($options[$current])) {
            $options[$current] = $current;
        }

        return $options;
    }

    /**
     * Atualização periódica enquanto houver job queued/running (wire:poll na view).
     */
    public function pollTemaDetalhe(): void
    {
        if (! $this->hasActiveAnalysisJob()) {
            return;
        }

        $this->refreshDetail();
    }

    public function hasActiveAnalysisJob(): bool
    {
        if ($this->detail === []) {
            return false;
        }

        if (($this->detail['active_job'] ?? null) !== null) {
            return true;
        }

        $latest = $this->detail['jobs']?->first();

        return $latest !== null && in_array($latest->status, ['queued', 'running'], true);
    }

    protected function refreshDetail(): void
    {
        $loaded = app(AcordaoTemaDetailService::class)->loadByNumero($this->tribunal, $this->numero);

        if ($loaded !== null) {
            $this->detail = $loaded;
            $this->teseId = $loaded['tese_id'];
        }
    }
}
