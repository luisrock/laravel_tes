<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanFeatureResource\Pages\CreatePlanFeature;
use App\Filament\Resources\PlanFeatureResource\Pages\EditPlanFeature;
use App\Filament\Resources\PlanFeatureResource\Pages\ListPlanFeatures;
use App\Models\PlanFeature;
use App\Services\StripeService;
use App\Support\SubscriptionUi;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PlanFeatureResource extends Resource
{
    protected static ?string $model = PlanFeature::class;

    protected static ?string $navigationGroup = 'Assinaturas';
    protected static ?string $navigationIcon = 'heroicon-o-adjustments';
    protected static ?string $navigationLabel = 'Features do Plano';
    protected static ?string $modelLabel = 'Feature do Plano';
    protected static ?string $pluralModelLabel = 'Features do Plano';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('stripe_product_id')
                    ->label('Produto Stripe')
                    ->required()
                    ->searchable()
                    ->options(function (StripeService $stripeService): array {
                        return $stripeService
                            ->getActiveProducts()
                            ->mapWithKeys(fn ($product) => [$product->id => $product->name])
                            ->toArray();
                    }),
                Select::make('feature_key')
                    ->label('Feature')
                    ->required()
                    ->options(config('subscription.features')),
                TextInput::make('feature_value')
                    ->label('Valor (opcional)')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('stripe_product_label')
                    ->label('Plano')
                    ->getStateUsing(fn (PlanFeature $record): string => SubscriptionUi::tierLabel($record->stripe_product_id))
                    ->color(fn (string $state): string => SubscriptionUi::tierColor($state)),
                TextColumn::make('stripe_product_id')
                    ->label('Produto Stripe')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('feature_key')
                    ->label('Feature')
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('feature_value')
                    ->label('Valor')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlanFeatures::route('/'),
            'create' => CreatePlanFeature::route('/create'),
            'edit' => EditPlanFeature::route('/{record}/edit'),
        ];
    }
}
