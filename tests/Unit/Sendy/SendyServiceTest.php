<?php

use App\Enums\NewsletterEventSource;
use App\Enums\SendyStatus;
use App\Models\SiteSetting;
use App\Services\Sendy\NewsletterSubscriptionContext;
use App\Services\Sendy\SendyService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);

    fakeSendyConnection();
});

function sendyContext(): NewsletterSubscriptionContext
{
    return new NewsletterSubscriptionContext(
        source: NewsletterEventSource::NewslettersForm,
        userId: null,
        ip: '127.0.0.1',
    );
}

describe('SendyService::isEnabled', function () {
    it('retorna false quando feature flag desligada', function () {
        SiteSetting::set('newsletter_integration_enabled', '0');

        expect(app(SendyService::class)->isEnabled())->toBeFalse();
    });

    it('retorna true quando feature flag ligada', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        expect(app(SendyService::class)->isEnabled())->toBeTrue();
    });
});

describe('SendyService::subscribe', function () {
    it('retorna failure quando integração desligada', function () {
        SiteSetting::set('newsletter_integration_enabled', '0');

        $result = app(SendyService::class)->subscribe('a@b.com', 'Nome', sendyContext());

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('Integration disabled');
    });

    it('retorna success quando API responde true', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        Http::fake([
            'sendy.test/subscribe' => Http::response('true'),
        ]);

        $result = app(SendyService::class)->subscribe('a@b.com', 'Nome', sendyContext());

        expect($result->success)->toBeTrue();
    });

    it('retorna alreadySubscribed quando API responde Already subscribed', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        Http::fake([
            'sendy.test/subscribe' => Http::response('Already subscribed.'),
        ]);

        $result = app(SendyService::class)->subscribe('a@b.com', 'Nome', sendyContext());

        expect($result->success)->toBeTrue()
            ->and($result->alreadySubscribed)->toBeTrue();
    });

    it('retorna failure quando API responde erro', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        Http::fake([
            'sendy.test/subscribe' => Http::response('Invalid email address'),
        ]);

        $result = app(SendyService::class)->subscribe('bad', 'Nome', sendyContext());

        expect($result->success)->toBeFalse();
    });
});

describe('SendyService::getStatusFromDb', function () {
    it('retorna Subscribed para inscrito ativo', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        \Illuminate\Support\Facades\DB::connection('sendy')->table('subscribers')->insert([
            'email' => 'sub@example.com',
            'list' => 2,
            'unsubscribed' => 0,
            'bounced' => 0,
            'complaint' => 0,
            'confirmed' => 1,
        ]);

        expect(app(SendyService::class)->getStatusFromDb('sub@example.com'))
            ->toBe(SendyStatus::Subscribed);
    });

    it('retorna Unsubscribed quando unsubscribed=1', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        \Illuminate\Support\Facades\DB::connection('sendy')->table('subscribers')->insert([
            'email' => 'out@example.com',
            'list' => 2,
            'unsubscribed' => 1,
            'bounced' => 0,
            'complaint' => 0,
            'confirmed' => 1,
        ]);

        expect(app(SendyService::class)->getStatusFromDb('out@example.com'))
            ->toBe(SendyStatus::Unsubscribed);
    });

    it('retorna NotFound quando email não existe na lista', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        expect(app(SendyService::class)->getStatusFromDb('missing@example.com'))
            ->toBe(SendyStatus::NotFound);
    });
});

describe('SendyService::activeSubscriberCount', function () {
    it('conta apenas inscritos ativos na lista', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        $db = \Illuminate\Support\Facades\DB::connection('sendy');
        $db->table('subscribers')->insert([
            ['email' => 'a@x.com', 'list' => 2, 'unsubscribed' => 0, 'bounced' => 0, 'complaint' => 0, 'confirmed' => 1],
            ['email' => 'b@x.com', 'list' => 2, 'unsubscribed' => 1, 'bounced' => 0, 'complaint' => 0, 'confirmed' => 1],
            ['email' => 'c@x.com', 'list' => 3, 'unsubscribed' => 0, 'bounced' => 0, 'complaint' => 0, 'confirmed' => 1],
        ]);

        expect(app(SendyService::class)->activeSubscriberCount())->toBe(1);
    });
});

describe('SendyService::getStatus', function () {
    it('retorna NotFound quando integração desligada', function () {
        SiteSetting::set('newsletter_integration_enabled', '0');

        expect(app(SendyService::class)->getStatus('any@example.com'))
            ->toBe(SendyStatus::NotFound);
    });

    it('usa API quando db_enabled é false', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');
        config()->set('services.sendy.db_enabled', false);

        Http::fake([
            'https://sendy.test/api/subscribers/subscription-status.php' => Http::response('Subscribed'),
        ]);

        expect(app(SendyService::class)->getStatus('api@example.com'))
            ->toBe(SendyStatus::Subscribed);
    });
});

describe('SendyService com db_enabled false', function () {
    it('getStatusFromDb retorna null sem tentar conexão', function () {
        config()->set('services.sendy.db_enabled', false);

        expect(app(SendyService::class)->getStatusFromDb('any@example.com'))->toBeNull();
    });

    it('activeSubscriberCount retorna null sem tentar conexão', function () {
        config()->set('services.sendy.db_enabled', false);

        expect(app(SendyService::class)->activeSubscriberCount())->toBeNull();
    });
});
