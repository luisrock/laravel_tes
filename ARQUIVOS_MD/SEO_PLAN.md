# Plano SEO — Teses & Súmulas

> Última atualização: 19/03/2026  
> Dados de referência: Google Search Console (04/11/2025)  
> Revisado por: Claude Opus 4.6 (corrigiu prioridades com base em análise Pareto)

---

## Estado Atual

### Métricas Gerais (Search Console — Nov/2025)

| Página | Cliques | Impressões | CTR | Posição |
|--------|---------|-----------|-----|---------|
| Homepage `/` | 431 | 871 | 49.48% | 3.84 |
| `/tese/stj/1120880` | 74 | 760 | 9.74% | 5.31 |
| `/tese/stf/16540` | 50 | 3.809 | 1.31% | 8.85 |
| `/tese/stf/8239` | 48 | 2.873 | 1.67% | 6.92 |
| `/tese/stf/27395` | 52 | 2.172 | 2.39% | 7.4 |
| `/tema/mandado-de-seguranca` | 70 | 1.277 | 5.48% | 8.39 |
| `/teses/stf` (listagem) | 38 | 7.878 | **0.48%** | 17.14 |
| `/teses/stj` (listagem) | 28 | 4.052 | **0.69%** | 10.25 |

### Consultas Mais Buscadas

| Consulta | Cliques | Impressões | CTR | Posição |
|----------|---------|-----------|-----|---------|
| "teses e sumulas" | 227 | 272 | 83.46% | 1 |
| "tema 1192 stj" | 44 | 323 | 13.62% | 4 |
| "tema 1419 stf" | 29 | 780 | 3.72% | 7.34 |
| "tema 1404 stf" | — | 1.320 | — | 9 |
| "tema 1194 stj" | — | 1.282 | 1.56% | 8 |
| "sumulas mandado de segurança" | 14 | 86 | 16.28% | 2.92 |

### O Que Já Está Implementado (Fase 1 — ✅ completa)

- [x] URLs canônicas por `numero` para teses (commit `38edaa8`)
- [x] URLs canônicas por `numero` para súmulas, com prefixo `sv` para vinculantes STF (commit `246f4e7`)
- [x] Redirects 301 de URLs antigas (por id) para canônicas (por numero)
- [x] Meta descriptions dinâmicas com truncagem inteligente a 155 chars
- [x] Canonical tags (`<link rel="canonical">`) em todas as páginas
- [x] Open Graph tags (og:title, og:description, og:url, og:locale)
- [x] Twitter Card (summary)
- [x] Breadcrumbs com Schema.org JSON-LD (BreadcrumbList)
- [x] Sitemap dinâmico via Spatie (`php artisan sitemap:generate`) — regenerado 19/03
- [x] robots.txt com referência ao sitemap
- [x] Google Analytics (produção)
- [x] Cache de busca (`SearchCacheManager`)
- [x] www → non-www redirect 301 (nginx + SSL expandido) — feito 19/03
- [x] Seção "Decifrando a tese" com conteúdo IA original (~200 teses STF)
- [x] JSON-LD `Article` + `Paywalled Content` nas páginas de tese com IA (commit `0760d35`) — validado em produção em 19/03

---

## Análise de Prioridade (Pareto)

### Ativo SEO mais valioso: conteúdo "Decifrando a tese"

~200 teses STF (e crescendo) têm análise jurídica original gerada por IA — conteúdo único que nenhum concorrente possui. Esse conteúdo:
- Está no HTML para todos os visitantes (incluindo Googlebot) — renderizado server-side
- Para não-logados, aparece visualmente blurred via CSS (classe `.premium-content-blur`)
- `aria-hidden="true"` é usado no conteúdo premium — correto para acessibilidade, **sem impacto em SEO** (confirmado: não é fator de indexação do Google)

**Problema crítico**: sem o markup `isAccessibleForFree` do Schema.org, o Google pode interpretar o padrão "conteúdo visível no HTML mas blurred para o usuário" como **cloaking** (técnica de spam). A documentação oficial do Google diz: *"Esses dados estruturados ajudam o Google a diferenciar esse material das técnicas de cloaking."*

### Páginas de listagem têm CTR baixo, mas impacto de mudança de título é marginal

Títulos e meta descriptions NÃO são fatores de ranking significativos. Meta description sequer é fator de ranking (Google reescreve ~70% das descriptions). Para sair da posição 17 → top 5, precisaria de backlinks e conteúdo — não de title tags melhores.

---

## Plano de Ações — Reprioritizado

### Fase 1 — Correções Imediatas ✅ COMPLETA

| # | Ação | Status |
|---|------|--------|
| 1.1 | Regenerar sitemap em produção | ✅ Feito 19/03 |
| 1.2 | Limpar cache em produção | ✅ Feito 19/03 |
| 1.3 | Consolidar www → non-www com redirect 301 | ✅ Feito 19/03 |

### Fase 2A — Structured Data nas páginas de tese (ALTO IMPACTO)

| # | Ação | Impacto | Esforço | Status |
|---|------|---------|---------|--------|
| 2A.1 | JSON-LD `Article` + `isAccessibleForFree: false` + `hasPart` com `cssSelector: ".premium-content-blur"` nas teses com conteúdo IA | **Alto** | Médio | ✅ Feito 19/03 |
| 2A.2 | Melhorar meta description com menção a IA quando `$ai_sections->isNotEmpty()` | Médio | Baixo | ⬜ |

### Fase 2B — Sitemap inteligente (MÉDIO IMPACTO)

| # | Ação | Impacto | Esforço | Status |
|---|------|---------|---------|--------|
| 2B.1 | Adicionar `<priority>` 0.8 e `<lastmod>` para teses com conteúdo IA no sitemap | Médio | Baixo | ⬜ |

### Fase 2C — Títulos das listagens (BAIXO IMPACTO, BAIXO ESFORÇO)

| # | Ação | Impacto | Esforço | Status |
|---|------|---------|---------|--------|
| 2C.1 | Separar `<title>` do `<h1>` (novo `$pageTitle` independente do `$label`) | Baixo | Mínimo | ⬜ |
| 2C.2 | Títulos curtos (≤60 chars) com contagem formatada nos 8 controllers | Baixo | Baixo | ⬜ |
| 2C.3 | Padronizar meta descriptions das súmulas (adicionar data e CTA) | Baixo | Baixo | ⬜ |

### Fase 3 — Structured Data adicional

| # | Ação | Impacto | Esforço | Status |
|---|------|---------|---------|--------|
| 3.1 | `FAQPage` schema nas páginas de tema | Médio | Médio | ⬜ |
| 3.2 | `ItemList` schema nas páginas de listagem | Baixo | Médio | ⬜ |

### Fase 4 — Conteúdo e Indexação

| # | Ação | Impacto | Esforço | Status |
|---|------|---------|---------|--------|
| 4.1 | Landing pages para temas com alto volume de impressões | Alto | Alto | ⬜ |
| 4.2 | Otimizar internal linking (temas → teses individuais) | Médio | Médio | ⬜ |
| 4.3 | Avaliar `noindex` em newsletters | Baixo | Baixo | ⬜ |
| 4.4 | Redirect 301 de `/index` para `/` | Baixo | Mínimo | ⬜ |

### Fase 5 — Performance e Core Web Vitals

| # | Ação | Impacto | Esforço | Status |
|---|------|---------|---------|--------|
| 5.1 | Auditar Core Web Vitals via PageSpeed Insights | Médio | Variável | ⬜ |
| 5.2 | Lazy load de conteúdo abaixo do fold | Médio | Médio | ⬜ |
| 5.3 | Otimizar bundles CSS/JS | Médio | Médio | ⬜ |

---

## Decisões Técnicas Tomadas

- `aria-hidden="true"` no conteúdo premium: **MANTER** — correto para acessibilidade, sem impacto SEO (confirmado via documentação oficial do Google)
- JSON-LD `Article` com `isAccessibleForFree`: usar apenas em teses COM conteúdo IA (condicional)
- `Paywalled Content`: validado no Rich Results Test como item válido em produção
- `$label` (H1) nos controllers de listagem: **NÃO mudar** — criar `$pageTitle` separado para o `<title>`
- Refatoração DRY dos 8 controllers de listagem: **NÃO nesta fase**
- Números formatados com ponto de milhar pt-BR: `number_format($count, 0, ',', '.')`

## Validação — 19/03/2026

- URL testada em produção: `/tese/stf/1444`
- Rich Results Test: **3 itens válidos detectados**
- `Article`: **1 item válido detectado**
- `Breadcrumbs`: **1 item válido detectado**
- `Paywalled Content`: **1 item válido detectado**
- Rastreamento: com êxito
- Indexação: permitida
- Aviso remanescente: campo `image` ausente no `Article` (não crítico, opcional)

## Notas

- **Dados de performance** disponíveis em `relatorios/tesesesumulas.com.br-Performance-on-Search-2025-11-04/`
- **Sitemap gerado por**: `app/Console/Commands/GenerateSitemap.php` → `php artisan sitemap:generate`
- **Padrão de URLs canônicas**: `/tese/{tribunal}/{numero}`, `/sumula/{tribunal}/{numero}` (com `sv` prefix para vinculantes STF)
- **Testes de regressão**: `tests/MySQL/TesePageMysqlTest.php` (12 testes), `tests/MySQL/SumulaPageMysqlTest.php` (15 testes), `tests/MySQL/TesePaywallSchemaTest.php` (3 testes)
- **Conteúdo IA**: seção "Decifrando a tese" com 5 sub-seções (teaser, caso_fatico, contornos_juridicos, modulacao, tese_explicada). Teaser sempre visível; demais sob registerwall/paywall (CSS blur)
- **Documentação Google paywall markup**: https://developers.google.com/search/docs/appearance/structured-data/paywalled-content
