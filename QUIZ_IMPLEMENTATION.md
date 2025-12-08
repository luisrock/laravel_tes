# Sistema de Quizzes - Documentação de Implementação

## Visão Geral

Sistema completo de quizzes jurídicos integrado ao site Teses & Súmulas, permitindo testar conhecimentos sobre teses do STF e STJ. Implementado em **Dezembro de 2024**.

## Dashboard Administrativo

O painel administrativo (`/admin`) foi reformulado para ser um hub central de navegação:

### Cards Disponíveis
- **Temas & Pesquisas** (`/admin/temas`) - Gestão de temas de pesquisa com estatísticas em tempo real
- **Quizzes** (`/admin/quizzes`) - CRUD de quizzes com filtros por status, tribunal e categoria
- **Banco de Perguntas** (`/admin/questions`) - Perguntas reutilizáveis em múltiplos quizzes
- **Estatísticas de Quizzes** (`/admin/quizzes/stats`) - Dashboard com gráficos e métricas
- **Newsletters** - Visualização de newsletters publicadas
- **Tags de Perguntas** (`/admin/questions/tags`) - Gestão de tags para categorização

### Controle de Visibilidade na Home
O botão "Visível na Home" / "Oculto na Home" na página `/admin/quizzes` controla se a seção "Teste seus Conhecimentos" aparece na página inicial. Útil para ocultar a seção até ter um número adequado de quizzes criados.

### Links Internos para Quizzes
- **Home**: Seção "Teste seus Conhecimentos" com até 3 quizzes em destaque (quando ativada)
- **Páginas de Teses**: Seção "Quizzes Relacionados" mostra quizzes do mesmo tribunal ou com keywords similares
- **Footer**: Link permanente para `/quizzes`

## Estrutura de Arquivos

### Migrations (database/migrations/)
```
2025_12_08_001000_create_quiz_categories_table.php   - Categorias de quiz (Direito Civil, Penal, etc.)
2025_12_08_001001_create_question_tags_table.php     - Tags para perguntas
2025_12_08_001002_create_quizzes_table.php           - Tabela principal de quizzes
2025_12_08_001003_create_questions_table.php         - Banco de perguntas
2025_12_08_001004_create_question_options_table.php  - Alternativas das perguntas
2025_12_08_001005_create_quiz_question_table.php     - Pivot: quiz <-> perguntas (N:N)
2025_12_08_001006_create_question_tag_table.php      - Pivot: pergunta <-> tags (N:N)
2025_12_08_001007_create_quiz_tese_table.php         - Pivot: quiz <-> teses (pesquisas)
2025_12_08_001008_create_question_tese_table.php     - Pivot: pergunta <-> teses (pesquisas)
2025_12_08_001009_create_quiz_attempts_table.php     - Tentativas de quiz
2025_12_08_001010_create_quiz_answers_table.php      - Respostas individuais
2025_12_08_194058_add_quizzes_home_visibility_setting.php - Config visibilidade na home
```

### Models (app/Models/)
```
Quiz.php           - Model principal do quiz
Question.php       - Model de pergunta (reutilizável em múltiplos quizzes)
QuestionOption.php - Alternativas de resposta
QuestionTag.php    - Tags para categorização
QuizCategory.php   - Categorias jurídicas
QuizAttempt.php    - Registro de tentativas
QuizAnswer.php     - Registro de respostas
```

### Controllers

#### API (app/Http/Controllers/Api/)
```
QuizApiController.php     - CRUD de quizzes via API
QuestionApiController.php - CRUD de perguntas via API
```

#### Admin (app/Http/Controllers/Admin/)
```
QuizAdminController.php     - Gerenciamento de quizzes no admin
QuestionAdminController.php - Gerenciamento de perguntas e tags
QuizStatsController.php     - Dashboard de estatísticas
```

#### Frontend (app/Http/Controllers/)
```
QuizController.php - Listagem, execução e resultados dos quizzes
```

### Views

#### Admin (resources/views/admin/)
```
dashboard.blade.php        - Dashboard central com cards de navegação
temas.blade.php            - Gestão de temas/pesquisas (movido de admin.blade.php)

quiz/
  ├── index.blade.php      - Lista de quizzes com filtros + botão visibilidade home
  ├── create.blade.php     - Criar novo quiz
  ├── edit.blade.php       - Editar quiz existente
  ├── questions.blade.php  - Gerenciar perguntas do quiz
  ├── stats.blade.php      - Dashboard geral de estatísticas
  └── stats-quiz.blade.php - Estatísticas de quiz específico

questions/
  ├── index.blade.php      - Banco de perguntas
  ├── create.blade.php     - Criar nova pergunta
  ├── edit.blade.php       - Editar pergunta
  └── tags.blade.php       - Gerenciar tags
```

#### Frontend (resources/views/front/)
```
quizzes.blade.php      - Lista pública de quizzes
quiz.blade.php         - Página de execução do quiz
quiz-result.blade.php  - Página de resultados
```

## Rotas

### Rotas Públicas (web.php)
```php
// Lista de quizzes
GET  /quizzes                           → QuizController@index
GET  /quizzes/categoria/{category}      → QuizController@byCategory

// Quiz individual
GET  /quiz/{quiz}                       → QuizController@show
POST /quiz/{quiz}/answer                → QuizController@submitAnswer (AJAX)
GET  /quiz/{quiz}/result                → QuizController@result
POST /quiz/{quiz}/restart               → QuizController@restart
```

### Rotas Admin (web.php) - Protegidas por middleware
```php
// Dashboard
GET    /admin                                   → HomeController@index (dashboard)
GET    /admin/temas                             → HomeController@temas (gestão de temas)

// Quizzes
GET    /admin/quizzes                           → QuizAdminController@index
POST   /admin/quizzes/toggle-home               → QuizAdminController@toggleHomeVisibility
GET    /admin/quizzes/create                    → QuizAdminController@create
POST   /admin/quizzes                           → QuizAdminController@store
GET    /admin/quizzes/{quiz}/edit               → QuizAdminController@edit
PUT    /admin/quizzes/{quiz}                    → QuizAdminController@update
DELETE /admin/quizzes/{quiz}                    → QuizAdminController@destroy
POST   /admin/quizzes/{quiz}/duplicate          → QuizAdminController@duplicate
GET    /admin/quizzes/{quiz}/questions          → QuizAdminController@questions
POST   /admin/quizzes/{quiz}/questions          → QuizAdminController@addQuestion
DELETE /admin/quizzes/{quiz}/questions/{question} → QuizAdminController@removeQuestion
POST   /admin/quizzes/{quiz}/questions/reorder  → QuizAdminController@reorderQuestions
GET    /admin/quizzes/{quiz}/questions/search   → QuizAdminController@searchQuestions

// Perguntas
GET    /admin/questions                         → QuestionAdminController@index
GET    /admin/questions/create                  → QuestionAdminController@create
POST   /admin/questions                         → QuestionAdminController@store
GET    /admin/questions/{question}/edit         → QuestionAdminController@edit
PUT    /admin/questions/{question}              → QuestionAdminController@update
DELETE /admin/questions/{question}              → QuestionAdminController@destroy
POST   /admin/questions/{question}/duplicate    → QuestionAdminController@duplicate
POST   /admin/questions/store-inline            → QuestionAdminController@storeInline (AJAX)

// Tags
GET    /admin/questions/tags                    → QuestionAdminController@tags
POST   /admin/questions/tags                    → QuestionAdminController@storeTag
DELETE /admin/questions/tags/{tag}              → QuestionAdminController@destroyTag

// Estatísticas
GET    /admin/quizzes/stats                     → QuizStatsController@index
GET    /admin/quizzes/{quiz}/stats              → QuizStatsController@show
GET    /admin/quizzes/stats/export              → QuizStatsController@export
```

### Rotas API (api.php) - Protegidas por bearer.token
```php
// Quizzes
GET    /api/quizzes                     → QuizApiController@index
GET    /api/quizzes/{quiz}              → QuizApiController@show
POST   /api/quizzes                     → QuizApiController@store
PUT    /api/quizzes/{quiz}              → QuizApiController@update
DELETE /api/quizzes/{quiz}              → QuizApiController@destroy
POST   /api/quizzes/{quiz}/questions    → QuizApiController@addQuestions
DELETE /api/quizzes/{quiz}/questions/{question} → QuizApiController@removeQuestion
PUT    /api/quizzes/{quiz}/questions/reorder    → QuizApiController@reorderQuestions
GET    /api/quizzes/categories          → QuizApiController@categories

// Perguntas
GET    /api/questions                   → QuestionApiController@index
GET    /api/questions/{question}        → QuestionApiController@show
POST   /api/questions                   → QuestionApiController@store
PUT    /api/questions/{question}        → QuestionApiController@update
DELETE /api/questions/{question}        → QuestionApiController@destroy
GET    /api/questions/tags              → QuestionApiController@tags
POST   /api/questions/tags              → QuestionApiController@storeTag
GET    /api/questions/search            → QuestionApiController@search
POST   /api/questions/bulk              → QuestionApiController@bulkStore (para IA)
```

## Schema do Banco de Dados

### quiz_categories
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| name | varchar(100) | Nome da categoria |
| slug | varchar(100) | Slug único |
| created_at, updated_at | timestamp | Timestamps |

**Categorias pré-cadastradas:**
- Direito Administrativo, Direito Ambiental, Direito Civil, Direito Constitucional
- Direito do Consumidor, Direito do Trabalho, Direito Empresarial, Direito Penal
- Direito Previdenciário, Direito Processual Civil, Direito Processual Penal, Direito Tributário

### quizzes
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| title | varchar(255) | Título do quiz |
| slug | varchar(255) | Slug único (auto-gerado) |
| description | text | Descrição |
| tribunal | enum('STF','STJ','TST','TNU') | Tribunal |
| tema_number | int | Número do tema (opcional) |
| category_id | bigint | FK para quiz_categories |
| difficulty | enum('easy','medium','hard') | Dificuldade |
| estimated_time | int | Tempo estimado em minutos |
| color | varchar(7) | Cor primária (#RRGGBB) |
| show_ads | boolean | Mostrar anúncios (default: true) |
| show_share | boolean | Mostrar compartilhamento |
| show_progress | boolean | Mostrar barra de progresso |
| random_order | boolean | Ordem aleatória das perguntas |
| show_feedback_immediately | boolean | Feedback imediato |
| meta_keywords | varchar(255) | Keywords para SEO |
| status | enum('draft','published','archived') | Status |
| views_count | int | Contador de visualizações |
| created_at, updated_at | timestamp | Timestamps |

### questions
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| text | text | Enunciado da pergunta |
| explanation | text | Explicação da resposta |
| category_id | bigint | FK para quiz_categories |
| difficulty | enum('easy','medium','hard') | Dificuldade |
| times_answered | int | Vezes respondida |
| times_correct | int | Vezes acertada |
| created_at, updated_at | timestamp | Timestamps |

### question_options
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| question_id | bigint | FK para questions |
| letter | char(1) | Letra da alternativa (A, B, C...) |
| text | text | Texto da alternativa |
| is_correct | boolean | Se é a resposta correta |
| created_at, updated_at | timestamp | Timestamps |

### quiz_question (pivot)
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| quiz_id | bigint | FK para quizzes |
| question_id | bigint | FK para questions |
| order | int | Ordem da pergunta no quiz |
| created_at | timestamp | Quando foi adicionada |

### quiz_attempts
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| quiz_id | bigint | FK para quizzes |
| user_id | bigint | FK para users (nullable) |
| session_id | varchar(255) | ID da sessão (visitantes) |
| started_at | timestamp | Início |
| finished_at | timestamp | Término (nullable) |
| score | int | Pontuação (nullable) |
| total_questions | int | Total de perguntas |
| time_spent_seconds | int | Tempo gasto (nullable) |
| status | enum('in_progress','completed','abandoned') | Status |
| created_at, updated_at | timestamp | Timestamps |

### quiz_answers
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | PK |
| attempt_id | bigint | FK para quiz_attempts |
| question_id | bigint | FK para questions |
| selected_option_id | bigint | FK para question_options (nullable) |
| is_correct | boolean | Se acertou |
| time_spent_seconds | int | Tempo na pergunta (nullable) |
| answered_at | timestamp | Quando respondeu |
| created_at, updated_at | timestamp | Timestamps |

## Funcionalidades Implementadas

### Interface Admin
- ✅ Dashboard central com cards de navegação e estatísticas
- ✅ CRUD completo de quizzes
- ✅ CRUD completo de perguntas
- ✅ Gestão de tags
- ✅ Adicionar/remover perguntas de quizzes
- ✅ Reordenar perguntas (drag-and-drop preparado)
- ✅ Buscar perguntas existentes para adicionar ao quiz
- ✅ Criar pergunta inline (direto na tela de gerenciamento)
- ✅ Duplicar quizzes e perguntas
- ✅ Filtros por status, tribunal, categoria
- ✅ Dashboard de estatísticas com gráficos (Chart.js)
- ✅ Paleta de cores pré-definida + cor customizada
- ✅ Toggle de visibilidade da seção de quizzes na home

### Interface Pública
- ✅ Lista de quizzes com filtros (categoria, tribunal, dificuldade)
- ✅ Página do quiz com:
  - Header colorido com informações
  - Barra de progresso
  - Questões com alternativas clicáveis
  - Navegação entre questões
  - Botão confirmar resposta
  - Feedback imediato (configurável)
  - Espaço para anúncios
- ✅ Página de resultados com:
  - Pontuação e porcentagem
  - Mensagem baseada no desempenho
  - Revisão das respostas com explicações
  - Links para teses relacionadas
  - Opções de reiniciar ou ver outros quizzes
- ✅ Links internos para quizzes:
  - Seção "Teste seus Conhecimentos" na home (controlável pelo admin)
  - Seção "Quizzes Relacionados" nas páginas de teses individuais
  - Link permanente no footer do site

### API REST
- ✅ Autenticação via Bearer Token (mesmo token da API existente)
- ✅ CRUD completo de quizzes
- ✅ CRUD completo de perguntas
- ✅ Criação em lote de perguntas (endpoint `/api/questions/bulk`)
- ✅ Busca de perguntas
- ✅ Gestão de tags via API
- ✅ Vinculação quiz <-> perguntas

### Estatísticas
- ✅ Cards com métricas gerais (total quizzes, perguntas, tentativas, taxa conclusão)
- ✅ Gráfico de tentativas nos últimos 30 dias
- ✅ Lista de quizzes mais populares
- ✅ Lista de perguntas mais difíceis
- ✅ Tentativas recentes
- ✅ Estatísticas detalhadas por quiz

## Paleta de Cores dos Quizzes

Cores pré-definidas para seleção no admin:
- `#912F56` - Vinho/Bordô (padrão)
- `#0D090A` - Preto
- `#F0C808` - Amarelo
- `#6BAA75` - Verde
- `#A53860` - Rosa escuro
- `#D5B0AC` - Rosa claro

## Autenticação API

Usa o mesmo token configurado em `.env`:
```env
API_TOKEN=seu-token-aqui
```

Exemplo de uso:
```bash
curl -X POST "https://teses.test/api/quizzes" \
  -H "Authorization: Bearer seu-token-aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Título do Quiz",
    "description": "Descrição",
    "tribunal": "STJ",
    "category_id": 3,
    "difficulty": "medium",
    "status": "published"
  }'
```

## Correções Aplicadas Durante Desenvolvimento

1. **Relacionamento pivot sem timestamps**: Removido `withTimestamps()` do relacionamento `questions()` no model `Quiz.php` e `quizzes()` no model `Question.php`, pois a migration não criou a coluna `updated_at` na tabela pivot.

## Sugestões para o Futuro

### Melhorias de UX
1. **Drag-and-drop para reordenar perguntas** - O JavaScript está preparado com Sortable.js, mas precisa integrar com a API
2. **Preview do quiz antes de publicar** - Mostrar como ficará para o visitante
3. **Timer opcional por pergunta** - Adicionar cronômetro regressivo
4. **Modo prática vs Modo avaliação** - Diferentes tipos de execução

### Funcionalidades Adicionais
1. **Compartilhamento social** - Botões para compartilhar resultado
2. **Ranking/Leaderboard** - Comparar pontuações entre usuários
3. **Certificados** - Gerar PDF de conclusão
4. **Quizzes programados** - Agendar publicação automática
5. **Importação em massa** - Upload de CSV/Excel com perguntas
6. **Integração com IA** - Geração automática de perguntas baseadas em teses

### SEO
1. **Schema.org markup** - Adicionar structured data para quizzes
2. **Sitemap dinâmico** - Incluir quizzes no sitemap
3. **Meta tags OG** - Open Graph para compartilhamento

### Analytics
1. **Heatmap de respostas** - Visualizar padrões de erro
2. **Tempo médio por pergunta** - Identificar perguntas confusas
3. **Abandono por pergunta** - Onde usuários desistem
4. **Exportação de relatórios** - PDF/Excel com estatísticas

### Integrações
1. **Google Analytics eventos** - Tracking de interações
2. **Webhook para conclusão** - Notificar sistemas externos
3. **API para embedar quiz** - Permitir incorporar em outros sites

## Comandos Úteis

```bash
# Rodar migrations
php artisan migrate

# Seed de categorias (já incluído na migration)
# Categorias são criadas automaticamente

# Limpar cache de rotas
php artisan route:clear

# Verificar rotas de quiz
php artisan route:list | grep quiz
```

## Dependências JavaScript (já existentes no projeto)

- **Bootstrap 4/5** - UI framework
- **Chart.js** - Gráficos de estatísticas
- **Sortable.js** - Drag-and-drop (incluir via CDN se necessário)

---

*Documentação criada em Dezembro de 2024*
