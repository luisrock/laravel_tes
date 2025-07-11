## Teses & Súmulas (Laravel Site)

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

### Códigos de Status HTTP

- **200**: Sucesso
- **400**: Parâmetros inválidos
- **401**: Token de autenticação inválido ou não fornecido (apenas endpoints com autenticação)
- **404**: Súmula/Tese não encontrada

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
```

### Compatibilidade

- ✅ **Endpoints existentes** continuam funcionando sem autenticação
- ✅ **Extensão Chrome** continua funcionando normalmente
- ✅ **Nova funcionalidade** requer autenticação Bearer Token
- ✅ **Validações robustas** em todos os endpoints
- ✅ **Mensagens de erro** em português

### Rate Limiting

A API possui rate limiting configurado pelo Laravel para prevenir abuso. Consulte a documentação do Laravel para mais detalhes sobre configuração de throttling.
