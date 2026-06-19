<?php

/**
 * Testes dos links da extensão Chrome com atribuição UTM (LH-1 / passo S1).
 */
describe('Links da extensão com UTM', function () {

    it('o helper monta a URL da Web Store com os parâmetros UTM', function () {
        $url = extension_webstore_url('footer');

        expect($url)
            ->toContain(config('teses.extension.webstore_url'))
            ->toContain('utm_source=site')
            ->toContain('utm_medium=footer')
            ->toContain('utm_campaign=extensao');
    });

    it('permite sobrescrever source, medium e campaign', function () {
        $url = extension_webstore_url('header', 'parceiro', 'lancamento');

        expect($url)
            ->toContain('utm_source=parceiro')
            ->toContain('utm_medium=header')
            ->toContain('utm_campaign=lancamento');
    });

    it('renderiza o link da extensão com UTM no footer do site', function () {
        $this->get('/termos')
            ->assertOk()
            ->assertSee('utm_source=site', false)
            ->assertSee('utm_medium=footer', false);
    });

    it('o CTA do header aponta para a landing /extensao', function () {
        $this->get('/termos')
            ->assertOk()
            ->assertSee('Extensão', false)
            ->assertSee('href="'.route('extensao').'"', false);
    });

});

describe('Landing /extensao (LH-13 / passo S8)', function () {

    it('responde 200 e mostra o CTA de instalação', function () {
        $this->get('/extensao')
            ->assertOk()
            ->assertSee('Instalar no Chrome', false);
    });

    it('o CTA da landing usa o UTM extensao_page', function () {
        $this->get('/extensao')
            ->assertOk()
            ->assertSee('utm_medium=extensao_page', false)
            ->assertSee('utm_source=site', false);
    });

    it('expõe a rota nomeada extensao', function () {
        expect(route('extensao'))->toContain('/extensao');
    });

});
