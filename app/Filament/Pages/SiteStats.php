<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\NewsletterBySourceChart;
use App\Filament\Widgets\NewsletterDailyChart;
use App\Filament\Widgets\NewsletterPopupAbStats;
use App\Filament\Widgets\SiteOverviewStats;
use App\Services\Newsletter\SiteMetrics;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Concerns\HasFilters;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;

class SiteStats extends Page
{
    use HasFilters;
    use HasFiltersForm;

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Estatísticas';

    protected static ?string $title = 'Estatísticas do site';

    protected static ?string $slug = 'estatisticas';

    protected static ?int $navigationSort = 54;

    public function mount(): void
    {
        $this->mountHasFilters();

        if (! count($this->filters ?? [])) {
            $this->filters = ['period' => '30'];
        }
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('period')
                    ->label('Período')
                    ->options(SiteMetrics::PERIOD_OPTIONS)
                    ->default('30')
                    ->native(false),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncNow')
                ->label('Atualizar')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Atualizar contas inscritas?')
                ->modalDescription('Compara as contas do site com a lista de email (Sendy) e atualiza quem aparece como inscrito em Minha conta.')
                ->action(function (): void {
                    Artisan::call('newsletter:sync', ['--all' => true]);

                    Notification::make()
                        ->success()
                        ->title('Atualização concluída')
                        ->body(trim(Artisan::output()) ?: 'Contas atualizadas com a lista de email.')
                        ->send();
                }),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            NewsletterDailyChart::class,
            NewsletterBySourceChart::class,
            NewsletterPopupAbStats::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 2;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFiltersFormContentComponent(),
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 3,
                ])
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                        SiteOverviewStats::class,
                    ])),
            ]);
    }

    public function getFiltersFormContentComponent(): Component
    {
        return EmbeddedSchema::make('filtersForm');
    }
}
