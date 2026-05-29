<?php

use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Storage\DatabaseConversationStore;

it('cria as tabelas de conversas do AI SDK', function () {
    expect(Schema::hasTable('agent_conversations'))->toBeTrue()
        ->and(Schema::hasTable('agent_conversation_messages'))->toBeTrue();
});

it('resolve o ConversationStore para o store de banco do SDK', function () {
    expect(resolve(ConversationStore::class))->toBeInstanceOf(DatabaseConversationStore::class);
});

it('não gera título por LLM (usa o início do prompt)', function () {
    expect(config('ai.conversations.generate_title'))->toBeFalse();
});
