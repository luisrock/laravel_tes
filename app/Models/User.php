<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use Billable, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Autoriza acesso ao painel Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        $admins = config('tes_constants.admins', []);

        return $this->email && in_array($this->email, $admins, true);
    }

    /**
     * Retorna a fonte da assinatura (prepara para assinaturas coletivas futuras).
     *
     * Hoje: retorna $this (assinatura individual)
     * Futuro: pode retornar Team se usuario faz parte de um
     */
    public function getSubscriptionSource(): ?Model
    {
        return $this;
    }

    /**
     * Verifica se usuario e assinante ativo (inclui grace period).
     */
    public function isSubscriber(): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        return $source?->subscribed($subscriptionName) ?? false;
    }

    /**
     * Verifica se usuario tem acesso a uma feature especifica.
     */
    public function hasFeature(string $featureKey): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if (! $source || ! $source->subscribed($subscriptionName)) {
            return false;
        }

        $subscription = $source->subscription($subscriptionName);
        if (! $subscription) {
            return false;
        }

        $tierProductIds = config('subscription.tier_product_ids', []);

        if (empty($tierProductIds)) {
            Log::error('hasFeature: tier_product_ids nao configurado', [
                'user_id' => $this->id,
                'feature_key' => $featureKey,
            ]);

            return false;
        }

        $item = $subscription->items()
            ->whereIn('stripe_product', $tierProductIds)
            ->first();

        if (! $item) {
            Log::warning('hasFeature: subscription sem item de tier valido', [
                'user_id' => $this->id,
                'subscription_id' => $subscription->id,
                'tier_product_ids' => $tierProductIds,
            ]);

            return false;
        }

        return PlanFeature::productHasFeature($item->stripe_product, $featureKey);
    }

    /**
     * Retorna o nome do plano atual do usuario.
     */
    public function getSubscriptionPlan(): ?string
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if (! $source || ! $source->subscribed($subscriptionName)) {
            return null;
        }

        $subscription = $source->subscription($subscriptionName);
        if (! $subscription) {
            return null;
        }

        $tierProductIds = config('subscription.tier_product_ids', []);
        $item = $subscription->items()
            ->whereIn('stripe_product', $tierProductIds)
            ->first();

        return $item?->stripe_product;
    }

    /**
     * Verifica se usuario pode acessar conteudo exclusivo.
     */
    public function canAccessExclusiveContent(): bool
    {
        return $this->hasFeature(config('subscription.features.exclusive_content', 'exclusive_content'));
    }

    /**
     * Verifica se usuario deve ver anuncios.
     */
    public function shouldSeeAds(): bool
    {
        return ! $this->hasFeature(config('subscription.features.no_ads', 'no_ads'));
    }

    /**
     * Verifica se usuario esta em grace period (cancelou mas ainda tem acesso).
     */
    public function isOnGracePeriod(): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        $subscription = $source?->subscription($subscriptionName);

        return $subscription?->onGracePeriod() ?? false;
    }

    /**
     * Retorna a data de termino do acesso (se em grace period).
     */
    public function getAccessEndsAt(): ?Carbon
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        $subscription = $source?->subscription($subscriptionName);

        return $subscription?->ends_at;
    }
}
