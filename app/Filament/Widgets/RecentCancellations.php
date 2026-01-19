<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Cashier\Subscription;

class RecentCancellations extends BaseWidget
{
    protected static ?string $heading = 'Últimos cancelamentos';

    protected function getTableQuery(): Builder
    {
        return Subscription::query()
            ->with('user')
            ->whereNotNull('ends_at')
            ->latest('ends_at');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.email')
                ->label('Usuário')
                ->searchable(),
            Tables\Columns\TextColumn::make('stripe_status')
                ->label('Status')
                ->formatStateUsing(fn (string $state): string => ucfirst($state)),
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
