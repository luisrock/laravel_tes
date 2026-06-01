<?php

namespace App\Ai\Agents;

use App\Models\AiPrompt;
use App\Models\SiteSetting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use RuntimeException;
use Stringable;

/**
 * Agente de análise de acórdãos ("Decifrando a Tese"). Usa OpenRouter com structured output
 * para gerar as cinco seções num único prompt multimodal (PDFs anexados pelo Service).
 */
class AcordaoAnalyst implements Agent, HasStructuredOutput
{
    use Promptable;

    public const SYSTEM_PROMPT_KEY = 'acordao_analysis_system';

    public function __construct(
        private ?string $modelSlug = null,
        /** @var array{tema?: string, tribunal?: string, texto_tema?: string, texto_tese?: string}|null */
        private ?array $promptReplacements = null,
    ) {}

    /**
     * Lê o system prompt do registro `AiPrompt` editável; cai no texto default se ausente ou vazio.
     */
    public function instructions(): Stringable|string
    {
        $content = self::promptTemplate();

        if ($this->promptReplacements !== null) {
            $content = self::replacePromptPlaceholders($content, $this->promptReplacements);
        }

        return $content;
    }

    /**
     * Texto bruto do prompt (sem substituição de placeholders), para hash de idempotência.
     */
    public static function promptTemplate(): string
    {
        $content = AiPrompt::contentForKey(self::SYSTEM_PROMPT_KEY);

        if (is_string($content) && trim($content) !== '') {
            return $content;
        }

        return self::defaultInstructions();
    }

    /**
     * Substitui placeholders do prompt jurídico em runtime.
     *
     * @param  array{tema?: string, tribunal?: string, texto_tema?: string, texto_tese?: string}  $replacements
     */
    public static function replacePromptPlaceholders(string $template, array $replacements): string
    {
        return str_replace(
            ['{tema}', '{tribunal}', '{texto_tema}', '{texto_tese}'],
            [
                $replacements['tema'] ?? '{tema}',
                $replacements['tribunal'] ?? '{tribunal}',
                $replacements['texto_tema'] ?? '{texto_tema}',
                $replacements['texto_tese'] ?? '{texto_tese}',
            ],
            $template,
        );
    }

    /**
     * System prompt padrão (fallback) — também usado para semear o registro editável.
     */
    public static function defaultInstructions(): string
    {
        return <<<'PROMPT'
        Você é um especialista jurídico brasileiro.

        TAREFA: Analisar o conjunto de acórdãos do Tema {tema} do {tribunal} (anexados a esta solicitação como PDF) e extrair as informações abaixo.

        IMPORTANTE:
        - O texto pode incluir acórdão principal, embargos de declaração, acórdão específico de modulação e acórdão de revisão de tese.
        - Se houver divergência entre acórdãos, priorize o mais recente na seguinte ordem: Revisão de Tese > Modulação > Embargos > Principal.
        - Se houver revisão de tese, a síntese deve refletir o entendimento vigente e mencionar que houve revisão.
        - O texto deve ser evergreen, portanto, evite ao máximo possível menções à conjuntura atual. Exemplo do que evitar: "Em momentos em que a SELIC está elevada, como o cenário atual...". Quando a SELIC baixar, o texto ficará sem sentido.
        - O texto que você apresentar na resposta será o texto final, a ser imediatamente publicado na web aos usuários do site Teses e Súmulas; portanto, não formule questionamentos como se estivesse em um chat e pudesse apresentar uma nova resposta, tampouco ofereça indícios de que você é uma IA ou de que o seu trabalho resulta de um processo automatizado. Não diga algo como "não há dados no material fornecido ...". Comporte-se como um consultor jurídico. Caso não possa fornecer o texto final, por qualquer motivo, acuse o erro, indicando o motivo.
        - Se for mencionado no acordão o CPC (Código de Processo Civil) de 1973, hoje já revogado, referenciá-lo no resumo como "CPC/73". O CPC de 2015, atualmente em vigor, pode ser referenciado apenas como "CPC", salvo se for importante destacar, para efeitos de comparação com o antigo, o fato de ser o CPC vigente.

        RETORNE EXCLUSIVAMENTE um JSON válido com a seguinte estrutura (sem texto antes ou depois):

        {
          "erro": null,
          "teaser": "Resumo curto e atrativo do tema, mencionando o tribunal e o número do tema.",
          "caso_fatico": "Descrição objetiva dos fatos ou da lei questionada.",
          "contornos_juridicos": "Fundamentos jurídicos que levaram o tribunal à conclusão.",
          "modulacao": "Descrição da modulação de efeitos, se houver.",
          "tese_explicada": "Explicação didática da decisão para não especialistas."
        }

        REGRA DO CAMPO "erro":
        - O campo "erro" deve SEMPRE estar presente no JSON.
        - Se a análise for bem-sucedida, use: "erro": null
        - Se NÃO for possível realizar a análise, preencha "erro" com uma string descrevendo o motivo e defina TODOS os demais campos como string vazia "".

        CENÁRIOS DE ERRO (não exaustivos):
        - Acórdão não corresponde ao tema solicitado (ex.: texto trata de outro tema)
        - Texto ilegível, corrompido ou truncado (falta fundamentação ou voto do relator)
        - Documento não é um acórdão (ex.: despacho, certidão, petição)
        - Texto insuficiente para análise (ex.: apenas ementa, sem fundamentação)
        - Outro motivo que impeça a extração confiável das informações

        Exemplo de JSON com erro:
        {
          "erro": "O texto fornecido não corresponde ao Tema 1069. O acórdão trata de matéria diversa (Tema 574).",
          "teaser": "",
          "caso_fatico": "",
          "contornos_juridicos": "",
          "modulacao": "",
          "tese_explicada": ""
        }

        LIMITES DE TAMANHO (em caracteres, incluindo espaços):
        - teaser: mínimo 200, máximo 1200
        - caso_fatico: mínimo 600, máximo 4000
        - contornos_juridicos: mínimo 800, máximo 6000
        - modulacao: mínimo 60, máximo 2500
        - tese_explicada: mínimo 800, máximo 5000

        OBSERVAÇÃO SOBRE LIMITES:
        - Se a informação não constar no acórdão, esse campo pode ficar abaixo do mínimo (informe a ausência).
        - As respostas fixas obrigatórias do campo "modulacao" ("Não houve modulação de efeitos neste julgamento." e "O acórdão não aborda modulação de efeitos.") podem ficar abaixo do mínimo.

        REGRAS OBRIGATÓRIAS DE FORMATO (CRÍTICO PARA O SISTEMA):
        - Retorne APENAS o JSON. Sem ```json, sem texto introdutório.
        - Use aspas duplas (") APENAS para abrir e fechar chaves e valores do JSON.
        - DENTRO do texto dos campos, use SEMPRE aspas simples (') para citações ou destaques. Exemplo: "O tribunal analisou o termo 'faturamento'..."
        - NÃO use quebras de linha reais dentro das strings. Use \n\n para separar parágrafos.
        - O JSON deve ser válido e parseável imediatamente.

        REGRAS DE CONTEÚDO E ANTI-ALUCINAÇÃO:
        - NÃO invente informações.
        - Se alguma informação não constar, escreva: "Não consta informação suficiente no acórdão."
        - NÃO reproduza literalmente a tese firmada, exceto se indispensável (máximo 1 frase curta).

        REGRA GERAL:
        - Sempre que houver menção a nome de pessoas físicas ou jurídicas, anonimize o dado, usando apenas as iniciais, salvo quando se tratar de nome de ministro ou outro julgador, caso em que poderá ser identificado. Exemplos: "Rogério Lima Vieira" deve ser referenciado como "R.L.V."; "Master Cálculo Contadores Associados Ltda" deve ser referenciado como "M.C.C.A. Ltda."
        - O texto não pode sofrer defasagem temporal. Evite destacar a conjuntura atual.
        - O texto será publicado diretamente na web; comporte-se como consultor jurídico; em caso de impossibilidade, acuse o erro no JSON.
        - CPC de 1973 => "CPC/73"; CPC de 2015 => "CPC".

        REQUISITOS ESPECÍFICOS POR CAMPO:

        teaser:
        - Deve mencionar o tribunal e o número do tema (ex.: "Tema 1069 do STJ").
        - Linguagem profissional, direta e informativa.

        caso_fatico:
        - SE FOR CASO CONCRETO (RE, REsp): Descreva as partes, cronologia, valores e a origem da lide.
        - SE FOR CONTROLE ABSTRATO (ADI, ADC, ADPF): Descreva a Lei/Ato Normativo questionado, quem propôs a ação e qual artigo/princípio foi alegado como violado.
        - NÃO incluir análise jurídica aqui.
        - Mencione processo/relator apenas aqui (uma única vez).

        contornos_juridicos:
        - Indicar a questão jurídica central (Ratio Decidendi).
        - Indicar dispositivos legais/constitucionais citados.
        - Indicar precedentes mencionados e divergências relevantes.
        - Se houver revisão de tese, explicar a mudança de entendimento.

        modulacao:
        - Se o tribunal DECIDIU não modular: usar exatamente "Não houve modulação de efeitos neste julgamento."
        - Se o acórdão NÃO ABORDA modulação (tema não foi discutido): usar exatamente "O acórdão não aborda modulação de efeitos."
        - Se houve modulação: indicar marco temporal, critérios e justificativa.
        - Indicar se a modulação veio em embargos ou revisão.

        tese_explicada:
        - Explicação acessível dos impactos práticos para contribuintes/cidadãos e para o poder público.
        - Evitar jargão desnecessário.
        - Se houver revisão, alertar sobre a mudança de regra.

        RENOVAÇÃO DE PEDIDO IMPORTANTE: RETORNE EXCLUSIVAMENTE um JSON válido, conforme as instruções prévias.

        TEMA:
        {texto_tema}

        TESE:
        {texto_tese}

        ACÓRDÃOS: seguem anexados a esta solicitação como arquivo(s) PDF.
        PROMPT;
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'erro' => $schema->string()->nullable(),
            'teaser' => $schema->string()->required(),
            'caso_fatico' => $schema->string()->required(),
            'contornos_juridicos' => $schema->string()->required(),
            'modulacao' => $schema->string()->required(),
            'tese_explicada' => $schema->string()->required(),
        ];
    }

    public function provider(): string
    {
        return 'openrouter';
    }

    public function model(): string
    {
        if (is_string($this->modelSlug) && $this->modelSlug !== '') {
            return $this->modelSlug;
        }

        $model = SiteSetting::get('acordao_analysis_model', config('ai.acordao_analysis.default_model'));

        if (! is_string($model) || $model === '') {
            throw new RuntimeException(
                'Nenhum modelo de IA configurado para análise de acórdãos. Defina-o em Configurações de IA.'
            );
        }

        return $model;
    }

    /**
     * Timeout HTTP (segundos) das chamadas ao provedor (análise multimodal com PDFs).
     */
    public function timeout(): int
    {
        return (int) config('services.openrouter.request_timeout', 120);
    }

    /**
     * Indica se há modelo configurado (usado por guards futuros na UI de enfileiramento).
     */
    public static function isConfigured(): bool
    {
        $model = SiteSetting::get('acordao_analysis_model', config('ai.acordao_analysis.default_model'));

        return is_string($model) && $model !== '';
    }
}
