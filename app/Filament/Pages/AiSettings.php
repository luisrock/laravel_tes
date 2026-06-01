<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\Ai\OpenRouterManagementService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

/**
 * Configurações de IA (OpenRouter): escolha do modelo de chat e crédito residual.
 *
 * @property-read Schema $form
 */
class AiSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Configurações de IA';

    protected static ?string $title = 'Configurações de IA';

    protected static ?string $slug = 'configuracoes-ia';

    protected static ?int $navigationSort = 55;

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'ai_chat_model' => SiteSetting::get('ai_chat_model'),
            'acordao_analysis_model' => SiteSetting::get(
                'acordao_analysis_model',
                config('ai.acordao_analysis.default_model')
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Crédito OpenRouter')
                    ->description('Saldo residual da conta usada para as requisições de IA.')
                    ->schema([
                        Placeholder::make('remaining_credits')
                            ->label('Crédito residual')
                            ->content(fn (): HtmlString => $this->remainingCreditsLabel()),
                    ]),
                Section::make('Modelo de chat')
                    ->description('Modelo do OpenRouter usado no chat e na avaliação das estatísticas.')
                    ->schema([
                        Select::make('ai_chat_model')
                            ->label('Modelo')
                            ->options(fn (): array => $this->modelOptions())
                            ->searchable()
                            ->placeholder('Selecione um modelo')
                            ->helperText($this->modelSelectHelper()),
                    ]),
                Section::make('Análise de Acórdãos')
                    ->description('Modelo do OpenRouter usado para gerar as análises de acórdãos ("Decifrando a Tese").')
                    ->schema([
                        Select::make('acordao_analysis_model')
                            ->label('Modelo')
                            ->options(fn (): array => $this->acordaoModelOptions())
                            ->searchable()
                            ->placeholder('Selecione um modelo')
                            ->helperText($this->acordaoModelSelectHelper()),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('ai_chat_model', (string) ($data['ai_chat_model'] ?? ''));
        SiteSetting::set('acordao_analysis_model', (string) ($data['acordao_analysis_model'] ?? ''));

        Notification::make()
            ->success()
            ->title('Configurações salvas')
            ->body('Modelos de IA atualizados.')
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->key('form-actions'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshCatalogue')
                ->label('Atualizar catálogo e crédito')
                ->icon('heroicon-o-arrow-path')
                ->action(function (): void {
                    app(OpenRouterManagementService::class)->clearModelsCache();

                    Notification::make()
                        ->success()
                        ->title('Catálogo atualizado')
                        ->body('Lista de modelos e crédito recarregados do OpenRouter.')
                        ->send();
                }),
        ];
    }

    /** @return array<Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar')
                ->submit('save'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function modelOptions(): array
    {
        $options = app(OpenRouterManagementService::class)->availableModels();

        $current = SiteSetting::get('ai_chat_model');

        if ($current && ! isset($options[$current])) {
            $options[$current] = $current;
        }

        return $options;
    }

    protected function modelSelectHelper(): string
    {
        return $this->modelOptions() === []
            ? 'Nenhum modelo carregado. Verifique a chave de gerenciamento (OPENROUTER_API_KEY_MANAGEMENT) e use "Atualizar catálogo".'
            : 'A lista vem do catálogo do OpenRouter (modelos de texto).';
    }

    /**
     * Opções do Select de análise de acórdãos: apenas modelos PDF-capable do catálogo.
     *
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

    protected function acordaoModelSelectHelper(): string
    {
        return app(OpenRouterManagementService::class)->pdfCapableModels() === []
            ? 'Nenhum modelo PDF-capable carregado. Verifique a chave de gerenciamento (OPENROUTER_API_KEY_MANAGEMENT) e use "Atualizar catálogo".'
            : 'Apenas modelos do OpenRouter que aceitam PDF como anexo (multimodal).';
    }

    protected function remainingCreditsLabel(): HtmlString
    {
        $credits = app(OpenRouterManagementService::class)->remainingCredits();

        if ($credits === null) {
            return new HtmlString('<span class="text-warning-600 dark:text-warning-400">Indisponível — verifique a chave de gerenciamento.</span>');
        }

        return new HtmlString(sprintf(
            '<span class="text-2xl font-bold text-primary-600 dark:text-primary-400">$%s</span>',
            number_format($credits, 2, '.', ',')
        ));
    }
}
