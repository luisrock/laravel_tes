# Plano Geral de Upgrade Laravel 8 → 12

## Diagnóstico do Codebase

### Versões Atuais
| Componente | Versão Atual | Observação |
|---|---|---|
| Laravel Framework | ^8.0 | Precisa upgrade incremental |
| PHP | ^7.3\|^8.0 (em prod: 8.3) | OK para todos os upgrades |
| Filament | ^2.0 | Precisa upgrade para 3.x (a partir do L10) |
| Laravel Cashier | ^13.0 | Precisa upgrade |
| Spatie Permission | ^5.10 | Precisa upgrade |
| Spatie Honeypot | ^4.3 | Verificar compatibilidade |
| Spatie Sitemap | ^5.9 | Verificar compatibilidade |
| Laravel UI | ^3.0 | Precisa upgrade |
| Laravel Mix | ^5.0.1 | Migrar para Vite (a partir do L10) |
| PHPUnit | ^9.3 | Migrar para Pest quando possível |

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
- **4 Testes** (Feature e Unit — cobertura mínima)
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

### Fase 1: L8 → L9 ⭐ (Mais Complexa)
**Guia detalhado:** `UPGRADE_L8_TO_L9.md`

Mudanças mais impactantes:
- Flysystem 1.x → 3.x
- SwiftMailer → Symfony Mailer
- Remoção de `fideloper/proxy`
- Remoção de `fruitcake/laravel-cors`
- `facade/ignition` → `spatie/laravel-ignition`

### Fase 2: L9 → L10
**Guia detalhado:** `UPGRADE_L9_TO_L10.md` (a ser criado)

Mudanças previstas:
- PHP 8.1 mínimo
- Laravel Mix → Vite
- Filament 2 → 3 (grande refactor)
- Upgrade de pacotes Spatie
- Introdução do Pest para testes

### Fase 3: L10 → L11
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
1. **L8→L9**: testes manuais + PHPUnit (Pest ainda não é padrão no L9)
2. **L9→L10**: instalar Pest e migrar testes existentes
3. **L10→L11**: expandir cobertura com Pest
4. **L11→L12**: cobertura completa

### Testes a Implementar (progressivamente)
- [ ] **Smoke tests**: todas as rotas retornam HTTP 200 (ou redirect esperado)
- [ ] **Views públicas**: Home, busca, temas, súmulas, teses, quizzes, newsletter
- [ ] **Autenticação**: login, registro, reset de senha
- [ ] **Área admin**: dashboard, CRUD de temas, quizzes, perguntas, stats
- [ ] **API**: busca, CRUD de quizzes/perguntas, autenticação Bearer
- [ ] **Subscription**: checkout, webhook, cancelamento, refund
- [ ] **Filament admin**: acesso, listagem de resources

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
