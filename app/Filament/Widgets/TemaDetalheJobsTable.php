<?php

namespace App\Filament\Widgets;

use App\Models\TeseAnalysisJob;
use App\Services\Ai\AcordaoAnalysisEnqueueService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
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
                    ->formatStateUsing(fn (TeseAnalysisJob $record): string => "{$record->attempts}/{$record->max_attempts}")
                    ->alignCenter(),
                TextColumn::make('last_error')
                    ->label('Erro')
                    ->placeholder('—')
                    ->wrap()
                    ->lineClamp(4)
                    ->tooltip(fn (TeseAnalysisJob $record): ?string => $record->last_error)
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('remove')
                    ->label('Remover')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remover job de análise')
                    ->modalDescription('Remove o registro da fila para este tema. Depois pode enfileirar de novo (com outro modelo, se quiser).')
                    ->action(function (TeseAnalysisJob $record): void {
                        app(AcordaoAnalysisEnqueueService::class)->removeJob(
                            $record->tese_id,
                            $record->tribunal,
                        );

                        Notification::make()
                            ->success()
                            ->title('Job removido')
                            ->send();

                        $this->dispatch('tema-detail-refresh');
                    }),
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
