<?php

use App\Models\SiteSetting;

beforeEach(function () {
    SiteSetting::clearCache('acordao_analysis_model');
});

it('defaults to claude sonnet 4.6 when unset', function () {
    expect(config('ai.acordao_analysis.default_model'))->toBe('anthropic/claude-sonnet-4.6');

    $model = SiteSetting::get('acordao_analysis_model', config('ai.acordao_analysis.default_model'));

    expect($model)->toBe('anthropic/claude-sonnet-4.6');
});

it('reads back the value written by the admin', function () {
    SiteSetting::set('acordao_analysis_model', 'google/gemini-2.5-pro');

    $model = SiteSetting::get('acordao_analysis_model', config('ai.acordao_analysis.default_model'));

    expect($model)->toBe('google/gemini-2.5-pro');

    expect(SiteSetting::query()->where('key', 'acordao_analysis_model')->count())->toBe(1);
});

it('overwrites a previous value without duplicating the row', function () {
    SiteSetting::set('acordao_analysis_model', 'google/gemini-2.5-pro');
    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.5');

    expect(SiteSetting::get('acordao_analysis_model'))->toBe('anthropic/claude-sonnet-4.5')
        ->and(SiteSetting::query()->where('key', 'acordao_analysis_model')->count())->toBe(1);
});
