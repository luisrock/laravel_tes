<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiPromptResource\Pages\CreateAiPrompt;
use App\Filament\Resources\AiPromptResource\Pages\EditAiPrompt;
use App\Filament\Resources\AiPromptResource\Pages\ListAiPrompts;
use App\Models\AiPrompt;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiPromptResource extends Resource
{
    protected static ?string $model = AiPrompt::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Prompts de IA';

    protected static ?string $modelLabel = 'Prompt de IA';

    protected static ?string $pluralModelLabel = 'Prompts de IA';

    protected static ?int $navigationSort = 56;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Chave')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->helperText('Identificador único usado no código (ex.: stats_analyst_system). Não editável após a criação.'),
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('Descrição')
                    ->maxLength(255),
                Textarea::make('content')
                    ->label('Conteúdo')
                    ->required()
                    ->rows(16)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Chave')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiPrompts::route('/'),
            'create' => CreateAiPrompt::route('/create'),
            'edit' => EditAiPrompt::route('/{record}/edit'),
        ];
    }
}
