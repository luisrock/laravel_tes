<?php

use App\Exceptions\AcordaoAnalysisPermanentException;
use App\Support\AcordaoJobFailure;
use RuntimeException;

it('trata erro OpenRouter 400 como permanente', function () {
    $exception = new RuntimeException('OpenRouter Error: [400] Provider returned error');

    expect(AcordaoJobFailure::isPermanent($exception))->toBeTrue();
});

it('trata exceção de domínio como permanente', function () {
    expect(AcordaoJobFailure::isPermanent(new AcordaoAnalysisPermanentException('falha')))->toBeTrue();
});

it('mantém timeout de rede como retryável', function () {
    expect(AcordaoJobFailure::isPermanent(new RuntimeException('Timeout de rede')))->toBeFalse();
});
