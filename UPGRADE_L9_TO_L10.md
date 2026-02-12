# Upgrade Laravel 9 → 10 ✅ CONCLUÍDO

> Concluído em 2026-02-12. Laravel Framework 10.50.0 instalado com sucesso.
> Baseado na [documentação oficial](https://laravel.com/docs/10.x/upgrade#main-content)
> Tempo estimado: ~15min (impacto baixo neste projeto)

---

## Checklist

### 1. Dependências (composer.json) — Alto Impacto
- [ ] Alterar `"php"` de `"^8.0.2"` para `"^8.1"` (na prática mantemos `"^8.1"`, prod usa 8.3)
- [x] Alterar `"laravel/framework"` de `"^9.0"` para `"^10.0"`
- [x] Alterar `"spatie/laravel-ignition"` de `"^1.0"` para `"^2.0"`
- [x] Alterar `"nunomaduro/collision"` de `"^6.1"` para `"^6.4"` (v7 exige PHPUnit 10)
- [x] Verificar/atualizar versões de:
  - [x] `"laravel/cashier"` — `^14.0` (já compatível com L10 ✅)
  - [x] `"filament/filament"` — `^2.0` funciona com L10 (upgrade v3 adiado)
  - [x] `"spatie/laravel-permission"` — `^5.10` funciona com L10
  - [x] `"spatie/laravel-sitemap"` — atualizado para `^7.0` (requer PHP 8.2+ e browsershot seguro)
  - [x] `"amirami/localizator"` — atualizado para `^0.12@alpha`

---

### 2. Minimum Stability — Alto Impacto
**Arquivo:** `composer.json`

Alterar ou remover `minimum-stability`:
```json
// De:
"minimum-stability": "dev",
// Para (remover ou alterar):
"minimum-stability": "stable",
```

> **Nota:** O projeto atualmente usa `"minimum-stability": "dev"` com `"prefer-stable": true`. O L10 recomenda `stable`. Podemos remover esta linha — o default do Composer já é `stable`.

---

### 3. PHPUnit / phpunit.xml — Impacto Médio
**Opção A (recomendado):** Manter PHPUnit 9 por enquanto
- Mantém compatibilidade, menos risco

**Opção B:** Upgrade para PHPUnit 10
- Alterar `"phpunit/phpunit"` de `"^9.3"` para `"^10.0"`
- Remover atributo `processUncoveredFiles` da seção `<coverage>` no `phpunit.xml`
- Ajustar `"nunomaduro/collision"` para `"^7.0"`

> **Decisão tomada:** Manter PHPUnit 9 para reduzir risco.

---

### 4. Eloquent $dates property — Impacto Médio
**Verificado:** Nenhum model usa `protected $dates`. ✅

---

### 5. Database Expressions — Impacto Médio
`DB::raw()` agora retorna objeto em vez de string. Cast com `(string)` não funciona mais.

**Verificado:** Nenhum cast de DB::raw para string no código. ✅

---

### 6. Monolog 3 — Impacto Médio
Laravel 10 usa Monolog 3. Se houver interação direta com Monolog, ajustar.

**Verificado:** Nenhuma interação direta com Monolog no projeto. ✅

---

### 7. Bus::dispatchNow — Impacto Baixo
`Bus::dispatchNow` e `dispatch_now` foram removidos. Usar `Bus::dispatchSync` / `dispatch_sync`.

**Verificado:** Nenhum uso no projeto. ✅

---

### 8. Redirect::home — Impacto Baixo
`Redirect::home()` foi removido.

**Verificado:** Nenhum uso no projeto. ✅

---

### 9. $routeMiddleware → $middlewareAliases — Opcional
No L10, `$routeMiddleware` pode ser renomeado para `$middlewareAliases` no `Kernel.php`.

- [ ] Renomear `$routeMiddleware` para `$middlewareAliases` (opcional mas recomendado)

---

### 10. registerPolicies — Impacto Baixo
`registerPolicies()` agora é chamado automaticamente. Remover do `boot()` do `AuthServiceProvider`.

- [ ] Verificar e remover se aplicável

---

### 11. Testing — MocksApplicationServices
`MocksApplicationServices` trait foi removido. Usar `Event::fake`, `Bus::fake`, `Notification::fake`.

**Verificado:** Nenhum uso no projeto. ✅

---

## Ordem de Execução Sugerida

```
1. Editar composer.json (dependências + minimum-stability)
2. Editar Kernel.php ($routeMiddleware → $middlewareAliases, opcional)
3. Verificar AuthServiceProvider (registerPolicies)
4. composer update -W
5. php artisan config:clear && php artisan cache:clear
6. php artisan test
7. Testar no Valet (teses.test)
```

---

## Verificação

### Testes Automatizados
```bash
php artisan test
# Idealmente com PHP 8.3: /opt/homebrew/opt/php@8.3/bin/php artisan test
```

### Verificação Manual (Dev - teses.test)
1. Acessar home page
2. Fazer busca
3. Login/logout
4. Acessar painel admin `/painel`
