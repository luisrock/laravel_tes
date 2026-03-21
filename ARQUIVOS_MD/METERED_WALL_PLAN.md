# Plano: Metered Wall + Features de Assinatura

Implementar sistema de visualizações limitadas diárias para conteúdo premium ("Decifrando a tese", e futuramente "Decifrando a súmula"), coleções de teses por usuário, e features premium adicionais. Objetivo: criar incentivos reais para conversão de usuários gratuitos em assinantes.

---

## Variáveis `.env` em **produção** (opcionais)

**Metered wall** (limite, on/off): **não** vai para o `.env` — configura-se no Filament → `site_settings`.

**Barra de teste** (`config/teses.php`): só se precisares dela no servidor (ex.: validar metered wall em prod no primeiro deploy). Acrescenta ao `.env` de **produção**:

```env
# Opcional — desliga ou remove quando deixares de precisar
TEST_TOOLBAR_ENABLED=true
# Opcional — email que vê a barra e pode usar POST /test-toolbar/* (default no código: ivanaredler@gmail.com)
TEST_TOOLBAR_EMAIL=teu-email@exemplo.com
```

Se **não** acrescentares nada: em `APP_ENV=production` a barra fica **desligada** por defeito.

**Não** coloques no `.env` de produção as variáveis `MYSQL_TESTING_*` — são só para desenvolvimento/CI.

---

## Testes MySQL (local e CI)

- **Suíte completa** (Feature + Arch + **MySQL**): `composer test` — sobe o container `mysql-testing` (`docker compose up -d --wait mysql-testing`) e corre `php artisan test`.
- **Só SQLite** (sem Docker / sem MySQL): `composer test:sqlite` — equivalente a excluir a suíte `MySQL`.
- Se correres `php artisan test` **sem** MySQL acessível na porta/credenciais por defeito, os testes em `tests/MySQL` ficam **skipped** (exit code 0), com mensagem a indicar o `docker compose`.

**Overrides** (ficheiro `.env` local ou export antes do comando):

```env
MYSQL_TESTING_HOST=127.0.0.1
MYSQL_TESTING_PORT=3307
MYSQL_TESTING_DATABASE=teses_test
MYSQL_TESTING_USERNAME=root
MYSQL_TESTING_PASSWORD=password
```

Valores por defeito = `docker-compose.yml` (MySQL 8.4, porta **3307** no host).

---

## Observações importantes

### Produção e seeds (metered wall)

- O **deploy automático** já executa `php artisan migrate --force`. As tabelas `content_views` e `site_settings` passam a existir após a migração correspondente estar no código implantado.
- **Não** rode `php artisan db:seed` nem `DatabaseSeeder` completo em produção só por causa do metered wall: isso pode reexecutar `AdminUserSeeder`, `RolesAndPermissionsSeeder`, etc., com efeitos indesejados (duplicação, sobrescrita de dados).
- Para criar **apenas** as linhas default em `site_settings` (se ainda não existirem), use **uma vez**:
  ```bash
  php artisan db:seed --class=SiteSettingsSeeder --force
  ```
  O seeder usa `firstOrCreate` — **não** apaga nem altera valores que já tenham sido guardados pelo Filament.

### Comportamento sem linhas em `site_settings`

- Com a key `metered_wall_enabled` **ausente**, a app trata o metered wall como **ativo** (default), e o Filament mostra o toggle coerente com isso (via `SiteSetting::getAsBool`, não via cast `(bool) '0'`, que em PHP seria incorreto).
- O limite diário default numérico segue **3** quando a key `metered_wall_daily_limit` não existe.

### Onde configurar o metered wall

Alterações de limite ou ativação/desativação devem ser feitas **no painel Filament** (Metered Wall), não via `.env` para as flags do meter em si.

### Barra de teste (header + rotas `test-toolbar.*`)

- Controlada por `config/teses.php`: `TEST_TOOLBAR_ENABLED` e `TEST_TOOLBAR_EMAIL`.
- **Local / não produção:** por defeito a barra fica **ligada** (útil para QA).
- **Produção:** por defeito **desligada**; para o primeiro deploy de testes, defina no `.env` do servidor: `TEST_TOOLBAR_ENABLED=true` (e opcionalmente `TEST_TOOLBAR_EMAIL=...`). Depois remova ou defina `false`.

### Testes e alterações futuras

Qualquer alteração futura no metered wall exige cobertura Pest para os cenários relevantes (ver ficheiros listados abaixo).

---

## Fase 1 — Metered Wall (Contagem de Views de Conteúdo Premium)

### Escopo

- **Genérico**: aplica-se a qualquer tese/súmula que possua seções IA ("Decifrando..."), independente do tribunal. Hoje o bloco completo de UI (banner + paywall) está em `resources/views/front/tese.blade.php` (fluxos STF/STJ).
- **TST / TNU**: o `TesePageController` já envia `$remainingViews`, `$isMeteredPaywall`, etc.; os templates `tese_tst` / `tese_tnu` **ainda não** renderizam o cartão "Decifrando a tese". Quando a IA for exposta nesses layouts, reutilizar o mesmo markup (idealmente um partial partilhado com `tese.blade.php`) para manter o metered wall e o CTA consistentes.
- A contagem é disparada pela **existência de conteúdo IA na página**, não pelo tribunal.
- Contar views **únicas por conteúdo** (revisitar o mesmo não recomputa).
- Janela de **24h rolling** (não meia-noite).
- Limites gerenciáveis pelo admin via Filament.

### Tabela `content_views` — campo `content_type`

Para ser future-proof (teses hoje, súmulas amanhã), a tabela usa `content_type` (`'tese'`, `'sumula'`, …) com `content_id` e `tribunal`.

### Steps (implementação — referência)

**1.1** — Migration + model `ContentView` + scopes.

**1.2** — `SiteSetting` + migration `site_settings` + página Filament `MeteredWallSettings` + `SiteSettingsSeeder`.

**1.3** — `ContentViewService`: `recordView`, `hasReachedDailyLimit` (contagem > limite após `recordView` no controller), `remainingViews`, `getDailyLimit`, `isMeteredWallEnabled` (`getAsBool`).

**1.4** — `TesePageController::index()`: se há IA e utilizador logado, **`recordView` só se não for admin**; depois aplica metered para registered com limite; passa `isMeteredPaywall`, `remainingViews`, `dailyLimit` à view.

**1.5** — `tese.blade.php`: CTA com views restantes sempre que aplicável; paywall metered quando limite excedido.

**1.6** — Testes Pest (ficheiros reais no repositório):

| Ficheiro | Conteúdo |
|----------|-----------|
| `tests/Feature/ContentViewServiceTest.php` | Serviço: `recordView`, limites por tier, `isMeteredWallEnabled`, etc. |
| `tests/Feature/SiteSettingTest.php` | `SiteSetting::get/set`, cache, **`getAsBool`** |
| `tests/Feature/MeteredWallSettingsPageTest.php` | Acesso HTTP à página Filament Metered Wall |
| `tests/Feature/UserPanelHistoryTest.php` | Dashboard + histórico com `content_views` |
| `tests/MySQL/MeteredWallMysqlTest.php` | Integração HTTP + DB real (STF fixtures): visitante, registered, subscriber, admin, 24h, settings |

Cenários da matriz original (visitante, revisita, limite, subscriber, admin, janela 24h, toggle off, etc.) devem estar cobertos por esta suíte em conjunto — não é obrigatório o nome `MeteredWallTest.php`.

**1.7** — `vendor/bin/pint --dirty --format agent`

---

### Arquivos (Fase 1) — estado

| Ficheiro | Notas |
|----------|--------|
| Migrations `content_views`, `site_settings` | OK |
| `ContentView`, `SiteSetting`, `ContentViewService` | OK (`getAsBool` para booleanos persistidos) |
| `MeteredWallSettings` (Filament) | OK |
| `SiteSettingsSeeder` | OK |
| `TesePageController` | OK (admin sem `recordView`) |
| `tese.blade.php` | UI metered completa |
| `UserPanelController` | Histórico/dashboard com enriquecimento em lote (evita N+1) |
| `config/teses.php` | Barra de teste + env |

---

### Decisões (Fase 1)

- **Limite inicial**: 3/dia (Filament).
- **Janela**: 24h rolling.
- **Contagem**: por conteúdo único; revisita grátis.
- **CTA**: utilizador registered com limite vê aviso de views restantes (em `tese.blade.php`).
- **Subscriber / premium**: sem limite e sem CTA de meter; views **registadas** para histórico.
- **Admin**: bypass total; **sem** registo em `content_views` (sem tracking).
- **Future-proof**: `content_type` para súmulas (implementação futura).

---

## Fases seguintes

| Fase | Feature | Status |
|------|---------|--------|
| **2** | Coleções de Teses | Aguarda validação Fase 1 |
| **3** | Painel: histórico (`content_views`), assinatura, coleções, alertas | Histórico **parcialmente** feito; resto aguarda Fase 2 |
| **4** | Alertas por tema | Aguarda Fase 3 |
| **5** | PDF "Decifrando a tese" | Aguarda Fase 4 |

---

## Verificação (Fase 1)

Suíte completa (recomendado, com Docker):

```bash
composer test
```

Apenas ficheiros do metered wall (SQLite):

```bash
php artisan test --compact \
  tests/Feature/ContentViewServiceTest.php \
  tests/Feature/SiteSettingTest.php \
  tests/Feature/MeteredWallSettingsPageTest.php \
  tests/Feature/UserPanelHistoryTest.php
```

MySQL (requer `docker compose up -d --wait mysql-testing` ou MySQL compatível nas mesmas credenciais):

```bash
php artisan test --compact tests/MySQL/
```

```bash
php vendor/bin/pint --dirty --format agent
```

Checklist manual:

- Registered: 3 teses com IA + CTA; **4.ª** tese distinta com paywall metered.
- Subscriber: sem CTA de limite; views no histórico.
- Filament: alterar limite e toggle; efeito coerente.
- Produção (após deploy): `SiteSettingsSeeder` uma vez se faltar `site_settings`; opcionalmente `TEST_TOOLBAR_ENABLED=true` temporário.

---

## Checklist pós-deploy (metered wall)

- [ ] Confirmar que `migrate --force` correu (pipeline ou manual).
- [ ] Se `site_settings` ainda não tiver as keys do meter: `php artisan db:seed --class=SiteSettingsSeeder --force`
- [ ] **Não** rodar `DatabaseSeeder` completo em prod sem necessidade explícita.
- [ ] Validar Filament (Metered Wall).
- [ ] (Opcional, testes em prod) `TEST_TOOLBAR_ENABLED=true` no `.env`; remover depois.
- [ ] Correr testes Pest relevantes localmente antes de releases.
