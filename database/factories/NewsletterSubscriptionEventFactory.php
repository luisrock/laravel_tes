<?php

namespace Database\Factories;

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSubscriptionEvent>
 */
class NewsletterSubscriptionEventFactory extends Factory
{
    protected $model = NewsletterSubscriptionEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'email' => fake()->safeEmail(),
            'action' => NewsletterEventAction::Subscribed->value,
            'source' => NewsletterEventSource::NewslettersForm->value,
            'popup_variant' => null,
            'popup_trigger' => null,
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->url(),
            'page_url' => fake()->url(),
            'meta' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
