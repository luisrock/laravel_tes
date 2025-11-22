# Resumo da API POST para Atualizar Tese - Para Script Python

## Endpoint
```
POST /api/tese/{tribunal}/{numero}
```

## URL Base
A URL base é configurada através da variável `APP_URL` no arquivo `.env`:

```
{APP_URL}/api/tese/{tribunal}/{numero}
```

## Autenticação
**Bearer Token obrigatório** no header:
```
Authorization: Bearer {seu-token}
```

Token configurado em `API_TOKEN` no `.env` do servidor.

## Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Body (JSON)
```json
{
  "tese_texto": "Texto da tese aqui"
}
```

**Validações:**
- `tese_texto`: nullable, string, máximo 65535 caracteres
- **Não aceita string vazia `""`** - retorna erro 422 para proteger contra erro acidental
- **Aceita `null`** para limpar o campo (limpa o texto da tese)
- Texto puro (sem HTML/Markdown)
- Substitui completamente o valor atual (null ou texto existente)

## Parâmetros URL
- `tribunal`: "STF" ou "STJ" (case-insensitive)
- `numero`: número da tese (inteiro)

## Resposta de Sucesso (200)
```json
{
  "success": true,
  "message": "Tese atualizada com sucesso.",
  "data": {
    "id": 33061,
    "numero": 1438,
    "tema_texto": "...",
    "tese_texto": "...",
    // ... outros campos
  }
}
```

## Respostas de Erro
- **400**: Parâmetros inválidos (tribunal ou número)
- **401**: Token ausente/inválido
- **404**: Tese não encontrada
- **422**: String vazia não permitida (use `null` ou DELETE para limpar)

## Exemplo Python
```python
import requests
import os
from dotenv import load_dotenv

# Carrega variáveis do .env
load_dotenv()

# Configurações do .env
BASE_URL = os.getenv('APP_URL')
TOKEN = os.getenv('API_TOKEN')

url = f"{BASE_URL}/api/tese/stf/1438"
headers = {
    "Authorization": f"Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}
data = {
    "tese_texto": "Texto da tese aqui"
}

response = requests.post(url, headers=headers, json=data)
print(response.json())
```

## Limpar Texto da Tese

**Opção 1: POST com null**
```python
data = {"tese_texto": None}  # ou null no JSON
response = requests.post(url, headers=headers, json=data)
```

**Opção 2: DELETE específico (recomendado para limpeza explícita)**
```
DELETE /api/tese/{tribunal}/{numero}/tese_texto
```

```python
url = f"{BASE_URL}/api/tese/stf/1438/tese_texto"
response = requests.delete(url, headers=headers)
```

**⚠️ String vazia não é permitida:** Enviar `{"tese_texto": ""}` retorna erro 422 para proteger contra erros acidentais.

## Observações Importantes
1. Busca por **número** (não ID)
2. Substitui completamente o valor atual
3. **String vazia `""` retorna erro** - use `null` ou DELETE para limpar
4. **DELETE remove apenas o campo `tese_texto`** - não remove a tese inteira
5. Apenas STF e STJ suportados
6. Texto é armazenado sem processamento
