<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
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
class CollectionSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Coleções';

    protected static ?string $title = 'Configurações de Coleções';

    protected static ?int $navigationSort = 51;

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'collections_registered_max' => (int) SiteSetting::get('collections_registered_max', '3'),
            'collections_registered_items_max' => (int) SiteSetting::get('collections_registered_items_max', '15'),
            'collections_pro_max' => (int) SiteSetting::get('collections_pro_max', '10'),
            'collections_pro_items_max' => (int) SiteSetting::get('collections_pro_items_max', '50'),
            'collections_premium_max' => (int) SiteSetting::get('collections_premium_max', '-1'),
            'collections_premium_items_max' => (int) SiteSetting::get('collections_premium_items_max', '-1'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Registrado (sem assinatura)')
                    ->description('Limites para usuários registrados sem plano ativo.')
                    ->schema([
                        TextInput::make('collections_registered_max')
                            ->label('Máx. de coleções')
                            ->numeric()
                            ->minValue(-1)
                            ->required()
                            ->helperText('Número máximo de coleções que um usuário registrado pode criar. -1 = ilimitado.'),
                        TextInput::make('collections_registered_items_max')
                            ->label('Máx. de itens por coleção')
                            ->numeric()
                            ->minValue(-1)
                            ->required()
                            ->helperText('Número máximo de itens por coleção para usuários registrados. -1 = ilimitado.'),
                    ])
                    ->columns(2),
                Section::make('PRO')
                    ->description('Limites para assinantes do plano PRO.')
                    ->schema([
                        TextInput::make('collections_pro_max')
                            ->label('Máx. de coleções')
                            ->numeric()
                            ->minValue(-1)
                            ->required()
                            ->helperText('Número máximo de coleções que um assinante PRO pode criar. -1 = ilimitado.'),
                        TextInput::make('collections_pro_items_max')
                            ->label('Máx. de itens por coleção')
                            ->numeric()
                            ->minValue(-1)
                            ->required()
                            ->helperText('Número máximo de itens por coleção para assinantes PRO. -1 = ilimitado.'),
                    ])
                    ->columns(2),
                Section::make('PREMIUM')
                    ->description('Limites para assinantes do plano PREMIUM.')
                    ->schema([
                        TextInput::make('collections_premium_max')
                            ->label('Máx. de coleções')
                            ->numeric()
                            ->minValue(-1)
                            ->required()
                            ->helperText('Número máximo de coleções para assinantes PREMIUM. -1 = ilimitado.'),
                        TextInput::make('collections_premium_items_max')
                            ->label('Máx. de itens por coleção')
                            ->numeric()
                            ->minValue(-1)
                            ->required()
                            ->helperText('Número máximo de itens por coleção para assinantes PREMIUM. -1 = ilimitado.'),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('collections_registered_max', (string) $data['collections_registered_max']);
        SiteSetting::set('collections_registered_items_max', (string) $data['collections_registered_items_max']);
        SiteSetting::set('collections_pro_max', (string) $data['collections_pro_max']);
        SiteSetting::set('collections_pro_items_max', (string) $data['collections_pro_items_max']);
        SiteSetting::set('collections_premium_max', (string) $data['collections_premium_max']);
        SiteSetting::set('collections_premium_items_max', (string) $data['collections_premium_items_max']);

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
