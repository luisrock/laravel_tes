<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Sendy\SendyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SyncNewsletterStatus extends Command
{
    protected $signature = 'newsletter:sync
                            {--all : Sincronizar todos os usuários}
                            {--user= : ID de um usuário específico}';

    protected $description = 'Reconcilia users.newsletter_subscribed_at com a base Sendy';

    public function handle(SendyService $sendy): int
    {
        if (! Schema::hasColumn('users', 'newsletter_subscribed_at')) {
            $this->warn('Colunas de newsletter ausentes em users — nada a sincronizar.');

            return self::SUCCESS;
        }

        $userId = $this->option('user');

        if ($userId !== null) {
            $user = User::query()->find($userId);

            if ($user === null) {
                $this->error("Usuário {$userId} não encontrado.");

                return self::FAILURE;
            }

            $synced = $sendy->syncUsersFromSendyDb(collect([$user]));
            $this->info("Sincronizado 1 usuário (id {$userId}).");

            Log::info('newsletter:sync completed', ['synced' => $synced, 'user_id' => $userId]);

            return self::SUCCESS;
        }

        $query = User::query()->whereNotNull('email');

        if (! $this->option('all')) {
            $query->where(function ($builder): void {
                $builder
                    ->whereNull('newsletter_synced_at')
                    ->orWhere('newsletter_synced_at', '<', now()->subHours(6));
            });
        }

        $total = 0;

        $query->orderBy('id')->chunkById(500, function ($users) use ($sendy, &$total): void {
            $total += $sendy->syncUsersFromSendyDb($users);
        });

        $this->info("Sincronizados {$total} usuários.");

        Log::info('newsletter:sync completed', [
            'synced' => $total,
            'all' => (bool) $this->option('all'),
        ]);

        return self::SUCCESS;
    }
}
