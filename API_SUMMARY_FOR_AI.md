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
- `tese_texto`: obrigatório, string, máximo 65535 caracteres
- Texto puro (sem HTML/Markdown)
- Substitui completamente o valor atual (null, "", ou texto existente)

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
- **400**: Parâmetros inválidos
- **401**: Token ausente/inválido
- **404**: Tese não encontrada

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

## Observações Importantes
1. Busca por **número** (não ID)
2. Substitui completamente o valor atual
3. Aceita string vazia `""` para limpar
4. Apenas STF e STJ suportados
5. Texto é armazenado sem processamento
