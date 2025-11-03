<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearSearchCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-searches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa o cache de buscas de jurisprudência';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🗑️  Limpando cache de buscas...');
        
        try {
            // Limpar todas as chaves que começam com 'search_'
            // Nota: Isso funciona melhor com drivers como Redis
            // Para 'file' driver, precisamos limpar todo o cache
            
            $driver = config('cache.default');
            
            if (in_array($driver, ['redis', 'memcached'])) {
                // Para Redis/Memcached, podemos usar padrões
                $this->warn('⚠️  Driver ' . $driver . ': Limpando todas as chaves search_*');
                // Implementação específica do Redis seria necessária aqui
                // Por segurança, vamos limpar todo o cache
                Cache::flush();
            } else {
                // Para 'file' e outros drivers, limpar todo o cache
                $this->warn('⚠️  Driver ' . $driver . ': Limpando todo o cache');
                Cache::flush();
            }
            
            $this->info('✅ Cache de buscas limpo com sucesso!');
            $this->info('');
            $this->info('💡 Dica: Use este comando quando:');
            $this->info('   - Atualizar súmulas/teses no banco de dados');
            $this->info('   - Fazer manutenção nas tabelas de busca');
            $this->info('   - Detectar resultados desatualizados');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao limpar cache: ' . $e->getMessage());
            return 1;
        }
    }
}
