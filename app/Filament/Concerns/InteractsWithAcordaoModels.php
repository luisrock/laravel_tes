<?php

namespace App\Filament\Concerns;

use App\Models\SiteSetting;
use App\Services\Ai\OpenRouterManagementService;
use Filament\Forms\Components\Select;

trait InteractsWithAcordaoModels
{
    /**
     * @return array<string, string>
     */
    protected function acordaoModelOptions(): array
    {
        $options = app(OpenRouterManagementService::class)->pdfCapableModels();

        $current = $this->defaultAcordaoModelSlug();

        if ($current !== '' && ! isset($options[$current])) {
            $options[$current] = $current;
        }

        return $options;
    }

    protected function defaultAcordaoModelSlug(): string
    {
        return (string) SiteSetting::get(
            'acordao_analysis_model',
            config('ai.acordao_analysis.default_model')
        );
    }

    protected function acordaoModelSelectField(): Select
    {
        return Select::make('model_slug')
            ->label('Modelo')
            ->options(fn (): array => $this->acordaoModelOptions())
            ->default(fn (): string => $this->defaultAcordaoModelSlug())
            ->searchable()
            ->required();
    }
}
