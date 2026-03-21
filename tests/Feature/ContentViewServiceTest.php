<?php

use App\Models\ContentView;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ContentViewService;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('registered', 'web');
    \Spatie\Permission\Models\Role::findOrCreate('admin', 'web');

    $this->service = new ContentViewService;
});

// ==========================================
// recordView()
// ==========================================

describe('recordView', function () {

    it('registra uma nova view e retorna true', function () {
        $user = User::factory()->create();

        $result = $this->service->recordView($user, 'tese', 42, 'stf');

        expect($result)->toBeTrue();
        expect(ContentView::count())->toBe(1);
        expect(ContentView::first())
            ->user_id->toBe($user->id)
            ->content_type->toBe('tese')
            ->content_id->toBe(42)
            ->tribunal->toBe('stf');
    });

    it('e idempotente para o mesmo conteudo nas ultimas 24h', function () {
        $user = User::factory()->create();

        $this->service->recordView($user, 'tese', 42, 'stf');
        $result = $this->service->recordView($user, 'tese', 42, 'stf');

        expect($result)->toBeFalse();
        expect(ContentView::count())->toBe(1);
    });

    it('permite re-registro apos 24h', function () {
        $user = User::factory()->create();

        ContentView::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'tese',
            'content_id' => 42,
            'tribunal' => 'stf',
            'viewed_at' => now()->subHours(25),
        ]);

        $result = $this->service->recordView($user, 'tese', 42, 'stf');

        expect($result)->toBeTrue();
        expect(ContentView::count())->toBe(2);
    });

    it('registra views de content_types diferentes como distintas', function () {
        $user = User::factory()->create();

        $this->service->recordView($user, 'tese', 42, 'stf');
        $result = $this->service->recordView($user, 'sumula', 42, 'stf');

        expect($result)->toBeTrue();
        expect(ContentView::count())->toBe(2);
    });

    it('registra views de teses diferentes como distintas', function () {
        $user = User::factory()->create();

        $this->service->recordView($user, 'tese', 1, 'stf');
        $this->service->recordView($user, 'tese', 2, 'stf');
        $this->service->recordView($user, 'tese', 3, 'stj');

        expect(ContentView::count())->toBe(3);
    });
});

// ==========================================
// getDailyLimit()
// ==========================================

describe('getDailyLimit', function () {

    it('retorna limite da SiteSetting para usuario registered', function () {
        SiteSetting::set('metered_wall_daily_limit', '5');
        $user = User::factory()->create();
        $user->assignRole('registered');

        expect($this->service->getDailyLimit($user))->toBe(5);
    });

    it('retorna null para admin', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        expect($this->service->getDailyLimit($user))->toBeNull();
    });

    it('retorna null para subscriber', function () {
        $user = createSubscribedUser('prod_test');

        expect($this->service->getDailyLimit($user))->toBeNull();
    });

    it('usa default 3 quando SiteSetting nao existe', function () {
        $user = User::factory()->create();
        $user->assignRole('registered');

        expect($this->service->getDailyLimit($user))->toBe(3);
    });
});

// ==========================================
// hasReachedDailyLimit()
// ==========================================

describe('hasReachedDailyLimit', function () {

    it('retorna false quando usuario nao atingiu o limite', function () {
        SiteSetting::set('metered_wall_daily_limit', '3');
        $user = User::factory()->create();
        $user->assignRole('registered');

        $this->service->recordView($user, 'tese', 1, 'stf');
        $this->service->recordView($user, 'tese', 2, 'stf');

        expect($this->service->hasReachedDailyLimit($user))->toBeFalse();
    });

    it('retorna true quando usuario excedeu o limite', function () {
        SiteSetting::set('metered_wall_daily_limit', '3');
        $user = User::factory()->create();
        $user->assignRole('registered');

        $this->service->recordView($user, 'tese', 1, 'stf');
        $this->service->recordView($user, 'tese', 2, 'stf');
        $this->service->recordView($user, 'tese', 3, 'stj');

        expect($this->service->hasReachedDailyLimit($user))->toBeFalse();

        $this->service->recordView($user, 'tese', 4, 'stf');

        expect($this->service->hasReachedDailyLimit($user))->toBeTrue();
    });

    it('retorna false para subscriber mesmo com muitas views', function () {
        SiteSetting::set('metered_wall_daily_limit', '1');
        $user = createSubscribedUser('prod_test');

        $this->service->recordView($user, 'tese', 1, 'stf');
        $this->service->recordView($user, 'tese', 2, 'stf');
        $this->service->recordView($user, 'tese', 3, 'stf');

        expect($this->service->hasReachedDailyLimit($user))->toBeFalse();
    });

    it('retorna false para admin mesmo com muitas views', function () {
        SiteSetting::set('metered_wall_daily_limit', '1');
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->service->recordView($user, 'tese', 1, 'stf');
        $this->service->recordView($user, 'tese', 2, 'stf');

        expect($this->service->hasReachedDailyLimit($user))->toBeFalse();
    });

    it('nao conta views com mais de 24h', function () {
        SiteSetting::set('metered_wall_daily_limit', '2');
        $user = User::factory()->create();
        $user->assignRole('registered');

        // View antiga (25h atrás)
        ContentView::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'tese',
            'content_id' => 1,
            'tribunal' => 'stf',
            'viewed_at' => now()->subHours(25),
        ]);

        // View recente
        $this->service->recordView($user, 'tese', 2, 'stf');

        expect($this->service->hasReachedDailyLimit($user))->toBeFalse();
    });
});

// ==========================================
// remainingViews()
// ==========================================

describe('remainingViews', function () {

    it('retorna null para subscriber', function () {
        $user = createSubscribedUser('prod_test');

        expect($this->service->remainingViews($user))->toBeNull();
    });

    it('retorna limite cheio quando nao ha views', function () {
        SiteSetting::set('metered_wall_daily_limit', '3');
        $user = User::factory()->create();
        $user->assignRole('registered');

        expect($this->service->remainingViews($user))->toBe(3);
    });

    it('retorna views restantes corretamente', function () {
        SiteSetting::set('metered_wall_daily_limit', '3');
        $user = User::factory()->create();
        $user->assignRole('registered');

        $this->service->recordView($user, 'tese', 1, 'stf');

        expect($this->service->remainingViews($user))->toBe(2);
    });

    it('nunca retorna valor negativo', function () {
        SiteSetting::set('metered_wall_daily_limit', '1');
        $user = User::factory()->create();
        $user->assignRole('registered');

        $this->service->recordView($user, 'tese', 1, 'stf');
        $this->service->recordView($user, 'tese', 2, 'stf');

        expect($this->service->remainingViews($user))->toBe(0);
    });
});

// ==========================================
// isMeteredWallEnabled()
// ==========================================

describe('isMeteredWallEnabled', function () {

    it('retorna true por default', function () {
        expect($this->service->isMeteredWallEnabled())->toBeTrue();
    });

    it('retorna false quando desativado', function () {
        SiteSetting::set('metered_wall_enabled', '0');

        expect($this->service->isMeteredWallEnabled())->toBeFalse();
    });

    it('retorna true quando ativado', function () {
        SiteSetting::set('metered_wall_enabled', '1');

        expect($this->service->isMeteredWallEnabled())->toBeTrue();
    });
});
