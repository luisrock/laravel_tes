# Specs: Script Python para Análise de Acórdãos com IA

**Versão:** 1.3 (Final)  
**Data:** 05/02/2026  
**Referência:** ANALISE_IA_PLAN.md v2.5

---

## Objetivo

Implementar **um único executável** com 3 modos de operação:

| Modo | Flag | Descrição |
|------|------|-----------|
| **UI** | `--ui` | Interface Rich para listar temas, criar jobs e monitorar |
| **Worker** | `--worker` | Processa jobs da fila (claim atômico), sem interação |
| **Enqueue CLI** | `--enqueue` | Cria jobs via linha de comando |

**Fluxo correto:**
```
UI/CLI cria jobs (queued) → Worker processa → UI/CLI monitora status
```

---

## Ambiente

- **Servidor:** AWS EC2 Arm64 (`vito@15.229.244.115`)
- **Diretório:** `home/vito/teses-scripts/analise_ia/`
- **Banco:** MySQL (produção)
- **PDFs:** AWS S3

---

## Arquivo `.env`

```env
# MySQL (banco de produção)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=

# APIs de IA
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
GOOGLE_API_KEY=
```

> **Segurança:** `chmod 600 .env` e nunca logar chaves ou conteúdo completo de PDFs.

---

## Schema do Banco

### Regra Fundamental: Sempre usar `(tese_id, tribunal)` juntos

O `tese_id` referencia `stf_teses.id` OU `stj_teses.id` (FK polimórfica). IDs podem coincidir entre tabelas. **Nunca buscar só por id.**

```python
def load_tese(tribunal: str, tese_id: int) -> dict:
    table = 'stf_teses' if tribunal == 'STF' else 'stj_teses'
    tema_col = 'tema_texto' if tribunal == 'STF' else 'tema'
    # ...
```

### `stf_teses` (LEITURA)
| Coluna | Descrição |
|--------|-----------|
| id | PK |
| numero | Número do tema |
| tema_texto | Descrição do tema |
| tese_texto | Texto da tese (NULL = sem tese) |
| acordao | Número do acórdão |
| link | Link STF |

### `stj_teses` (LEITURA)
| Coluna | Descrição |
|--------|-----------|
| id | PK |
| numero | Número do tema |
| tema | Descrição (**coluna diferente!**) |
| tese_texto | Texto da tese |

### `tese_acordaos` (LEITURA)
| Coluna | Tipo | Descrição |
|--------|------|-----------|
| id | bigint | PK |
| tese_id | bigint | FK (usar com tribunal!) |
| tribunal | enum | 'STF', 'STJ' |
| numero_acordao | varchar(100) | Ex: "RE 559937" |
| tipo | enum | Ver valores abaixo |
| s3_key | varchar(500) | **Caminho do PDF no S3** |
| file_size | int | Tamanho em bytes |
| deleted_at | timestamp | **Filtrar: IS NULL** |

**Valores do enum `tipo`:**
- 'Principal'
- 'Embargos de Declaração'
- 'Modulação de Efeitos'
- 'Recurso Extraordinário'
- 'Recurso Especial'
- 'Outros'

> **Confirmar no banco:** `SHOW COLUMNS FROM tese_acordaos LIKE 'tipo';`  
> Tratar valores desconhecidos como 'Outros'.

### `ai_models` (LEITURA)
| Coluna | Descrição |
|--------|-----------|
| id | PK |
| provider | 'openai', 'anthropic', 'google' |
| name | Nome amigável |
| model_id | ID para API |
| price_input_per_million | Preço input/1M tokens |
| price_output_per_million | Preço output/1M tokens |
| is_active | Modelo ativo |
| deprecated_at | NULL = válido |

### `tese_analysis_sections` (ESCRITA)
| Coluna | Regra |
|--------|-------|
| status | **Sempre 'draft'**, exceto teaser que passa QA → 'published' |
| is_active | **Sempre FALSE** (Laravel ativa) |
| price_snapshot_* | Copiar de ai_models no momento da geração |
| cost_usd | `(tokens_input/1M)*price_input + (tokens_output/1M)*price_output` |
| tokens_input/output | NULL se provider não retornar |
| raw_usage | `{"input_tokens": X, "output_tokens": Y}` mínimo |

### `tese_analysis_jobs` (ESCRITA)
- **Criar:** `INSERT ... ON DUPLICATE KEY UPDATE` (unique: tese_id+tribunal+section_type)
- **Claim:** `FOR UPDATE` atômico
- **Erro:** `attempts++`, re-enfileira ou marca 'error'

---

## Idempotência (Economia de Custo)

Antes de gerar uma seção, verificar se já existe com mesmos hashes:

```sql
SELECT id FROM tese_analysis_sections
WHERE tese_id = ? AND tribunal = ? AND section_type = ?
  AND source_hash = ? AND prompt_hash = ? AND ai_model_id = ?
ORDER BY generated_at DESC
LIMIT 1;
```

Se existir: **pular**. Para reprocessar, use `--force` para re-enfileirar e altere prompt/modelo se quiser nova geração.

### source_hash Determinístico (Múltiplos PDFs)

Para seções que usam múltiplos PDFs (ex: modulação):

```python
def compute_source_hash(acordaos: list[dict], textos: dict[int, str]) -> str:
    # Ordenar por: Principal primeiro, depois por id ASC
    sorted_acordaos = sorted(acordaos, key=lambda a: (
        0 if a['tipo'] == 'Principal' else 1,
        a['id']
    ))
    
    combined = "\n\n".join(textos[a['id']] for a in sorted_acordaos)
    return hashlib.sha256(combined.encode()).hexdigest()
```

> O marcador "--- Página N ---" faz parte do texto, garantindo estabilidade.

---

## Extração de PDF

```python
import fitz  # pymupdf

def extract_text(pdf_path: str) -> tuple[str, bool]:
    doc = fitz.open(pdf_path)
    pages = []
    for i, page in enumerate(doc):
        text = page.get_text().strip()
        if text:
            pages.append(f"--- Página {i+1} ---\n{text}")
    doc.close()
    
    full_text = "\n\n".join(pages)
    
    # Fallback: texto muito curto = provavelmente PDF-imagem
    if len(full_text) < 500:
        return full_text, False  # needs_ocr = True
    
    return full_text, True  # ok
```

Se `needs_ocr=True`: **marcar job como erro definitivo** (sem retentar):

```python
def mark_job_error_permanent(db, job_id: int, error_msg: str):
    cursor = db.cursor()
    cursor.execute("""
        UPDATE tese_analysis_jobs
        SET status = 'error',
            last_error = %s,
            locked_by = NULL,
            completed_at = NOW()
        WHERE id = %s
    """, (error_msg, job_id))
    db.commit()
```

> Isso evita que o worker fique "martelando" a mesma falha.

---

## Regras por Seção

| section_type | Acórdãos incluídos | Auto-publish |
|--------------|-------------------|--------------|
| teaser | Principal | ✅ Se passar QA |
| caso_fatico | Principal | ❌ |
| contornos_juridicos | Principal | ❌ |
| modulacao | Principal + EDs + Modulação de Efeitos | ❌ |
| tese_explicada | Principal | ❌ |

### Modulação: regra completa

1. Buscar acordãos com:
   ```sql
   tipo IN ('Principal', 'Embargos de Declaração', 'Modulação de Efeitos')
   ```
2. Ordenar: Principal primeiro, depois por id ASC
3. Concatenar textos
4. Se texto total > limite do modelo: priorizar ementa/dispositivo ou truncar
5. Se não identificar modulação no texto:
   ```
   "Não foi identificada modulação de efeitos no(s) acórdão(s) analisado(s)."
   ```

---

## Cálculo de Custo por Provider

```python
def extract_usage(provider: str, response) -> dict:
    """Extrai tokens de forma defensiva (SDKs mudam!)."""
    try:
        if provider == 'openai':
            usage = getattr(response, 'usage', None)
            return {
                'input_tokens': getattr(usage, 'prompt_tokens', None),
                'output_tokens': getattr(usage, 'completion_tokens', None)
            }
        elif provider == 'anthropic':
            usage = getattr(response, 'usage', None)
            return {
                'input_tokens': getattr(usage, 'input_tokens', None),
                'output_tokens': getattr(usage, 'output_tokens', None)
            }
        elif provider == 'google':
            meta = getattr(response, 'usage_metadata', None)
            return {
                'input_tokens': getattr(meta, 'prompt_token_count', None),
                'output_tokens': getattr(meta, 'candidates_token_count', None)
            }
    except Exception:
        pass
    return {'input_tokens': None, 'output_tokens': None}

def calculate_cost(tokens: dict, price_in: float, price_out: float) -> float | None:
    if tokens['input_tokens'] is None:
        return None
    return round(
        (tokens['input_tokens'] / 1_000_000) * price_in +
        (tokens['output_tokens'] / 1_000_000) * price_out,
        6
    )
```

---

## CLI: Modos de Operação

### `--ui` (Interface Rich)
```bash
python main.py --ui
```

Funcionalidades:
- Listar temas com acórdãos (filtro por tribunal)
- Selecionar temas para enfileirar
- Escolher modelo de IA
- Criar jobs (queued)
- Monitorar status de jobs/seções

### `--worker` (Processamento)
```bash
python main.py --worker [--id WORKER_ID]
```

#### Fluxo do Worker (Pseudocódigo)

```python
def worker_loop(db, worker_id: str):
    while True:
        job = claim_job(db, worker_id)
        if not job:
            time.sleep(5)
            continue
        
        try:
            process_job(db, job)
        except Exception as e:
            # Erro inesperado: retry normal
            mark_job_error(db, job['id'], str(e))


def process_job(db, job: dict):
    # 1. Carregar tese e acórdãos
    tese = load_tese(job['tribunal'], job['tese_id'])
    acordaos = load_acordaos(job['tese_id'], job['tribunal'])
    
    if not acordaos:
        mark_job_error_permanent(db, job['id'], "Nenhum acórdão encontrado")
        return
    
    # 2. Baixar PDFs e extrair texto
    textos = {}
    for acordao in acordaos:
        local_path = download_pdf(acordao['s3_key'])  # retorna path local ou None
        # NOTA: download_pdf() deve diferenciar:
        #   - 404/NoSuchKey → retorna None (erro permanente)
        #   - Timeout/conexão → raise Exception (retry)
        if not local_path:
            mark_job_error_permanent(db, job['id'], 
                f"PDF não encontrado no S3: {acordao['s3_key']}")
            return
        
        text, ok = extract_text(local_path)
        if not ok:
            # PDF sem texto legível: erro PERMANENTE
            mark_job_error_permanent(db, job['id'], 
                f"PDF {acordao['numero_acordao']} sem texto legível (precisa OCR)")
            return
        
        textos[acordao['id']] = text
    
    # 3. Determinar seções a gerar
    sections = get_sections_for_job(job['section_type'])
    # section_type='all' → ['teaser','caso_fatico','contornos_juridicos','modulacao','tese_explicada']
    # section_type='teaser' → ['teaser']
    
    # 4. Processar cada seção
    all_skipped = True
    for section_type in sections:
        # 4a. Selecionar acórdãos para esta seção
        section_acordaos = filter_acordaos_for_section(acordaos, section_type)
        # NOTA: filter_acordaos_for_section() deve retornar:
        #   - teaser/caso_fatico/contornos_juridicos/tese_explicada → só tipo='Principal'
        #   - modulacao → tipo IN ('Principal','Embargos de Declaração','Modulação de Efeitos')
        
        if not section_acordaos:
            # Só acontece se Principal foi deletado entre enqueue e processamento
            mark_job_error_permanent(db, job['id'], 
                f"Acórdão Principal não encontrado para seção {section_type}")
            return
        
        # 4b. Computar hashes
        source_hash = compute_source_hash(section_acordaos, textos)
        prompt_hash = compute_prompt_hash(section_type)
        
        # 4c. Verificar idempotência (worker sempre aplica)
        if section_exists(db, job, section_type, source_hash, prompt_hash):
            continue  # skip por idempotência
        
        all_skipped = False
        
        # 4d. Gerar com IA
        combined_text = combine_texts(section_acordaos, textos)
        prompt = load_prompt(section_type).format(texto=combined_text, tema=tese['numero'])
        
        try:
            response = call_ai(job['ai_model_id'], prompt)
        except Exception as e:
            # Erro de API: retry (pode ser rate limit)
            raise Exception(f"Erro IA: {e}")
        
        # 4e. QA para teaser
        status = 'draft'
        if section_type == 'teaser':
            qa_ok, qa_msg = validar_teaser(response.content, job['tribunal'], tese['numero'])
            if qa_ok:
                status = 'published'
            # Se não passar QA: fica draft (admin revisa)
        
        # 4f. Salvar seção
        save_section(db, job, section_type, response, source_hash, prompt_hash, status)
    
    # 5. Finalizar job (mesmo se tudo foi skip)
    mark_job_done(db, job['id'])


def mark_job_error_permanent(db, job_id: int, error_msg: str):
    """Erro definitivo (sem retry). Mantém started_at para auditoria."""
    cursor = db.cursor()
    cursor.execute("""
        UPDATE tese_analysis_jobs
        SET status = 'error',
            last_error = %s,
            locked_by = NULL,
            completed_at = NOW()
        WHERE id = %s
    """, (error_msg, job_id))
    db.commit()


def mark_job_error(db, job_id: int, error_msg: str):
    """Erro com retry (incrementa attempts)."""
    cursor = db.cursor()
    cursor.execute("""
        UPDATE tese_analysis_jobs
        SET attempts = attempts + 1,
            last_error = %s,
            locked_by = NULL,
            started_at = NULL,
            status = IF(attempts + 1 >= max_attempts, 'error', 'queued')
        WHERE id = %s
    """, (error_msg, job_id))
    db.commit()


def mark_job_done(db, job_id: int):
    cursor = db.cursor()
    cursor.execute("""
        UPDATE tese_analysis_jobs
        SET status = 'done',
            completed_at = NOW(),
            locked_by = NULL
        WHERE id = %s
    """, (job_id,))
    db.commit()
```

**Roteamento de erros:**
| Tipo de erro | Função | Comportamento |
|--------------|--------|---------------|
| PDF sem texto (OCR) | `mark_job_error_permanent` | Erro definitivo, sem retry |
| Erro S3 404/NoSuchKey | `mark_job_error_permanent` | Erro definitivo |
| Erro S3 timeout/rede | `mark_job_error` | Retry até max_attempts |
| Acórdão não encontrado | `mark_job_error_permanent` | Erro definitivo |
| Erro de rede/API (rate limit) | `mark_job_error` | Retry até max_attempts |
| Erro de DB temporário | `mark_job_error` | Retry |
| Todas seções skip (idempotência) | `mark_job_done` | Job concluído ✓ |

### `--enqueue` (CLI direto)
```bash
python main.py --enqueue --tribunal STF --tema 1234 --model 4
python main.py --enqueue --tribunal STJ --tema 100-200 --model 4  # range
python main.py --enqueue --tribunal STF --all --model 4  # todos com acórdãos
```

Opções:
- `--section teaser` (default: all)
- `--force` (re-enfileira jobs done/error; não altera idempotência do worker)

---

## Criar Job (SQL)

### Modo Normal (não altera job existente)
```sql
INSERT INTO tese_analysis_jobs 
    (tese_id, tribunal, section_type, ai_model_id, status, created_at)
VALUES (?, ?, ?, ?, 'queued', NOW())
ON DUPLICATE KEY UPDATE id = id;
```

### Modo `--force` (reseta e re-enfileira)
```sql
INSERT INTO tese_analysis_jobs 
    (tese_id, tribunal, section_type, ai_model_id, status, created_at)
VALUES (?, ?, ?, ?, 'queued', NOW())
ON DUPLICATE KEY UPDATE
    status = 'queued',
    attempts = 0,
    last_error = NULL,
    locked_by = NULL,
    started_at = NULL,
    completed_at = NULL,
    ai_model_id = VALUES(ai_model_id),
    created_at = NOW();
```

---

## QA do Teaser

```python
def validar_teaser(content: str, tribunal: str, tema_numero: int) -> tuple[bool, str]:
    if len(content) < 100:
        return False, "Muito curto (< 100 chars)"
    if len(content) > 2000:
        return False, "Muito longo (> 2000 chars)"
    
    proibidas = [
        "como IA", "como modelo de linguagem", 
        "não tenho acesso", "não posso acessar",
        "baseado no texto fornecido"
    ]
    for p in proibidas:
        if p.lower() in content.lower():
            return False, f"Frase proibida: '{p}'"
    
    termos_validos = [
        tribunal.upper(), f"Tema {tema_numero}",
        "repercussão geral", "recursos repetitivos"
    ]
    if not any(t.lower() in content.lower() for t in termos_validos):
        return False, "Não menciona tribunal/tema"
    
    return True, "OK"
```

---

## Estrutura de Diretórios

```
home/vito/teses-scripts/analise_ia/
├── .env                  # chmod 600!
├── main.py               # CLI principal (--ui, --worker, --enqueue)
├── config.py             # Carrega .env
├── db.py                 # Conexão MySQL + queries
├── s3.py                 # Download S3
├── extractor.py          # Extração PDF + fallback
├── qa.py                 # Validações
├── cost.py               # Cálculo de tokens/custo
├── providers/
│   ├── base.py
│   ├── openai_provider.py
│   ├── anthropic_provider.py
│   └── google_provider.py
├── prompts/
│   ├── teaser.txt
│   ├── caso_fatico.txt
│   ├── contornos_juridicos.txt
│   ├── modulacao.txt
│   └── tese_explicada.txt
└── tmp/                  # PDFs temporários
```

---

## Dependências

```
mysql-connector-python
boto3
pymupdf
python-dotenv
openai
anthropic
google-generativeai
rich
click  # para CLI
```

---

## Modelos Disponíveis

| id | Provider | Nome | model_id | In/1M | Out/1M |
|----|----------|------|----------|-------|--------|
| 1 | anthropic | Claude Opus 4.5 | claude-4.5-opus-20260128 | $5.00 | $25.00 |
| 2 | openai | GPT-5.2 | gpt-5.2 | $1.75 | $14.00 |
| 3 | google | Gemini 3 Pro | gemini-3-pro | $2.00 | $12.00 |
| 4 | google | Gemini 3 Flash | gemini-3-flash | $0.50 | $3.00 |

---

## Checklist de Implementação

### Core
- [ ] config.py: carregar .env
- [ ] db.py: conexão + queries (load_tese, load_acordaos, etc)
- [ ] s3.py: download_pdf (diferenciar 404 vs timeout)
- [ ] extractor.py: extract_text com fallback
- [ ] providers/*.py: chamadas às APIs
- [ ] cost.py: extract_usage + calculate_cost
- [ ] qa.py: validar_teaser
- [ ] main.py --enqueue: criar jobs
- [ ] main.py --worker: claim + processar
- [ ] main.py --ui: interface Rich
- [ ] prompts/*.txt: prompts por seção

### Extras (recomendado)
- [ ] requirements.txt ou pyproject.toml
- [ ] `--dry-run`: só mostra o que faria (enqueue/worker)
- [ ] Logs estruturados por job_id (JSON ou formato para grep)
