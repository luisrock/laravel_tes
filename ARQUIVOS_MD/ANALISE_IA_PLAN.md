# Fase 2 - Análise com IA

**Documento:** ANALISE_IA_PLAN.md  
**Versão:** 2.5  
**Status:** ✅ Aprovado  
**Atualizado:** 04/02/2026  

---

## Relação com Outros Planos

| Plano | Relação |
|-------|---------|
| [ANALISE_DO_PRECEDENTE_PLAN.md](./ANALISE_DO_PRECEDENTE_PLAN.md) | Fase 1 (Upload) ✅ |
| [ASSINATURA_PLAN.md](./ASSINATURA_PLAN.md) | Integração via `feature_key` |

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────┐
│               SCRIPT PYTHON (servidor)                   │
│  - Roda no mesmo servidor da DB                          │
│  - Lê jobs da tabela, processa, salva resultados         │
│  - NÃO ativa versões (apenas cria com published)         │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                     BANCO MySQL                          │
│  - tese_analysis_sections (histórico + is_active)        │
│  - tese_analysis_jobs (fila de processamento)            │
│  - ai_models (modelos disponíveis)                       │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                   SITE LARAVEL                           │
│  - Exibe seções ativas (is_active=true)                  │
│  - Admin: histórico, ativar versão, ver custos           │
│  - ÚNICO responsável por ativar versões                  │
└─────────────────────────────────────────────────────────┘
```

---

## Banco de Dados

### Tabela `ai_models`

```sql
CREATE TABLE ai_models (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    provider ENUM('openai', 'anthropic', 'google') NOT NULL,
    name VARCHAR(100) NOT NULL,
    model_id VARCHAR(100) NOT NULL,
    price_input_per_million DECIMAL(10,4),
    price_output_per_million DECIMAL(10,4),
    is_active BOOLEAN DEFAULT TRUE,
    deprecated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_model (provider, model_id)
);
```

### Tabela `tese_analysis_sections`

Cada registro = 1 geração de 1 seção. Histórico completo + controle de versão ativa.

```sql
CREATE TABLE tese_analysis_sections (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tese_id BIGINT NOT NULL,
    tribunal ENUM('STF', 'STJ') NOT NULL,
    section_type ENUM(
        'caso_fatico',
        'contornos_juridicos', 
        'modulacao',
        'tese_explicada',
        'teaser'
    ) NOT NULL,
    
    -- Conteúdo
    content MEDIUMTEXT NOT NULL,
    status ENUM('draft', 'reviewed', 'published') DEFAULT 'draft',
    is_active BOOLEAN DEFAULT FALSE,
    
    -- Coluna gerada para garantir unicidade de is_active
    active_key VARCHAR(500) GENERATED ALWAYS AS (
        IF(is_active, CONCAT(tese_id, ':', tribunal, ':', section_type), NULL)
    ) STORED,
    
    -- Modelo e prompt (rastreabilidade)
    ai_model_id BIGINT NOT NULL,
    prompt_key VARCHAR(100) NULL,
    prompt_hash CHAR(64) NULL,
    source_hash CHAR(64) NULL,
    
    -- Tokens e custo
    tokens_input INT NULL,
    tokens_output INT NULL,
    cost_usd DECIMAL(10,6) NULL,
    
    -- Snapshot de preço (para auditoria histórica)
    price_snapshot_input DECIMAL(10,4) NULL,
    price_snapshot_output DECIMAL(10,4) NULL,
    
    -- Metadados de execução
    provider_request_id VARCHAR(100) NULL,
    latency_ms INT NULL,
    finish_reason VARCHAR(30) NULL,
    raw_usage JSON NULL,
    
    -- Erro (se falhou)
    error_message TEXT NULL,
    
    -- Auditoria
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activated_by BIGINT NULL,
    activated_at TIMESTAMP NULL,
    
    FOREIGN KEY (ai_model_id) REFERENCES ai_models(id),
    
    -- Índices
    INDEX idx_tese_section (tese_id, tribunal, section_type),
    INDEX idx_section_history (tese_id, tribunal, section_type, generated_at),
    UNIQUE KEY uniq_active_key (active_key)
);
```

> **Garantia de unicidade:** A coluna gerada `active_key` só é preenchida quando `is_active=TRUE`. O índice único impede duas versões ativas para a mesma seção no próprio banco.

> **Requisito:** Tabelas InnoDB; transações habilitadas (padrão MySQL 8+).

### Tabela `tese_analysis_jobs`

Controle de processamento com suporte a retomada e retry.

```sql
CREATE TABLE tese_analysis_jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tese_id BIGINT NOT NULL,
    tribunal ENUM('STF', 'STJ') NOT NULL,
    section_type ENUM(
        'all',
        'caso_fatico',
        'contornos_juridicos',
        'modulacao',
        'tese_explicada',
        'teaser'
    ) NOT NULL DEFAULT 'all',
    
    ai_model_id BIGINT NOT NULL,
    
    status ENUM('queued', 'running', 'done', 'error') DEFAULT 'queued',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    last_error TEXT NULL,
    locked_by VARCHAR(50) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    
    FOREIGN KEY (ai_model_id) REFERENCES ai_models(id),
    UNIQUE KEY unique_job (tese_id, tribunal, section_type),
    INDEX idx_status_created (status, created_at)
);
```

> **Sem NULL:** `section_type` usa `'all'` em vez de NULL para garantir unicidade correta.

---

## Seções da Análise

| section_type | Descrição | Visibilidade | Auto-publish |
|--------------|-----------|--------------|--------------|
| `teaser` | Preview curto | Público | ✅ Sim (com QA) |
| `caso_fatico` | Resumo do caso | Público | ❌ Não |
| `contornos_juridicos` | Análise jurídica | Assinantes | ❌ Não |
| `modulacao` | Efeitos da decisão | Assinantes | ❌ Não |
| `tese_explicada` | Explicação didática | Assinantes | ❌ Não |

---

## Regra de Ativação

| Quem | Pode ativar? | Observação |
|------|--------------|------------|
| **Laravel (Admin)** | ✅ Sim | Único responsável por `is_active=true` |
| **Script Python** | ❌ Não | Pode publicar teaser (com QA), demais ficam draft |

Isso evita concorrência entre admin e script.

---

## Controle de `is_active` (Laravel)

```php
public function ativarVersao(TeseAnalysisSection $section)
{
    // 1. Só pode ativar se estiver publicado
    if ($section->status !== 'published') {
        return back()->withErrors(['error' => 'Só é possível ativar seções publicadas']);
    }
    
    DB::transaction(function () use ($section) {
        // 2. Desativa todas
        TeseAnalysisSection::where('tese_id', $section->tese_id)
            ->where('tribunal', $section->tribunal)
            ->where('section_type', $section->section_type)
            ->update(['is_active' => false]);
        
        // 3. Ativa a selecionada
        $section->update([
            'is_active' => true,
            'activated_by' => auth()->id(),
            'activated_at' => now(),
        ]);
    });
    
    return back()->with('success', 'Versão ativada');
}
```

> **Regra:** Só pode ativar seções com `status='published'`.

---

## Claim Atômico de Job (Python)

Para múltiplos workers, usar `FOR UPDATE` (padrão canônico):

```python
def claim_job(db, worker_id: str) -> dict | None:
    """Pega próximo job disponível com lock."""
    cursor = db.cursor(dictionary=True)
    
    # IMPORTANTE: desabilitar autocommit para FOR UPDATE funcionar
    db.start_transaction()
    
    # 1. Selecionar com lock
    cursor.execute("""
        SELECT id FROM tese_analysis_jobs
        WHERE status = 'queued'
        ORDER BY created_at ASC
        LIMIT 1
        FOR UPDATE
    """)
    row = cursor.fetchone()
    if not row:
        db.rollback()
        return None
    
    job_id = row['id']
    
    # 2. Atualizar por id (confirma que ainda é queued)
    cursor.execute("""
        UPDATE tese_analysis_jobs
        SET status = 'running', started_at = NOW(), locked_by = %s
        WHERE id = %s AND status = 'queued'
    """, (worker_id, job_id))
    
    if cursor.rowcount == 0:
        db.rollback()
        return None
    
    db.commit()
    
    # 3. Retornar job completo
    cursor.execute("SELECT * FROM tese_analysis_jobs WHERE id = %s", (job_id,))
    return cursor.fetchone()


def mark_job_done(db, job_id: int):
    """Marca job como concluído."""
    cursor = db.cursor()
    cursor.execute("""
        UPDATE tese_analysis_jobs
        SET status = 'done', completed_at = NOW(), locked_by = NULL
        WHERE id = %s
    """, (job_id,))
    db.commit()


def mark_job_error(db, job_id: int, error_msg: str):
    """Incrementa attempts e re-enfileira ou marca erro final."""
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
```

> **Nota:** `attempts` = número de execuções que falharam.

---

## raw_usage JSON (formato limitado)

```json
{"input_tokens": 1234, "output_tokens": 567, "cache_hit": false}
```

A transação + constraint `uniq_active_key` garantem que nunca haverá duplicidade.

---

## QA Mínimo para Teaser

```python
def validar_teaser(content: str, tribunal: str, tema_numero: int) -> tuple[bool, str]:
    # Tamanho
    if len(content) < 100:
        return False, "Muito curto (< 100 chars)"
    if len(content) > 2000:
        return False, "Muito longo (> 2000 chars)"
    
    # Frases proibidas (alucinação de IA)
    proibidas = [
        "como IA", "como modelo de linguagem", 
        "não tenho acesso", "não posso acessar",
        "baseado no texto fornecido"
    ]
    for p in proibidas:
        if p.lower() in content.lower():
            return False, f"Frase proibida: '{p}'"
    
    # Deve mencionar tribunal OU tema OU tipo de recurso
    termos_validos = [
        tribunal.upper(),
        f"Tema {tema_numero}",
        "repercussão geral",
        "recursos repetitivos"
    ]
    if not any(t.lower() in content.lower() for t in termos_validos):
        return False, "Não menciona tribunal/tema"
    
    return True, "OK"
```

---

## Regra para Modulação

Se não houver ED ou não for identificada modulação:

```
"Não foi identificada modulação de efeitos no(s) acórdão(s) analisado(s)."
```

Isso deve constar no prompt.

---

## Fluxo de Processamento (Python)

```
1. Loop principal:
   job = claim_job(db, worker_id)
   if not job:
       sleep(5); continue

2. Processar job:
   a. Buscar acordãos do tema (Principal + ED se modulação)
   b. Baixar PDF do S3, extrair texto
   c. Calcular source_hash do texto
   d. Determinar seções a gerar:
      - Se section_type='all': todas as 5
      - Senão: apenas a especificada
   e. Para cada seção:
      - Chamar API do modelo
      - Salvar em tese_analysis_sections
        (status='draft', is_active=FALSE)
      - Se teaser + passou QA: status='published'
   f. mark_job_done(db, job['id'])

3. Em caso de erro:
   mark_job_error(db, job['id'], str(error))
```

---

## Script Python - Estrutura

```
home/vito/teses-scripts/analise_ia/
├── main.py              # CLI principal
├── config.py            # Credenciais DB/S3/APIs
├── db.py                # Conexão MySQL
├── s3.py                # Download de PDFs
├── extractor.py         # Extração de texto
├── qa.py                # Validações QA
├── providers/
│   ├── base.py          # Interface
│   ├── openai.py
│   ├── anthropic.py
│   └── google.py
└── prompts/
    ├── teaser.txt
    ├── caso_fatico.txt
    ├── contornos_juridicos.txt
    ├── modulacao.txt
    └── tese_explicada.txt
```

---

## Checklist

### Banco de Dados (Laravel)
- [ ] Migration `ai_models`
- [ ] Migration `tese_analysis_sections` (com coluna gerada)
- [ ] Migration `tese_analysis_jobs`
- [ ] Seed modelos iniciais
- [ ] Models Eloquent

### Script Python
- [ ] Estrutura de diretórios
- [ ] Conexão MySQL
- [ ] Download S3
- [ ] Extração de texto
- [ ] Provider OpenAI
- [ ] Provider Anthropic
- [ ] Provider Google
- [ ] Prompts por seção
- [ ] Job runner com retry
- [ ] QA para teaser
- [ ] CLI (rodar batch, rodar tema específico)

### Admin Laravel
- [ ] Listar temas com análises
- [ ] Ver histórico por seção
- [ ] Ativar versão (transação)
- [ ] Mudar status (draft → reviewed → published)
- [ ] Exibir custos totais

### Frontend
- [ ] Exibir seções ativas (is_active=true, status='published')
- [ ] Paywall para seções premium

---

## Modelos Iniciais

| Provider | Nome | model_id | Input/1M | Output/1M |
|----------|------|----------|----------|-----------|
| anthropic | Claude Opus 4.5 | claude-4.5-opus-20260128 | $5.00 | $25.00 |
| openai | GPT-5.2 | gpt-5.2 | $1.75 | $14.00 |
| google | Gemini 3 Pro | gemini-3-pro | $2.00 | $12.00 |
| google | Gemini 3 Flash | gemini-3-flash | $0.50 | $3.00 |

---

## Próximos Passos

1. ✅ Aprovar este plano
2. Criar migrations no Laravel
3. Definir prompts para cada seção
4. Implementar script Python
5. Testar com 1 tema
6. Rodar batch para todos os temas

