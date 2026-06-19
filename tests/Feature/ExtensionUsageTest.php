<?php

use App\Models\ExtensionUsageDaily;

/**
 * Instrumentação de uso da extensão (LH-7 / passo S5).
 */
describe('ExtensionUsageDaily::sanitizeVersion', function () {

    it('mantém versões válidas (dígitos e pontos)', function () {
        expect(ExtensionUsageDaily::sanitizeVersion('1.0.0'))->toBe('1.0.0');
        expect(ExtensionUsageDaily::sanitizeVersion(' 2.3 '))->toBe('2.3');
    });

    it('rejeita versões inválidas como "unknown"', function () {
        expect(ExtensionUsageDaily::sanitizeVersion(null))->toBe('unknown');
        expect(ExtensionUsageDaily::sanitizeVersion(''))->toBe('unknown');
        expect(ExtensionUsageDaily::sanitizeVersion('<script>'))->toBe('unknown');
        expect(ExtensionUsageDaily::sanitizeVersion('1.0.0-beta'))->toBe('unknown');
        expect(ExtensionUsageDaily::sanitizeVersion('1.0.0.0.0.0.0'))->toBe('unknown'); // > 12 chars
    });

});

describe('ExtensionUsageDaily::record', function () {

    it('cria a linha do dia com hits = 1 na primeira chamada', function () {
        ExtensionUsageDaily::record('1.0.0');

        $this->assertDatabaseHas('extension_usage_dailies', [
            'date' => now()->toDateString(),
            'extension_version' => '1.0.0',
            'hits' => 1,
        ]);
    });

    it('incrementa hits na mesma data e versão', function () {
        ExtensionUsageDaily::record('1.0.0');
        ExtensionUsageDaily::record('1.0.0');

        expect(ExtensionUsageDaily::where('extension_version', '1.0.0')->value('hits'))->toBe(2);
        expect(ExtensionUsageDaily::count())->toBe(1);
    });

    it('separa contadores por versão', function () {
        ExtensionUsageDaily::record('1.0.0');
        ExtensionUsageDaily::record('1.1.0');

        expect(ExtensionUsageDaily::count())->toBe(2);
    });

});

describe('Agregações para o painel (passo stats)', function () {

    it('soma os hits do período em totalHits', function () {
        ExtensionUsageDaily::create(['date' => now()->toDateString(), 'extension_version' => '1.0.0', 'hits' => 10]);
        ExtensionUsageDaily::create(['date' => now()->subDays(2)->toDateString(), 'extension_version' => '1.0.0', 'hits' => 5]);
        ExtensionUsageDaily::create(['date' => now()->subDays(40)->toDateString(), 'extension_version' => '1.0.0', 'hits' => 99]);

        expect(ExtensionUsageDaily::totalHits('30'))->toBe(15);
    });

    it('calcula a média diária pelo número de dias do período', function () {
        ExtensionUsageDaily::create(['date' => now()->toDateString(), 'extension_version' => '1.0.0', 'hits' => 30]);

        expect(ExtensionUsageDaily::dailyAverage('30'))->toBe(1.0);
    });

    it('retorna a versão com mais hits em topVersion', function () {
        ExtensionUsageDaily::create(['date' => now()->toDateString(), 'extension_version' => '1.0.0', 'hits' => 3]);
        ExtensionUsageDaily::create(['date' => now()->toDateString(), 'extension_version' => '1.1.0', 'hits' => 8]);

        expect(ExtensionUsageDaily::topVersion('30'))->toBe('1.1.0');
    });

    it('retorna null em topVersion quando não há dados', function () {
        expect(ExtensionUsageDaily::topVersion('30'))->toBeNull();
    });

});

describe('Endpoint unified-search registra uso', function () {

    it('registra a versão recebida no header X-Extension-Version', function () {
        $this->postJson('/api/unified-search', ['keyword' => 'dano moral'], [
            'X-Extension-Version' => '1.0.0',
        ]);

        $this->assertDatabaseHas('extension_usage_dailies', [
            'extension_version' => '1.0.0',
        ]);
    });

    it('registra como unknown quando o header está ausente', function () {
        $this->postJson('/api/unified-search', ['keyword' => 'dano moral']);

        $this->assertDatabaseHas('extension_usage_dailies', [
            'extension_version' => 'unknown',
        ]);
    });

});
