<?php

namespace App\Filament\Pages;

use App\Services\Ai\AcordaoAnalysisEnqueueService;
use App\Services\Ai\EligibleTemasQuery;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Browser de temas STF/STJ com acórdãos — enfileiramento da análise "Decifrando a Tese".
 */
class TemasElegiveis extends Page implements HasTable
{
    use InteractsWithTable {
        makeTable as makeBaseTable;
    }

    protected static string|\UnitEnum|null $navigationGroup = 'Decifrando a Tese';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Temas elegíveis';

    protected static ?string $title = 'Temas elegíveis';

    protected static ?string $slug = 'temas-elegiveis';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament-panels::pages.page';

    public function table(Table $table): Table
    {
        return $table
            ->queryStringIdentifier('temasElegiveis')
            ->records(function (
                int $page,
                int $recordsPerPage,
                ?array $filters,
                ?string $search,
                ?string $sortColumn,
                ?string $sortDirection,
            ): LengthAwarePaginator {
                return app(EligibleTemasQuery::class)->paginate(
                    $filters,
                    $search,
                    $sortColumn,
                    $sortDirection,
                    $page,
                    $recordsPerPage,
                );
            })
            ->columns([
                TextColumn::make('descricao')
                    ->label('Tema')
                    ->formatStateUsing(fn (string $state, array $record): string => $record['numero'].' — '.$state)
                    ->limit(70)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('acordaos_count')
                    ->label('Acórdãos')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('job_status')
                    ->label('Fila')
                    ->alignCenter()
                    ->badge()
                    ->placeholder('—')
                    ->color(fn (?string $state): string => match ($state) {
                        'queued' => 'gray',
                        'running' => 'warning',
                        'done' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('has_ai')
                    ->label('Tem IA')
                    ->alignCenter()
                    ->boolean(),
                TextColumn::make('tribunal')
                    ->label('Tribunal')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tribunal')
                    ->label('Tribunal')
                    ->options([
                        'STF' => 'STF',
                        'STJ' => 'STJ',
                    ])
                    ->default('STF'),
                TernaryFilter::make('has_ai')
                    ->label('Tem IA')
                    ->placeholder('Todos'),
                Filter::make('only_transito')
                    ->label('Somente trânsito em julgado')
                    ->toggle(),
            ])
            ->defaultSort('numero', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->searchable()
            ->selectCurrentPageOnly()
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Ver tema')
                        ->icon(Heroicon::OutlinedEye)
                        ->url(fn (array $record): string => TemaDetalhe::getUrl([
                            'tribunal' => $record['tribunal'],
                            'numero' => $record['numero'],
                        ])),
                    Action::make('analyze')
                        ->label('Analisar com IA')
                        ->icon(Heroicon::OutlinedSparkles)
                        ->visible(fn (array $record): bool => (bool) ($record['is_eligible'] ?? false))
                        ->action(function (array $record): void {
                            $job = app(AcordaoAnalysisEnqueueService::class)->enqueue(
                                $record['tese_id'],
                                $record['tribunal'],
                            );

                            if ($job === null) {
                                Notification::make()
                                    ->warning()
                                    ->title('Não enfileirado')
                                    ->body('O tema não está elegível ou já possui job ativo.')
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->success()
                                ->title('Análise enfileirada')
                                ->body("Tema {$record['numero']} ({$record['tribunal']}) na fila.")
                                ->send();

                            $this->resetTable();
                        }),
                    Action::make('force')
                        ->label('Forçar reprocesso')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (array $record): void {
                            app(AcordaoAnalysisEnqueueService::class)->enqueue(
                                $record['tese_id'],
                                $record['tribunal'],
                                force: true,
                            );

                            Notification::make()
                                ->success()
                                ->title('Reprocesso enfileirado')
                                ->body("Tema {$record['numero']} ({$record['tribunal']}) será reprocessado.")
                                ->send();

                            $this->resetTable();
                        }),
                ])
                    ->label('Ações')
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->button()
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkAction::make('enqueueEligible')
                    ->label('Enfileirar elegíveis')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $eligible = $records
                            ->filter(fn (array $record): bool => (bool) ($record['is_eligible'] ?? false))
                            ->values()
                            ->all();

                        $count = app(AcordaoAnalysisEnqueueService::class)->enqueueEligibleBatch($eligible);

                        Notification::make()
                            ->success()
                            ->title('Lote enfileirado')
                            ->body("{$count} tema(s) elegível(is) enfileirado(s).")
                            ->send();

                        $this->resetTable();
                    }),
                BulkAction::make('dequeue')
                    ->label('Retirar da fila')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $payload = $records->values()->all();
                        $count = app(AcordaoAnalysisEnqueueService::class)->dequeueBatch($payload);

                        Notification::make()
                            ->success()
                            ->title('Jobs removidos')
                            ->body("{$count} job(s) na fila removido(s).")
                            ->send();

                        $this->resetTable();
                    }),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Temas elegíveis';
    }
}
