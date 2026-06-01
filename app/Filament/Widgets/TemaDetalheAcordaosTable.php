<?php

namespace App\Filament\Widgets;

use App\Models\TeseAcordao;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TemaDetalheAcordaosTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    public int $teseId = 0;

    public string $tribunal = 'STF';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TeseAcordao::query()
                    ->forTese($this->teseId, $this->tribunal)
                    ->orderByDesc('version')
                    ->orderByDesc('created_at')
            )
            ->heading('Acórdãos')
            ->columns([
                TextColumn::make('tipo'),
                TextColumn::make('numero_acordao')
                    ->label('Nº acórdão'),
                TextColumn::make('filename_original')
                    ->label('Arquivo'),
                TextColumn::make('version')
                    ->label('Ver.')
                    ->alignCenter(),
                TextColumn::make('presigned_url')
                    ->label('PDF')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Abrir' : '—')
                    ->url(fn (TeseAcordao $record): ?string => $record->presigned_url)
                    ->openUrlInNewTab()
                    ->color(fn (?string $state): string => $state ? 'primary' : 'gray')
                    ->placeholder('—'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Nenhum acórdão')
            ->emptyStateDescription('Não há PDFs cadastrados para este tema.');
    }
}
