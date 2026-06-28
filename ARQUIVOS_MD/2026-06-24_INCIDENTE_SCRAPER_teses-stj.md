# Incidente Scraper — Pico de acessos em `/teses/stj` (tesesesumulas)

**Datas do evento:** 23–24/06/2026
**Relatório consolidado em:** 2026-06-28
**Site:** `tesesesumulas.com.br` · **Matomo:** `maurolopes.com.br/matomo` (idSite=2)
**Status:** Diagnosticado. **Nenhuma alteração de código/infra feita** (decisão consciente). Monitoramento ativo via alertas Matomo.

> **LEIA ISTO SE UM NOVO INCIDENTE COM BOTS OCORRER.** Este arquivo registra a assinatura do scraper, o que foi descartado, as mitigações já analisadas (com efeitos colaterais) e os alertas configurados. Não refazer a investigação do zero — comparar o novo evento com a assinatura abaixo primeiro.

---

## 1. Resumo executivo

Pico **real** de acessos em 23–24/06/2026, **sem** aumento correspondente de registros ou inscrições na newsletter. Causa: **scraper automatizado distribuído** (Chrome headless via rede de proxies residenciais em ~97 países) varrendo repetidamente **uma única página** — `/teses/stj` (lista de Temas Repetitivos e Teses Vinculantes do STJ). Tráfego **não-humano**: inflou o contador de acessos mas **não gerou conversões**, exatamente o sintoma observado.

**A extensão Chrome 2.0.0 foi DESCARTADA como causa, com dados.** (Não confundir com o [incidente 504 de 23/06](2026-06-23_INCIDENTE_504_tesesesumulas.md), esse sim causado pela versão **antiga** da extensão. São eventos distintos, próximos no tempo.)

---

## 2. Confirmação do pico (Matomo, idSite=2)

- **24/06:** 7.169 visitas / 7.155 visitantes únicos (**+184,3%** sobre a base); 7.406 exibições de página (+180%). 23/06 similar.
- Gráfico 12–25/06: base estável (centenas/dia) até ~22/06 → subida vertical, pico em 24/06 → volta ao normal em 25/06.
- **Comportamento típico de não-humano:** duração média ~21–22s; rejeição **97%**; ~1 ação por visita (nenhuma navegação/busca/conversão).

### Cruzamento com painel T&S (mesmo período, tudo plano)
- Novos registros: 1 (24h), 16 (3d), 22 (7d) — ritmo normal.
- Newsletter: pico isolado de 17 em 23/06, caiu para 3 (24/06) e 1 (25/06); conversão do popup ~0,1%.
- O tráfego do pico não interage → não move registros nem newsletter.

---

## 3. Por que a extensão 2.0.0 foi descartada

- No relatório de aquisição do Matomo (24/06), a campanha "extension" gerou **apenas 2 visitas**; `/extensao` teve 1 exibição no dia.
- No painel T&S, as buscas da extensão **caíram** durante o pico (~10/dia em 23–24/06) frente ao início da semana (~22/dia em 19–22/06) — movimento **oposto** ao do tráfego.
- Conclusão: a v2.0.0 **não tem relação causal** com o pico.

---

## 4. Página alvo

- **`/teses/stj`**: 7.009 exibições / 6.821 únicas ≈ **97,8% de todo o pico**, com 98% de rejeição e ~20s na página.
- Resíduo normal: `/tese/...` 295, `/sumula` 35, `/tema` 21, `/index` 18.

---

## 5. Assinatura técnica do bot (fingerprint)

Não é robô nomeado (ex.: Googlebot). É **scraper distribuído** rodando Chrome headless e/ou via proxies residenciais. Fingerprint quase idêntico em massa:

- **Windows 11:** 7.053 visitantes (98,6%) — uma única versão de SO.
- **"Windows / Chrome / 1920×1080":** 6.859 (95,8%); resolução 1920×1080: 6.921 (96,6%).
- **Chrome:** 6.960 (97%); Matomo rotulou explicitamente "Headless Chrome" em 4 (o resto mascara o user-agent como Chrome comum).
- Marca de dispositivo "Desconhecido": 7.139; modelo "Genérico Desktop": 7.134; 99,8% desktop.
- **Comportamento (log filtrado por `/teses/stj`):** 100% "Entrada direta" (sem referrer); 100% "1 Ação" (abre `/teses/stj` e sai).

### Origem geográfica (rede distribuída — 97 países)
- Continentes: Ásia **4.766 (66,6%)**, Am. do Sul 1.311, Am. do Norte 469, Europa 187.
- Países (24/06): Vietnã 1.121, Malásia 778, Indonésia 580, **Brasil 557**, Índia 394, EUA 300, Tailândia 299, Colômbia 251, Emirados 239, China 233, Filipinas 182, Hong Kong 180, Argentina 172, Equador 150, Singapura 129…
- IPs no Matomo são **anonimizados** (2 últimos octetos zerados) → IP completo só nos logs do Nginx do vito. A dispersão por dezenas de países é típica de **proxy residencial**, não de datacenter único.

> ⚠️ **Ponto crítico para mitigação:** a rede de proxy **já inclui o Brasil** (557 visitas). Qualquer geo-bloqueio do exterior é, por isso, **parcial** — o scraper pode migrar para nós de saída brasileiros, indistinguíveis de usuários reais.

### Canais de aquisição (24/06)
Entrada direta 6.858 (96%, 22s, 97% rejeição); busca orgânica 293 (normal); campanhas 12 (chatgpt 8, copilot 2, extension 2); social 4; sites 2.

---

## 6. Achados no código (investigação local, 2026-06-28)

A rota tem uma fragilidade que o relatório original (feito só pelo Matomo) não enxergava:

- [`routes/web.php:84`](../routes/web.php#L84) → [`AllStjTesesPageController.php`](../app/Http/Controllers/AllStjTesesPageController.php): **sem cache** e **sem throttle**.
- Cada hit faz `SELECT *` na tabela **`stj_teses` inteira (~1.437 registros)** + renderiza uma view de 262 linhas iterando todas as linhas → **página "cara" por requisição**.
- Somado ao histórico de **504 sob flood** e à ausência de **Redis** (máquina RAM-bound), um scrape mais agressivo nessa rota tem potencial concreto de degradar a aplicação. O conteúdo é **público e muda pouco** → candidato ideal a cache.

---

## 7. Mitigações analisadas (com efeitos colaterais) — **NENHUMA implementada ainda**

### Opção 1 — Cache da `/teses/stj`
A página **não é igual para todos** (variações por usuário):
- `@if($admin)` em [`teses.blade.php:17,82,94-97`](../resources/views/front/teses.blade.php) (link Admin, botões IA, jobs, acórdãos).
- `data-csrf="{{ csrf_token() }}"` em [`teses.blade.php:108`](../resources/views/front/teses.blade.php#L108) — token **por sessão**.
- `shouldSeeAds()` em [`base.blade.php:70`](../resources/views/front/base.blade.php#L70) e `@auth` em [`base.blade.php:141`](../resources/views/front/base.blade.php#L141).

**→ Cache de resposta HTTP inteira é PERIGOSO:** congela CSRF (419), pode vazar controles de admin para anônimos, congela estado de anúncios/menu do logado.
**→ Cache de CONSULTA (`Cache::remember` no resultado de `stj_teses`) é seguro:** a view continua renderizando por request (CSRF/sessão/admin/ads corretos). Único efeito: **defasagem** até o TTL — mitigar limpando a chave no mesmo gancho do `cache:clear-searches` pós-update da base.
**Veredito:** se for mitigar, usar **cache de consulta**, nunca de resposta.

### Opção 4 — Geo-bloqueio
- **Usuários BR prejudicados (minoria):** brasileiros no exterior/viajando, em VPN com saída estrangeira, ou com GeoIP errado. **Mitigável usando "challenge" (CAPTCHA/JS) em vez de bloqueio duro.**
- **Risco maior NÃO é o usuário BR e sim o SEO:** Googlebot/Bingbot rastreiam dos EUA/Europa. Bloquear esses países **derruba a indexação**. → manter allowlist de **Verified Bots**.
- **Eficácia parcial:** proxy já inclui o Brasil (ver §5).
- **Sweet spot:** challenge/bloqueio focado na **Ásia** (66% do pico, ~zero público jurídico BR e ~zero crawler de SEO relevante), Brasil sempre liberado, EUA/Europa no máximo challenge brando.
- **Camada certa:** Cloudflare (edge, não toca a app, tem Verified Bots + challenge prontos), **não** Nginx/middleware PHP.

> As Opções 1 e 4 **se complementam**: como o geo-block nunca é 100% (nós BR), o cache garante que o tráfego que furar bata numa página barata.

---

## 8. Decisão atual (2026-06-28)

**Não mexer em código nem infra por ora.** O evento foi absorvido, não houve dano persistente, e a aplicação seguiu de pé. Em vez de mitigar preventivamente, optou-se por **monitorar** e só agir se houver reincidência com impacto.

---

## 9. Monitoramento ativo — Alertas Matomo Custom Alerts (idSite=2)

Plugin **Custom Alerts** ativado. **Dois alertas configurados (A + B):**

**Alerta A — Volume geral (rede de segurança)**
`Visits Summary` · métrica **Visits** · período **Day** · condição **is greater than 1500** · email para o titular.
(Base = centenas/dia; pico foi 7.169. 1.500 ≈ 2–3× a base.)

**Alerta B — Página alvo (mais preciso)**
Report `Behaviour → Pages`, linha **`/teses/stj`** · métrica **Pageviews** · período **Day** · condição **is greater than 1000** · email para o titular.
(97,8% do pico caiu nessa URL; crescimento orgânico não concentra 1.000+ num único listão.)

**Notas:** período = Day → aviso quando o archiving do dia fecha (no dia seguinte), não em tempo real. Ajustar limiares se houver falso positivo. Para tempo real + IP/ASN completos, a fase 2 seria um watcher do `access.log` no vito (não implementado).

---

## 10. Se um novo incidente com bots ocorrer — roteiro rápido

1. **Comparar com a assinatura (§5)**: mesma cara (Windows 11 / Chrome 1920×1080, entrada direta, 1 ação, rejeição ~97%, Ásia dominante)? Se sim, é o mesmo padrão de scraper.
2. **Confirmar ausência de conversão** no painel T&S (registros/newsletter planos) — é o que distingue scraper de crescimento real.
3. **Identificar a página alvo** no Matomo (`Behaviour → Pages`). Antes foi `/teses/stj`; pode ser outra listagem pesada (`/tese/...`, outro tribunal).
4. **Descartar/confirmar a extensão** olhando a campanha "extension" e as buscas da extensão no painel T&S (§3).
5. **Forense de IP/ASN (se necessário):** logs do Nginx no vito — `ssh -o RemoteCommand=none -T vito 'grep "/teses/stj" /var/log/nginx/access.log | ...'` (IP completo só está aqui; Matomo anonimiza).
6. **Se decidir mitigar:** seguir §7 — **cache de consulta** (Opção 1) como base, **Cloudflare challenge na Ásia** (Opção 4) como camada; lembrar que geo-block é parcial (proxy tem nós BR).

---

## Referências cruzadas
- [`2026-06-23_INCIDENTE_504_tesesesumulas.md`](2026-06-23_INCIDENTE_504_tesesesumulas.md) — incidente vizinho, causa diferente (extensão **antiga**).
- `EXTENSAO_CHROME_ESTRATEGIA.md` / `EXTENSAO_SITE_PLANO_IMPLEMENTACAO.md` — contexto da extensão v2.0.0.
