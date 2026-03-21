<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
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
class MeteredWallSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Metered Wall';

    protected static ?string $title = 'Configurações do Metered Wall';

    protected static ?int $navigationSort = 50;

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'metered_wall_enabled' => SiteSetting::getAsBool('metered_wall_enabled', true),
            'metered_wall_daily_limit' => (int) SiteSetting::get('metered_wall_daily_limit', '3'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Metered Wall')
                    ->description('Controle o limite de visualizações gratuitas de análises de IA para usuários não-assinantes.')
                    ->schema([
                        Toggle::make('metered_wall_enabled')
                            ->label('Ativar Metered Wall')
                            ->helperText('Quando desativado, todos os usuários registrados têm acesso ilimitado às análises de IA.'),
                        TextInput::make('metered_wall_daily_limit')
                            ->label('Limite diário de visualizações')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->helperText('Número máximo de análises de IA que um usuário registrado (não-assinante) pode visualizar em 24 horas.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('metered_wall_enabled', $data['metered_wall_enabled'] ? '1' : '0');
        SiteSetting::set('metered_wall_daily_limit', (string) $data['metered_wall_daily_limit']);

        Notification::make()
            ->success()
            ->title('Configurações salvas')
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
