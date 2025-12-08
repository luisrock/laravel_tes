# Guia de Criação de Quizzes via API + Prompt para IA

## Índice
1. [Autenticação](#autenticação)
2. [Categorias Disponíveis](#categorias-disponíveis)
3. [Criar Perguntas](#criar-perguntas)
4. [Criar Perguntas em Lote](#criar-perguntas-em-lote)
5. [Criar Quiz](#criar-quiz)
6. [Adicionar Perguntas ao Quiz](#adicionar-perguntas-ao-quiz)
7. [Fluxo Completo](#fluxo-completo)
8. [Prompt para IA](#prompt-para-ia)

---

## Autenticação

Todas as requisições à API requerem o header de autorização:

```
Authorization: Bearer SEU_TOKEN_AQUI
Content-Type: application/json
Accept: application/json
```

---

## Categorias Disponíveis

```bash
GET /api/quizzes/categories
```

| ID | Nome | Slug |
|----|------|------|
| 1 | Direito Administrativo | direito-administrativo |
| 2 | Direito Ambiental | direito-ambiental |
| 3 | Direito Civil | direito-civil |
| 4 | Direito Constitucional | direito-constitucional |
| 5 | Direito do Consumidor | direito-do-consumidor |
| 6 | Direito do Trabalho | direito-do-trabalho |
| 7 | Direito Empresarial | direito-empresarial |
| 8 | Direito Penal | direito-penal |
| 9 | Direito Previdenciário | direito-previdenciario |
| 10 | Direito Processual Civil | direito-processual-civil |
| 11 | Direito Processual Penal | direito-processual-penal |
| 12 | Direito Tributário | direito-tributario |

---

## Criar Perguntas

### Criar uma pergunta individual

```bash
POST /api/questions
```

```json
{
  "text": "De acordo com a Súmula 331 do TST, qual é a regra sobre a responsabilidade subsidiária do tomador de serviços?",
  "explanation": "A Súmula 331, IV, do TST estabelece que o inadimplemento das obrigações trabalhistas, por parte do empregador, implica a responsabilidade subsidiária do tomador dos serviços quanto àquelas obrigações, desde que haja participado da relação processual e conste também do título executivo judicial.",
  "category_id": 6,
  "difficulty": "medium",
  "options": [
    {
      "letter": "A",
      "text": "O tomador de serviços é sempre responsável solidário pelas obrigações trabalhistas.",
      "is_correct": false
    },
    {
      "letter": "B",
      "text": "O tomador de serviços responde subsidiariamente pelo inadimplemento das obrigações trabalhistas do empregador.",
      "is_correct": true
    },
    {
      "letter": "C",
      "text": "Não há responsabilidade do tomador de serviços em nenhuma hipótese.",
      "is_correct": false
    },
    {
      "letter": "D",
      "text": "A responsabilidade do tomador é objetiva e independe de culpa.",
      "is_correct": false
    }
  ]
}
```

**Campos obrigatórios:**
- `text` (string): Enunciado da pergunta
- `category_id` (integer): ID da categoria jurídica
- `difficulty` (string): `easy`, `medium` ou `hard`
- `options` (array): Mínimo 2, máximo 6 alternativas

**Campos opcionais:**
- `explanation` (string): Explicação da resposta correta
- `tag_ids` (array): IDs de tags existentes

---

## Criar Perguntas em Lote

Ideal para integração com IA - cria múltiplas perguntas em uma única requisição.

```bash
POST /api/questions/bulk
```

```json
{
  "questions": [
    {
      "text": "Pergunta 1...",
      "explanation": "Explicação 1...",
      "category_id": 3,
      "difficulty": "easy",
      "options": [
        {"letter": "A", "text": "Alternativa A", "is_correct": false},
        {"letter": "B", "text": "Alternativa B", "is_correct": true},
        {"letter": "C", "text": "Alternativa C", "is_correct": false},
        {"letter": "D", "text": "Alternativa D", "is_correct": false}
      ]
    },
    {
      "text": "Pergunta 2...",
      "explanation": "Explicação 2...",
      "category_id": 3,
      "difficulty": "medium",
      "options": [
        {"letter": "A", "text": "Alternativa A", "is_correct": true},
        {"letter": "B", "text": "Alternativa B", "is_correct": false},
        {"letter": "C", "text": "Alternativa C", "is_correct": false},
        {"letter": "D", "text": "Alternativa D", "is_correct": false}
      ]
    }
  ]
}
```

**Resposta:**
```json
{
  "success": true,
  "message": "2 perguntas criadas com sucesso.",
  "data": {
    "created": [
      {"id": 10, "text": "Pergunta 1..."},
      {"id": 11, "text": "Pergunta 2..."}
    ],
    "errors": []
  }
}
```

---

## Criar Quiz

```bash
POST /api/quizzes
```

```json
{
  "title": "Súmulas do TST sobre Terceirização",
  "description": "Teste seus conhecimentos sobre as principais súmulas do TST relacionadas à terceirização e responsabilidade trabalhista.",
  "tribunal": "TST",
  "category_id": 6,
  "difficulty": "medium",
  "estimated_time": 10,
  "color": "#5c80d1",
  "meta_keywords": "TST, terceirização, súmula 331, responsabilidade subsidiária",
  "show_feedback_immediately": true,
  "random_order": false,
  "status": "draft"
}
```

**Campos obrigatórios:**
- `title` (string): Título do quiz
- `tribunal` (string): `STF`, `STJ`, `TST` ou `TNU`
- `category_id` (integer): ID da categoria

**Campos opcionais:**
- `description` (string): Descrição do quiz
- `difficulty` (string): `easy`, `medium` ou `hard` (default: `medium`)
- `estimated_time` (integer): Tempo estimado em minutos
- `color` (string): Cor primária em hex (default: `#5c80d1`)
- `tema_number` (integer): Número do tema/tese relacionado
- `meta_keywords` (string): Palavras-chave para SEO
- `show_feedback_immediately` (boolean): Mostrar feedback após cada resposta (default: `true`)
- `random_order` (boolean): Embaralhar ordem das perguntas (default: `false`)
- `show_progress` (boolean): Mostrar barra de progresso (default: `true`)
- `show_ads` (boolean): Mostrar anúncios (default: `true`)
- `status` (string): `draft`, `published` ou `archived` (default: `draft`)

---

## Adicionar Perguntas ao Quiz

```bash
POST /api/quizzes/{quiz_id}/questions
```

```json
{
  "question_ids": [10, 11, 12, 13, 14]
}
```

**Resposta:**
```json
{
  "success": true,
  "message": "5 perguntas adicionadas ao quiz.",
  "data": {
    "quiz_id": 1,
    "total_questions": 5
  }
}
```

---

## Fluxo Completo

### Exemplo: Criar quiz completo sobre Direito Civil

```bash
# 1. Criar perguntas em lote
curl -X POST "https://tesesesumulas.com.br/api/questions/bulk" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "questions": [
      {
        "text": "Qual o prazo prescricional para ações de reparação civil?",
        "explanation": "Conforme art. 206, §3º, V do CC, prescreve em 3 anos a pretensão de reparação civil.",
        "category_id": 3,
        "difficulty": "easy",
        "options": [
          {"letter": "A", "text": "1 ano", "is_correct": false},
          {"letter": "B", "text": "3 anos", "is_correct": true},
          {"letter": "C", "text": "5 anos", "is_correct": false},
          {"letter": "D", "text": "10 anos", "is_correct": false}
        ]
      }
    ]
  }'

# Resposta: {"success": true, "data": {"created": [{"id": 15, ...}]}}

# 2. Criar o quiz
curl -X POST "https://tesesesumulas.com.br/api/quizzes" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Prescrição no Direito Civil",
    "description": "Teste seus conhecimentos sobre prazos prescricionais no Código Civil.",
    "tribunal": "STJ",
    "category_id": 3,
    "difficulty": "medium",
    "estimated_time": 5,
    "status": "draft"
  }'

# Resposta: {"success": true, "data": {"id": 5, "slug": "prescricao-no-direito-civil", ...}}

# 3. Adicionar perguntas ao quiz
curl -X POST "https://tesesesumulas.com.br/api/quizzes/5/questions" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "question_ids": [15, 16, 17, 18, 19]
  }'

# 4. Publicar o quiz
curl -X PUT "https://tesesesumulas.com.br/api/quizzes/5" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "published"
  }'
```

---

## Prompt para IA

Use o prompt abaixo para gerar perguntas com qualquer modelo de IA (GPT-4, Claude, Gemini, etc.):

---

### PROMPT PARA GERAÇÃO DE PERGUNTAS DE QUIZ JURÍDICO

```
Você é um especialista em Direito brasileiro e vai me ajudar a criar perguntas para quizzes jurídicos sobre súmulas e teses dos tribunais superiores (STF, STJ, TST, TNU).

## FORMATO DE SAÍDA

Gere as perguntas EXCLUSIVAMENTE no formato JSON abaixo, pronto para ser enviado à API:

{
  "questions": [
    {
      "text": "Enunciado da pergunta aqui",
      "explanation": "Explicação detalhada da resposta correta, citando o fundamento legal ou jurisprudencial",
      "category_id": [ID_DA_CATEGORIA],
      "difficulty": "[easy|medium|hard]",
      "options": [
        {"letter": "A", "text": "Alternativa A", "is_correct": false},
        {"letter": "B", "text": "Alternativa B", "is_correct": true},
        {"letter": "C", "text": "Alternativa C", "is_correct": false},
        {"letter": "D", "text": "Alternativa D", "is_correct": false}
      ]
    }
  ]
}

## CATEGORIAS DISPONÍVEIS

Use APENAS estes IDs:
- 1 = Direito Administrativo
- 2 = Direito Ambiental
- 3 = Direito Civil
- 4 = Direito Constitucional
- 5 = Direito do Consumidor
- 6 = Direito do Trabalho
- 7 = Direito Empresarial
- 8 = Direito Penal
- 9 = Direito Previdenciário
- 10 = Direito Processual Civil
- 11 = Direito Processual Penal
- 12 = Direito Tributário

## REGRAS OBRIGATÓRIAS

1. **Quantidade**: Gere exatamente [QUANTIDADE] perguntas
2. **Alternativas**: Sempre 4 alternativas (A, B, C, D)
3. **Apenas UMA correta**: Exatamente uma alternativa com "is_correct": true
4. **Dificuldade**:
   - easy: Conhecimento básico, texto direto da súmula/tese
   - medium: Requer interpretação ou combinação de conceitos
   - hard: Casos complexos, exceções, entendimentos recentes
5. **Explicação**: Sempre cite a fonte (número da súmula, tema, artigo de lei)
6. **Alternativas erradas**: Devem ser plausíveis, não absurdas
7. **Linguagem**: Formal e técnica, como em provas de concurso

## TIPOS DE PERGUNTA

Varie entre estes formatos:
- "De acordo com a Súmula X do [tribunal]..."
- "Segundo o entendimento firmado no Tema Y..."
- "Qual a posição do [tribunal] sobre..."
- "É CORRETO afirmar que..."
- "É INCORRETO afirmar que..." (neste caso, a correta é a falsa)
- "Assinale a alternativa que NÃO corresponde..."

## EXEMPLO

{
  "questions": [
    {
      "text": "De acordo com a Súmula Vinculante 37 do STF, é correto afirmar que:",
      "explanation": "A Súmula Vinculante 37 estabelece que 'Não cabe ao Poder Judiciário, que não tem função legislativa, aumentar vencimentos de servidores públicos sob o fundamento de isonomia.' Este entendimento decorre da separação dos poderes e da reserva legal em matéria de remuneração de servidores.",
      "category_id": 1,
      "difficulty": "medium",
      "options": [
        {"letter": "A", "text": "O Judiciário pode equiparar vencimentos entre cargos similares para garantir isonomia.", "is_correct": false},
        {"letter": "B", "text": "O Judiciário não pode aumentar vencimentos de servidores sob fundamento de isonomia.", "is_correct": true},
        {"letter": "C", "text": "A isonomia salarial pode ser determinada judicialmente em casos excepcionais.", "is_correct": false},
        {"letter": "D", "text": "O princípio da isonomia prevalece sobre a separação dos poderes em matéria remuneratória.", "is_correct": false}
      ]
    }
  ]
}

## TAREFA

[INSIRA AQUI SUA SOLICITAÇÃO ESPECÍFICA]

Exemplos de solicitações:
- "Gere 10 perguntas sobre súmulas do STJ em Direito Civil (category_id: 3)"
- "Crie 5 perguntas difíceis sobre o Tema 1.046 do STF (vinculação de acordos coletivos)"
- "Faça 8 perguntas variadas sobre Direito Tributário no STJ"
- "Gere perguntas sobre as súmulas vinculantes mais cobradas em concursos"
```

---

### DICAS DE USO

1. **Seja específico**: Quanto mais detalhada a solicitação, melhor o resultado
   - ❌ "Gere perguntas de direito"
   - ✅ "Gere 5 perguntas médias sobre a Súmula 7 do STJ (reexame de provas)"

2. **Forneça contexto**: Se tiver o texto da súmula/tese, inclua no prompt
   ```
   Baseado na súmula abaixo, gere 3 perguntas:
   "Súmula 479/STJ: As instituições financeiras respondem objetivamente pelos danos gerados por fortuito interno relativo a fraudes e delitos praticados por terceiros no âmbito de operações bancárias."
   ```

3. **Peça revisão**: Após gerar, peça para a IA revisar se há erros
   ```
   Revise as perguntas acima e verifique:
   1. Se todas têm exatamente uma resposta correta
   2. Se as explicações citam corretamente as fontes
   3. Se as alternativas erradas são plausíveis
   ```

4. **Gere em lotes**: Para evitar erros, gere 5-10 perguntas por vez

5. **Valide o JSON**: Antes de enviar à API, valide o JSON em jsonlint.com

---

## Scripts Úteis

### Python: Enviar perguntas em lote

```python
import requests
import json

API_URL = "https://tesesesumulas.com.br/api"
TOKEN = "SEU_TOKEN_AQUI"

headers = {
    "Authorization": f"Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}

# JSON gerado pela IA
questions_json = {
    "questions": [
        # ... perguntas aqui
    ]
}

# Enviar para API
response = requests.post(
    f"{API_URL}/questions/bulk",
    headers=headers,
    json=questions_json
)

result = response.json()
print(json.dumps(result, indent=2, ensure_ascii=False))

# Pegar IDs das perguntas criadas
if result.get("success"):
    question_ids = [q["id"] for q in result["data"]["created"]]
    print(f"IDs criados: {question_ids}")
```

### Bash: Criar quiz e adicionar perguntas

```bash
#!/bin/bash
TOKEN="SEU_TOKEN_AQUI"
BASE_URL="https://tesesesumulas.com.br/api"

# Criar quiz
QUIZ_RESPONSE=$(curl -s -X POST "$BASE_URL/quizzes" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Meu Quiz",
    "tribunal": "STJ",
    "category_id": 3,
    "status": "draft"
  }')

QUIZ_ID=$(echo $QUIZ_RESPONSE | jq -r '.data.id')
echo "Quiz criado: ID $QUIZ_ID"

# Adicionar perguntas (substitua pelos IDs reais)
curl -X POST "$BASE_URL/quizzes/$QUIZ_ID/questions" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"question_ids": [1, 2, 3, 4, 5]}'
```

---

*Documentação criada em Dezembro de 2024*
