# Extensão Chrome T&S — Análise, Estratégia e Plano de Execução

> **Propósito deste documento:** servir como fonte única de verdade para evoluir a extensão Chrome
> do tesesesumulas.com.br e as mudanças correlatas no site. Compila diagnóstico, medição de uso real,
> roadmap priorizado, desenho de freemium, plano de aquisição (botão + página no site) e um
> **briefing técnico para os modelos de IA** que vão ajudar a implementar no editor de código.
>
> **Última atualização:** 2026-06-18.
> **Status geral:** diagnóstico concluído; implementação não iniciada.
>
> **Como usar este doc (para humanos e IAs):**
> - As seções 1–4 são contexto e diagnóstico — leia antes de codar.
> - A seção 5 é o roadmap com checkboxes — marque conforme avançar.
> - A seção 10 é o briefing técnico obrigatório para quem for implementar.

---

## Índice
1. Contexto do produto
2. Estado atual da extensão
3. Medição de uso real (resultados)
4. Diagnóstico e estratégia
5. Roadmap de features (com checkboxes)
6. Features para usuário registrado/logado
7. Aquisição: botão de instalar + página de features no site
8. Desenho do freemium
9. Atribuição e métricas (UTM + instrumentação própria)
10. Briefing técnico para os modelos de IA (editor de código)
11. Próximos passos (checklist mestre)
12. Glossário de fatos do codebase (referência rápida)
13. Low hanging fruit (quick wins priorizados)

---

## 1. Contexto do produto

**tesesesumulas.com.br** — Laravel 12 + Filament 4 + Livewire 3, em produção (servidor Vito,
deploy automático no push). Núcleo:

- Busca de súmulas/teses em 7 tribunais/órgãos (STF, STJ, TST, TNU, CARF, FONAJE, CEJ).
- Quizzes.
- **Collections** (feature recém-lançada: o usuário salva/organiza itens em coleções públicas/privadas).
- **IA** já existente no site (`AnalisarAcordaoJob`, "Resumir com IA" via OpenRouter).

**Assinaturas: infra pronta, desligada por flag.**
- Cashier/Stripe instalado.
- `config/subscription.php` → `'enabled' => env('ENABLE_SUBSCRIPTIONS', false)`, tiers PRO/PREMIUM.
- `User::isSubscriber()` e `User::hasFeature(string $key)` já implementados.
- Para ligar: definir `ENABLE_SUBSCRIPTIONS=true` + `STRIPE_PRODUCT_PRO`/`STRIPE_PRODUCT_PREMIUM` no `.env`.

---

## 2. Estado atual da extensão

- **Repo:** `luisrock/tes_chrome` (GitHub, privado). Branch padrão: `master`.
- **Stack:** Manifest V3, **vanilla JS (sem build/bundler)**, CSS3 puro. Permissão só `storage`.
  `host_permissions`: `https://tesesesumulas.com.br/*`.
- **Arquivos:** `manifest.json`, `popup.html`, `js/tes.js`, `css/tes.css`, `icons/`.
- **O que faz hoje:** `POST /api/unified-search` com `{keyword}` e mostra **contagens** por tribunal +
  link pro site. Persiste a última busca via `chrome.storage.session`.
- **Limitações:** não mostra o **teor** da súmula/tese (só número/contagem); sem histórico além da
  sessão; sem login; sem integração com Collections; sem detecção de referências em páginas.

### Esclarecimento sobre versões (resolvido)
Existe **uma única** extensão. Linha do tempo:

| Data | Versão | Endpoint |
|---|---|---|
| 2022-09-08 | v0.0.9 | `/api/*.php` (legados) |
| 2026-02-18 | v1.0.0 (reescrita) | `/api/unified-search` |

Web Store hoje: **v1.0.0, atualizada 19/02/2026, 3.000 usuários, nota 5,0 (11 avaliações), 44 KiB.**
Os 3k já estão na v1.0.0. Endpoints `.php` em `routes/api.php` são só rede de segurança (e a medição
confirmou: **0 hits legados** — ninguém usa mais a versão antiga).

---

## 3. Medição de uso real (logs nginx Vito, 2026-06-18)

Os endpoints da API são **exclusivos da extensão** (o site faz busca server-side via Livewire, não
chama `/api/*`). Logo, **todo hit nesses endpoints = uso da extensão**.

Fonte: `/var/log/nginx/access.log*` (parseado direto como `vito`; a Ivana localizou mas não tinha
permissão de leitura — `640`, dono `vito`, grupo `adm`). Período: **~04/06 a 18/06/2026 (≈14 dias)**.

| Métrica | Valor |
|---|---|
| `/api/unified-search` — requisições | **43** no período (~3/dia) |
| **Pessoas distintas (IPs únicos)** | **16** no período (1–4/dia) |
| Endpoints `.php` legados | **0** (base toda na v1.0.0) |
| User-Agents | 100% Chrome/Windows (Chrome 149/148, 1 Edge) — confirma extensão |

### ⚠️ Achado central: gap instalação × uso real
**3.000 instalações vs. ~16 pessoas buscando em 2 semanas.** A maioria das instalações está
**dormente**. O "3k" é métrica de vaidade; o uso ativo é pequeno.

Ressalvas honestas:
- `chrome.storage.session` restaura a última busca sem novo hit na API → engajamento *por usuário*
  pode estar subestimado, mas a contagem de **pessoas distintas (16) é robusta**.
- Janela curta (~14 dias) e IP como proxy (escritórios com IP compartilhado podem fundir pessoas).
- Mesmo dobrando por essas ressalvas, segue minúsculo frente a 3k.

---

## 4. Diagnóstico e estratégia

**O gargalo não é aquisição — é ativação/retenção.** Já há 3k instalados; pouquíssimos usam.

Duas causas prováveis e suas contramedidas:

| Causa provável | Evidência | Contramedida |
|---|---|---|
| **A) Extensão pouco útil** (só dá contagem, não o teor) | feature atual limitada | Onda 1 — teor inline, side panel, detecção de referência (padrão Planalto Express) |
| **B) Esquecimento / sumiu do site** | botão de instalar existia antes da refatoração e foi removido; 3k vêm de quando havia | Seção 7 — reintroduzir botão + página de features; "novidades" no popup |

**Ordem recomendada:** primeiro tornar a extensão genuinamente útil (Onda 1), depois reativar a base
dormente e atrair novos (Seção 7), e só então pensar em monetização (Seção 8). Monetizar ~16 ativos
é prematuro.

**Norte de UX (inspirado na Planalto Express, ~1.000 usuários nota 5,0):** o sucesso dela vem de
detectar a referência no texto selecionado (ex.: "art. 186") e abrir um **painel lateral** com o teor
completo ao lado do documento, sem abrir aba, com cache offline. A T&S deve replicar esse padrão para
**súmulas e teses** (ex.: selecionar "Súmula 7 do STJ" ou "Tema 69" → painel lateral com o teor).

---

## 5. Roadmap de features (com checkboxes)

> Cada item tem um identificador (F1, F2…) para referência em commits/PRs.
> Marque `[x]` ao concluir. "Site" indica mudança no repo Laravel; "Ext" no repo `tes_chrome`.

### Onda 1 — Utilidade sem login (reduz atrito, reativa dormentes)
- [ ] **F1 — Menu de contexto (Ext):** selecionar texto em qualquer página → clique direito →
      "Buscar no Teses & Súmulas". Requer permissão `contextMenus`. Maior impacto/menor custo.
- [ ] **F2 — Side panel com teor inline (Ext):** substituir/complementar o popup minúsculo por um
      painel lateral (Chrome Side Panel API) que mostra o **teor completo** da súmula/tese, não só a
      contagem. Usa os endpoints de detalhe (ver seção 10). É o coração da proposta de valor.
- [ ] **F3 — Detecção de referência + botão flutuante (Ext):** content script que reconhece padrões
      como "Súmula 7 do STJ", "Súmula Vinculante 25", "Tema 69 do STF/Repercussão Geral",
      "Tese 123" no texto selecionado e oferece bolha flutuante → abre o teor no side panel.
- [ ] **F4 — Omnibox (Ext):** digitar `ts <termo>` na barra de endereço dispara a busca.
- [ ] **F5 — Cache local (Ext):** `chrome.storage.local` para reter resultados/teores recentes
      (ex.: 7 dias, padrão Planalto Express) — performance e uso offline parcial.
- [ ] **F6 — UTM nos links (Ext):** anexar `?utm_source=extension&utm_medium=<popup|sidepanel>` em
      todos os links que levam ao site (ver seção 9). ~1 linha por link.
- [ ] **F7 — "Novidades" no popup/side panel (Ext):** bloco discreto de changelog para reengajar quem
      reabre a extensão após muito tempo.

### Onda 2 — Conta e sincronização (puxa cadastro)
- [ ] **F8 — Login na extensão (Ext+Site):** autenticação via bearer token. O middleware
      `bearer.token` já existe no site. Fluxo sugerido: usuário gera token numa página da conta e cola
      na extensão (MVP), evoluindo depois para fluxo OAuth-lite. (Ver seção 10 para o que falta no site.)
- [ ] **F9 — Salvar na Coleção a partir da extensão (Ext+Site):** botão "Salvar na Coleção" no side
      panel, conectando à feature Collections. Requer F8.
- [ ] **F10 — Histórico e favoritos sincronizados (Ext+Site):** `chrome.storage.sync` para uso
      anônimo; sincronização com a conta quando logado.

### Onda 3 — Valor premium (depois de base ativa)
- [ ] **F11 — "Resumir com IA" no side panel (Ext+Site):** reaproveita `AnalisarAcordaoJob`/OpenRouter
      do site. Candidata natural a feature paga.
- [ ] **F12 — Alertas (Ext+Site):** avisar (badge no ícone) quando surgir súmula/tese nova batendo com
      um termo salvo pelo usuário.

> **Nota de arquitetura (Ext):** a extensão é vanilla JS sem build. Login + sync + side panel
> justificam organizar `js/` em módulos ES, mas **não** introduzir framework/bundler — manter leve e
> sem etapa de build, fiel à convenção atual.

---

## 6. Features para usuário registrado/logado

A ideia é criar uma escada de valor: anônimo → cadastrado (grátis) → assinante. O cadastro é o pivô
que liga a extensão à conta do site.

**Para o usuário logado (grátis), candidatas:**
- [ ] **R1 — Histórico de buscas persistente** (cross-device, via conta) — vs. só sessão no anônimo.
- [ ] **R2 — Favoritos / "Minhas súmulas e teses"** salvos na conta.
- [ ] **R3 — Salvar direto em Coleções** (F9) e ver/gerenciar coleções pelo side panel.
- [ ] **R4 — Sincronização de preferências** (tribunais favoritos, filtros padrão) entre site e extensão.
- [ ] **R5 — Anotações pessoais** em súmulas/teses (ex.: "usei na petição X").
- [ ] **R6 — Exportar/copiar citação formatada** (pronta para petição), com estilo configurável.

**Reservadas a assinante (quando o freemium abrir — ver seção 8):**
- [ ] **R7 — Resumo com IA** (F11), com limite generoso/ilimitado.
- [ ] **R8 — Alertas de novas súmulas/teses** (F12).
- [ ] **R9 — Busca sem rate-limit** (anônimo/grátis limitado a X buscas/dia).
- [ ] **R10 — Histórico ilimitado / coleções ilimitadas** (alinhado aos tiers de Collections do site).

> Princípio: **o que é grátis hoje continua grátis.** Monetizar apenas capacidades novas
> (IA, alertas, limites maiores), nunca o que os 3k já têm — evita churn e avaliação ruim.

---

## 7. Aquisição: botão de instalar + página de features no site

Hipótese forte: os 3k usuários vêm de quando o **site exibia um botão de instalar a extensão**, que
foi removido na refatoração. Reintroduzir é provavelmente a alavanca de aquisição de maior ROI.

- [ ] **A1 — Botão "Instalar extensão" no site (Site):** reintroduzir CTA visível (header, página de
      busca e/ou resultados). Decisão de UX: botão leva à **página de features** (A2), não direto à
      Web Store (educar antes de instalar converte melhor e reduz instalação dormente).
- [ ] **A2 — Página de features da extensão no site (Site):** página dedicada (ex.: rota
      `/extensao`) mostrando o que a extensão faz, GIFs/prints das features (incluindo as novas das
      Ondas 1–3), e CTA final "Instalar no Chrome" → Web Store (com UTM). Serve de landing page.
- [ ] **A3 — Nudge contextual (Site):** em quem está no Chrome e não tem a extensão, exibir banner
      discreto nos resultados de busca: "Pesquise de qualquer site com nossa extensão".
      (Detecção de "tem a extensão?" pode ser feita por um ping de content script a um elemento/flag;
      decidir na implementação se vale a complexidade ou se basta exibir a todos no Chrome.)
- [ ] **A4 — Link extensão→site→cadastro:** garantir que quem chega da extensão (via UTM) veja CTA de
      criar conta, fechando o funil aquisição→ativação→cadastro.

**Fluxo desejado:** usuário no site clica "Instalar extensão" → página de features (A2) → Web Store →
instala → usa (Ondas 1–3) → cria conta (Seção 6) → [futuro] assina (Seção 8).

---

## 8. Desenho do freemium

Infra já existe e está **desligada por flag** (seção 1). **Abertura adiada** até o uso ativo crescer.

| Camada | O quê | Objetivo |
|---|---|---|
| Grátis (anônimo) | Contagens + teor inline + side panel + menu de contexto + omnibox + cache | Mantém/atrai usuários; não tira nada do atual |
| Logado (grátis) | R1–R6 (histórico, favoritos, coleções, anotações, citação) | Instalação → cadastro (lead) |
| Pago (PRO/PREMIUM) | R7–R10 (IA, alertas, sem rate-limit, limites maiores) | Receita |

Rate-limit no grátis (ex.: X buscas/dia) é o limitador mais aceito e menos hostil.

---

## 9. Atribuição e métricas

**Problema:** Matomo é tracker JS de página — **não vê a extensão** (o popup/side panel não roda o JS
do site). E os logs nginx só guardam ~14 dias e exigem acesso `vito`.

- [ ] **M1 — UTM (F6):** `?utm_source=extension&utm_medium=<popup|sidepanel>` em todos os links
      extensão→site. Permite ao Matomo segmentar o tráfego vindo da extensão e medir o funil
      extensão→site→cadastro→assinatura.
- [ ] **M2 — Instrumentação própria (Site):** registrar uso da extensão de forma durável e consultável,
      em vez de depender de parsear nginx. Opções: tabela `extension_searches` (keyword hash, tribunal,
      timestamp, versão da extensão via header/UA) **ou** um log channel dedicado. Cuidar de LGPD
      (não logar PII; keyword pode ser sensível → considerar só métricas agregadas).
- [ ] **M3 — Versão no request (Ext+Site):** enviar a versão da extensão num header custom
      (ex.: `X-Extension-Version`) para medir adoção de updates server-side.

---

## 10. Briefing técnico para os modelos de IA (editor de código)

> **Leitura obrigatória antes de implementar qualquer item.** Esta seção orienta os assistentes de IA
> que vão codar tanto no repo da extensão quanto no repo do site.

### 10.1 Repositórios e convenções
- **Extensão:** `luisrock/tes_chrome` (privado). **Vanilla JS, Manifest V3, sem build/bundler.**
  - NÃO introduzir framework, npm, TypeScript ou etapa de build sem aprovação explícita do Mauro.
  - Manter o design system CSS existente (variáveis `--brand-*`, `--slate-*`; fonte Inter).
  - Toda nova permissão deve ser justificada no PR (princípio do menor privilégio; a loja revisa isso).
- **Site:** Laravel 12 + Filament 4 + Livewire 3 + TailwindCSS 3 + Pest 3. Seguir `CLAUDE.md` do repo
  (Boost guidelines): usar `php artisan make:`, Form Requests, Eloquent (sem `DB::`), `route()`,
  testes obrigatórios, rodar `vendor/bin/pint --dirty` antes de finalizar.

### 10.2 Contrato da API (já existente no site)
- `POST /api/unified-search` — público, sem token.
  - Body: `{ "keyword": "<min 3 chars>" }`
  - Resposta: objeto com chave por tribunal (`stf`, `stj`, `tst`, `tnu`, `carf`, `fonaje`, `cej`),
    cada um com `{ total, sumulas, teses }`, mais `meta: { keyword, total_global }`.
- `GET /api/sumula/{tribunal}/{numero}` — **protegido por `bearer.token`**. Retorna teor da súmula.
- `GET /api/tese/{tribunal}/{numero}` — **protegido por `bearer.token`**. Retorna teor da tese.
- Endpoints legados `POST /api/{stf|stj|tst|tcu|tnu|carf|fonaje|cej}.php` — compat da v0.0.9; **não usar
  em código novo** (0 uso atual; tendem a ser removidos).
- Controller: `app/Http/Controllers/ApiController.php`. Rotas: `routes/api.php`.

### 10.3 Decisões em aberto que exigem trabalho no SITE antes da extensão
- **Teor inline (F2/F3):** hoje `/api/sumula` e `/api/tese` exigem `bearer.token`. Decidir:
  (a) expor um endpoint público de teor resumido para uso anônimo, **ou** (b) exigir login na extensão
  para ver teor. Recomendação: teor básico público (valor imediato) + extras (IA, salvar) atrás de login.
- **Login na extensão (F8):** falta no site uma página de geração/gestão de token de API por usuário
  (criar/revogar). Verificar o que o middleware `bearer.token` espera (formato/origem do token) antes
  de desenhar a UI.
- **CORS:** popup e side panel são páginas de extensão e fazem fetch sob `host_permissions` (sem CORS
  da página, como já ocorre hoje). O ponto de atenção é o **content script** (F3): ele roda no contexto
  da página e está sujeito a CORS — fetch direto à API a partir dele pode falhar. Padrão recomendado:
  content script envia `chrome.runtime.sendMessage` ao service worker, que faz a chamada à API e
  devolve o resultado. Validar na implementação.

### 10.4 Como testar
- **Extensão:** carregar sem compactação em `chrome://extensions` (modo desenvolvedor) → testar popup,
  side panel, menu de contexto, omnibox em páginas reais. Verificar console do service worker.
- **Site:** Pest. `php artisan test --compact --filter=<algo>`. Toda mudança de código deve ter teste
  (regra do `CLAUDE.md`). Endpoints novos → feature test cobrindo auth, validação e resposta.

### 10.5 O que NÃO fazer
- Não tirar funcionalidade hoje gratuita atrás de paywall.
- Não logar PII/keywords cruas sem decisão de LGPD (ver M2).
- Não adicionar dependências (npm no site, libs na extensão) sem aprovação.
- Não mexer nos endpoints `.php` legados além de eventual remoção planejada.

---

## 11. Próximos passos (checklist mestre)
- [x] Medir uso real (seção 3) — concluído. Achado: gap instalação×uso; problema é ativação.
- [ ] **Sprint de aquisição (Site):** A1 + A2 (botão + página de features) — provável maior ROI, pode
      andar em paralelo às features da extensão.
- [ ] **Sprint 1 da extensão:** F1 (menu de contexto) + F2 (side panel + teor) + F6/M1 (UTM).
- [ ] **Decisão de produto (Mauro):** teor anônimo público vs. atrás de login (seção 10.3).
- [ ] **Sprint 2:** F3 (detecção de referência), F4 (omnibox), F5 (cache), F7 (novidades).
- [ ] **Sprint 3 (conta):** F8 (login) → F9 (coleções) → F10 (sync) + R1–R6.
- [ ] **M2/M3:** instrumentação própria de métricas.
- [ ] **Quando base ativa crescer:** abrir freemium (seção 8) + R7–R10 / Onda 3.

---

## 12. Glossário de fatos do codebase (referência rápida)
- Tribunais suportados: STF, STJ, TST, TNU, CARF, FONAJE, CEJ (legado também TCU).
- `config/subscription.php`: flag `ENABLE_SUBSCRIPTIONS` (default false), tiers PRO/PREMIUM por
  `STRIPE_PRODUCT_PRO`/`STRIPE_PRODUCT_PREMIUM`.
- `User::isSubscriber(): bool` e `User::hasFeature(string $key): bool` já existem.
- Feature **Collections** já em produção (coleções públicas/privadas, itens de vários tribunais).
- Features de **IA** no site: `AnalisarAcordaoJob`, "Resumir com IA" via OpenRouter.
- Middleware `bearer.token` protege endpoints de detalhe e de escrita da API.
- Matomo: sync de views via `php artisan matomo:sync` (`SyncMatomoViews`); **não** enxerga a extensão.
- Logs nginx do Vito: `/var/log/nginx/access.log*`, retenção ~14 dias, leitura exige usuário `vito`.
- Repo extensão: `luisrock/tes_chrome` (privado), branch `master`, vanilla JS MV3 sem build.

---

## 13. Low hanging fruit (quick wins priorizados)

> Itens identificados após cruzar este plano com o codebase Laravel (`teses`) em 2026-06-19.
> Ordenados por **impacto ÷ esforço**. Cada um tem ID `LH-*` para referência.
> Legenda de esforço: **S** (&lt;½ dia), **M** (1–2 dias), **L** (3+ dias).

### Correções de documentação / decisão rápida

- [ ] **LH-0 — Esclarecer o bearer token atual (doc + produto):** o middleware `bearer.token` valida um
      **único** `API_TOKEN` global (`config/services.php` ← `.env`), **não** token por usuário. O F8
      (login na extensão) exige trabalho novo no site; até lá, a extensão **não** consegue chamar
      `/api/sumula` e `/api/tese` sem embutir o token global (inaceitável no cliente) **ou** sem um
      endpoint público de leitura. **Decisão LH-0 desbloqueia F2/F3.**

### Site — ganho imediato, quase só Blade/config

| ID | O quê | Por quê | Esforço |
|---|---|---|---|
| **LH-1** | **UTM nos links existentes** — footer (`partials/footer.blade.php`) e `/atualizacoes` já apontam à Web Store; acrescentar `?utm_source=site&utm_medium=footer` (e `header` quando existir CTA). | Mede tráfego site→loja sem esperar F6 na extensão. Complementa M1. | **S** |
| **LH-2** | **CTA "Extensão" no header** — link discreto no `partials/header.blade.php` (desktop + mobile), igual ao que já existe no footer. Não precisa da página `/extensao` (A2) para começar. | O doc cita remoção do botão visível na refatoração; o footer sobreviveu, o header não. Reativa descoberta com diff mínimo. | **S** |
| **LH-3** | **Compat `q` em `/api/unified-search`** — `ApiController::index()` aceita `q` **ou** `keyword`; `unifiedSearch()` só `keyword`. Alinhar evita regressão se a extensão ainda enviar `q` em algum fluxo. | Consistência de API; teste Pest de 5 linhas. | **S** |
| **LH-4** | **Throttle leve em `/api/unified-search`** — ex.: `throttle:120,1` (sem auth). Hoje não há rate limit na API pública. | Protege abuso antes do freemium (R9) e custo de FULLTEXT. | **S** |

### Site — desbloqueia teor inline (F2) sem F8

| ID | O quê | Por quê | Esforço |
|---|---|---|---|
| **LH-5** | **Endpoint público de leitura `GET /api/public/sumula/{trib}/{n}` e `.../tese/...`** — retorna só campos necessários ao painel (número, tribunal, texto, link canônico); sem bearer; com throttle. Manter `/api/sumula` autenticado para escrita/admin. | Resolve o bloqueio da seção 10.3 sem embutir `API_TOKEN` na extensão. Valor imediato para Onda 1. | **M** |
| **LH-6** | **Ampliar tribunais em `getSumula`/`getTese`** — hoje só **STF e STJ**; `unified-search` cobre 7 órgãos. Reutilizar `SearchTribunalRegistry` + tabelas legadas (TST, TNU, CARF, FONAJE, CEJ). | Sem isso, side panel só serve para metade do marketing do produto. | **M** |
| **LH-7** | **Instrumentação mínima M2/M3 no `unifiedSearch`** — ler header `X-Extension-Version` (M3) e incrementar contador diário (tabela simples `extension_api_hits` ou `Log::channel('extension')` parseável). **Não** logar keyword crua (LGPD). | Fim da dependência de parsear nginx a cada 14 dias. | **M** |

### Extensão (`tes_chrome`) — alto impacto, baixo risco

| ID | O quê | Por quê | Esforço |
|---|---|---|---|
| **LH-8** | **Deep links com UTM nos resultados atuais** — cada contagem linka para `https://tesesesumulas.com.br/?keyword=...` (busca cobre os 7 órgãos) e, quando aplicável, para a página individual `/sumula/{trib}/{n}` ou `/tese/{trib}/{n}` (**só existe para stf/stj/tst/tnu**; para carf/fonaje/cej cair na busca). Anexar `utm_source=extension&utm_medium=popup`. | Melhora utilidade **hoje** (antes de F2) sem nova API; usuário vê o teor no site com um clique. | **S** |
| **LH-9** | **F1 — Menu de contexto** — texto selecionado → "Buscar no T&S". Já priorizado no roadmap; permissão `contextMenus` + ~30 linhas no service worker. | Maior impacto/menor custo da Onda 1; não depende de LH-5. | **S** |
| **LH-10** | **F4 — Omnibox** — `ts <termo>` na barra de endereço. Entrada no `manifest.json` + handler no service worker. | Fluxo rápido para power users; padrão Planalto Express. | **S** |
| **LH-11** | **F6 — UTM em todos os links extensão→site** | Uma linha por `href`; habilita segmentação Matomo. | **S** |
| **LH-12** | **Bloco "Ver no site" / changelog estático no popup** — versão antecipada de F7; texto fixo ("Agora: busca em 7 tribunais") reengaja quem reabre após meses. | Custo zero de backend; ataca causa B (esquecimento). | **S** |

### Aquisição — MVP de A2 enxuto

| ID | O quê | Por quê | Esforço |
|---|---|---|---|
| **LH-13** | **Página `/extensao` mínima (A2 lite)** — uma Blade estática nova (sem controller pesado, sem DB): título, 3–4 bullets (busca rápida, 7 tribunais, gratuito), 1 GIF/print do popup e CTA "Instalar no Chrome" com UTM. LH-2 aponta para cá em vez de ir direto à Web Store. **Não** reaproveitar `/atualizacoes`: aquela rota é changelog de conteúdo jurídico e está **desativada para visitantes** (uso interno). | Entrega o essencial do A2 (educar antes de instalar) com uma view simples, sem o escopo completo de landing. | **S/M** |

### O que **não** é low hanging (evitar subestimar)

- **F2 side panel + teor completo** — depende de LH-0 + LH-5 (ou F8).
- **F3 detecção de referência** — content script + regex + side panel; médio/alto na extensão.
- **F8 login / token por usuário** — middleware atual é token global; precisa UI em `/minha-conta`, migration de tokens, revogação.
- **F9 coleções** — API REST de coleções ainda não existe (`CollectionModalController` é rota web autenticada).
- **Remover endpoints `.php` legados** — 0 hits, mas exige release coordenado da extensão (já na v1.0.0) + período de observação.

### Sequência sugerida (1–2 sprints curtas)

```
Semana A (paralelo):
  Site:  LH-1 → LH-2 → LH-3 → LH-4
  Ext:   LH-8 → LH-11 → LH-9 → LH-12

Semana B (após decisão LH-0):
  Site:  LH-5 + LH-6 + LH-7
  Ext:   F2 side panel consumindo LH-5
  Site:  LH-13 (landing provisória) em paralelo
```

> **Nota:** LH-1/LH-2/LH-13 atacam retenção/aquisição no site **sem** esperar release na Web Store.
> LH-8 dá valor perceptível na extensão atual (v1.0.0) no próximo publish — útil para reativar parte dos 3k dormentes antes do side panel.
