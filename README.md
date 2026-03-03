## Teses & Súmulas (Laravel Site)

### Dashboard Administrativo

O painel em `/admin` oferece navegação centralizada para todas as áreas administrativas:

- **Temas & Pesquisas** - Gestão de temas com estatísticas em tempo real
- **Quizzes** - Criação e gestão de quizzes jurídicos
- **Banco de Perguntas** - Perguntas reutilizáveis em múltiplos quizzes
- **Estatísticas** - Dashboard com gráficos e métricas de desempenho
- **Newsletters** - Visualização de newsletters publicadas
- **Tags** - Gestão de tags para categorização

### Emails Transacionais

**Enviados pelo site (Laravel):**
- Reset de senha
- Verificação de email (se habilitada)
- Boas-vindas à assinatura
- Cancelamento de assinatura
- Lembrete de renovação (7 dias antes)
- Confirmação de solicitação de estorno

**Enviados pelo Stripe:**
- Recibos/invoices de pagamento
- Falha de pagamento (Smart Retries)
- Atualização de método de pagamento

**Onde editar textos/layout:**
- Notificações de assinatura: `app/Notifications/*`
- Templates padrão do Laravel (reset/verification): `resources/views/vendor/notifications/*` (após publicar)

### Sistema de Quizzes Jurídicos (Novo - Dez/2024)

O site agora conta com um sistema completo de quizzes para testar conhecimentos sobre teses do STF e STJ.

**Documentação completa:** Consulte o arquivo `QUIZ_IMPLEMENTATION.md` para detalhes técnicos da implementação.

#### Principais Recursos

- **Interface Admin** (`/admin/quizzes`)
  - CRUD de quizzes e perguntas
  - Perguntas reutilizáveis (N:N com quizzes)
  - Dashboard de estatísticas com gráficos
  - Gestão de tags e categorias
  - Paleta de cores customizável por quiz
  - **Controle de visibilidade na home** (botão toggle para mostrar/ocultar seção de quizzes)

- **Interface Pública** (`/quizzes`)
  - Lista de quizzes com filtros (categoria, tribunal, dificuldade)
  - Execução do quiz com barra de progresso
  - Feedback imediato ou ao final (configurável)
  - Página de resultados com revisão de respostas
  - Espaço para anúncios entre questões
  - **Links internos** na home e páginas de teses

- **API REST** (`/api/quizzes`, `/api/questions`)
  - CRUD completo via API
  - Criação em lote de perguntas (integração com IA)
  - Mesma autenticação Bearer Token do restante da API

#### Estrutura do Banco
```
quiz_categories     - Categorias jurídicas (Direito Civil, Penal, etc.)
quizzes             - Quizzes com config (tribunal, dificuldade, cor, etc.)
questions           - Banco de perguntas reutilizáveis
question_options    - Alternativas de cada pergunta
quiz_question       - Pivot N:N entre quiz e perguntas
question_tags       - Tags para perguntas
quiz_attempts       - Tentativas registradas
quiz_answers        - Respostas individuais
```

#### URLs Principais
```
/admin                      - Dashboard administrativo central
/admin/temas                - Gestão de temas/pesquisas
/admin/quizzes              - Gerenciar quizzes (admin)
/admin/questions            - Banco de perguntas (admin)
/admin/quizzes/stats        - Estatísticas (admin)
/quizzes                    - Lista pública de quizzes
/quiz/{slug}                - Executar um quiz
```

---

### Testes

A aplicação possui uma bateria abrangente de testes usando Pest v3 + PHPUnit 11.

```bash
# Rodar todos os testes (usar PHP 8.3)
/opt/homebrew/opt/php@8.3/bin/php artisan test

# Rodar um arquivo específico
/opt/homebrew/opt/php@8.3/bin/php artisan test --filter=QuizTest

# Rodar testes de arquitetura
/opt/homebrew/opt/php@8.3/bin/php artisan test --filter=ArchTest
```

**Estrutura de testes:**

| Arquivo | Testes | O que cobre |
|---------|--------|-------------|
| ArchTest | 16 | Arquitetura: presets security, namespaces, sufixos |
| AuthTest | 17 | Login, logout, reset de senha, registro habilitado |
| SmokeTest | 27 | Todas as rotas públicas e protegidas |
| MiddlewareTest | 15 | AdminMiddleware, BearerToken, Subscribed, Feature, Config |
| QuizTest | 17 | Listagem, filtro, visualização, resposta AJAX, resultado |
| NewsletterTest | 5 | Listagem, individual, accessor web_content |
| EditableContentTest | 8 | Página pública, edição admin, validação |
| AdminCrudTest | 19 | Proteção 403 e acesso 200 para todas as rotas admin |
| FilamentPanelTest | 7 | Auth, recursos, proteção do painel /painel |
| ApiTest | 13 | Bearer token auth, endpoints protegidos, busca pública |
| WebhookTest | 6 | Validação payload, idempotência, checkout session |
| SubscriptionTest | 22 | Model helpers, estorno completo, PlanFeature |
| SearchTest | 22 | Validação, acentos, paginação, todos os 8 tribunais |
| SubscriptionNotifications | 3 | Notificações de boas-vindas, cancelamento, estorno |
| SubscriptionRenewalReminder | 3 | Job de lembrete de renovação |
| RolesAndPermissionsTest | 14 | Roles, permissions, ad_free, registerwall, paywall, canAccessPanel |

**Observações:**
- DB de teste: SQLite in-memory (configurado em `phpunit.xml`)
- Queries MySQL-específicas (FULLTEXT, etc.) podem retornar 500 no SQLite — testes usam `assertRouteResponds()` que aceita 200 ou 500
- Helpers reutilizáveis em `tests/Pest.php`: `createAdminUser()`, `createPublishedQuiz()`, `createSubscribedUser()`
- Plano completo: `TEST_PLAN.md`

### Registerwall e Controle de Acesso

O conteúdo de análise IA ("Decifrando a Tese") usa **registerwall** (registro gratuito) em vez de paywall. A lógica é baseada em permissões Spatie:

- **`view_ai_analysis`** na role `registered` → qualquer usuário logado vê o conteúdo (registerwall).
- Removendo essa permissão do `registered` → somente `subscriber`/`premium`/`admin` veem (paywall futuro).
- O CTA na view muda automaticamente entre "Criar Conta Grátis" e "Assine o T&S".
- Administração de admin agora via `hasRole('admin')` (Spatie) — constante `tes_constants.admins` eliminada.
- Registro público habilitado via Fortify.

**Próximos passos para deploy:**
1. Testar no browser local (tese STF com IA: sem login → registerwall; com login → conteúdo completo)
2. `git push` (deploy automático via Vito Deploy)
3. Em produção: `php artisan db:seed --class=RolesAndPermissionsSeeder`
4. Em `/admin/roles`: atribuir role `admin` ao usuário administrador
5. Detalhes completos: `PAYWALL_IA_PLAN.md`, seção 3

---

### Migrações Concluídas (Q1 2026)
O projeto passou por extensas modernizações no início de 2026. Os planejamentos originais `.md` foram concluídos e removidos para limpar o repositório, mas as conquistas permanecem ativas na base de código:
- **Upgrade de Framework**: Migração de Laravel 8/10 para **Laravel 12** com PHP 8.3, incluindo Filament v2 para v3.
- **Modernização de Frontend**: Transição completa do antigo framework CSS (OneUI/Bootstrap) para **Tailwind CSS** via Vite em todo o site público.
- **Testes Automatizados**: Suíte de testes migrada e ampliada de 67 testes soltos para >240 testes integrados no ecossistema Pest v3.
- **Sistema de Assinatura Completo**: Integração robusta via Laravel Cashier na versão 15 com a API do Stripe (Webhooks, Portal de Assinante e proteção de Feature Flags), atualmente com acesso via Toggle global ocultado (`ENABLE_SUBSCRIPTIONS=false`).

---

### Prepare local

1. create db tes
2. import tes tables from production
3. clone repo
4. ``` mv laravel_tes [name] ```
5. ``` composer install ```
6. ``` npm install ```
7. ``` npm run dev ```
8. ``` cp .env.example .env ```

```
APP_NAME=[name]
APP_KEY=
APP_DEBUG=true
APP_URL=https://[name].test
DB_DATABASE=tes
DB_USERNAME=root
DB_PASSWORD=
```

9. ``` php artisan key:generate ```
10. ``` php artisan session:table ```
11. ``` php artisan migrate ```
12. ``` valet secure [name] ```

## API Documentation

### Autenticação

A API utiliza autenticação Bearer Token **apenas para os novos endpoints** de busca individual. Os endpoints de busca por termo não requerem autenticação.

Configure o token no arquivo `.env`:

```env
API_TOKEN=seu-token-secreto-aqui
```

### Headers Obrigatórios

#### Para endpoints com autenticação:
```
Authorization: Bearer seu-token-secreto-aqui
Content-Type: application/json
Accept: application/json
```

#### Para endpoints sem autenticação:
```
Content-Type: application/json
Accept: application/json
```

### Endpoints

#### 🔍 1. Busca por Termo (Sem Autenticação)

**POST** `/api/`

**Parâmetros:**
- `q` ou `keyword` (obrigatório, mínimo 3 caracteres) - Termo de busca
- `tribunal` (obrigatório) - Tribunal/órgão para pesquisa

**Exemplo:**
```bash
curl -X POST "https://teses.test/api/" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "q": "medicamento",
    "tribunal": "STF"
  }'
```

**Resposta de Sucesso (200):**
```json
{
  "total_sum": 5,
  "total_rep": 3,
  "hits_sum": [
    {
      "trib_sum_titulo": "Título da Súmula",
      "trib_sum_numero": "123",
      "trib_sum_texto": "Texto da súmula...",
      "trib_sum_id": 456
    }
  ],
  "hits_rep": [
    {
      "trib_rep_titulo": "Título da Tese",
      "trib_rep_tema": "Tema da tese",
      "trib_rep_tese": "Texto da tese...",
      "trib_rep_data": "01/01/2023",
      "trib_rep_id": 789
    }
  ]
}
```

#### 🔍 2. Busca por Tribunal Específico (Sem Autenticação)

**POST** `/api/{tribunal}.php`

**Parâmetros:**
- `q` ou `keyword` (obrigatório, mínimo 3 caracteres) - Termo de busca
- `tribunal` (obrigatório) - Tribunal/órgão para pesquisa

**Endpoints disponíveis:**
- `POST /api/stf.php` - Busca STF
- `POST /api/stj.php` - Busca STJ
- `POST /api/tst.php` - Busca TST
- `POST /api/tcu.php` - Busca TCU
- `POST /api/tnu.php` - Busca TNU
- `POST /api/carf.php` - Busca CARF
- `POST /api/fonaje.php` - Busca FONAJE
- `POST /api/cej.php` - Busca CEJ

**Exemplo:**
```bash
curl -X POST "https://teses.test/api/stf.php" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "q": "medicamento",
    "tribunal": "STF"
  }'
```

#### 🔍 3. Buscar Súmula por Número (Com Autenticação)

**GET** `/api/sumula/{tribunal}/{numero}`

**Parâmetros:**
- `tribunal` (string): STF ou STJ
- `numero` (integer): Número da súmula

**Exemplo:**
```bash
curl -X GET "https://teses.test/api/sumula/stf/269" \
  -H "Authorization: Bearer seu-token-secreto-aqui" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "id": 269,
    "numero": 269,
    "titulo": "Súmula 269",
    "texto": "O mandado de segurança não é substitutivo de ação de cobrança.",
    "aprovadaEm": "13/12/1963",
    "obs": "",
    "legis": "Constituição Federal de 1946, art. 141, § 24...",
    "precedentes": "RMS 6747 Publicações: DJ de 27/06/1963...",
    "is_vinculante": 0,
    "link": "https://jurisprudencia.stf.jus.br/...",
    "seq": 269
  }
}
```

#### 🔍 4. Buscar Tese por Número (Com Autenticação)

**GET** `/api/tese/{tribunal}/{numero}`

**Parâmetros:**
- `tribunal` (string): STF ou STJ
- `numero` (integer): Número da tese

**Exemplo:**
```bash
curl -X GET "https://teses.test/api/tese/stj/1303" \
  -H "Authorization: Bearer seu-token-secreto-aqui" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "id": 1608524,
    "numero": 1303,
    "orgao": "TERCEIRA SEÇÃO",
    "tema": "Definir se a ausência de confissão pelo investigado...",
    "tese_texto": null,
    "ramos": "Furto",
    "atualizadaEm": "09/02/2025",
    "situacao": "Afetado"
  }
}
```

#### ✏️ 5. Atualizar Tese por Número (Com Autenticação)

**POST** `/api/tese/{tribunal}/{numero}`

**Parâmetros URL:**
- `tribunal` (string): STF ou STJ
- `numero` (integer): Número da tese

**Body (JSON):**
```json
{
  "tese_texto": "Texto da tese que será atualizado"
}
```

**Validações:**
- `tese_texto`: obrigatório, string, máximo 65535 caracteres
- Texto puro (sem HTML ou Markdown)
- Substitui completamente o valor atual (null, "", ou texto existente)

**Exemplo:**
```bash
curl -X POST "https://teses.test/api/tese/stf/1438" \
  -H "Authorization: Bearer seu-token-secreto-aqui" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tese_texto": "É constitucional a admissão de trabalhadores..."
  }'
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "message": "Tese atualizada com sucesso.",
  "data": {
    "id": 33061,
    "numero": 1438,
    "tema_texto": "...",
    "tese_texto": "É constitucional a admissão de trabalhadores...",
    // ... outros campos
  }
}
```

**Validações:**
- `tese_texto`: obrigatório, string, máximo 65535 caracteres
- **Não aceita string vazia `""`** - retorna erro 422 para alertar sobre possível erro acidental
- **Aceita `null`** para limpar o campo (alternativa ao DELETE)
- Texto puro (sem HTML ou Markdown)
- Substitui completamente o valor atual

**Respostas de Erro:**
- **400**: Parâmetros inválidos (tribunal ou número inválido)
- **401**: Token não fornecido ou inválido
- **404**: Tese não encontrada
- **422**: String vazia não permitida (use `null` ou DELETE para limpar)

**Observações:**
- Use `null` no campo `tese_texto` para limpar o texto
- Para limpeza explícita e segura, use o endpoint DELETE abaixo

#### 🗑️ 6. Remover Texto da Tese (Com Autenticação)

**DELETE** `/api/tese/{tribunal}/{numero}/tese_texto`

Remove apenas o campo `tese_texto` da tese (não remove a tese inteira). Forma explícita e segura de limpar o texto.

**Parâmetros URL:**
- `tribunal` (string): STF ou STJ
- `numero` (integer): Número da tese

**Exemplo:**
```bash
curl -X DELETE "https://teses.test/api/tese/stf/1438/tese_texto" \
  -H "Authorization: Bearer seu-token-secreto-aqui" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "message": "Texto da tese removido com sucesso.",
  "data": {
    "id": 33061,
    "numero": 1438,
    "tema_texto": "...",
    "tese_texto": "",
    // ... outros campos
  }
}
```

**Respostas de Erro:**
- **400**: Parâmetros inválidos (tribunal ou número inválido)
- **401**: Token não fornecido ou inválido
- **404**: Tese não encontrada

#### 🔍 7. Buscar Temas Aleatórios (Com Autenticação)

**GET** `/api/random-themes/{limit?}/{min_judgments?}`

> **Observação:** Todos os parâmetros são opcionais. Se não informar nenhum, o endpoint retorna 5 temas com pelo menos 2 julgados STF+STJ (valores padrão).
>
> - `/api/random-themes` → retorna 5 temas, mínimo 2 julgados STF+STJ (padrão)
> - `/api/random-themes/3` → retorna 3 temas, mínimo 2 julgados STF+STJ (padrão)
> - `/api/random-themes/3/5` → retorna 3 temas, mínimo 5 julgados STF+STJ

**Parâmetros:**
- `limit` (integer, opcional): Número de temas a retornar (1-50, padrão: 5)
- `min_judgments` (integer, opcional): Mínimo de julgados STF+STJ (padrão: 2)

**Exemplo:**
```bash
curl -X GET "https://teses.test/api/random-themes/5/2" \
  -H "Authorization: Bearer seu-token-secreto-aqui" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "keyword": "base de cálculo do iss",
      "label": "Base de Cálculo do ISS",
      "slug": "base-de-clculo-do-iss",
      "concept": "O Imposto Sobre Serviços (ISS) é um tributo...",
      "concept_validated_at": "2024-01-15T10:30:00.000000Z",
      "url": "https://teses.test/tema/base-de-clculo-do-iss",
      "tribunais": {
        "stf": {
          "sumula": {
            "total": 0,
            "hits": []
          },
          "tese": {
            "total": 2,
            "hits": [
              {
                "trib_rep_titulo": "RE 1285845",
                "trib_rep_tema": "TEMA: 1135 - Inclusão do Imposto sobre Serviços...",
                "trib_rep_tese": "É constitucional a inclusão do Imposto Sobre Serviços...",
                "trib_rep_data": "21/06/2021",
                "trib_rep_id": 758
              }
            ]
          }
        },
        "stj": {
          "sumula": {
            "total": 1,
            "hits": [
              {
                "trib_sum_titulo": "Súmula 524",
                "trib_sum_numero": "524",
                "trib_sum_texto": "No tocante à base de cálculo, o ISSQN incide...",
                "trib_sum_id": 524
              }
            ]
          },
          "tese": {
            "total": 2,
            "hits": [
              {
                "trib_rep_titulo": "Tema/Repetitivo 634",
                "trib_rep_tema": "QUESTÃO: Discute-se a inclusão do ISS...",
                "trib_rep_tese": "O valor suportado pelo beneficiário do serviço...",
                "trib_rep_data": "14/07/2025",
                "trib_rep_id": 634
              }
            ]
          }
        }
      }
    }
  ],
  "total_found": 5,
  "requested_limit": 5,
  "min_judgments_required": 2
}
```

**Resposta de Erro (404):**
```json
{
  "success": false,
  "error": "Nenhum tema encontrado com pelo menos 2 julgados do STF ou STJ."
}
```

### Códigos de Status HTTP

- **200**: Sucesso
- **400**: Parâmetros inválidos
- **401**: Token de autenticação inválido ou não fornecido (apenas endpoints com autenticação)
- **404**: Súmula/Tese não encontrada ou nenhum tema encontrado

### Tribunais Suportados

#### Busca por Termo (Todos os Tribunais):
- **STF** - Súmulas e Teses
- **STJ** - Súmulas e Teses
- **TST** - Súmulas e Teses
- **TNU** - Súmulas e Questões de Ordem
- **TCU** - Via API externa
- **CARF** - Súmulas
- **FONAJE** - Súmulas
- **CEJ** - Súmulas

#### Busca Individual (Apenas STF e STJ):
- **STF**: Súmulas e Teses
- **STJ**: Súmulas e Teses

### Estrutura das Tabelas

#### STF
- **Súmulas**: `stf_sumulas`
- **Teses**: `stf_teses`

#### STJ
- **Súmulas**: `stj_sumulas`
- **Teses**: `stj_teses`

### Configuração do Token

1. **Local**: Adicione ao arquivo `.env`:
```env
API_TOKEN=seu-token-secreto-aqui
```

2. **Produção**: Configure no painel do Forge (Environment Variables):
```env
API_TOKEN=seu-token-secreto-producao
```

3. **Segurança**: Use tokens diferentes para local e produção.

### Exemplos de Uso

#### Busca por Termo (Sem Autenticação)
```bash
# Buscar "medicamento" no STF
curl -X POST "https://teses.test/api/" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"q": "medicamento", "tribunal": "STF"}'
```

#### Busca Individual (Com Autenticação)
```bash
# Buscar súmula 269 do STF
curl -X GET "https://teses.test/api/sumula/stf/269" \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json"

# Buscar tese 1234 do STF
curl -X GET "https://teses.test/api/tese/stf/1234" \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json"

# Atualizar tese 1438 do STF
curl -X POST "https://teses.test/api/tese/stf/1438" \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json" \
  -d '{"tese_texto": "Texto da tese aqui"}'

# Limpar texto da tese 1438 (usando null no POST)
curl -X POST "https://teses.test/api/tese/stf/1438" \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json" \
  -d '{"tese_texto": null}'

# Remover texto da tese 1438 explicitamente (usando DELETE)
curl -X DELETE "https://teses.test/api/tese/stf/1438/tese_texto" \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json"

# Buscar 5 temas aleatórios com pelo menos 2 julgados STF+STJ
curl -X GET "https://teses.test/api/random-themes/5/2" \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json"

# Buscar 10 temas aleatórios com pelo menos 3 julgados STF+STJ
curl -X GET "https://teses.test/api/random-themes/10/3" \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json"
```

### Compatibilidade

- ✅ **Endpoints existentes** continuam funcionando sem autenticação
- ✅ **Extensão Chrome** continua funcionando normalmente
- ✅ **Nova funcionalidade** requer autenticação Bearer Token
- ✅ **Validações robustas** em todos os endpoints
- ✅ **Mensagens de erro** em português

### Rate Limiting

A API possui rate limiting configurado pelo Laravel para prevenir abuso. Consulte a documentação do Laravel para mais detalhes sobre configuração de throttling.

---

## API de Quizzes (Novo - Dez/2024)

A API de quizzes utiliza a mesma autenticação Bearer Token das outras APIs.

### Endpoints de Quizzes

#### Listar Quizzes
```bash
GET /api/quizzes
GET /api/quizzes?status=published&tribunal=STJ&category_id=3
```

#### Criar Quiz
```bash
POST /api/quizzes
{
  "title": "Prescrição no Direito Civil",
  "description": "Teste seus conhecimentos...",
  "tribunal": "STJ",
  "category_id": 3,
  "difficulty": "medium",
  "estimated_time": 5,
  "color": "#912F56",
  "status": "published"
}
```

#### Adicionar Perguntas ao Quiz
```bash
POST /api/quizzes/{quiz_id}/questions
{
  "question_ids": [1, 2, 3]
}
```

### Endpoints de Perguntas

#### Listar Perguntas
```bash
GET /api/questions
GET /api/questions?category_id=3&difficulty=medium
```

#### Criar Pergunta
```bash
POST /api/questions
{
  "text": "Qual é o prazo prescricional para...",
  "explanation": "Conforme art. 206 do CC...",
  "category_id": 3,
  "difficulty": "medium",
  "options": [
    {"letter": "A", "text": "3 anos", "is_correct": false},
    {"letter": "B", "text": "5 anos", "is_correct": true},
    {"letter": "C", "text": "10 anos", "is_correct": false},
    {"letter": "D", "text": "2 anos", "is_correct": false}
  ]
}
```

#### Criar Perguntas em Lote (para integração com IA)
```bash
POST /api/questions/bulk
{
  "questions": [
    {
      "text": "Pergunta 1...",
      "category_id": 3,
      "difficulty": "easy",
      "options": [...]
    },
    {
      "text": "Pergunta 2...",
      "category_id": 3,
      "difficulty": "medium",
      "options": [...]
    }
  ]
}
```

#### Buscar Perguntas
```bash
GET /api/questions/search?q=prescrição&category_id=3
```

### Listar Categorias
```bash
GET /api/quizzes/categories
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Direito Administrativo", "slug": "direito-administrativo"},
    {"id": 2, "name": "Direito Ambiental", "slug": "direito-ambiental"},
    {"id": 3, "name": "Direito Civil", "slug": "direito-civil"},
    ...
  ]
}
```

### Documentação Completa

Para documentação detalhada da implementação, incluindo schema do banco, todas as rotas e sugestões de melhorias futuras, consulte o arquivo `QUIZ_IMPLEMENTATION.md`.
