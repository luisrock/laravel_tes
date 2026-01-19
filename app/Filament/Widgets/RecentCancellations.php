<?php

namespace App\Filament\Widgets;

use App\Support\SubscriptionUi;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Cashier\Subscription;

class RecentCancellations extends BaseWidget
{
    protected static ?string $heading = 'Últimos cancelamentos';

    protected function getTableQuery(): Builder
    {
        return Subscription::query()
            ->with(['user', 'items'])
            ->whereNotNull('ends_at')
            ->latest('ends_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.email')
                ->label('Usuário')
                ->searchable(),
            BadgeColumn::make('stripe_status')
                ->label('Status')
                ->getStateUsing(fn (Subscription $record): string => SubscriptionUi::statusLabel($record->stripe_status, $record->ends_at))
                ->color(fn (string $state): string => SubscriptionUi::statusColor($state)),
            BadgeColumn::make('plan')
                ->label('Plano')
                ->getStateUsing(fn (Subscription $record): string => SubscriptionUi::tierLabel(
                    SubscriptionUi::resolveTierProductId($record->items ?? [])
                ))
                ->color(fn (string $state): string => SubscriptionUi::tierColor($state)),
            Tables\Columns\TextColumn::make('ends_at')
                ->label('Acesso até')
                ->dateTime('d/m/Y'),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Criada em')
                ->dateTime('d/m/Y')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
