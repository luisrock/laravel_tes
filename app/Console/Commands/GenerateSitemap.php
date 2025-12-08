<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $baseUrl = config('app.url');
        
        SitemapGenerator::create($baseUrl)
            ->hasCrawled(function (Url $url) use ($baseUrl) {
                // Excluir URLs com query strings (paginação)
                if (str_contains($url->url, '?')) {
                    return null;
                }
                
                // Excluir /index (duplicata da home)
                if ($url->url === $baseUrl . '/index') {
                    return null;
                }
                
                // Excluir URL vazia ou apenas com barra final duplicada
                // Manter apenas a versão canônica (sem barra final)
                $path = parse_url($url->url, PHP_URL_PATH);
                if ($path === '/') {
                    // Manter apenas uma versão da home
                    $url->url = $baseUrl;
                }
                
                return $url;
            })
            ->writeToFile(public_path('sitemap.xml'));
            
        $this->info('Sitemap generated successfully!');
    }
}
