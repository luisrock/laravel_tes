<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

describe('SiteSetting Model', function () {

    it('cria e recupera setting via helpers estaticos', function () {
        SiteSetting::set('test_key', 'test_value');

        expect(SiteSetting::get('test_key'))->toBe('test_value');
    });

    it('retorna default quando key nao existe', function () {
        expect(SiteSetting::get('inexistente', 'fallback'))->toBe('fallback');
    });

    it('atualiza valor existente sem duplicar', function () {
        SiteSetting::set('minha_key', 'v1');
        SiteSetting::set('minha_key', 'v2');

        expect(SiteSetting::where('key', 'minha_key')->count())->toBe(1);
        expect(SiteSetting::get('minha_key'))->toBe('v2');
    });

    it('invalida cache ao salvar', function () {
        SiteSetting::set('cached_key', 'original');

        // Força leitura do cache
        SiteSetting::get('cached_key');

        // Atualiza
        SiteSetting::set('cached_key', 'updated');

        expect(SiteSetting::get('cached_key'))->toBe('updated');
    });

    it('suporta valor null', function () {
        SiteSetting::set('null_key', null);

        expect(SiteSetting::where('key', 'null_key')->exists())->toBeTrue();
    });
});

describe('SiteSetting::getAsBool', function () {

    it('retorna default quando a key nao existe', function () {
        expect(SiteSetting::getAsBool('bool_missing_xyz', true))->toBeTrue();
        expect(SiteSetting::getAsBool('bool_missing_xyz', false))->toBeFalse();
    });

    it('interpreta 1 e 0 da base sem usar cast bool incorreto', function () {
        SiteSetting::set('bool_a', '1');
        SiteSetting::set('bool_b', '0');

        expect(SiteSetting::getAsBool('bool_a', false))->toBeTrue();
        expect(SiteSetting::getAsBool('bool_b', true))->toBeFalse();
    });
});
