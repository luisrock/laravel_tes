# Plano de Implementação: Paywall e Interface para Análises de IA

**Objetivo:** Estruturar a exibição das seções geradas pela IA (`tese_analysis_sections`) nas diversas camadas do site, garantindo atração para o usuário (teaser), bloqueio eficiente para conteúdo premium (paywall) do ponto de vista do plano de negócios, mas mantendo a indexação rica para os buscadores (SEO).

---

## 1. Mapeamento de Exibição (Onde a IA aparece)

Conforme sua descrição, um Tema/Tese aparece em 4 locais principais. A estratégia de UI sugerida é:

### 1.1. Na Busca (Cards)
*   **Exemplo:** `/pesquisa?q=adocao`
*   **Comportamento:** O Card do resultado deve exibir apenas uma "badge" ou etiqueta visual estilosa indicando: `✨ Resumo com IA disponível`.
*   **Opcional:** Se houver espaço, podemos colocar as 2 primeiras linhas úteis do `teaser` da IA, com um _fade-out_ (degradê sumindo) e um link "Exibir Análise Completa" apontando para a página isolada do Tema.

### 1.2. Na Listagem do Tribunal
*   **Exemplo:** `/teses/stf`
*   **Comportamento:** Igual ao da busca. Uma badge discreta `✨ IA` ou o `teaser` muito curto com um "Ler mais", forçando o clique para a página do tema isolado.

### 1.3. Página de Pesquisas Prontas (Agrupamento)
*   **Exemplo:** `/tema/abuso-de-autoridade`
*   **Comportamento:** Aqui costuma se concentrar a pesquisa pronta. Pode exibir as teses completas deste sub-tema. A IA deve aparecer através do texto do `teaser` integralmente aberto ao público (pois atrai o leitor). Abaixo do teaser, um _Call-to-Action_ (CTA) "Veja a Análise e os Contornos Jurídicos completos deste tema (exclusivo para Assinantes Pro e Premium)" enviando para a página isolada.

### 1.4. Página Isolada do Tema/Tese (O Destino Final)
*   **Exemplo:** `/tese/stf/788`
*   **Comportamento:** Este é o coração do conteúdo. 
    *   **Público (Não-logado / Não-assinante):** Exibe apenas o `teaser` (Ponto Central) livremente como atrativo. As seções `caso_fatico`, `contornos_juridicos`, `modulacao` e `tese_explicada` existirão no código-fonte, mas com seus parágrafos visualmente ofuscados por um **Hard Paywall Blur** puro de CSS que os torna ilegíveis. Os Títulos dos tópicos ficam à vista para atrair curiosidade.
    *   **Assinante (Logado) e Admins:** Todo o conteúdo é renderizado de forma limpa, sem blur nem cadeados.
    *   **Call-to-Action / Paywall Card:** Para os públicos, um card escuro pedindo "Assine o T&S" para ler a "_Análise Jurídica Exclusiva_" flutuará sobre o conteúdo borrado com `position: sticky`, acompanhando a rolagem (scroll) até o fim.
    *   **Disclaimer Obrigatório**: Antes do rodapé, uma nota de advertência em destaque alerta o usuário de que os comentários gerados não possuem caráter oficial nem substituem a leitura do inteiro teor.
    *   **Acórdãos Originais:** Ao final da seção de IA, haverá links gerados dinamicamente via Model (`presigned_url`) do S3 AWS para visualizar os Acórdãos PDFs base formatados pelas colunas correspondentes (RE/AgRg, etc), **também de acesso exclusivo para Assinantes (ocultos para visitantes).**

---

## 2. Estratégia de SEO vs. Paywall (CSS/JS)

Sua sugestão de injetar no código server-side (Blade) todo o conteúdo e usar JS/CSS para cobri-lo é a exata estratégia usada pelas grandes empresas de notícias (_Folha, NYT, JOTA_). Para o Googlebot, o texto do acórdão analisado estará todo ali, o que joga o rankeamento do site nas alturas.

### Como funciona tecnicamente na Blade (`/tese/stf/788`):
1.  **Server-Side (Laravel):** Independentemente de ser assinante ou não, passamos todas as seções (ativas e publicadas) para a View.
2.  **Schema de SEO (`JSON-LD`):** Marcamos o conteúdo como paywalled para não sermos punidos pelo Google (o Google permite paywall se você colocar a flag de que é paywalled via Schema Markup). Isso diz para o buscador: "O texto está aqui, eu deixo você ler para rankear, mas o meu usuário humano precisa pagar pra ver".
3.  **Client-Side (Tailwind + Alpine/JS):** 
    *   Se o usuário for assinante (planos Pro ou Premium, validado via `$has_access` integrado ao `isSubscriber()` e Admins): o conteúdo é limpo.
    *   Se não for assinante: a classe aplica o CSS customizado de ofuscação visual nos textos com pseudo-elemento/overlay de `backdrop-blur` (embaçado) e traz a caixa flutuante do **Assine Agora o T&S**.

**Consideração de Segurança (The Bypass):**
Como o conteúdo real vai no HTML, usuários muito avançados (1%) poderiam abrir o DevTools (F12) e remover o CSS de blur para ler. Porém:
- Em se tratando do seu público comercial, essa parcela é irrelevante.
- O ganho exponencial de SEO indexando textos gerados por IA (que são ricos em termos jurídicos e "explicam" os casos pro Googlebot) compensa infinitamente a eventual perda desse 1% de curiosos técnicos.

---

## 3. Estado Atual e Próximos Passos

### ✅ Implementado

1.  ✅ **Model/Controller:** `TesePageController` englobando `$tema->analysis_sections()` em *Eager Loading*.
2.  ✅ **View de Listagem (1.1, 1.2):** Componente `<x-ia-badge />` com badge "Decifrando a tese" (link limpo, sem âncora).
3.  ✅ **View Individual (1.4):** Estrutura com seções protegidas (blur CSS).
4.  ✅ **SEO Schema:** `isAccessibleForFree: false` nas áreas Premium.
5.  ✅ **Termos de Serviço:** Disclaimers jurídicos implementados.
6.  ✅ **Registerwall (substitui Paywall temporariamente):**
    - `$has_access` agora via Spatie `hasPermissionTo('view_ai_analysis')`.
    - Se role `registered` tem `view_ai_analysis` → **registerwall** (qualquer logado acessa).
    - Se não → **paywall** (somente subscriber/premium/admin acessam).
    - CTA dinâmico: "Criar Conta Grátis" (registerwall) ou "Assine o T&S" (paywall).
    - Registerwall menciona navegação sem anúncios como benefício.
7.  ✅ **Registro público habilitado** (Fortify `Features::registration()`).
8.  ✅ **Admin via Spatie:** Todo o codebase usa `hasRole('admin')` — constante `tes_constants.admins` eliminada.
9.  ✅ **Link "Assinar"** no navbar comentado (desabilitado até decisão de monetização).

### 🔜 Próximos Passos (Deploy)

1.  **Testar no browser local** — visitar tese STF com IA sem login (ver registerwall) e com login (ver conteúdo completo).
2.  `git push` — dispara deploy automático via Vito Deploy.
3.  **Em produção**, rodar: `php artisan db:seed --class=RolesAndPermissionsSeeder` para criar permissões/roles.
4.  **Em `/admin/roles`**, atribuir role `admin` ao seu próprio usuário.
5.  **Futuro:** Para ativar paywall, basta ir em `/admin/roles` → `registered` → desmarcar `view_ai_analysis`.

