<?php

namespace App\Filament\Widgets;

use App\Support\SubscriptionUi;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Cashier\Subscription;

class RecentSubscriptions extends BaseWidget
{
    protected static ?string $heading = 'Últimas assinaturas';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->with(['user', 'items'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('user.email')
                    ->label('Usuário')
                    ->searchable(),
                TextColumn::make('stripe_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Subscription $record): string => SubscriptionUi::statusLabel($record->stripe_status, $record->ends_at))
                    ->color(fn (string $state): string => SubscriptionUi::statusColor($state)),
                TextColumn::make('plan')
                    ->label('Plano')
                    ->badge()
                    ->getStateUsing(fn (Subscription $record): string => SubscriptionUi::tierLabel(
                        SubscriptionUi::resolveTierProductId($record->items ?? [])
                    ))
                    ->color(fn (string $state): string => SubscriptionUi::tierColor($state)),
                TextColumn::make('current_period_end')
                    ->label('Renova em')
                    ->dateTime('d/m/Y')
                    ->toggleable(),
                TextColumn::make('ends_at')
                    ->label('Acesso até')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}
