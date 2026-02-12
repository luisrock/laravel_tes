<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestMatomoApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matomo:test {token?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a API do Matomo e busca as páginas mais visitadas';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = $this->argument('token') ?? env('MATOMO_TOKEN');

        if (! $token) {
            $this->error('❌ Token do Matomo não encontrado!');
            $this->info('');
            $this->info('Para obter o token:');
            $this->info('1. Acesse: https://maurolopes.com.br/matomo/');
            $this->info('2. Login → Configurações → API');
            $this->info('3. Copie o token e execute: php artisan matomo:test SEU_TOKEN');
            $this->info('4. Ou adicione no .env: MATOMO_TOKEN=seu_token');

            return 1;
        }

        $this->info('🔍 Testando API do Matomo...');
        $this->info('');

        try {
            // Testa se a API está acessível
            $response = Http::timeout(30)->get('https://maurolopes.com.br/matomo/', [
                'module' => 'API',
                'method' => 'Actions.getPageUrls',
                'idSite' => 2,
                'period' => 'range',
                'date' => 'last30',
                'format' => 'json',
                'token_auth' => $token,
                'filter_limit' => 100,
                'expanded' => 1,  // Expandir subníveis
                'flat' => 1,       // Retornar em lista plana
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $this->info('✅ API do Matomo está funcionando!');
                $this->info('');

                // Debug: mostrar primeiros 5 itens
                $this->info('🔍 DEBUG - Análise dos dados:');
                $this->info('Total de itens retornados: '.count($data));
                $this->info('');

                if (count($data) > 0) {
                    $this->info('Exemplo do primeiro item:');
                    $first = $data[0];
                    foreach ($first as $key => $value) {
                        $this->line("  $key: ".(is_array($value) ? json_encode($value) : $value));
                    }
                }
                $this->info('');

                $this->info('📊 Top 20 Temas Mais Visitados (último mês):');
                $this->info('');

                if (empty($data)) {
                    $this->warn('⚠️  Nenhum dado encontrado. Isso pode significar:');
                    $this->warn('   - O filtro não encontrou páginas /tema/');
                    $this->warn('   - Não há dados no período selecionado');

                    return 0;
                }

                $themes = collect($data)
                    ->filter(function ($item) {
                        return isset($item['label']) &&
                               strpos($item['label'], '/tema/') !== false;
                    })
                    ->take(20)
                    ->map(function ($item, $index) {
                        $slug = str_replace('/tema/', '', $item['label']);
                        $slug = trim($slug, '/');

                        return [
                            'pos' => $index + 1,
                            'slug' => $slug,
                            'visits' => $item['nb_visits'] ?? 0,
                            'hits' => $item['nb_hits'] ?? 0,
                            'avg_time' => $item['avg_time_on_page'] ?? 0,
                        ];
                    });

                $this->table(
                    ['#', 'Slug do Tema', 'Visitas', 'Pageviews', 'Tempo Médio'],
                    $themes->map(function ($t) {
                        return [
                            $t['pos'],
                            $t['slug'],
                            $t['visits'],
                            $t['hits'],
                            gmdate('i:s', $t['avg_time']),
                        ];
                    })
                );

                $this->info('');
                $this->info('💡 Próximos passos:');
                $this->info('   1. Adicionar MATOMO_TOKEN ao .env de produção');
                $this->info('   2. Criar comando para sincronizar views_count com Matomo');
                $this->info('   3. Agendar comando semanal no cron');

                return 0;
            } else {
                $this->error('❌ Erro na API: '.$response->status());
                $this->error($response->body());

                return 1;
            }

        } catch (Exception $e) {
            $this->error('❌ Erro ao conectar com Matomo: '.$e->getMessage());

            return 1;
        }
    }
}
