# Incidente 504 — tesesesumulas / mestrelivre (servidor Vito)

**Data:** 2026-06-23
**Sintoma:** `tesesesumulas.com.br` retornando 504 Gateway Timeout; ping/health-check do `mestrelivre.com.br` falhando. Vários sites do servidor Vito lentos ou fora.
**Servidor:** Vito — `ssh vito` (15.229.244.115). ⚠️ o host tem `RemoteCommand cd /home/vito` fixo no `~/.ssh/config`, então para rodar comandos use:
`ssh -o RemoteCommand=none -T vito '<cmd>'`

---

## ✅ DESFECHO (2026-06-24) — RESOLVIDO POR ORA

Avaliação completa feita em 2026-06-24 (site + codebase da extensão). **Conclusão: estamos seguros; a causa raiz já foi corrigida na extensão e o servidor está saudável. Não foi preciso exigir login.**

### Diagnóstico que mudou a decisão
- A extensão foi **refatorada para a v2.0.0** e **já não bate mais nas páginas HTML pesadas** (`/tese/stj/<id>`, `/teses/stj`). Agora ela usa endpoints **JSON enxutos**: `POST /api/unified-search` (busca) e `GET /api/public/{tipo}/{orgao}/{numero}` (teor). O flood de 160k veio da **versão antiga ainda instalada na base**, que decai sozinha conforme a Web Store atualiza os usuários.
- A v2.0.0 só chama a API **em gesto do usuário** (submit/clique); o service worker **não faz fetch**; sob erro 429 ela **para** (não remartela). `apiFetch` não envia `credentials` → o cookie `ts_session` **nunca** é carregado → o bypass do cache não é acionado pela extensão.
- **Servidor saudável.** Mix de status em `/tese/stj/` (40k linhas, 2026-06-24): `4587×200`, `10613×302`, `122×419`, **`0×504`, `0×499`**. O cache nginx está absorvendo a base antiga sem tocar no PHP. (No incidente eram 18.954×499.)

### Decisão de produto (revista)
**NÃO exigir login para o uso básico da extensão.** Motivos: (1) era a opção de maior esforço e quebraria os usuários atuais; (2) era justamente o que **recriaria os 504** (sessão → bypass total do cache); (3) a causa raiz já está corrigida sem isso. O login fica **reservado para features extras / aumento de cota / assinatura** (modelo A — Cache-first), em endpoints autenticados separados, por **token próprio (Sanctum), nunca o cookie `ts_session`** — o que não conflita com o cache.

### O que FOI feito
- **(Código do site, commit `e766a0f`)** TTL do cache de busca do Laravel ampliado de **1h → 1 dia** em `app/Services/SearchCacheManager.php` (base muda só 1–2×/semana; poupa CPU, o recurso escasso). Teste adicionado travando o valor.
- **(Acoplamento operacional)** Como o cache agora dura 1 dia, **após cada atualização da base** deve-se rodar `php artisan cache:clear-searches`. Isso será integrado à rotina do app **`sendy.maurolopes.com.br`** (mesmo servidor), que é onde a base é atualizada. Comando: `sudo -u vito php8.3 /home/vito/tesesesumulas.com.br/artisan cache:clear-searches`.

### O que NÃO foi feito (e por quê)
- **Login obrigatório na extensão** — descartado (ver acima).
- **Redis / outro cache** — descartado. A máquina é RAM-bound (139 MB livres, swap no incidente); Redis *consome* RAM e *fica atrás do PHP* (o cache nginx que nos salvou fica **antes**). Cache em `file` é adequado para servidor único. Reavaliar só se escalar a máquina ou for para múltiplos servidores.
- **Mexer no `fastcgi_cache_bypass $cookie_ts_session`** — mantido como está. A extensão não envia `ts_session`, então o bypass continua correto (humanos logados recebem página viva; extensão é servida do cache).
- **Desligar o micro-cache nginx** — mantido LIGADO. É o que segura a **base antiga ainda instalada**. Só aposentar quando o volume cair (ver monitoramento).

### Monitoramento até encerrar de vez
O volume ainda é alto (`grep -c "/tese/stj/"` ≈ **99k** em 2026-06-24) → base antiga ainda ativa. **Manter o cache nginx.** Reavaliar periodicamente:
```bash
ssh -o RemoteCommand=none -T vito 'grep -c "/tese/stj/" /var/log/nginx/access.log'   # deve cair com a migração p/ v2.0.0
```
Quando esse número estiver baixo, aí sim dá para considerar aposentar o cache nginx do HTML (seção "Como reverter o cache" abaixo).

---

## Causa raiz

A extensão **Tese & Súmulas (T&S)** consulta a API do site em `/tese/stj/<id>` e `/teses/stj`. Uma atualização recente da extensão (mexeu no throttle) aumentou o volume de chamadas. Multiplicado pela base de usuários instalada, virou um **DDoS acidental** contra um pool de PHP minúsculo.

Evidências coletadas:

| Métrica | Valor no incidente |
|---|---|
| Requisições a `/tese/stj/` no log do dia | **160.741** (~13 mil/h, pico 18.613 às 06:00) |
| Tamanho do `access.log` do dia | 52 MB (dias normais ~1,5 MB comprimido) |
| IPs distintos em 40k linhas | **6.554** (cada usuário da extensão = 1 IP residencial) |
| User-Agents | dezenas de versões de Chrome desktop (110,112,116,…,133) = base instalada |
| Status dominante | **499** (cliente desiste): 18.954 × 499 vs só 2.362 × 200 |
| `load average` | **10.6** numa máquina de **2 vCPUs** |
| RAM livre | 139 MB (swap em uso) |
| MySQL | **ocioso** — o gargalo é CPU/processos PHP, não o banco |

**Por que derrubou tudo:** `tesesesumulas` roda em `php8.3-fpm`, pool `www`, com `pm.max_children = 5`. Dos 5 slots, 2 estavam **presos há 2 dias** (workers travados do pool `mlplayeruser`, que compartilha o master php8.3), sobrando ~3 workers. Havia ainda workers php7.4 presos **há 4 dias**. Com 3 workers contra 13k req/h de requisições CPU-bound, o pool saturou → nginx sem upstream → **504**; e como `calculageral.com.br`, `epminutas.com.br` e `setlister.com.br` usam o **mesmo socket php8.3**, caíram junto. O `mestrelivre` (php8.1) sofreu por contenção geral de CPU/IO da máquina a load 10.

Detalhe do app: `/tese/stj/<id>` retorna **302 → /teses/stj** quando o ID não existe/não é acessível. Logo, o flood gera 302 (redirect) + um 200 pesado na listagem `/teses/stj`.

---

## O que foi feito no servidor (2026-06-23)

### 1. Guards nos pools PHP-FPM (anti workers-presos) — `;`-comentado (INI usa `;`, não `#`!)
Adicionado ao fim de cada pool e recarregado (`systemctl reload phpX-fpm`):
```ini
; --- vito-hardening (504 fix) ---
pm.max_requests = 500
request_terminate_timeout = 120s
```
Arquivos: `/etc/php/{7.4,8.1,8.2,8.3}/fpm/pool.d/*.conf` (backups `*.bak-<timestamp>` ao lado).
Efeito: qualquer request que passe de 120s é morto (mata vazamentos), e cada worker recicla a cada 500 requests. Liberou os slots presos.

### 2. Micro-cache nginx (`fastcgi_cache`) para os endpoints da extensão
- Zona: `/etc/nginx/conf.d/tese_cache.conf`
  ```nginx
  fastcgi_cache_path /var/cache/nginx/tese levels=1:2 keys_zone=TESE:10m max_size=500m inactive=10m use_temp_path=off;
  ```
- Location dedicada em `/etc/nginx/sites-enabled/tesesesumulas.com.br` (antes de `location ~ \.php$`):
  ```nginx
  location ~ ^/(tese/stj/[0-9]+|teses/stj)/?$ {
      fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
      fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
      fastcgi_param SCRIPT_NAME /index.php;
      include fastcgi_params;
      fastcgi_hide_header X-Powered-By;
      fastcgi_hide_header Set-Cookie;          # NUNCA cacheia/vaza sessao
      fastcgi_cache TESE;
      fastcgi_cache_key "$scheme$request_method$host$request_uri";
      fastcgi_cache_valid 200 302 30s;         # TTL curto
      fastcgi_ignore_headers Cache-Control Expires Set-Cookie;
      fastcgi_cache_bypass $cookie_ts_session; # usuario com sessao = pagina viva
      fastcgi_no_cache $cookie_ts_session;
      fastcgi_cache_lock on;                    # colapsa thundering-herd
      fastcgi_cache_lock_timeout 5s;
      fastcgi_cache_use_stale error timeout updating http_500 http_503 http_429;
      fastcgi_read_timeout 60s;
      add_header X-Cache $upstream_cache_status always;
  }
  ```

**Design / segurança do cache:**
- TTL curto (30s) → no máximo 30s de staleness.
- `Set-Cookie` é escondido e ignorado → **sessão/CSRF de um usuário nunca é servida a outro**.
- `bypass`/`no_cache` por `$cookie_ts_session` → quem já tem sessão ativa recebe página viva com CSRF correto. O flood da extensão (sem sessão) é servido do cache.
- ⚠️ **Risco residual conhecido:** o HTML carrega `XSRF-TOKEN`. Um visitante **novo** cujo 1º acesso seja uma página `/tese/stj/<id>` cacheada recebe HTML sem cookie de sessão; se submeter um form na sequência pode tomar **419 (CSRF)** uma vez (retry resolve). Foi visto 419 nos logs. Trade-off aceito para restaurar o serviço; reversível.

**Resultado imediato:** HIT serve em **~5ms** sem tocar no PHP. `load average 10.6 → 0.58`. 0 timeouts. Sites em HTTP 200.

> **IMPORTANTE — persistência:** o vhost do tesesesumulas é gerado pelo painel **Vito Deploy** (template com marcadores `#[...]`). A location de cache foi colada no template do painel, então **sobrevive a deploys/regenerações**. A zona `TESE` (`/etc/nginx/conf.d/tese_cache.conf`) NÃO é gerenciada pelo painel, mas persiste via `include conf.d`. Os dois precisam coexistir — se a zona sumir, `nginx -t` falha. Backups de vhost movidos para `/etc/nginx/sites-backups/` (fora de sites-enabled, que carrega `*`).

### Observabilidade
```bash
ssh -o RemoteCommand=none -T vito 'curl -s -o /dev/null -w "%header{x-cache}\n" -H "Host: tesesesumulas.com.br" -k https://127.0.0.1/tese/stj/611'
# X-Cache: HIT | MISS | EXPIRED | BYPASS
```

### Como reverter o cache (se necessário)
```bash
sudo rm /etc/nginx/conf.d/tese_cache.conf
# remover a location ~ ^/(tese/stj...)$ do vhost (ha .bak ao lado)
sudo nginx -t && sudo systemctl reload nginx
sudo rm -rf /var/cache/nginx/tese/*
```

---

## Pendências / recomendações

1. **(LADO EXTENSÃO — causa raiz)** Revisar/apertar o throttle da versão nova da extensão e adicionar **backoff em 429/5xx**. Sem isso, o servidor segue dependente do cache para aguentar o volume. Ver doc gêmeo em `chromeExtensions/tes_chrome`.
2. Remover `tesesesumulas.com.br.bak` de `/etc/nginx/sites-enabled/` (vhost duplicado, gera warning "conflicting server name").
3. Avaliar `limit_req` (rate-limit por IP) como proteção extra contra um único IP abusivo.
4. Capacidade: só 2 vCPUs e RAM no limite (139 MB livres). Aumentar `pm.max_children` ajuda pouco (CPU-bound). Se o tráfego legítimo crescer, escalar o servidor (mais vCPU/RAM).
5. Cache cobre só `tesesesumulas`. Se a extensão consultar outros domínios, replicar o padrão.

---

## ⚠️ MUDANÇA PLANEJADA: exigir login na extensão — impacto no cache

> **SUPERADO em 2026-06-24 — ver "✅ DESFECHO" no topo.** Decidiu-se **NÃO** exigir login para o uso básico (recriaria os 504 e era desnecessário, pois a v2.0.0 já corrigiu a causa raiz). Login fica para features extras/assinatura. A análise abaixo fica registrada como histórico do raciocínio.

Decisão de produto (2026-06-23): a extensão passará a **exigir usuário logado**. Isso colide com o design atual do cache:

- O cache faz `fastcgi_cache_bypass $cookie_ts_session` (request com sessão = página viva). Se a extensão chamar a API **com a sessão do usuário logado**, **todo request vira BYPASS** → o cache para de proteger e os **504 voltam**.
- Um **cache HIT não executa PHP** → não dá para enforçar auth e servir do cache no mesmo endpoint.

**Antes de a extensão exigir login, alinhar o modelo (ver doc gêmeo em `chromeExtensions/tes_chrome`):**
- **A) Cache-first:** endpoints de leitura seguem anônimos/cacheáveis; login não bloqueia a consulta básica. Cache atual permanece como está.
- **B) Auth-first:** API autenticada por **token** + **rate-limit por usuário no app**; não depender do cache. Exige capacidade/rate-limit robustos (máquina é só 2 vCPU).
- **C) Híbrido (recomendado avaliar):** endpoint `/api/tese/<id>` JSON, auth por **token da extensão (não o cookie `ts_session`)**, cacheado por id, auth leve via `auth_request`/token assinado.

**Ação concreta no servidor quando decidir:** revisar/ajustar a linha `fastcgi_cache_bypass $cookie_ts_session;` e `fastcgi_no_cache $cookie_ts_session;` na location de cache do vhost (no **template do Vito Deploy**, não só no disco). Se a extensão usar token próprio em vez do cookie de sessão, o bypass-por-sessão continua válido e o cache segue funcionando para o tráfego da extensão.

## Comandos úteis de diagnóstico (para a próxima)
```bash
S='ssh -o RemoteCommand=none -T vito'
$S 'uptime; free -h; nproc'
$S 'grep -hiE "upstream timed out|504" /var/log/nginx/error.log | tail -20'
$S 'grep -c "/tese/stj/" /var/log/nginx/access.log'                       # volume do flood
$S 'tail -40000 /var/log/nginx/access.log | grep "/tese/stj/" | awk "{print \$9}" | sort | uniq -c'  # status mix
$S 'ps -eo pid,etime,cmd | grep "[p]hp-fpm: pool"'                        # workers presos
$S 'mysqladmin processlist'                                              # (estava ocioso)
```
