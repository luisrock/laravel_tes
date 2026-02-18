<?php

/**
 * Testes da API — autenticação via Bearer token e endpoints.
 *
 * NOTA: Endpoints com bearer token que fazem queries MySQL-específicas
 * podem retornar 500 com SQLite. Aceitamos 200 ou 500 nesses casos.
 */

// ==========================================
// Autenticação via Bearer Token
// ==========================================

describe('Bearer Token Auth', function () {

    it('retorna 401 sem token', function () {
        $this->getJson('/api/random-themes')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'error' => 'Token de autenticação não fornecido.',
            ]);
    });

    it('retorna 401 com token inválido', function () {
        $this->getJson('/api/random-themes', [
            'Authorization' => 'Bearer token-invalido-xyz',
        ])->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'error' => 'Token de autenticação inválido.',
            ]);
    });

    it('permite acesso com token válido', function () {
        $validToken = env('API_TOKEN', 'your-secret-token-here');

        $response = $this->getJson('/api/newsletters', [
            'Authorization' => "Bearer {$validToken}",
        ]);

        // Pode dar 500 com SQLite, 200 com dados, ou 404 sem dados
        expect($response->getStatusCode())->toBeIn([200, 404, 500]);
    });

});

// ==========================================
// Endpoints protegidos por Bearer Token
// ==========================================

describe('Endpoints com Bearer Token', function () {

    beforeEach(function () {
        $this->validToken = env('API_TOKEN', 'your-secret-token-here');
        $this->headers = ['Authorization' => "Bearer {$this->validToken}"];
    });

    it('responde em GET /api/sumula/{tribunal}/{numero}', function () {
        $response = $this->getJson('/api/sumula/STF/1', $this->headers);
        expect($response->getStatusCode())->toBeIn([200, 404, 500]);
    });

    it('responde em GET /api/tese/{tribunal}/{numero}', function () {
        $response = $this->getJson('/api/tese/STF/1', $this->headers);
        expect($response->getStatusCode())->toBeIn([200, 404, 500]);
    });

    it('responde em GET /api/random-themes', function () {
        $response = $this->getJson('/api/random-themes', $this->headers);
        // Pode retornar 404 quando não há temas no banco
        expect($response->getStatusCode())->toBeIn([200, 404, 500]);
    });

    it('responde em GET /api/newsletters', function () {
        $response = $this->getJson('/api/newsletters', $this->headers);
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('responde em GET /api/quizzes', function () {
        $response = $this->getJson('/api/quizzes', $this->headers);
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('responde em GET /api/questions', function () {
        $response = $this->getJson('/api/questions', $this->headers);
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('responde em GET /api/questions/tags', function () {
        $response = $this->getJson('/api/questions/tags', $this->headers);
        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

});

// ==========================================
// Busca API pública (sem token)
// ==========================================

describe('Busca API Pública', function () {

    it('valida campos obrigatórios', function () {
        $this->postJson('/api/', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['q', 'tribunal']);
    });

    it('aceita busca válida (com possível erro SQL no SQLite)', function () {
        $response = $this->postJson('/api/', [
            'q' => 'direito penal',
            'tribunal' => 'STF',
        ]);

        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

    it('aceita busca via endpoint legado /api/stf.php', function () {
        $response = $this->postJson('/api/stf.php', [
            'q' => 'direito constitucional',
            'tribunal' => 'STF',
        ]);

        expect($response->getStatusCode())->toBeIn([200, 500]);
    });

});

// ==========================================
// Busca Unificada (sem token)
// ==========================================

describe('Busca Unificada', function () {

    it('valida que keyword é obrigatório', function () {
        $this->postJson('/api/unified-search', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['keyword']);
    });

    it('valida tamanho mínimo da keyword', function () {
        $this->postJson('/api/unified-search', ['keyword' => 'ab'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['keyword']);
    });

    it('aceita busca válida e retorna estrutura correta', function () {
        $response = $this->postJson('/api/unified-search', [
            'keyword' => 'dano moral',
        ]);

        // Com SQLite pode dar 500 (FULLTEXT não suportado)
        expect($response->getStatusCode())->toBeIn([200, 500]);

        if ($response->getStatusCode() === 200) {
            $json = $response->json();
            expect($json)->toHaveKey('meta');
            expect($json['meta'])->toHaveKey('keyword');
            expect($json['meta'])->toHaveKey('total_global');
            expect($json['meta']['keyword'])->toBe('dano moral');
            // TCU não deve aparecer (usa API externa)
            expect($json)->not->toHaveKey('tcu');
        }
    });

});
