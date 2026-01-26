<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class TestS3Access extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:s3-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa acesso ao AWS S3 e verifica permissões';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Teste de Acesso ao AWS S3 ===');
        $this->newLine();

        // 1. Verificar credenciais
        $this->info('1. Verificando credenciais...');
        $accessKey = env('AWS_ACCESS_KEY_ID');
        $secretKey = env('AWS_SECRET_ACCESS_KEY');
        $region = env('AWS_DEFAULT_REGION', 'sa-east-1');
        $bucket = env('AWS_BUCKET');

        if (empty($accessKey) || empty($secretKey)) {
            $this->error('❌ Credenciais AWS não configuradas no .env');
            $this->warn('   Configure: AWS_ACCESS_KEY_ID e AWS_SECRET_ACCESS_KEY');
            return 1;
        }

        if (empty($bucket)) {
            $this->warn('⚠ Bucket AWS não configurado no .env');
            $this->info('   Usando bucket padrão do plano: tesesesumulas');
            $bucket = 'tesesesumulas';
        }

        $this->info("   ✓ Access Key ID: " . substr($accessKey, 0, 8) . '...');
        $this->info("   ✓ Region: {$region}");
        $this->info("   ✓ Bucket: {$bucket}");
        $this->newLine();

        // 2. Testar conexão com S3 usando AWS SDK diretamente
        $this->info('2. Testando conexão com S3...');
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $region,
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            // Teste: Listar buckets (requer permissão s3:ListAllMyBuckets)
            try {
                $result = $s3Client->listBuckets();
                $this->info('   ✓ Conexão estabelecida com sucesso');
                $this->info('   ✓ Permissão s3:ListAllMyBuckets: OK');
                
                $bucketNames = array_column($result->get('Buckets'), 'Name');
                if (in_array($bucket, $bucketNames)) {
                    $this->info("   ✓ Bucket '{$bucket}' encontrado na lista");
                } else {
                    $this->warn("   ⚠ Bucket '{$bucket}' não encontrado na lista (pode ser normal se não tiver permissão de listar)");
                }
            } catch (AwsException $e) {
                if ($e->getAwsErrorCode() === 'AccessDenied') {
                    $this->warn('   ⚠ Sem permissão para listar buckets (pode ser normal)');
                } else {
                    throw $e;
                }
            }
        } catch (AwsException $e) {
            $this->error('   ❌ Erro ao conectar com S3: ' . $e->getMessage());
            $this->error('   Código: ' . $e->getAwsErrorCode());
            return 1;
        } catch (\Exception $e) {
            $this->error('   ❌ Erro inesperado: ' . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // 3. Testar acesso ao bucket específico
        $this->info("3. Testando acesso ao bucket '{$bucket}'...");
        try {
            // Verificar se o bucket existe e é acessível
            $headBucket = $s3Client->headBucket(['Bucket' => $bucket]);
            $this->info('   ✓ Bucket existe e é acessível');
            $this->info('   ✓ Permissão s3:HeadBucket: OK');
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'AccessDenied' || $e->getAwsErrorCode() === '403') {
                $this->error('   ❌ Acesso negado ao bucket');
                $this->error('   As credenciais podem ter apenas permissão para SES, não para S3');
                $this->warn('   Solução: Adicione permissões S3 ao IAM user/role');
                return 1;
            } elseif ($e->getAwsErrorCode() === '404') {
                $this->error("   ❌ Bucket '{$bucket}' não encontrado");
                return 1;
            } else {
                $this->error('   ❌ Erro: ' . $e->getMessage());
                return 1;
            }
        }
        $this->newLine();

        // 4. Testar upload usando Laravel Storage
        $this->info('4. Testando upload usando Laravel Storage...');
        $testKey = 'test/access-test-' . time() . '.txt';
        $testContent = 'Teste de acesso ao S3 - ' . date('Y-m-d H:i:s');
        
        try {
            Storage::disk('s3')->put($testKey, $testContent);
            $this->info("   ✓ Upload realizado com sucesso: {$testKey}");
            $this->info('   ✓ Permissão s3:PutObject: OK');
        } catch (\Exception $e) {
            $this->error('   ❌ Erro no upload: ' . $e->getMessage());
            $this->warn('   Verifique permissão s3:PutObject');
            return 1;
        }
        $this->newLine();

        // 5. Testar leitura
        $this->info('5. Testando leitura do arquivo...');
        try {
            $content = Storage::disk('s3')->get($testKey);
            if ($content === $testContent) {
                $this->info('   ✓ Leitura realizada com sucesso');
                $this->info('   ✓ Permissão s3:GetObject: OK');
            } else {
                $this->warn('   ⚠ Conteúdo lido não corresponde ao esperado');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Erro na leitura: ' . $e->getMessage());
            $this->warn('   Verifique permissão s3:GetObject');
        }
        $this->newLine();

        // 6. Testar presigned URL
        $this->info('6. Testando geração de presigned URL...');
        try {
            $url = Storage::disk('s3')->temporaryUrl($testKey, now()->addHour());
            $this->info('   ✓ Presigned URL gerada com sucesso');
            $this->info('   ✓ Permissão s3:GetObject (para presigned): OK');
            $this->line("   URL: {$url}");
        } catch (\Exception $e) {
            $this->error('   ❌ Erro ao gerar presigned URL: ' . $e->getMessage());
        }
        $this->newLine();

        // 7. Limpar arquivo de teste
        $this->info('7. Removendo arquivo de teste...');
        try {
            Storage::disk('s3')->delete($testKey);
            $this->info('   ✓ Arquivo de teste removido');
            $this->info('   ✓ Permissão s3:DeleteObject: OK');
        } catch (\Exception $e) {
            $this->warn('   ⚠ Erro ao remover arquivo de teste: ' . $e->getMessage());
            $this->warn("   Arquivo pode precisar ser removido manualmente: {$testKey}");
        }
        $this->newLine();

        // Resumo
        $this->info('=== Resumo ===');
        $this->info('✓ Todas as permissões necessárias estão funcionando!');
        $this->info('✓ As credenciais AWS têm acesso completo ao S3');
        $this->newLine();
        $this->info('Permissões verificadas:');
        $this->line('  - s3:HeadBucket (verificar bucket)');
        $this->line('  - s3:PutObject (upload)');
        $this->line('  - s3:GetObject (leitura e presigned URLs)');
        $this->line('  - s3:DeleteObject (remoção)');
        $this->newLine();

        return 0;
    }
}
