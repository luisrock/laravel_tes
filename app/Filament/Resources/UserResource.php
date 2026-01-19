<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use App\Support\SubscriptionUi;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Assinaturas';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('subscription_status')
                    ->label('Assinatura')
                    ->getStateUsing(function (User $record): string {
                        $subscriptionName = config('subscription.default_subscription_name', 'default');
                        $subscription = $record->subscriptions->firstWhere('name', $subscriptionName);

                        if (!$subscription) {
                            return SubscriptionUi::LABEL_NONE;
                        }

                        return SubscriptionUi::statusLabel($subscription->stripe_status, $subscription->ends_at);
                    })
                    ->color(fn (string $state): string => SubscriptionUi::statusColor($state)),
                BadgeColumn::make('subscription_plan')
                    ->label('Plano')
                    ->getStateUsing(function (User $record): string {
                        $subscriptionName = config('subscription.default_subscription_name', 'default');
                        $subscription = $record->subscriptions->firstWhere('name', $subscriptionName);

                        if (!$subscription) {
                            return SubscriptionUi::LABEL_NONE;
                        }

                        $productId = SubscriptionUi::resolveTierProductId($subscription->items ?? []);

                        return SubscriptionUi::tierLabel($productId);
                    })
                    ->color(fn (string $state): string => SubscriptionUi::tierColor($state)),
                TextColumn::make('stripe_id')
                    ->label('Stripe Customer')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subscription_status')
                    ->label('Assinatura')
                    ->options([
                        'active' => 'Ativa',
                        'grace' => 'Grace period',
                        'canceled' => 'Cancelada',
                        'none' => 'Sem assinatura',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'active' => $query->whereHas('subscriptions', function (Builder $subQuery) {
                                $subQuery->where('stripe_status', 'active')
                                    ->whereNull('ends_at');
                            }),
                            'grace' => $query->whereHas('subscriptions', function (Builder $subQuery) {
                                $subQuery->whereNotNull('ends_at')
                                    ->where('ends_at', '>', now());
                            }),
                            'canceled' => $query->whereHas('subscriptions', function (Builder $subQuery) {
                                $subQuery->whereNotNull('ends_at')
                                    ->where('ends_at', '<=', now());
                            }),
                            'none' => $query->whereDoesntHave('subscriptions'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('subscription_plan')
                    ->label('Plano')
                    ->options(function (): array {
                        return config('subscription.tier_labels', []);
                    })
                    ->query(function (Builder $query, array $data) {
                        $productId = $data['value'] ?? null;

                        if (!$productId) {
                            return $query;
                        }

                        return $query->whereHas('subscriptions.items', function (Builder $itemQuery) use ($productId) {
                            $itemQuery->where('stripe_product', $productId);
                        });
                    }),
            ])
            ->actions([
                Action::make('stripe')
                    ->label('Stripe')
                    ->url(fn (User $record): ?string => static::getStripeCustomerUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (User $record): bool => !empty($record->stripe_id)),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['subscriptions', 'subscriptions.items']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    protected static function getStripeCustomerUrl(User $record): ?string
    {
        if (empty($record->stripe_id)) {
            return null;
        }

        $base = static::getStripeDashboardBaseUrl();

        return "{$base}/customers/{$record->stripe_id}";
    }

    protected static function getStripeDashboardBaseUrl(): string
    {
        $secret = (string) config('cashier.secret');
        $isTestMode = str_starts_with($secret, 'sk_test');

        return $isTestMode ? 'https://dashboard.stripe.com/test' : 'https://dashboard.stripe.com';
    }
}
