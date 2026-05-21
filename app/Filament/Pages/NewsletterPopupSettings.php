<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class NewsletterPopupSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-window';

    protected static ?string $navigationLabel = 'Newsletter Popup';

    protected static ?string $title = 'Popup de newsletter (visitantes)';

    protected static ?int $navigationSort = 53;

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'newsletter_popup_enabled' => SiteSetting::getAsBool('newsletter_popup_enabled', false),
            'newsletter_popup_trigger' => SiteSetting::get('newsletter_popup_trigger', 'timer'),
            'newsletter_popup_delay_seconds' => (int) SiteSetting::get('newsletter_popup_delay_seconds', '20'),
            'newsletter_popup_scroll_percent' => (int) SiteSetting::get('newsletter_popup_scroll_percent', '50'),
            'newsletter_popup_frequency_days' => (int) SiteSetting::get('newsletter_popup_frequency_days', '14'),
            'newsletter_popup_variant_a_title' => SiteSetting::get(
                'newsletter_popup_variant_a_title',
                'Acompanhe as decisões mais importantes',
            ),
            'newsletter_popup_variant_a_body' => SiteSetting::get(
                'newsletter_popup_variant_a_body',
                'Receba semanalmente um resumo dos novos repetitivos e súmulas dos tribunais superiores.',
            ),
            'newsletter_popup_variant_a_cta' => SiteSetting::get('newsletter_popup_variant_a_cta', 'Quero receber'),
            'newsletter_popup_variant_b_enabled' => SiteSetting::getAsBool('newsletter_popup_variant_b_enabled', false),
            'newsletter_popup_variant_b_title' => SiteSetting::get('newsletter_popup_variant_b_title', ''),
            'newsletter_popup_variant_b_body' => SiteSetting::get('newsletter_popup_variant_b_body', ''),
            'newsletter_popup_variant_b_cta' => SiteSetting::get('newsletter_popup_variant_b_cta', ''),
            'newsletter_popup_split_percent' => (int) SiteSetting::get('newsletter_popup_split_percent', '50'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Geral')
                    ->description('Requer a integração newsletter ligada em «Newsletter Sendy». O popup só aparece para visitantes não autenticados.')
                    ->schema([
                        Toggle::make('newsletter_popup_enabled')
                            ->label('Ativar popup para visitantes'),
                        Select::make('newsletter_popup_trigger')
                            ->label('Gatilho de exibição')
                            ->options([
                                'timer' => 'Timer (após X segundos)',
                                'exit_intent' => 'Exit intent (cursor sai pelo topo)',
                                'scroll' => 'Scroll (% da página)',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),
                        TextInput::make('newsletter_popup_delay_seconds')
                            ->label('Atraso (segundos)')
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(120)
                            ->required(fn (Get $get): bool => $get('newsletter_popup_trigger') === 'timer')
                            ->visible(fn (Get $get): bool => $get('newsletter_popup_trigger') === 'timer')
                            ->helperText('Só para gatilho Timer.'),
                        TextInput::make('newsletter_popup_scroll_percent')
                            ->label('Percentual de scroll')
                            ->numeric()
                            ->minValue(25)
                            ->maxValue(95)
                            ->required(fn (Get $get): bool => $get('newsletter_popup_trigger') === 'scroll')
                            ->suffix('%')
                            ->visible(fn (Get $get): bool => $get('newsletter_popup_trigger') === 'scroll')
                            ->helperText('O popup abre quando o visitante rolou esta % da página (ex.: 50 = metade). Padrão ao guardar: 50%.'),
                        TextInput::make('newsletter_popup_frequency_days')
                            ->label('Não mostrar novamente por (dias)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(90)
                            ->required()
                            ->helperText('Após fechar o popup, o visitante não o verá até expirar este período. Use «Resetar espera» abaixo para invalidar fechos anteriores (testes e suporte).'),
                    ]),
                Section::make('Variante A')
                    ->schema([
                        TextInput::make('newsletter_popup_variant_a_title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('newsletter_popup_variant_a_body')
                            ->label('Texto')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000),
                        TextInput::make('newsletter_popup_variant_a_cta')
                            ->label('Botão (CTA)')
                            ->required()
                            ->maxLength(64),
                    ]),
                Section::make('Variante B (A/B test)')
                    ->schema([
                        Toggle::make('newsletter_popup_variant_b_enabled')
                            ->label('Ativar variante B'),
                        TextInput::make('newsletter_popup_variant_b_title')
                            ->label('Título')
                            ->maxLength(255),
                        Textarea::make('newsletter_popup_variant_b_body')
                            ->label('Texto')
                            ->rows(3)
                            ->maxLength(1000),
                        TextInput::make('newsletter_popup_variant_b_cta')
                            ->label('Botão (CTA)')
                            ->maxLength(64),
                        TextInput::make('newsletter_popup_split_percent')
                            ->label('% do tráfego para variante B')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->suffix('%')
                            ->helperText('Quando B está ativa, visitantes novos são sorteados uma vez e fixados por cookie.'),
                    ]),
            ]);
    }

    public function resetDismissWait(): void
    {
        SiteSetting::set('newsletter_popup_dismiss_reset_epoch', (string) $this->currentDismissResetEpoch());

        Notification::make()
            ->success()
            ->title('Espera do popup resetada')
            ->body('Visitantes que tinham fechado o popup voltam a poder vê-lo no próximo carregamento da página (aba anónima ou após refresh).')
            ->send();
    }

    public function resetPopupTestCookies(): void
    {
        SiteSetting::set('newsletter_popup_dismiss_reset_epoch', (string) $this->currentDismissResetEpoch());
        SiteSetting::set('newsletter_popup_subscribed_reset_epoch', (string) $this->currentDismissResetEpoch());

        Notification::make()
            ->success()
            ->title('Estado de teste do popup resetado')
            ->body('Invalida «Agora não» e o cookie de inscrição pelo popup. Útil para repetir testes A/B e fluxo de inscrição.')
            ->send();
    }

    private function currentDismissResetEpoch(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('newsletter_popup_enabled', $data['newsletter_popup_enabled'] ? '1' : '0');
        SiteSetting::set('newsletter_popup_trigger', (string) $data['newsletter_popup_trigger']);
        SiteSetting::set(
            'newsletter_popup_delay_seconds',
            (string) ($data['newsletter_popup_delay_seconds'] ?? SiteSetting::get('newsletter_popup_delay_seconds', '20')),
        );
        SiteSetting::set(
            'newsletter_popup_scroll_percent',
            (string) ($data['newsletter_popup_scroll_percent'] ?? SiteSetting::get('newsletter_popup_scroll_percent', '50')),
        );
        SiteSetting::set('newsletter_popup_frequency_days', (string) $data['newsletter_popup_frequency_days']);
        SiteSetting::set('newsletter_popup_variant_a_title', (string) $data['newsletter_popup_variant_a_title']);
        SiteSetting::set('newsletter_popup_variant_a_body', (string) $data['newsletter_popup_variant_a_body']);
        SiteSetting::set('newsletter_popup_variant_a_cta', (string) $data['newsletter_popup_variant_a_cta']);
        SiteSetting::set('newsletter_popup_variant_b_enabled', $data['newsletter_popup_variant_b_enabled'] ? '1' : '0');
        SiteSetting::set('newsletter_popup_variant_b_title', (string) ($data['newsletter_popup_variant_b_title'] ?? ''));
        SiteSetting::set('newsletter_popup_variant_b_body', (string) ($data['newsletter_popup_variant_b_body'] ?? ''));
        SiteSetting::set('newsletter_popup_variant_b_cta', (string) ($data['newsletter_popup_variant_b_cta'] ?? ''));
        SiteSetting::set('newsletter_popup_split_percent', (string) $data['newsletter_popup_split_percent']);

        Notification::make()
            ->success()
            ->title('Configurações do popup salvas')
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

    /** @return array<Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('resetDismissWait')
                ->label('Resetar espera (X dias)')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Resetar período de espera do popup?')
                ->modalDescription('Quem fechou o popup com «Agora não» ou no X voltará a vê-lo no próximo carregamento. Não exige limpar cookies manualmente no browser.')
                ->action('resetDismissWait'),
            Action::make('resetPopupTestCookies')
                ->label('Reset completo (testes)')
                ->color('gray')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Resetar todo o estado do popup nos browsers?')
                ->modalDescription('Além da espera, invalida o cookie de «já inscrito pelo popup». Use em dev/staging ou quando precisar repetir o fluxo de inscrição.')
                ->action('resetPopupTestCookies'),
            Action::make('save')
                ->label('Salvar')
                ->submit('save'),
        ];
    }
}
