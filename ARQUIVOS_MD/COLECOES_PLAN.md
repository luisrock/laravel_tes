# Plano: Feature Coleções

Permitir que usuários registrados e assinantes organizem teses e súmulas em coleções nomeadas. A feature é um incentivo de conversão: registrados têm coleções sempre públicas e com limites baixos; assinantes ganham privacidade e limites maiores; premium tem acesso ilimitado.

---

## Status Geral

**Etapa atual:** Etapa 8 — Revisão Final e Deploy. ✅ **Concluída.**
**Próximo passo:** Deploy via Vito Deploy + teste manual em produção.

---

## Regras de Negócio

| Tier | Coleções | Itens/coleção | Privacidade |
|---|---|---|---|
| Registrado (sem plano) | 3 | 15 | Sempre pública |
| PRO | 10 | 50 | Pública ou privada |
| PREMIUM | -1 (ilimitado) | -1 (ilimitado) | Pública ou privada |

- Limites configuráveis pelo admin no Filament (análogo ao MeteredWallSettings).
- Cada item pode estar em múltiplas coleções (many-to-many via `collection_items`).
- Coleções suportam teses e súmulas. Estrutura do DB já prevê `quiz` para o futuro.
- Coluna `notes` em `collection_items` criada mas não exposta na UI (reservada para feature futura de anotações por item).
- Ordenação dos itens por data de inclusão, com reordenação via drag-and-drop.

---

## Checklist de Implementação

### Etapa 1 — Migrations e Models ✅
- [x] Migration `create_collections_table`
- [x] Migration `create_collection_items_table`
- [x] Model `Collection` com relacionamentos e slug gerado via `boot()` (sem pacote externo)
- [x] Model `CollectionItem` com método `getContent()`
- [x] Relacionamento `hasMany(Collection)` no model `User`
- [x] Factories para `Collection` e `CollectionItem` (com states `private()`, `public()`, `tese()`, `sumula()`)
- [x] **Testes Pest:** 18 testes, 26 assertions — todos passando (`CollectionModelTest.php`)
- [x] **Pint:** sem issues

### Etapa 2 — CollectionService e Policy ✅
- [x] `CollectionService` com métodos: `getLimitsForUser`, `canCreateCollection`, `canAddItem`, `getUserCollectionsWithItemStatus`
- [x] `CollectionPolicy` (view, create, update, delete, addItem, removeItem)
- [x] Registro da policy em `AuthServiceProvider`
- [x] **Testes Pest:** 32 testes, 40 assertions — todos passando (`CollectionServicePolicyTest.php`)
- [x] **Pint:** sem issues

### Etapa 3 — Configuração Admin no Filament ✅
- [x] Nova página Filament `CollectionSettings` (análoga a `MeteredWallSettings`)
- [x] Settings no `site_settings`: `collections_registered_max`, `collections_registered_items_max`, `collections_pro_max`, `collections_pro_items_max`, `collections_premium_max` (-1 = ilimitado), `collections_premium_items_max`
- [x] `CollectionService` já lê os valores do `SiteSetting` desde a Etapa 2 (com fallback para defaults)
- [x] **Seed:** `CollectionSettingsSeeder` criado e **já adicionado ao script Vito Deploy** ✅
- [x] **Testes Pest:** 6 testes, 15 assertions — todos passando (`CollectionSettingsFilamentTest.php`)
- [x] **Pint:** sem issues

### Etapa 4 — Rotas e Controllers ✅
- [x] Rotas públicas: `GET /colecoes/{username}/{slug}` e `GET /colecoes` (diretório)
- [x] Rotas autenticadas em `/minha-conta/colecoes` (listar, criar, editar, excluir)
- [x] Rotas de itens: adicionar, remover, reordenar (`PATCH reorder`)
- [x] Rota API interna: `GET /api/colecoes/modal/{type}/{tribunal}/{contentId}`
- [x] `CollectionPublicController@show` (retorna 403 para coleções privadas se não for o dono)
- [x] `CollectionDirectoryController@index` (diretório público paginado) — adicionado na sessão 3
- [x] `CollectionController` (CRUD + itens + reorder)
- [x] `CollectionModalController@show` (retorna JSON com coleções + `has_item` + `can_create`)
- [x] Form Requests: `StoreCollectionRequest`, `UpdateCollectionRequest`, `StoreCollectionItemRequest`
- [x] **Testes Pest:** 33 testes, 57 assertions — todos passando (`CollectionRoutesTest.php`)
- [x] **Pint:** sem issues

### Etapa 5 — Componentes Livewire (área /minha-conta) ✅
- [x] `CollectionList` — listagem em grid 2 col, formulário inline de criação, delete com `wire:confirm`, CTA ao atingir limite
- [x] `CollectionEdit` — formulário wire:model, toggle de privacidade, drag-and-drop (SortableJS CDN via `@assets`), remoção de item, delete da coleção
- [x] `resources/views/components/collection-upgrade-cta.blade.php`: Blade component reutilizável (prop `compact` para versão inline)
- [x] Sidebar `user-panel.blade.php`: link Coleções ativado, destaque `colecoes.*`
- [x] **Refinamentos (sessão 3):** toast "Nova ordem salva" após drag-and-drop; span redundante "Tese"/"Súmula" removido dos cards de item
- [x] **Testes Pest:** 40 testes passando (`CollectionLivewireTest.php`)
- [x] **Pint:** sem issues

### Etapa 6 — Modal "Salvar em Coleção" ✅
- [x] Componente Livewire `SaveToCollectionModal`: `$isOpen`, `$contentType`, `$tribunal`, `$contentId`, `$collections`, `$newTitle`, `$showCreate`, `$justToggledId`
- [x] Método `open()` — carrega coleções, reseta estado
- [x] Método `toggle()` — add/remove; define `$justToggledId`; despacha `item-toggled`
- [x] Método `createAndAdd()` — cria coleção inline e adiciona o item
- [x] Blade component `x-save-to-collection-btn` — botão bookmark
- [x] `<livewire:save-to-collection-modal />` em `app.blade.php` e `front/base.blade.php`
- [x] Botão inserido em 13 cards de resultado de busca (inners)
- [x] Botão inserido nas 4 páginas de detalhe e 4 páginas de listagem
- [x] **Refinamentos (sessão 3):** CTA só aparece ao clicar "Nova coleção" (não por padrão); checkmark ✓ verde ao lado da coleção após toggle; modal fecha automaticamente após 2 segundos
- [x] **Testes Pest:** 19 testes passando (`SaveToCollectionModalTest.php`)
- [x] **Pint:** sem issues

### Etapa 7 — Página Pública da Coleção ✅
- [x] View `colecoes/show.blade.php` estende `front.base`
- [x] Hero: nome do dono, título, descrição, badge Privada (quando aplicável), contador de itens
- [x] Botão "WhatsApp" e "Copiar link" (clipboard API via Alpine)
- [x] Botão "Editar coleção" visível apenas para o dono logado
- [x] **Cards completos** (`x-collection-item-card`) — mesmo estilo dos cards de busca, com: badge tribunal+tipo, título linkado, seção tema/questão, texto da tese/enunciado, footer com metadados + copiar + salvar. Normaliza campos para todos os tribunais/tipos (STF/STJ/TST/TNU × tese/súmula)
- [x] Estado vazio quando sem itens
- [x] CTA "Organize suas pesquisas — Crie sua conta grátis" para guests
- [x] Meta tags OG no `@section('styles')`
- [x] `CollectionItem`: métodos estáticos `resolveLabel()` e `resolveDetailUrl()` extraídos do `CollectionEdit` (DRY); `CollectionEdit` atualizado para usar os métodos do model
- [x] `CollectionPublicController` pré-computa `label`, `url` e `content` para cada item
- [x] **Testes Pest:** 38 testes passando (`CollectionPublicTest.php`)
- [x] **Pint:** sem issues

### Etapa 7b — Diretório Público `/colecoes` ✅ *(adicionada na sessão 3)*
- [x] Rota `GET /colecoes` → `CollectionDirectoryController@index` (registrada antes da rota dinâmica)
- [x] View `colecoes/directory.blade.php`: grid 3 colunas de cards (título, descrição truncada, contador de itens, nome do dono)
- [x] Paginação de 24 por página
- [x] CTA de registro para guests
- [x] Testes incluídos em `CollectionPublicTest.php`

### Etapa 8 — Revisão Final e Deploy ✅

**Revisão de código (code review + plano de correções aplicado por dois modelos em sessão 4):**

*Correções aplicadas pelo outro modelo:*
- [x] **users.name único:** migration `add_unique_to_users_name`, validação `Rule::unique` em `CreateNewUser`, `generateUniqueName()` em `GoogleAuthController` (sufixo numérico para evitar colisão em OAuth)
- [x] **Whitelist de tribunal:** `Rule::in(SearchTribunalRegistry::keys())` em `StoreCollectionItemRequest`; `abort_unless` via `validateTribunal()` em `SaveToCollectionModal` (chamado em `toggle()` e `createAndAdd()`)

*Correções aplicadas por Claude (sessão 4):*
- [x] **Docblock `CollectionSettingsSeeder`:** "0 = ilimitado" → "-1 = ilimitado" (sentinela estava errado)
- [x] **`CollectionDirectoryController`:** `Collection::query()->where('is_private', false)` → `Collection::public()` (escopo já existia no model, DRY)
- [x] **`catch` muito amplo:** `catch (\Exception)` → `catch (QueryException)` em `CollectionPublicController::resolveItems()` e `CollectionEdit::render()` (só `getContent()` lança `QueryException`; outras exceções devem propagar)
- [x] **`StoreCollectionRequest`:** `title` sem `min:2` (inconsistente com Livewire components que já tinham) → regra adicionada com mensagem; teste correspondente adicionado em `CollectionRoutesTest`
- [x] **`SaveToCollectionModal::loadCollections()`:** refatorado de 3 queries para 2 — `withCount('items')` inline, `canCreate` derivado dos dados já em memória, sem chamar `CollectionService` para o count
- [x] **UX "cheio" → CTA:** coleção cheia no modal não mostra mais badge "cheio" + botão disabled; ao clicar na linha, Alpine define `showItemLimitCta = true` revelando `x-collection-upgrade-cta compact` no footer (mesmo padrão do CTA de coleções). Reset ao reabrir o modal.
- [x] **`CollectionEdit::render()`:** `->with('items')` → `->with(['items', 'user'])` — elimina lazy load de `$collection->user->name` no link "Ver página pública"
- [x] **`CollectionController::index()`:** `canCreateCollection()` substituído por derivação inline — `$limits` e `$collections->count()` já em memória, elimina segunda chamada a `getLimitsForUser()` + query extra `->count()`
- [x] **`x-collection-upgrade-cta`:** quando `ENABLE_SUBSCRIPTIONS=false`, suprime descrição "Faça upgrade para..." e link "Ver planos →" em todos os locais onde o componente é usado (uma mudança cobre tudo)
- [x] **Testes:** 193 testes, 285 assertions — todos passando
- [x] **Pint:** sem issues

---

## Estrutura do Banco de Dados

### Tabela `collections`
| Coluna | Tipo | Observação |
|---|---|---|
| id | bigIncrements | |
| user_id | FK users, cascade delete | |
| title | string(100) | |
| description | text, nullable | validação max 500 na app |
| slug | string | único por usuário |
| is_private | boolean, default false | registered sempre false (forçado na app) |
| created_at / updated_at | timestamps | |

Índice único: `(user_id, slug)`

### Tabela `collection_items`
| Coluna | Tipo | Observação |
|---|---|---|
| id | bigIncrements | |
| collection_id | FK collections, cascade delete | |
| content_type | string — `tese`, `sumula` | futuro: `quiz` |
| content_id | unsignedBigInteger | ID na tabela do tribunal |
| tribunal | string(10) | stf, stj, tst, tnu… |
| order | unsignedInteger, default 0 | drag-and-drop |
| notes | text, nullable | **não exposto na UI** — reservado para feature futura |
| created_at | timestamp | |

Índice único: `(collection_id, content_type, content_id, tribunal)`

---

## Rotas

```
// Pública
GET  /colecoes                        → diretório público paginado
GET  /colecoes/{username}/{slug}      → página da coleção

// Autenticada (/minha-conta)
GET    /minha-conta/colecoes
POST   /minha-conta/colecoes
GET    /minha-conta/colecoes/{id}/editar
PUT    /minha-conta/colecoes/{id}
DELETE /minha-conta/colecoes/{id}
POST   /minha-conta/colecoes/{id}/itens
DELETE /minha-conta/colecoes/{id}/itens/{itemId}
PATCH  /minha-conta/colecoes/{id}/itens/reorder

// API interna (modal)
GET  /api/colecoes/modal/{type}/{tribunal}/{contentId}
```

---

## Settings no Filament (site_settings)

| Chave | Default | Descrição |
|---|---|---|
| `collections_registered_max` | 3 | Máx. coleções para registrado |
| `collections_registered_items_max` | 15 | Máx. itens por coleção para registrado |
| `collections_pro_max` | 10 | Máx. coleções para PRO |
| `collections_pro_items_max` | 50 | Máx. itens por coleção para PRO |
| `collections_premium_max` | -1 | -1 = ilimitado |
| `collections_premium_items_max` | -1 | -1 = ilimitado |

---

## Observação sobre Seeds e Vito Deploy

Sempre que uma etapa exigir seed, o seeder deve ser mencionado explicitamente aqui e adicionado manualmente ao script de deploy no painel do **Vito Deploy** (a interface web não é acessível pelo Claude). O script atual já executa `db:seed --class=RolesAndPermissionsSeeder`. Novos seeders devem ser adicionados na mesma seção do script.

**Seeders no script:**
- `RolesAndPermissionsSeeder` — papéis e permissões
- `CollectionSettingsSeeder` (Etapa 3) ✅ — já adicionado ao Vito Deploy

---

## Update de Desenvolvimento

*(Preenchido ao final de cada sessão de trabalho. Objetivo: permitir que um novo modelo de IA retome o trabalho com contexto completo, sem precisar reler todo o histórico do chat.)*

**Última sessão:** 2026-03-29
**Etapas concluídas:** 1, 2, 3, 4, 5, 6, 7, 7b, 8. Feature completa, pronta para deploy.

**O que foi feito (resumo por sessão):**

*Sessão 1 (2026-03-27) — Etapas 1 a 4:*
- Migrations, models, factories (Collection + CollectionItem)
- CollectionService, CollectionPolicy, AuthServiceProvider
- CollectionSettings (Filament), CollectionSettingsSeeder
- CollectionController, CollectionPublicController, CollectionModalController, 3 Form Requests, 10 rotas

*Sessão 2 (2026-03-28) — Etapas 5 e 6:*
- CollectionList e CollectionEdit (Livewire) com drag-and-drop SortableJS
- Blade component `x-collection-upgrade-cta`
- SaveToCollectionModal (Livewire) + `x-save-to-collection-btn` (Blade)
- Botão inserido em 13 cards de busca, 4 páginas de detalhe, 4 páginas de listagem
- Livewire integrado em `app.blade.php` e `front/base.blade.php`
- Bug fix: `CollectionItem::getContent()` retorno `?object` (DB::table retorna stdClass)

*Sessão 3 (2026-03-28) — Etapa 7 + refinamentos:*
- **Etapa 7:** `colecoes/show.blade.php` com hero, botões de compartilhamento, cards completos, CTA guest, meta OG
- **DRY:** `CollectionItem::resolveLabel()` e `CollectionItem::resolveDetailUrl()` extraídos para o model (antes eram privados no CollectionEdit)
- **x-collection-item-card:** Blade component que renderiza cards idênticos aos da busca, normalizando campos de todos os tribunais/tipos (STF/STJ/TST/TNU × tese/súmula)
- **Etapa 7b:** rota `GET /colecoes` + `CollectionDirectoryController` + `colecoes/directory.blade.php` (grid paginado de coleções públicas)
- **Modal refinamentos:** CTA só aparece ao clicar "Nova coleção"; checkmark ✓ verde após toggle; modal fecha após 2s (Alpine + `item-toggled` event)
- **CollectionEdit refinamentos:** toast "Nova ordem salva" após drag-and-drop (`reorder-saved` event); span "Tese"/"Súmula" removido dos cards de item (redundante)
- **Testes:** `CollectionPublicTest.php` (38 testes), atualização de `SaveToCollectionModalTest.php` (+3) e `CollectionLivewireTest.php` (+1)

*Sessão 4 (2026-03-29) — Etapa 8 (revisão + correções):*
- Ver checklist completo da Etapa 8 acima.

**Próximo passo concreto:** Deploy via Vito Deploy (executar `CollectionSettingsSeeder`) + teste manual em produção.

**Decisões arquiteturais importantes:**
- **Sentinela de ilimitado = -1** (não 0). 0 = "nenhuma coleção permitida".
- Registrados: `can_be_private = false`; policy `view` aceita `?User` (guests veem coleções públicas).
- `CollectionItem::resolveLabel()` e `resolveDetailUrl()` são métodos **estáticos** no model — DRY entre CollectionEdit (Livewire) e CollectionPublicController.
- `resolveDetailUrl()` retorna `null` para tribunais sem rota individual (CARF, CEJ, FONAJE, TCU) — card ainda renderiza mas sem link.
- Modal auto-close: 2s após toggle, Alpine escuta `item-toggled.window` e chama `$wire.close()` com `clearTimeout` para evitar duplo disparo.
- `reorder-saved` event: dispatchado pelo Livewire após `reorderItems()`, escutado pelo Alpine na view para toast de 2s.
- Diretório `/colecoes` deve ser declarado na rota **antes** de `/colecoes/{username}/{slug}` para não ser capturado como username.
- Coluna `notes` em `collection_items` existe na migration mas não é exposta na UI.

---

## Histórico de Etapas

*(Atualizado a cada etapa concluída)*

| Etapa | Status | Data | Resumo |
|---|---|---|---|
| 1 — Migrations e Models | ✅ Concluída | 2026-03-27 | Migrations, models, factories e 18 testes Pest passando |
| 2 — Service e Policy | ✅ Concluída | 2026-03-27 | CollectionService, CollectionPolicy, AuthServiceProvider e 32 testes Pest passando |
| 3 — Admin Filament | ✅ Concluída | 2026-03-27 | CollectionSettings (Filament), CollectionSettingsSeeder e 6 testes Pest passando |
| 4 — Rotas e Controllers | ✅ Concluída | 2026-03-27 | CollectionController, CollectionPublicController, CollectionModalController, 3 Form Requests, 11 rotas, 33 testes Pest passando |
| 5 — Livewire /minha-conta | ✅ Concluída | 2026-03-28 | CollectionList, CollectionEdit, CTA upgrade, drag-and-drop SortableJS, 40 testes Pest passando. Refinamentos (sessão 3): toast reordenação, remoção spans redundantes |
| 6 — Modal Salvar | ✅ Concluída | 2026-03-28 | SaveToCollectionModal (Livewire), x-save-to-collection-btn (Blade), botão em 13 cards + 8 páginas, 19 testes Pest passando. Refinamentos (sessão 3): CTA on-demand, checkmark ✓, auto-close 2s |
| 7 — Página Pública | ✅ Concluída | 2026-03-28 | show.blade.php, x-collection-item-card (cards completos por tribunal/tipo), 38 testes Pest passando |
| 7b — Diretório /colecoes | ✅ Concluída | 2026-03-28 | CollectionDirectoryController, directory.blade.php (grid paginado) |
| 8 — Revisão e Deploy | ✅ Concluída | 2026-03-29 | Code review + 10 correções (segurança, N+1, DRY, UX, consistência). 193 testes passando. |
