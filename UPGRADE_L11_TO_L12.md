# Upgrade Laravel 11 → 12 + Filament v3 → v4 + Pest 2 → 3

> Concluído em 2026-02-12. Laravel Framework 12.51.0 instalado com sucesso.
> Baseado na [documentação oficial L12](https://laravel.com/docs/12.x/upgrade),
> [Filament v4](https://filamentphp.com/docs/4.x/upgrade-guide) e
> [Pest v3](https://pestphp.com/docs/upgrade-guide).

---

## Resumo das Mudanças

### Fase 1: Filament v3 → v4 (no L11, como pré-requisito)

Filament v3 não suporta Laravel 12. O upgrade foi feito primeiro no L11.
Utilizado o script automatizado `filament-v4` + correções manuais.

#### Dependências Atualizadas
| Pacote | De | Para |
|---|---|---|
| `filament/filament` | ^3.2 (v3.3.48) | ^4.0 (v4.7.1) |

#### Mudanças de Código — Resources (3 arquivos)

**Imports e assinaturas atualizados em todos os Resources:**
- `Filament\Forms\Form` → `Filament\Schemas\Schema`
- `form(Form $form): Form` → `form(Schema $schema): Schema`
- `->schema([])` → `->components([])`
- `->actions([])` → `->recordActions([])`
- `->bulkActions([])` → `->toolbarActions([])`
- `Tables\Actions\EditAction` → `Filament\Actions\EditAction`
- `Tables\Actions\DeleteAction` → `Filament\Actions\DeleteAction`
- `Tables\Actions\Action` → `Filament\Actions\Action`
- `navigationGroup`: `?string` → `string | \UnitEnum | null`
- `navigationIcon`: `?string` → `string | \BackedEnum | null`

#### Mudanças de Código — Widgets (4 arquivos)

Nenhuma mudança de API necessária. Já estavam compatíveis com v4.

#### Mudanças de Código — AdminPanelProvider

Imports reorganizados (cosmético):
- `Pages\Dashboard` → `Dashboard` (import direto)
- `Widgets\AccountWidget` → `AccountWidget` (import direto)

#### Mudanças de Código — Outros (cosmético)

O script `filament-v4` também reorganizou imports (inline → use statements) em:
- `app/Http/Kernel.php`
- `app/Providers/AppServiceProvider.php`
- Diversos controllers, middleware, models e commands

Estas mudanças são puramente cosméticas (código funciona igual).

---

### Fase 2: Laravel 11 → 12

#### Dependências Atualizadas (composer.json)
| Pacote | De | Para |
|---|---|---|
| `laravel/framework` | ^11.0 (v11.48.0) | ^12.0 (v12.51.0) |
| `phpunit/phpunit` | ^10.0 (v10.5.63) | ^11.0 (v11.5.50) |
| `pestphp/pest` | ^2.0 (v2.36.1) | ^3.0 (v3.8.5) |
| `pestphp/pest-plugin-laravel` | ^2.0 (v2.4.0) | ^3.0 (v3.2.0) |
| `nunomaduro/collision` | v8.5.0 | v8.8.3 |

#### Breaking Change: `@context` em Blade

Laravel 12 adicionou a diretiva Blade `@context` (parte da Context API).
Isso quebrava o JSON-LD Schema.org que usava `"@context"` literal em views Blade.

**5 views corrigidas** (escapando `@context` → `@@context`):
- `resources/views/components/breadcrumb.blade.php`
- `resources/views/front/newsletter.blade.php`
- `resources/views/front/tese.blade.php`
- `resources/views/front/quiz.blade.php`
- `resources/views/front/editable-content.blade.php`

#### Verificações Pré-Upgrade (sem ação necessária)
| Item | Status | Detalhes |
|---|---|---|
| HasUuids (UUIDv7) | ✅ N/A | Não usamos HasUuids |
| Carbon 3 | ✅ OK | Já estava desde L11 |
| Container nullable resolution | ✅ OK | Único caso (SubscriptionCanceledNotification) é instanciado via `new`, não via container |
| Image validation (SVG) | ✅ N/A | Nenhuma regra `image` no código |
| Local filesystem disk | ✅ OK | Disco `local` tem root explícito em `config/filesystems.php` |
| Nested array merging | ✅ N/A | Não usamos `mergeIfMissing` com dot notation |
| Concurrency index mapping | ✅ N/A | Não usamos `Concurrency::run` |
| Multi-schema DB inspecting | ✅ N/A | Schema único MySQL |

---

## Estrutura da Aplicação — NÃO MIGRADA

Mantidos intactos (mesma abordagem do upgrade L10→L11):
- `app/Http/Kernel.php`
- `app/Providers/*.php`
- `config/app.php` (com providers listados manualmente)

---

## Testes — Resultado Final

```
Tests: 67 passed (95 assertions)
Duration: ~4.4s
```

Todos os 67 testes do baseline passam sem nenhuma regressão.

---

## Estado Atual do Projeto

| Componente | Versão |
|---|---|
| Laravel Framework | 12.51.0 |
| PHP | 8.3 (dev via Valet + prod na AWS) |
| PHPUnit | 11.5.50 |
| Pest | 3.8.5 |
| Collision | 8.8.3 |
| Filament | 4.7.1 |
| Livewire | 3.7.10 |
| Laravel Cashier | 15.7.1 |
| Spatie Permission | 6.24.1 |
| Carbon | 3.11.1 |
| Stripe PHP | 16.6.0 |
