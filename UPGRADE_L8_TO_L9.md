# Upgrade Laravel 8 → 9 ✅ CONCLUÍDO

> Concluído em 2026-02-12. Laravel Framework 9.52.21 instalado com sucesso.
> Tempo estimado: ~60min (considerando as especificidades deste projeto)

---

## Checklist

### 1. Dependências (composer.json) — Alto Impacto
- [ ] Alterar `"php"` de `"^7.3|^8.0"` para `"^8.0.2"`
- [ ] Alterar `"laravel/framework"` de `"^8.0"` para `"^9.0"`
- [ ] Alterar `"nunomaduro/collision"` de `"^5.0"` para `"^6.1"`
- [ ] Substituir `"facade/ignition"` por `"spatie/laravel-ignition": "^1.0"`
- [ ] Substituir `"fzaninotto/faker"` por nada (fakerphp/faker já é dependência indireta)
- [ ] Alterar `"league/flysystem-aws-s3-v3"` de `"^1.0"` para `"^3.0"`
- [ ] Remover `"fideloper/proxy": "^4.2"` (built-in no L9)
- [ ] Remover `"fruitcake/laravel-cors": "^2.0"` (built-in no L9)
- [ ] Verificar/atualizar versões de:
  - [ ] `"laravel/cashier"` → `"^14.0"` (compatível com L9)
  - [ ] `"filament/filament"` → verificar se 2.x suporta L9 ✅ (sim, 2.17+)
  - [ ] `"laravel/ui"` → `"^4.0"` (compatível com L9)
  - [ ] `"laravel/tinker"` → `"^2.7"`
  - [ ] `"spatie/laravel-permission"` → verificar compatibilidade (5.x OK para L9)
  - [ ] `"spatie/laravel-honeypot"` → verificar compatibilidade
  - [ ] `"spatie/laravel-sitemap"` → verificar compatibilidade
  - [ ] `"predis/predis"` → `"^2.0"` (se necessário)
  - [ ] `"mpdf/mpdf"` → verificar compatibilidade

---

### 2. Trusted Proxies — Baixo Impacto
**Arquivo:** `app/Http/Middleware/TrustProxies.php`

Situação atual:
```php
use Fideloper\Proxy\TrustProxies as Middleware;
// ...
protected $headers = Request::HEADER_X_FORWARDED_ALL;
```

Alterar para:
```php
use Illuminate\Http\Middleware\TrustProxies as Middleware;
// ...
protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

> **Nota:** Especialmente importante por estar em AWS EC2.

---

### 3. CORS — Kernel.php — Baixo Impacto
**Arquivo:** `app/Http/Kernel.php`

Situação atual:
```php
\Fruitcake\Cors\HandleCors::class,
```

Alterar para:
```php
\Illuminate\Http\Middleware\HandleCors::class,
```

> O Laravel 9 inclui handler CORS nativo. Manter `config/cors.php` como está.

---

### 4. Flysystem 3.x — Alto Impacto
**Arquivo:** `config/filesystems.php`

4.1. Renomear env var (opcional mas recomendado):
```php
// De:
'default' => env('FILESYSTEM_DRIVER', 'local'),
// Para:
'default' => env('FILESYSTEM_DISK', 'local'),
```

4.2. Atualizar `.env` e `.env.example`:
```
# De:
FILESYSTEM_DRIVER=local
# Para:
FILESYSTEM_DISK=local
```

4.3. O disco S3 já está configurado corretamente. A dependência `league/flysystem-aws-s3-v3` será atualizada para `^3.0` no composer.json.

4.4. Mudanças de comportamento a verificar no código:
- `Storage::put()` agora **sobrescreve** arquivos existentes (antes lançava exceção)
- Leitura de arquivo inexistente retorna `null` em vez de lançar `FileNotFoundException`
- `Storage::delete()` de arquivo inexistente retorna `true`

> **Verificar:** buscar usos de `Storage::` no código e validar que não dependem do comportamento antigo.
>
> **Usos de S3 identificados:** `AcordaoUploadService` (put, exists, delete), `TestS3Access` (put, get, temporaryUrl, delete), `TeseAcordao` (temporaryUrl). Todos compatíveis com Flysystem 3.

---

### 5. Symfony Mailer — Alto Impacto (mas baixo neste projeto)
**Mudança:** SwiftMailer → Symfony Mailer

**Verificado:** Nenhum uso direto de `withSwiftMessage` ou `Swift_Message` no código. ✅

**Ajustes em `config/mail.php`:**
- [ ] Remover `'auth_mode' => null` do mailer SMTP (auto-negociado no L9)

---

### 5b. Postgres Config — `config/database.php` — Baixo Impacto
- [ ] Renomear `'schema' => 'public'` para `'search_path' => 'public'` na config `pgsql`

---

### 5c. Filament Config — `config/filament.php` — Baixo Impacto
- [ ] Renomear `env('FILAMENT_FILESYSTEM_DRIVER', 'public')` para `env('FILAMENT_FILESYSTEM_DISK', 'public')`
- [ ] Manter demais configurações

---

### 6. Variáveis de Ambiente (.env)
- [ ] Renomear `FILESYSTEM_DRIVER` → `FILESYSTEM_DISK` (se usado)
- [ ] Verificar `MAIL_MAILER` (já usa formato correto ✅)

---

### 7. Validation — Impacto Médio
- [ ] Verificar se a regra de validação `'password'` é usada para validar a senha atual do usuário. Se sim, renomear para `'current_password'`.
  - **Verificado:** não há uso da regra `password` no sentido de "current password validation" — apenas como nome de campo. ✅

---

### 8. Lang Directory — Impacto Médio
No L9, o diretório `resources/lang` pode ser movido para `lang/` na raiz. Isso é **opcional** e pode ser feito depois.

- [ ] Decidir: mover `resources/lang` → `lang/` (opcional, mas recomendado para L10+)

---

### 9. Eloquent — Impacto Médio
- **`belongsToMany` firstOrNew/firstOrCreate/updateOrCreate**: mudança de comportamento (compara com tabela do modelo, não pivot). Verificar se há uso disso.
- **Custom Casts & null**: Se houver custom casts, verificar se tratam `null` no método `set`.

> **Verificado:** Nenhum custom cast encontrado no projeto. ✅

---

### 10. when/unless Methods — Impacto Médio
Closures passadas para `when()` ou `unless()` agora são executadas e o retorno é usado como condição. Verificar se há uso com closures como primeiro argumento.

---

### 11. HTTP Client Default Timeout — Impacto Médio
O HTTP client agora tem timeout padrão de 30s. O projeto já usa `->timeout(15)` em `tes_functions.php`. ✅

---

### 12. Testing — assertDeleted → assertModelMissing
- [ ] Renomear chamadas de `assertDeleted()` para `assertModelMissing()` nos testes (se existirem)

---

### 13. PHP Return Types — Impacto Baixo
Se houver override de métodos como `offsetGet`, `offsetSet`, etc., adicionar return types. Improvável neste projeto.

---

## Ordem de Execução Sugerida

```
1. git checkout -b upgrade/l8-to-l9
2. Editar composer.json (todas as mudanças de dependências)
3. Editar TrustProxies.php
4. Editar Kernel.php (HandleCors)
5. Editar config/filesystems.php
6. Editar config/mail.php (remover auth_mode)
7. Editar config/database.php (schema → search_path no pgsql)
8. Editar config/filament.php (FILESYSTEM_DRIVER → FILESYSTEM_DISK)
9. Editar .env (FILESYSTEM_DRIVER → FILESYSTEM_DISK)
10. composer update
11. php artisan config:clear && php artisan cache:clear
12. Testar no dev (Laravel Valet)
13. Rodar testes: php artisan test
14. Merge e deploy
```

## Verificação

### Testes Automatizados
```bash
php artisan test
```

### Verificação Manual (Dev - teses.test)
1. Acessar home page
2. Fazer busca por termo (ex: "medicamento")
3. Navegar para uma tese/súmula
4. Acessar quiz
5. Login/logout
6. Acessar painel admin `/admin`
7. Acessar área de quizzes admin
8. Testar API: `curl -X POST https://teses.test/api/ -H "Content-Type: application/json" -d '{"q":"medicamento","tribunal":"STF"}'`

### Verificação em Produção (após deploy)
1. Repetir verificações manuais acima em `tesesesumulas.com.br`
2. Verificar emails (enviar um teste)
3. Verificar se S3 funciona (upload/download se aplicável)
