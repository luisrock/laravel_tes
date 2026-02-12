<?php

namespace App\Filament\Widgets;

use App\Enums\RefundRequestStatus;
use App\Models\RefundRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingRefundRequests extends BaseWidget
{
    protected static ?string $heading = 'Estornos pendentes';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RefundRequest::query()
                    ->with('user')
                    ->where('status', RefundRequestStatus::Pending)
                    ->latest()
            )
            ->columns([
                TextColumn::make('user.email')
                    ->label('Usuario')
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
