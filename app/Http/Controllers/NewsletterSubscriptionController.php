<?php

namespace App\Http\Controllers;

use App\Enums\NewsletterEventSource;
use App\Http\Requests\NewsletterSubscribeRequest;
use App\Services\Sendy\NewsletterSubscriptionContext;
use App\Services\Sendy\SendyService;
use Illuminate\Http\JsonResponse;

final class NewsletterSubscriptionController extends Controller
{
    public function __construct(private SendyService $sendy) {}

    public function subscribe(NewsletterSubscribeRequest $request): JsonResponse
    {
        if (! $this->sendy->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Inscrições temporariamente indisponíveis. Tente em alguns minutos.',
            ], 503);
        }

        $userId = $request->user()?->id;
        $silent = $userId
            ? (bool) config('services.sendy.silent_authenticated', true)
            : (bool) config('services.sendy.silent_visitor', false);

        $ctx = NewsletterSubscriptionContext::fromRequest(
            NewsletterEventSource::NewslettersForm,
            $request,
            $userId,
        );
        $ctx->silent = $silent;

        $email = $request->user()?->email ?? $request->string('email')->toString();

        $result = $this->sendy->subscribe(
            email: $email,
            name: $request->string('name')->toString(),
            ctx: $ctx,
        );

        return response()->json([
            'success' => $result->success,
            'message' => $result->alreadySubscribed
                ? 'Você já está inscrito!'
                : ($result->success ? 'Inscrição realizada! Verifique seu email.' : 'Não foi possível inscrever agora. Tente novamente em instantes.'),
            'already_subscribed' => $result->alreadySubscribed,
        ]);
    }
}
