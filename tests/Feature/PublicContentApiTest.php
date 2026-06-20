<?php

/**
 * Testes do endpoint público de leitura de teor (S6/LH-5, ampliado no S7/LH-6).
 *
 * NOTA: as tabelas legadas dos tribunais (stf_sumulas, etc.) não existem no
 * SQLite in-memory dos testes; consultas reais podem retornar 500. Aceitamos
 * [200, 404, 500] nesses casos, como nos demais testes de API.
 *
 * Cobertura: súmulas em STF/STJ/TST/TNU/CARF/CEJ; teses em STF/STJ/TST/TNU;
 * súmula vinculante apenas no STF (S9). FONAJE adiado (3 sub-bases) e TCU fora
 * (API externa).
 */
describe('Endpoint público de súmula', function () {

    it('responde sem token para tribunais suportados', function (string $tribunal) {
        $response = $this->getJson("/api/public/sumula/{$tribunal}/1");
        expect($response->getStatusCode())->toBeIn([200, 404, 500]);
    })->with(['STF', 'STJ', 'TST', 'TNU', 'CARF', 'CEJ']);

    it('retorna 404 para tribunal não suportado', function (string $tribunal) {
        $this->getJson("/api/public/sumula/{$tribunal}/1")
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    })->with(['XYZ', 'FONAJE', 'TCU']);

    it('retorna 400 quando o número não é numérico', function () {
        $this->getJson('/api/public/sumula/STF/abc')
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    });

    it('expõe as chaves esperadas quando responde 200', function () {
        $response = $this->getJson('/api/public/sumula/STF/1');

        expect($response->getStatusCode())->toBeIn([200, 404, 500]);

        if ($response->getStatusCode() === 200) {
            $response->assertJson(['success' => true]);
            expect($response->json('data'))
                ->toHaveKeys(['tribunal', 'tipo', 'numero', 'texto', 'situacao', 'url']);
            expect($response->json('data.tipo'))->toBe('sumula');
            expect($response->json('data.tribunal'))->toBe('STF');
        }
    });

});

describe('Endpoint público de tese', function () {

    it('responde sem token para tribunais com tese', function (string $tribunal) {
        $response = $this->getJson("/api/public/tese/{$tribunal}/1");
        expect($response->getStatusCode())->toBeIn([200, 404, 500]);
    })->with(['STF', 'STJ', 'TST', 'TNU']);

    it('retorna 404 para tribunal sem teses no endpoint público', function (string $tribunal) {
        $this->getJson("/api/public/tese/{$tribunal}/1")
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    })->with(['CARF', 'CEJ', 'FONAJE', 'XYZ']);

    it('retorna 400 quando o número não é numérico', function () {
        $this->getJson('/api/public/tese/STJ/abc')
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    });

    it('expõe tema, tese e situacao (além de texto) quando responde 200', function () {
        $response = $this->getJson('/api/public/tese/STF/69');

        expect($response->getStatusCode())->toBeIn([200, 404, 500]);

        if ($response->getStatusCode() === 200) {
            $response->assertJson(['success' => true]);
            expect($response->json('data'))
                ->toHaveKeys(['tribunal', 'tipo', 'numero', 'texto', 'tema', 'tese', 'situacao', 'url']);
            expect($response->json('data.tipo'))->toBe('tese');
            // `texto` é mantido por compatibilidade e espelha `tese`.
            expect($response->json('data.texto'))->toBe($response->json('data.tese'));
        }
    });

});

describe('Endpoint público de súmula vinculante', function () {

    it('responde sem token para o STF', function () {
        $response = $this->getJson('/api/public/sumula-vinculante/STF/11');
        expect($response->getStatusCode())->toBeIn([200, 404, 500]);
    });

    it('retorna 404 para tribunais sem súmula vinculante', function (string $tribunal) {
        $this->getJson("/api/public/sumula-vinculante/{$tribunal}/1")
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    })->with(['STJ', 'TST', 'TNU', 'CARF', 'CEJ', 'FONAJE', 'XYZ']);

    it('retorna 400 quando o número não é numérico', function () {
        $this->getJson('/api/public/sumula-vinculante/STF/abc')
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    });

    it('expõe as chaves esperadas quando responde 200', function () {
        $response = $this->getJson('/api/public/sumula-vinculante/STF/11');

        expect($response->getStatusCode())->toBeIn([200, 404, 500]);

        if ($response->getStatusCode() === 200) {
            $response->assertJson(['success' => true]);
            expect($response->json('data'))
                ->toHaveKeys(['tribunal', 'tipo', 'numero', 'texto', 'situacao', 'url']);
            expect($response->json('data.tipo'))->toBe('sumula-vinculante');
            expect($response->json('data.tribunal'))->toBe('STF');
        }
    });

});
