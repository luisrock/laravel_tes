<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundRequestResource\Pages\EditRefundRequest;
use App\Filament\Resources\RefundRequestResource\Pages\ListRefundRequests;
use App\Models\RefundRequest;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Assinaturas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Estornos';

    protected static ?string $modelLabel = 'Solicitação de Estorno';

    protected static ?string $pluralModelLabel = 'Solicitações de Estorno';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('user')
                    ->label('Usuário')
                    ->content(fn (RefundRequest $record): string => $record->user?->email ?? '-'),
                Placeholder::make('stripe_subscription_id')
                    ->label('Stripe Subscription')
                    ->content(fn (RefundRequest $record): string => $record->stripe_subscription_id ?? '-'),
                Placeholder::make('stripe_invoice_id')
                    ->label('Stripe Invoice')
                    ->content(fn (RefundRequest $record): string => $record->stripe_invoice_id ?? '-'),
                Placeholder::make('stripe_payment_intent_id')
                    ->label('Stripe Payment Intent')
                    ->content(fn (RefundRequest $record): string => $record->stripe_payment_intent_id ?? '-'),
                Placeholder::make('reason')
                    ->label('Motivo')
                    ->content(fn (RefundRequest $record): string => $record->reason ?? '-'),
                Select::make('status')
                    ->label('Status')
                    ->required()
                    ->options([
                        RefundRequest::STATUS_PENDING => 'Pendente',
                        RefundRequest::STATUS_APPROVED => 'Aprovado',
                        RefundRequest::STATUS_REJECTED => 'Rejeitado',
                        RefundRequest::STATUS_PROCESSED => 'Processado',
                    ]),
                Textarea::make('admin_notes')
                    ->label('Notas internas')
                    ->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Usuário')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        RefundRequest::STATUS_PENDING => 'Pendente',
                        RefundRequest::STATUS_APPROVED => 'Aprovado',
                        RefundRequest::STATUS_REJECTED => 'Rejeitado',
                        RefundRequest::STATUS_PROCESSED => 'Processado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        RefundRequest::STATUS_PENDING => 'warning',
                        RefundRequest::STATUS_APPROVED => 'success',
                        RefundRequest::STATUS_REJECTED => 'danger',
                        RefundRequest::STATUS_PROCESSED => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('stripe_subscription_id')
                    ->label('Stripe Subscription')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stripe_invoice_id')
                    ->label('Stripe Invoice')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        RefundRequest::STATUS_PENDING => 'Pendente',
                        RefundRequest::STATUS_APPROVED => 'Aprovado',
                        RefundRequest::STATUS_REJECTED => 'Rejeitado',
                        RefundRequest::STATUS_PROCESSED => 'Processado',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('stripe_subscription')
                    ->label('Stripe Sub')
                    ->url(fn (RefundRequest $record): ?string => static::getStripeSubscriptionUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (RefundRequest $record): bool => ! empty($record->stripe_subscription_id)),
                Action::make('stripe_invoice')
                    ->label('Stripe Invoice')
                    ->url(fn (RefundRequest $record): ?string => static::getStripeInvoiceUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (RefundRequest $record): bool => ! empty($record->stripe_invoice_id)),
                Action::make('stripe_payment_intent')
                    ->label('Stripe Payment')
                    ->url(fn (RefundRequest $record): ?string => static::getStripePaymentIntentUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (RefundRequest $record): bool => ! empty($record->stripe_payment_intent_id)),
            ])
            ->toolbarActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefundRequests::route('/'),
            'edit' => EditRefundRequest::route('/{record}/edit'),
        ];
    }

    protected static function getStripeSubscriptionUrl(RefundRequest $record): ?string
    {
        if (empty($record->stripe_subscription_id)) {
            return null;
        }

        $base = static::getStripeDashboardBaseUrl();

        return "{$base}/subscriptions/{$record->stripe_subscription_id}";
    }

    protected static function getStripeInvoiceUrl(RefundRequest $record): ?string
    {
        if (empty($record->stripe_invoice_id)) {
            return null;
        }

        $base = static::getStripeDashboardBaseUrl();

        return "{$base}/invoices/{$record->stripe_invoice_id}";
    }

    protected static function getStripePaymentIntentUrl(RefundRequest $record): ?string
    {
        if (empty($record->stripe_payment_intent_id)) {
            return null;
        }

        $base = static::getStripeDashboardBaseUrl();

        return "{$base}/payments/{$record->stripe_payment_intent_id}";
    }

    protected static function getStripeDashboardBaseUrl(): string
    {
        $secret = (string) config('cashier.secret');
        $isTestMode = str_starts_with($secret, 'sk_test');

        return $isTestMode ? 'https://dashboard.stripe.com/test' : 'https://dashboard.stripe.com';
    }
}
