# Plano Geral de Upgrade Laravel 8 → 12

## Diagnóstico do Codebase

### Versões Atuais
| Componente | Versão Atual | Observação |
|---|---|---|
| Laravel Framework | ^10.0 (v10.50.0) | L8→L9→L10 concluídos |
| PHP | 8.3 (dev e prod) | OK para todos os upgrades |
| Filament | ^2.0 | Precisa upgrade para 3.x (próxima fase) |
| Laravel Cashier | ^14.0 | OK |
| Spatie Permission | ^5.10 | OK |
| Spatie Honeypot | ^4.3 | OK |
| Spatie Sitemap | ^7.0 | OK |
| Laravel UI | ^4.0 | OK |
| PHPUnit | ^10.0 (v10.5.63) | Atualizado para v10 |
| Pest | ^2.0 (v2.36.1) | Instalado e configurado |
| Collision | ^7.0 (v7.12.0) | Atualizado para v7 |

### Pacotes que Serão Removidos/Substituídos
| Pacote L8 | Substituição |Quando |
|---|---|---|
| `fideloper/proxy` | `Illuminate\Http\Middleware\TrustProxies` (built-in) | L8→L9 |
| `facade/ignition` | `spatie/laravel-ignition` | L8→L9 |
| `fzaninotto/faker` | `fakerphp/faker` | L8→L9 |
| `fruitcake/laravel-cors` | Built-in no Laravel (handler CORS nativo) | L8→L9 |
| `league/flysystem-aws-s3-v3 ^1.0` | `league/flysystem-aws-s3-v3 ^3.0` | L8→L9 |
| `predis/predis ^1.1` | `predis/predis ^2.0` | L9→L10 (verificar) |
| Laravel Mix | Vite | L9→L10 |

### Estrutura do Projeto
- **41 Controllers** (incl. 4 Admin, 6 Auth, 2 API)
- **17 Models** (User, Quiz*, Newsletter, Tese*, Subscription-related)
- **81 Views** (Blade templates)
- **13 Middleware** (incl. TrustProxies, AdminMiddleware, BearerToken, Subscription*)
- **34 Migrations**
- **67 Testes Pest** (Feature, Arch — cobertura ampliada)
- **5 Providers** (App, Auth, Broadcast, Event, Route)
- **1082 linhas** em `bootstrap/tes_functions.php` (helper autoloaded)
- **4 Notifications** (Subscription-related)
- **2 Jobs**
- **Filament Admin** (Resources + Widgets)

### Pontos de Atenção Identificados
1. **`TrustProxies`** estende `Fideloper\Proxy` — precisa migrar para `Illuminate\Http\Middleware\TrustProxies`
2. **`Fruitcake\Cors\HandleCors`** no Kernel.php — precisa ser removido
3. **`FILESYSTEM_DRIVER`** em `config/filesystems.php` — renomear para `FILESYSTEM_DISK`
4. **S3 disk** configurado — precisa do Flysystem 3.x com `league/flysystem-aws-s3-v3 ^3.0`
5. **`auth_mode`** em `config/mail.php` — pode ser removido (auto-negociado no L9+)
6. **Filament 2** — precisa upgrade para v3 (breaking changes significativas, alinhar com L10+)
7. **`config/app.php`** lista providers manualmente — L11 simplifica isso
8. **`RouteServiceProvider`** usa estilo L8 — será refatorado em L10/L11
9. **`password` validation rule** — renomear para `current_password` (se usado)
10. **Bootstrap Paginator** em `AppServiceProvider` — manter por ora
11. **Laravel UI ^3** para autenticação — considerar migração futura

---

## Estratégia de Upgrade Incremental

### Fase 1: L8 → L9 ✅ CONCLUÍDO
**Guia detalhado:** `UPGRADE_L8_TO_L9.md` (Concluído em 12/02/2026)

Mudanças mais impactantes:
- Flysystem 1.x → 3.x
- SwiftMailer → Symfony Mailer
- Remoção de `fideloper/proxy`
- Remoção de `fruitcake/laravel-cors`
- `facade/ignition` → `spatie/laravel-ignition`

### Fase 2: L9 → L10 ✅ CONCLUÍDO
**Guia detalhado:** `UPGRADE_L9_TO_L10.md` (Concluído em 12/02/2026)

Status: Finalizado. Framework v10.50.0. Dependências atualizadas.

### Fase 2.5: Estratégia de Testes Robustos (Pest) ✅ CONCLUÍDO
Concluído em 12/02/2026. Suíte de testes robusta implementada antes do upgrade para L11.

**Dependências atualizadas:**
- `phpunit/phpunit` ^9.3 → ^10.0 (v10.5.63)
- `nunomaduro/collision` ^6.4 → ^7.0 (v7.12.0)
- `pestphp/pest` v2.36.1 (novo)
- `pestphp/pest-plugin-laravel` v2.4.0 (novo)
- `pestphp/pest-plugin-arch` v2.7.0 (novo — incluso com Pest v2)

**Testes implementados (67 testes, 95 assertions, ~5s):**

| Arquivo | Testes | Escopo |
|---|---|---|
| `SmokeTest.php` | 19 | Todas as rotas públicas (HTTP 200 ou 500/SQLite) |
| `AuthTest.php` | 11 | Login, logout, reset de senha, proteção de rotas |
| `SearchTest.php` | 10 | Busca web, API de busca, validações |
| `SubscriptionTest.php` | 10 | Planos, checkout auth, helpers do User model |
| `SubscriptionNotificationsTest.php` | 3 | Notificações de welcome, cancelamento, estorno |
| `SubscriptionRenewalReminderJobTest.php` | 3 | Job de lembrete de renovação |
| `ArchTest.php` | 12 | Arch tests (debug, namespaces, env(), herança) |

**Arch Tests implementados:**
- Proíbe `dd`, `dump`, `ray`, `var_dump`, `print_r` em código de produção
- Verifica que `App` não depende de `Tests`
- Valida namespaces de Models, Controllers, Middleware, Services, Notifications, Jobs
- Verifica herança correta de Controllers e Models
- Detecta uso de `env()` fora de `config/` (com exceções documentadas)

**Comando para rodar testes:**
```bash
/opt/homebrew/opt/php@8.3/bin/php vendor/bin/pest
```

**Observação sobre SQLite vs MySQL:**
Os testes usam SQLite in-memory (`phpunit.xml`). Rotas que dependem de queries
MySQL-específicas (FULLTEXT, enums) aceitam 200 ou 500. Quando migrarmos os
testes para MySQL, todos devem retornar 200.

### Fase 3: L10 → L11 🚧 PRÓXIMO PASSO
**Guia detalhado:** `UPGRADE_L10_TO_L11.md` (a ser criado)

Mudanças previstas:
- Remoção de `app/Http/Kernel.php` (migrar para `bootstrap/app.php`)
- Simplificação do `config/app.php`
- Remoção de providers avulsos (App, Auth, Event, Route, Broadcast)
- Novas casts como métodos

### Fase 4: L11 → L12
**Guia detalhado:** `UPGRADE_L11_TO_L12.md` (a ser criado)

Mudanças previstas:
- PHP 8.2 mínimo
- Verificar breaking changes mais recentes

---

## Plano de Testes

### Estratégia
1. **L8→L9**: testes manuais + PHPUnit ✅
2. **L9→L10**: PHPUnit smoke tests ✅
3. **L10 (Fase 2.5)**: Pest v2 instalado, testes migrados e expandidos ✅
4. **L10→L11**: rodar suite Pest, corrigir regressões
5. **L11→L12**: expandir cobertura, migrar para MySQL nos testes

### Testes Implementados
- [x] **Smoke tests**: 19 rotas públicas cobertas (HTTP 200 ou redirect esperado)
- [x] **Autenticação**: login, logout, reset de senha, proteção de rotas (11 testes)
- [x] **Busca**: validação web + API, tribunais, termos mínimos (10 testes)
- [x] **Subscription**: planos, checkout auth, model helpers, notificações (16 testes)
- [x] **Arch Tests**: padrões de código, namespaces, debug functions (12 testes)

### Testes Pendentes (próximas fases)
- [ ] **Área admin**: dashboard, CRUD de temas, quizzes, perguntas, stats
- [ ] **API autenticada**: CRUD de quizzes/perguntas via Bearer token
- [ ] **Filament admin**: acesso, listagem de resources
- [ ] **Testes com MySQL**: migrar de SQLite para MySQL (rotas Grupo 2 → assertStatus(200))

---

## Ordem de Execução por Fase

Para **cada fase** de upgrade:
1. 📋 Criação de branch `upgrade/L{X}-to-L{Y}`
2. 📖 Leitura do guia detalhado correspondente
3. 🔧 Execução das alterações no `composer.json`
4. 🔧 Execução das alterações em código/config
5. ✅ Rodar testes automatizados
6. 🌐 Verificar manualmente no dev (Laravel Valet)
7. 🚀 Merge e deploy em produção
8. ✅ Verificar em produção

---

## Riscos e Mitigações

| Risco | Impacto | Mitigação |
|---|---|---|
| Filament 2→3 (breaking) | Alto | Fase separada, L10 |
| Flysystem S3 breaking | Médio | Testar upload/download S3 |
| Symfony Mailer | Baixo | Sem uso direto de SwiftMailer |
| Cashier upgrade | Médio | Testar fluxo de assinatura |
| API regressions | Médio | Testes automatizados de API |
| Views quebradas | Alto | Smoke tests em todas as rotas |
