# Manual — Estatísticas do site (Filament)

Página: **`/admin/painel/estatisticas`** (URL antiga `/admin/painel/newsletter-stats` redireciona)  
Filtro: **Período** — 24 h, 3, 7, 30 ou 60 dias (aplica-se à maioria dos números; ver exceções abaixo)  
Botão: **Atualizar** → executa `php artisan newsletter:sync --all` (todas as contas de uma vez)

Este painel mistura **duas fontes de dados**. Para validar números, convém saber qual é qual.

| Fonte | O que é |
|-------|---------|
| **Sendy (MySQL remoto)** | Lista real de subscritores (`subscribers`). Fonte da verdade para «quem está na lista». |
| **T&S (`newsletter_subscription_events`)** | Auditoria de ações no site (inscrição, popup, etc.). Uma linha por **evento**, não necessariamente por pessoa única. |
| **T&S (`users.newsletter_subscribed_at`)** | Cache local: «este utilizador com conta T&S está inscrito». Atualizado por inscrições no site e por **sync**. |

---

## Pré-requisitos para os números fazerem sentido

1. **`newsletter_integration_enabled = 1`** (Filament → Newsletter Sendy). Com flag OFF, quase não há eventos novos.
2. **Em dev (Mac):** se `SENDY_DB_ENABLED=false`, o card **Inscritos no Sendy** mostra **—** (sem leitura à DB Sendy). Em **prod** deve mostrar um inteiro.
3. **`SENDY_LIST_INTERNAL_ID`** deve ser `2` (lista T&S no Sendy).
4. Depois de **Atualizar** (ou do sync automático), **Contas inscritas no site** deve aproximar-se dos utilizadores T&S que existem na lista Sendy (só contas com login).

---

## Bloco superior — 5 cards

### 1. Novos registos

**O que mostra:** Contas criadas no site no **período selecionado** (`users.created_at`).

**Como validar:** Comparar com admin de utilizadores ou SQL `COUNT(*) FROM users WHERE created_at >= …`.

---

### 2. Novas inscrições na newsletter

**O que mostra:** Eventos de inscrição (`subscribed` + `already_subscribed`) no **período selecionado**.

**Como validar:** Cruzar com «Novos registos» — útil ver quantas contas novas também se inscreveram na newsletter.

---

### 3. Total na lista de email (snapshot — não usa o filtro de período)

**O que mostra:** Total de contactos **ativos** na lista Sendy configurada.

**Como é calculado (query na DB Sendy, tabela `subscribers`):**

- `list` = `SENDY_LIST_INTERNAL_ID` (ex.: 2)
- `unsubscribed = 0`
- `bounced = 0`
- `complaint = 0`

**Não exclui** explicitamente «unconfirmed» (`confirmed = 0`); esses entram na contagem se não estiverem unsub/bounce/complaint.

**Como validar:**

- Painel Sendy → lista T&S → total de subscritores ativos (deve estar próximo; pequenas diferenças podem existir se o Sendy usar outra definição de «ativo»).
- Em dev com DB Sendy desligada: **—** é esperado.

**Não confundir com:** Cache local (só users T&S) nem «Novos (7 dias)» (eventos no site).

---

### 4. Contas inscritas no site (snapshot — não usa o filtro de período)

**O que mostra:** Utilizadores com conta no T&S marcados como inscritos na newsletter (`newsletter_subscribed_at` preenchido).

**Como é calculado:**

```sql
SELECT COUNT(*) FROM users WHERE newsletter_subscribed_at IS NOT NULL;
```

**Como validar:**

- Após **Atualizar**, comparar com subscritores Sendy que tenham **conta T&S** (mesmo email).
- Típico: **Cache local ≤ Inscritos no Sendy**, porque muitos emails na lista Sendy são visitantes sem conta (`/newsletters`, popup) ou inscrições antigas.

**Atenção:**

- Visitante inscrito só pelo popup **sem** registo → evento em `newsletter_subscription_events`, mas **não** aumenta Cache local.
- `newsletter_subscribed_at` é preenchido com `now()` no sync/inscrição; **não** é a data real de inscrição no Sendy.
- Unsubscribe no painel ou no Sendy → sync ou toggle deve pôr o campo a `NULL`.

---

### 5. Conversão do popup (usa o filtro de período)

**O que mostra:** Percentagem no período selecionado:

```
(inscrições com source = popup) / (impressões popup) × 100
```

**Numerador:** eventos `subscribed` ou `already_subscribed`, `source = popup`, últimos 30 dias.

**Denominador:** eventos `action = impression`, `source = popup`, últimos 30 dias.

**Se não houver impressões nos 30 dias:** mostra **—**.

**Como validar:**

```sql
-- Impressões 30d
SELECT COUNT(*) FROM newsletter_subscription_events
WHERE source = 'popup' AND action = 'impression'
  AND created_at >= NOW() - INTERVAL 30 DAY;

-- Conversões 30d
SELECT COUNT(*) FROM newsletter_subscription_events
WHERE source = 'popup' AND action IN ('subscribed', 'already_subscribed')
  AND created_at >= NOW() - INTERVAL 30 DAY;
```

Dividir (conversões / impressões) × 100 e arredondar 1 casa decimal.

**Atenção:**

- Taxa pode ser **> 100%** se houver mais inscrições que impressões (impressão não registada, cookie, ou inscrição noutra sessão sem impression).
- Só mede fluxo **popup**; não é conversão global do site.

---

## Gráfico — Inscrições por dia (30 dias)

**O que mostra:** Linha com **um ponto por dia** nos últimos 30 dias (inclui dias com **zero**).

**Por dia:** conta eventos `subscribed` + `already_subscribed` (qualquer `source`), agrupados por `DATE(created_at)`.

**Como validar:**

```sql
SELECT DATE(created_at) AS dia, COUNT(*) AS total
FROM newsletter_subscription_events
WHERE action IN ('subscribed', 'already_subscribed')
  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
GROUP BY dia
ORDER BY dia;
```

(30 dias = hoje + 29 dias anteriores, alinhado ao código.)

**Atenção:**

- Pico no dia do deploy / testes em prod é normal.
- Não mostra unsubscribes nem falhas.

---

## Gráfico — Inscrições por origem

**O que mostra:** Distribuição **histórica (sem limite de datas)** de eventos `subscribed` + `already_subscribed`, agrupados por `source`.

| Label no gráfico | Valor `source` na BD |
|------------------|----------------------|
| Registro | `registration` |
| Google | `google_oauth` |
| Painel | `panel_toggle` |
| /newsletters | `newsletters_form` |
| Popup | `popup` |
| Sync | `sync` |

**Como validar:**

```sql
SELECT source, COUNT(*) AS total
FROM newsletter_subscription_events
WHERE action IN ('subscribed', 'already_subscribed')
GROUP BY source
ORDER BY total DESC;
```

**Atenção:**

- Soma das fatias = total de eventos de inscrição **desde sempre** (com integração ligada), não «últimos 30 dias».
- `sync` só aparece se o sistema gravar eventos com essa origem (hoje o sync **não** grava evento por defeito).

---

## Tabela — Popup A/B

**O que mostra:** Para variantes **A** e **B**, métricas do popup (`source = popup`):

| Coluna | Significado |
|--------|-------------|
| **Impressões** | Eventos `action = impression`, `popup_variant` = A ou B |
| **Inscrições** | Eventos `subscribed` ou `already_subscribed`, mesma variante |
| **Taxa** | Inscrições ÷ Impressões × 100 (1 decimal), ou **—** se impressões = 0 |

**Período:** **histórico completo** (não filtra últimos 30 dias).

**Como validar:**

```sql
SELECT popup_variant, action, COUNT(*)
FROM newsletter_subscription_events
WHERE source = 'popup' AND popup_variant IN ('A', 'B')
GROUP BY popup_variant, action;
```

**Atenção:**

- Variante B com tudo zero → normal se A/B desligado no Filament (só tráfego A).
- **Taxa da tabela** ≠ card **Conversão popup (30d)** (períodos diferentes: tabela = sempre; card = 30 dias).
- Impressão dispara quando o popup **abre** (tracking `POST /newsletter/event`).

---

## Atualizar contas inscritas (manual + automático)

### Botão «Atualizar» no painel

**O que faz:** `php artisan newsletter:sync --all` — percorre **todas** as contas com email, consulta a lista Sendy e atualiza:

- Quem está inscrito no Sendy → conta marcada como inscrita no site
- Quem saiu / não está na lista → marca removida no site

**Quando usar:** depois de mudanças em massa no Sendy, antes de confiar no card **Contas inscritas no site**, ou quando notares contas desalinhadas com o perfil.

**O que muda nos stats:** só o card **Contas inscritas no site**. Os outros números não dependem deste comando.

---

### Sync automático (via Laravel Scheduler)

Todas as tarefas agendadas do T&S estão em `app/Console/Kernel.php` (fila, sitemap, Matomo, import newsletters, renewal reminders, `newsletter:sync`).

**Em produção (Vito):** remover do cron as linhas antigas que chamavam `artisan` diretamente e deixar **apenas**:

```text
* * * * * php8.3 /home/vito/tesesesumulas.com.br/artisan schedule:run >> /dev/null 2>&1
```

**Linhas a apagar no painel Vito** (já cobertas pelo Kernel):

```text
0 0 * * * php8.3 /home/vito/tesesesumulas.com.br/artisan queue:work --stop-when-empty
0 6 * * * php8.3 /home/vito/tesesesumulas.com.br/artisan sitemap:generate
0 3 * * 1 php8.3 /home/vito/tesesesumulas.com.br/artisan matomo:sync
0 23 * * 2 php8.3 /home/vito/tesesesumulas.com.br/artisan newsletters:import
```

**Comportamento de `newsletter:sync` no cron:** de 6 em 6 horas, só contas com sync antigo (> 6 h). O botão **Atualizar** no painel usa `--all`.

**Verificar após deploy:**

```bash
php artisan schedule:list
```

---

### Comandos Artisan (SSH / terminal)

| Comando | Uso |
|---------|-----|
| `php artisan newsletter:sync` | Contas «em atraso» (> 6 h sem sync) — **é o que o cron usa** |
| `php artisan newsletter:sync --all` | **Todas** as contas — **é o que o botão Atualizar usa** |
| `php artisan newsletter:sync --user=123` | Uma conta específica (id em `users`) |

---

**Nota:** em dev local (`teses.test`), o scheduler só corre se tiveres `schedule:run` no Mac ou executares comandos à mão.

---

## Checklist rápido de sanidade

| Relação esperada | Se estiver muito diferente… |
|------------------|-----------------------------|
| Cache local ≤ Inscritos Sendy | Normal; muitos emails só no Sendy |
| Novos (7d) ≤ soma dos últimos 7 dias no gráfico diário | Devem ser coerentes (mesmo tipo de evento) |
| Conversão popup 30d ≈ cálculo manual SQL acima | Ver impressões zero ou período |
| Após sync, users conhecidos inscritos no Sendy com conta | `newsletter_subscribed_at` deve bater certo |

---

## O que este painel **não** mede

- Emails na lista Sendy **sem** evento no T&S (inscrição antiga, import manual, outro formulário).
- Unsubscribes, bounces, complaints (não há cards dedicados).
- Taxa de abertura / cliques de campanhas (`newsletters` publicadas ≠ esta lista Sendy de opt-in).
- Utilizadores únicos (só eventos brutos).

---

## Referência técnica

- Métricas: `app/Services/Newsletter/SiteMetrics.php`
- Página: `app/Filament/Pages/SiteStats.php`
- Sendy count: `SendyService::activeSubscriberCount()`
- Página: `app/Filament/Pages/SiteStats.php`
- Comando: `app/Console/Commands/SyncNewsletterStatus.php`
- Tabela de eventos: `newsletter_subscription_events`
