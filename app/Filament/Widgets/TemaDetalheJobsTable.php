<?php

namespace App\Filament\Widgets;

use App\Models\TeseAnalysisJob;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\TableWidget;

class TemaDetalheJobsTable extends TableWidget
{
    use CanPoll;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.tema-detalhe-jobs-table';

    public int $teseId = 0;

    public string $tribunal = 'STF';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TeseAnalysisJob::query()
                    ->with('aiModel')
                    ->where('tese_id', $this->teseId)
                    ->where('tribunal', $this->tribunal)
                    ->orderByDesc('created_at')
                    ->limit(20)
            )
            ->heading('Jobs de análise')
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued' => 'gray',
                        'running' => 'warning',
                        'done' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('aiModel.name')
                    ->label('Modelo')
                    ->placeholder('—'),
                TextColumn::make('section_type')
                    ->label('Escopo'),
                TextColumn::make('attempts')
                    ->label('Tentativas')
                    ->alignCenter(),
                TextColumn::make('last_error')
                    ->label('Erro')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Nenhum job')
            ->emptyStateDescription('Ainda não há jobs de análise para este tema.');
    }

    protected function getPollingInterval(): ?string
    {
        if ($this->teseId === 0) {
            return null;
        }

        $hasActive = TeseAnalysisJob::query()
            ->where('tese_id', $this->teseId)
            ->where('tribunal', $this->tribunal)
            ->where('section_type', 'all')
            ->whereIn('status', ['queued', 'running'])
            ->exists();

        return $hasActive ? '5s' : null;
    }
}
