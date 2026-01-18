<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Retorna a fonte da assinatura (prepara para assinaturas coletivas futuras).
     * 
     * Hoje: retorna $this (assinatura individual)
     * Futuro: pode retornar Team se usuário faz parte de um
     */
    public function getSubscriptionSource(): ?Model
    {
        // Futuro: verificar se usuário pertence a um Team com assinatura
        // if ($team = $this->currentTeam) {
        //     return $team;
        // }
        
        return $this;
    }

    /**
     * Verifica se usuário é assinante ativo (inclui grace period).
     */
    public function isSubscriber(): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');
        
        return $source?->subscribed($subscriptionName) ?? false;
    }

    /**
     * Verifica se usuário tem acesso a uma feature específica.
     */
    public function hasFeature(string $featureKey): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if (!$source || !$source->subscribed($subscriptionName)) {
            return false;
        }

        $subscription = $source->subscription($subscriptionName);
        if (!$subscription) {
            return false;
        }

        // Busca o item do tier (produto que está na nossa lista de tiers)
        $tierProductIds = config('subscription.tier_product_ids', []);

        if (empty($tierProductIds)) {
            Log::error('hasFeature: tier_product_ids não configurado', [
                'user_id' => $this->id,
                'feature_key' => $featureKey,
            ]);
            return false;
        }

        $item = $subscription->items()
            ->whereIn('stripe_product', $tierProductIds)
            ->first();

        if (!$item) {
            Log::warning('hasFeature: subscription sem item de tier válido', [
                'user_id' => $this->id,
                'subscription_id' => $subscription->id,
                'tier_product_ids' => $tierProductIds,
            ]);
            return false;
        }

        return PlanFeature::productHasFeature($item->stripe_product, $featureKey);
    }

    /**
     * Retorna o nome do plano atual do usuário.
     */
    public function getSubscriptionPlan(): ?string
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        if (!$source || !$source->subscribed($subscriptionName)) {
            return null;
        }

        $subscription = $source->subscription($subscriptionName);
        if (!$subscription) {
            return null;
        }

        $tierProductIds = config('subscription.tier_product_ids', []);
        $item = $subscription->items()
            ->whereIn('stripe_product', $tierProductIds)
            ->first();

        return $item?->stripe_product;
    }

    /**
     * Verifica se usuário pode acessar conteúdo exclusivo.
     */
    public function canAccessExclusiveContent(): bool
    {
        return $this->hasFeature(config('subscription.features.exclusive_content', 'exclusive_content'));
    }

    /**
     * Verifica se usuário deve ver anúncios.
     */
    public function shouldSeeAds(): bool
    {
        return !$this->hasFeature(config('subscription.features.no_ads', 'no_ads'));
    }

    /**
     * Verifica se usuário está em grace period (cancelou mas ainda tem acesso).
     */
    public function isOnGracePeriod(): bool
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        $subscription = $source?->subscription($subscriptionName);
        
        return $subscription?->onGracePeriod() ?? false;
    }

    /**
     * Retorna a data de término do acesso (se em grace period).
     */
    public function getAccessEndsAt(): ?\Carbon\Carbon
    {
        $source = $this->getSubscriptionSource();
        $subscriptionName = config('subscription.default_subscription_name', 'default');

        $subscription = $source?->subscription($subscriptionName);
        
        return $subscription?->ends_at;
    }
}
