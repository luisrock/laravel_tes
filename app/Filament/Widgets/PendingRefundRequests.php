<?php

namespace App\Filament\Widgets;

use App\Models\RefundRequest;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingRefundRequests extends BaseWidget
{
    protected static ?string $heading = 'Estornos pendentes';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RefundRequest::query()
                    ->with('user')
                    ->where('status', RefundRequest::STATUS_PENDING)
                    ->latest()
            )
            ->columns([
                TextColumn::make('user.email')
                    ->label('Usuário')
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
