<?php

namespace App\Filament\Widgets;

use App\Models\RefundRequest;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingRefundRequests extends BaseWidget
{
    protected static ?string $heading = 'Estornos pendentes';

    protected function getTableQuery(): Builder
    {
        return RefundRequest::query()
            ->with('user')
            ->where('status', RefundRequest::STATUS_PENDING)
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.email')
                ->label('Usuário')
                ->searchable(),
            Tables\Columns\TextColumn::make('reason')
                ->label('Motivo')
                ->limit(40),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Criado em')
                ->dateTime('d/m/Y H:i'),
        ];
    }
}
