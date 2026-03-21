# Prompt: Implementar JSON-LD Article + Paywall Markup nas páginas de tese

## Contexto

O site **Teses & Súmulas** (tesesesumulas.com.br) tem ~200 teses STF com análise jurídica original por IA chamada "Decifrando a tese". O conteúdo IA é renderizado server-side no HTML para TODOS os visitantes (incluindo Googlebot), mas visualmente blurred via CSS (classe `.premium-content-blur`) para não-autenticados.

**Problema**: sem o markup `isAccessibleForFree` do Schema.org, o Google pode interpretar esse padrão como cloaking. A documentação oficial diz: *"Esses dados estruturados ajudam o Google a diferenciar esse material das técnicas de cloaking."*

Referência: https://developers.google.com/search/docs/appearance/structured-data/paywalled-content

## O que fazer

### 1. Criar componente Blade `<x-tese-article-schema>`

Arquivo: `resources/views/components/tese-article-schema.blade.php`

Seguir o padrão existente em `resources/views/components/breadcrumb.blade.php` (JSON-LD em `<script type="application/ld+json">`).

Props necessárias:
- `$label` (string) — headline
- `$description` (string) — meta description
- `$tese` (object) — objeto da tese com campos variáveis por tribunal
- `$tribunal` (string) — 'STF', 'STJ', etc.
- `$aiSections` (Collection) — seções IA (pode estar vazia)

JSON-LD a gerar (SOMENTE quando `$aiSections->isNotEmpty()`):

```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{ $label }}",
  "description": "{{ $description }}",
  "author": {
    "@type": "Organization",
    "name": "Teses & Súmulas",
    "url": "https://tesesesumulas.com.br"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Teses & Súmulas"
  },
  "datePublished": "<data da tese — ver abaixo>",
  "dateModified": "<generated_at da IA mais recente>",
  "mainEntityOfPage": "{{ url()->current() }}",
  "isAccessibleForFree": false,
  "hasPart": [
    {
      "@type": "WebPageElement",
      "isAccessibleForFree": false,
      "cssSelector": ".premium-content-blur"
    }
  ]
}
```

Se `$aiSections->isEmpty()`, o componente não renderiza nada.

**Datas por tribunal** (campo de `$tese`):
- STF: `$tese->aprovadaEm`
- STJ: `$tese->atualizadaEm`
- TST/TNU: `$tese->julgadoEm` (pode não existir — usar fallback `null`)

Para `dateModified`, a tabela `tese_analysis_sections` tem campo `generated_at`. O controller carrega as seções assim:
```php
$ai_sections = DB::table('tese_analysis_sections')
    ->where('tese_id', $tese->id)
    ->where('tribunal', $tribunal)
    ->orderBy('generated_at', 'desc')
    ->get()
    ->unique('section_type')
    ->pluck('content', 'section_type');
```

O `pluck()` descarta `generated_at`. Para ter essa data disponível, será preciso ajustar o controller para também extrair a data mais recente. Opção simples: adicionar uma query separada ou modificar a query existente para também pegar o max `generated_at`.

### 2. Incluir o componente na view `tese.blade.php`

Arquivo: `resources/views/front/tese.blade.php`

Adicionar ANTES do `</head>` ou junto ao breadcrumb (na seção adequada):
```blade
<x-tese-article-schema 
    :label="$label" 
    :description="$description" 
    :tese="$tese" 
    :tribunal="$tribunal" 
    :ai-sections="$ai_sections" 
/>
```

Nota: a view `tese.blade.php` é usada tanto para STF quanto STJ. As views `tese_tst.blade.php` e `tese_tnu.blade.php` ainda não têm conteúdo IA — incluir o componente nelas fica para depois.

### 3. Melhorar meta description quando há conteúdo IA

Arquivo: `app/Http/Controllers/TesePageController.php`

Método `generateMetaDescription()` (linha ~370). Quando `$ai_sections->isNotEmpty()`, adicionar ao final " Com análise jurídica por IA." antes de truncar.

Assinatura atual:
```php
private function generateMetaDescription($tribunal, $numero, $tema_texto, $tese_texto)
```

Adicionar parâmetro `$hasAi = false`:
```php
private function generateMetaDescription($tribunal, $numero, $tema_texto, $tese_texto, $hasAi = false)
```

Se `$hasAi`, reservar espaço para o sufixo " Com análise jurídica." (~24 chars) reduzindo o `$espaco_disponivel`.

Atualizar a chamada ao método para passar `$ai_sections->isNotEmpty()`.

## Arquivos envolvidos (resumo)

| Arquivo | Ação |
|---------|------|
| `resources/views/components/tese-article-schema.blade.php` | CRIAR |
| `resources/views/front/tese.blade.php` | Incluir `<x-tese-article-schema>` |
| `app/Http/Controllers/TesePageController.php` | Extrair `generated_at` + passar flag IA para meta description |

## Validação

1. Rodar testes existentes: `php artisan test --compact --configuration=phpunit.mysql.xml tests/MySQL/TesePageMysqlTest.php`
2. Criar teste para o componente JSON-LD (verificar que o schema aparece em teses com IA e não aparece em teses sem IA)
3. Validar o JSON-LD gerado em https://search.google.com/structured-data/testing-tool com uma página de exemplo
4. Rodar `vendor/bin/pint --dirty --format agent`

## O que NÃO fazer nesta fase

- NÃO alterar `aria-hidden="true"` — correto para acessibilidade, sem impacto SEO
- NÃO mexer nas views `tese_tst.blade.php` / `tese_tnu.blade.php` (ainda sem IA)
- NÃO alterar títulos ou descriptions das páginas de listagem
- NÃO refatorar os 8 controllers de listagem
- NÃO alterar o sitemap
