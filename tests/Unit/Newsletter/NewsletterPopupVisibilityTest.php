<?php

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Newsletter\NewsletterPopupVisibility;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    config()->set('services.sendy.list_internal_id', 2);
    SiteSetting::set('newsletter_integration_enabled', '0');
    SiteSetting::set('newsletter_popup_enabled', '0');
});

it('retorna false quando flags estão desligadas', function () {
    expect(app(NewsletterPopupVisibility::class)->shouldRender())->toBeFalse();
});

it('retorna true para visitante quando flags estão ligadas', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');

    expect(app(NewsletterPopupVisibility::class)->shouldRender())->toBeTrue();
});

it('retorna true para logado não inscrito no Sendy', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');
    fakeSendyConnection();

    $user = User::factory()->create([
        'newsletter_subscribed_at' => null,
    ]);

    Auth::login($user);

    expect(app(NewsletterPopupVisibility::class)->shouldRender())->toBeTrue();
});

it('retorna false para logado inscrito no Sendy', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');
    fakeSendyConnection();

    $user = User::factory()->create([
        'newsletter_subscribed_at' => null,
    ]);

    seedSendyActiveSubscriber($user->email);

    Auth::login($user);

    expect(app(NewsletterPopupVisibility::class)->shouldRender())->toBeFalse();
});

it('usa cache local quando Sendy lança exceção e user não está inscrito', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');

    $user = User::factory()->create([
        'newsletter_subscribed_at' => null,
    ]);

    bindNewsletterCheckerMock(function ($mock): void {
        $mock->shouldReceive('isEnabled')->andReturn(true);
        $mock->shouldReceive('isSubscribed')->once()->andThrow(new RuntimeException('offline'));
    });

    Auth::login($user);

    expect(app(NewsletterPopupVisibility::class)->shouldRender())->toBeTrue();
});

it('usa cache local quando Sendy lança exceção e user já está inscrito', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');

    $user = User::factory()->create([
        'newsletter_subscribed_at' => now(),
    ]);

    bindNewsletterCheckerMock(function ($mock): void {
        $mock->shouldReceive('isEnabled')->andReturn(true);
        $mock->shouldReceive('isSubscribed')->once()->andThrow(new RuntimeException('offline'));
    });

    Auth::login($user);

    expect(app(NewsletterPopupVisibility::class)->shouldRender())->toBeFalse();
});

it('usa cache local quando integração Sendy está desligada no service', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');

    $user = User::factory()->create([
        'newsletter_subscribed_at' => now(),
    ]);

    bindNewsletterCheckerMock(function ($mock): void {
        $mock->shouldReceive('isEnabled')->andReturn(false);
        $mock->shouldNotReceive('isSubscribed');
    });

    Auth::login($user);

    expect(app(NewsletterPopupVisibility::class)->shouldRender())->toBeFalse();
});
