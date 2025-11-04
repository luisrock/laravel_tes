<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EditableContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('editable_contents')->insert([
            'slug' => 'precedentes-vinculantes-cpc',
            'title' => 'Precedentes Vinculantes no Código de Processo Civil',
            'meta_description' => 'Entenda o que são precedentes vinculantes no CPC/2015 (Art. 927): súmulas vinculantes, repercussão geral, recursos repetitivos e suas consequências práticas.',
            'content' => $this->getContent(),
            'published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getContent()
    {
        return <<<HTML
<h2>O que são Precedentes Vinculantes?</h2>

<p>Os <strong>precedentes vinculantes</strong> são decisões judiciais que, por expressa determinação legal, devem ser obrigatoriamente observadas por juízes e tribunais na solução de casos futuros. O Código de Processo Civil de 2015 (Lei 13.105/2015) consolidou e ampliou significativamente o sistema de precedentes no Brasil, estabelecendo no <strong>artigo 927</strong> um rol de decisões de observância obrigatória.</p>

<h3>Fundamento Constitucional e Legal</h3>

<p>A vinculação aos precedentes busca concretizar princípios constitucionais fundamentais:</p>

<ul>
    <li><strong>Segurança jurídica:</strong> garantir previsibilidade nas decisões judiciais</li>
    <li><strong>Isonomia:</strong> tratar casos semelhantes de forma igual</li>
    <li><strong>Eficiência:</strong> evitar recursos desnecessários e decisões conflitantes</li>
    <li><strong>Duração razoável do processo:</strong> acelerar a solução de litígios</li>
</ul>

<h2>Art. 927 do CPC: O Rol dos Precedentes Obrigatórios</h2>

<p>O artigo 927 do CPC estabelece que <strong>juízes e tribunais devem observar</strong> as seguintes decisões:</p>

<h3>I - Decisões em Controle Concentrado de Constitucionalidade</h3>

<p>As decisões do <strong>Supremo Tribunal Federal (STF)</strong> proferidas em:</p>
<ul>
    <li>Ação Direta de Inconstitucionalidade (ADI)</li>
    <li>Ação Declaratória de Constitucionalidade (ADC)</li>
    <li>Arguição de Descumprimento de Preceito Fundamental (ADPF)</li>
    <li>Ação Direta de Inconstitucionalidade por Omissão (ADO)</li>
</ul>

<p>Essas decisões têm <strong>eficácia erga omnes</strong> (contra todos) e <strong>efeito vinculante</strong> para todos os órgãos do Poder Judiciário e da Administração Pública.</p>

<h3>II - Súmulas Vinculantes</h3>

<p>Os <a href="/sumulas/stf">enunciados de súmula vinculante do STF</a> (art. 103-A da CF/88) são criados após reiteradas decisões sobre matéria constitucional e têm por objetivo:</p>
<ul>
    <li>Evitar grave insegurança jurídica</li>
    <li>Evitar multiplicação de processos sobre idêntica questão</li>
    <li>Vincular <strong>todos os órgãos do Judiciário e da Administração Pública</strong></li>
</ul>

<p><strong>Exemplo:</strong> Súmula Vinculante 11 - "Só é lícito o uso de algemas em casos de resistência e de fundado receio de fuga ou de perigo à integridade física própria ou alheia."</p>

<h3>III - Acórdãos em Julgamento de Casos Repetitivos</h3>

<p>São precedentes vinculantes os acórdãos proferidos em:</p>

<h4>a) Incidente de Resolução de Demandas Repetitivas (IRDR)</h4>
<p>Técnica de julgamento de <a href="/temas">causas repetitivas</a> nos tribunais estaduais e regionais federais. A tese jurídica fixada será aplicada a todos os processos individuais ou coletivos que versem sobre a mesma questão de direito.</p>

<h4>b) Recursos Especial e Extraordinário Repetitivos</h4>
<p>Quando houver multiplicidade de recursos com fundamento em idêntica questão de direito, o STJ ou STF selecionam casos representativos da controvérsia (art. 1.036 do CPC):</p>
<ul>
    <li><strong><a href="/teses/stj">Recursos Repetitivos (STJ)</a>:</strong> matéria infraconstitucional</li>
    <li><strong><a href="/teses/stf">Recursos Extraordinários Repetitivos (STF)</a>:</strong> matéria constitucional com repercussão geral reconhecida</li>
</ul>

<p><strong>Exemplo:</strong> Tema 1.135 do STF - Base de cálculo do ISS na concessão de crédito</p>

<h3>IV - Súmulas do STF e STJ</h3>

<p>Diferentemente das súmulas vinculantes, as súmulas "comuns" não vinculam formalmente, mas devem ser observadas:</p>
<ul>
    <li><strong><a href="/sumulas/stf">Súmulas do STF</a>:</strong> em matéria constitucional</li>
    <li><strong><a href="/sumulas/stj">Súmulas do STJ</a>:</strong> em matéria infraconstitucional</li>
</ul>

<p>Embora não tenham força vinculante nos termos do art. 103-A da CF/88, sua não observância pode configurar fundamentação inadequada (art. 489, §1º, VI, do CPC).</p>

<h3>V - Orientação do Plenário ou Órgão Especial</h3>

<p>Os juízes e desembargadores devem observar a orientação do plenário ou do órgão especial do tribunal ao qual estão vinculados. Trata-se do respeito à <strong>hierarquia interna</strong> dos tribunais.</p>

<h2>Dever de Fundamentação Adequada (Art. 489, §1º)</h2>

<p>O CPC estabelece que <strong>não se considera fundamentada</strong> a decisão judicial que:</p>

<h3>Uso Inadequado de Precedentes</h3>

<p>Segundo o art. 489, §1º, incisos V e VI, a decisão deve:</p>

<ul>
    <li><strong>Inciso V:</strong> Ao invocar precedente ou súmula, <strong>identificar seus fundamentos determinantes</strong> (ratio decidendi) e <strong>demonstrar que o caso se ajusta</strong> a esses fundamentos</li>
    <li><strong>Inciso VI:</strong> Ao deixar de seguir precedente invocado pela parte, <strong>demonstrar a distinção</strong> (distinguishing) ou a <strong>superação</strong> (overruling) do entendimento</li>
</ul>

<h3>Técnicas de Aplicação</h3>

<p>Para aplicar corretamente os precedentes, deve-se utilizar:</p>

<ul>
    <li><strong>Distinguishing (Distinção):</strong> demonstrar que o caso concreto possui peculiaridades que o diferenciam do precedente</li>
    <li><strong>Overruling (Superação):</strong> demonstrar que o precedente está superado por mudança na realidade social ou no entendimento jurídico</li>
    <li><strong>Overriding (Afastamento Parcial):</strong> limitar o alcance do precedente em situações específicas</li>
</ul>

<h2>Consequências Práticas da Vinculação</h2>

<h3>1. Dispensa de Remessa Necessária (Art. 496, §4º)</h3>

<p>Não se aplica o duplo grau de jurisdição obrigatório quando a sentença estiver fundada em:</p>
<ul>
    <li>Súmula de tribunal superior</li>
    <li>Acórdão de recurso repetitivo</li>
    <li>Tese de IRDR ou assunção de competência</li>
    <li>Entendimento consolidado da própria Administração Pública</li>
</ul>

<h3>2. Tutela de Evidência (Art. 311, II)</h3>

<p>É possível conceder tutela provisória de urgência quando houver <strong>tese firmada em julgamento de casos repetitivos ou em súmula vinculante</strong>, mesmo sem demonstração de perigo de dano.</p>

<h3>3. Reclamação (Art. 988)</h3>

<p>Cabe <a href="#" title="Reclamação">reclamação</a> para garantir a observância de:</p>
<ul>
    <li>Súmula vinculante</li>
    <li>Decisão em controle concentrado de constitucionalidade</li>
    <li>Acórdão de IRDR ou assunção de competência</li>
    <li>Acórdão de recursos repetitivos (após esgotadas as instâncias ordinárias)</li>
</ul>

<h2>Estabilidade, Integridade e Coerência (Art. 926)</h2>

<p>O art. 926 do CPC estabelece que os <strong>tribunais devem uniformizar sua jurisprudência</strong> e mantê-la:</p>

<ul>
    <li><strong>Estável:</strong> evitar mudanças abruptas e injustificadas</li>
    <li><strong>Íntegra:</strong> considerar o sistema jurídico como um todo</li>
    <li><strong>Coerente:</strong> manter harmonia entre os diversos precedentes</li>
</ul>

<h3>Modulação de Efeitos (Art. 927, §3º)</h3>

<p>Na hipótese de alteração de jurisprudência dominante ou de tese firmada em julgamento de casos repetitivos, é possível <strong>modular os efeitos temporais</strong> da mudança, para proteger:</p>
<ul>
    <li>O <strong>interesse social</strong></li>
    <li>A <strong>segurança jurídica</strong></li>
</ul>

<p>Exemplo: determinar que a nova tese só se aplique a partir de determinada data, preservando situações consolidadas.</p>

<h2>Pesquise Precedentes Vinculantes</h2>

<p>Utilize nossa ferramenta de <a href="/">busca de precedentes</a> para encontrar:</p>

<ul>
    <li><a href="/sumulas/stf">Súmulas do STF</a> (vinculantes e não vinculantes)</li>
    <li><a href="/sumulas/stj">Súmulas do STJ</a></li>
    <li><a href="/teses/stf">Teses de Repercussão Geral (STF)</a></li>
    <li><a href="/teses/stj">Temas de Recursos Repetitivos (STJ)</a></li>
    <li><a href="/sumulas/tst">Súmulas do TST</a></li>
    <li><a href="/sumulas/tnu">Súmulas e Questões de Ordem (TNU)</a></li>
</ul>

<h2>Referências Normativas</h2>

<ul>
    <li><strong>Art. 926</strong> - Dever de uniformização da jurisprudência</li>
    <li><strong>Art. 927</strong> - Precedentes de observância obrigatória</li>
    <li><strong>Art. 928</strong> - Definição de julgamento de casos repetitivos</li>
    <li><strong>Art. 489, §1º</strong> - Fundamentação adequada</li>
    <li><strong>Art. 496, §4º</strong> - Dispensa de remessa necessária</li>
    <li><strong>Art. 311, II</strong> - Tutela de evidência</li>
    <li><strong>Art. 988</strong> - Reclamação</li>
</ul>

<p><small>Última atualização: Novembro de 2025</small></p>
HTML;
    }
}
