<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class NewsletterIntegrationSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Newsletter Sendy';

    protected static ?string $title = 'Integração Newsletter (Sendy)';

    protected static ?int $navigationSort = 52;

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'newsletter_integration_enabled' => SiteSetting::getAsBool('newsletter_integration_enabled', false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Kill switch global')
                    ->description('Controla form em /newsletters, toggle em Minha Conta e todas as chamadas ao Sendy. Com desligado, o site não exibe inscrição nem contacta a API.')
                    ->schema([
                        Toggle::make('newsletter_integration_enabled')
                            ->label('Ativar integração newsletter')
                            ->helperText('Ligado: form/link em /newsletters e toggle no perfil. Desligado: nada visível (tudo ou nada).'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set(
            'newsletter_integration_enabled',
            $data['newsletter_integration_enabled'] ? '1' : '0',
        );

        Notification::make()
            ->success()
            ->title('Configurações salvas')
            ->body($data['newsletter_integration_enabled']
                ? 'Integração newsletter ativada.'
                : 'Integração newsletter desativada.')
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
            Action::make('save')
                ->label('Salvar')
                ->submit('save'),
        ];
    }
}
