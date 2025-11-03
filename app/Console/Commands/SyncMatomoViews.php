<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SyncMatomoViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matomo:sync {--days=30 : Número de dias para buscar dados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza views_count dos temas com dados do Matomo Analytics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $token = env('MATOMO_TOKEN');
        $days = $this->option('days');
        
        if (!$token) {
            $this->error('❌ MATOMO_TOKEN não configurado no .env');
            return 1;
        }

        $this->info("🔄 Sincronizando views dos últimos {$days} dias com Matomo...");
        $this->info('');

        try {
            // Buscar dados do Matomo
            $response = Http::timeout(60)->get('https://maurolopes.com.br/matomo/', [
                'module' => 'API',
                'method' => 'Actions.getPageUrls',
                'idSite' => 2,
                'period' => 'range',
                'date' => "last{$days}",
                'format' => 'json',
                'token_auth' => $token,
                'filter_limit' => 500,
                'expanded' => 1,
                'flat' => 1
            ]);

            if (!$response->successful()) {
                $this->error('❌ Erro ao conectar com Matomo: ' . $response->status());
                return 1;
            }

            $data = $response->json();
            
            // Filtrar apenas páginas /tema/
            $themes = collect($data)
                ->filter(function($item) {
                    return isset($item['label']) && 
                           strpos($item['label'], '/tema/') !== false;
                })
                ->map(function($item) {
                    $slug = str_replace('/tema/', '', $item['label']);
                    $slug = trim($slug, '/');
                    return [
                        'slug' => $slug,
                        'visits' => $item['nb_visits'] ?? 0,
                    ];
                });

            if ($themes->isEmpty()) {
                $this->warn('⚠️  Nenhum dado de tema encontrado no Matomo');
                return 0;
            }

            $this->info("📊 Encontrados {$themes->count()} temas no Matomo");
            $this->info('');

            // Atualizar banco de dados
            $updated = 0;
            $notFound = 0;
            $bar = $this->output->createProgressBar($themes->count());
            $bar->start();

            foreach ($themes as $theme) {
                $result = DB::table('pesquisas')
                    ->where('slug', $theme['slug'])
                    ->update([
                        'views_count' => $theme['visits'],
                        'last_synced_at' => now()
                    ]);

                if ($result > 0) {
                    $updated++;
                } else {
                    $notFound++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->info('');
            $this->info('');

            // Resumo
            $this->info('✅ Sincronização concluída!');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Temas encontrados no Matomo', $themes->count()],
                    ['Temas atualizados no banco', $updated],
                    ['Temas não encontrados no banco', $notFound],
                    ['Top 5 mais visitados', '']
                ]
            );

            // Mostrar top 5
            $top5 = DB::table('pesquisas')
                ->select('keyword', 'label', 'slug', 'views_count')
                ->whereNotNull('slug')
                ->where('views_count', '>', 0)
                ->orderBy('views_count', 'desc')
                ->limit(5)
                ->get();

            if ($top5->count() > 0) {
                $this->info('');
                $this->info('🔥 Top 5 Temas Mais Visitados:');
                $this->table(
                    ['#', 'Tema', 'Views'],
                    $top5->map(function($t, $i) {
                        return [
                            $i + 1,
                            $t->label ?? $t->keyword,
                            number_format($t->views_count)
                        ];
                    })
                );
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            return 1;
        }
    }
}
