<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EditableContent;

class PrecedentesHomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EditableContent::updateOrCreate(
            ['slug' => 'precedentes-home'],
            [
                'title' => '⚖️ Precedentes Vinculantes no CPC/2015',
                'meta_description' => 'Conheça os 5 tipos de precedentes vinculantes do CPC/2015 (Art. 927): controle concentrado, súmulas vinculantes, recursos repetitivos, súmulas STF/STJ e orientação do tribunal.',
                'content' => '<p class="lead"><strong>Você sabe quais precedentes são de observância obrigatória?</strong></p>

<p>O <strong>artigo 927 do Código de Processo Civil de 2015</strong> estabelece que juízes e tribunais devem obrigatoriamente observar determinadas decisões judiciais. Esses <strong>precedentes vinculantes</strong> têm como objetivo garantir a <strong>segurança jurídica</strong>, a <strong>isonomia</strong> no tratamento de casos semelhantes e a <strong>eficiência processual</strong>, evitando decisões conflitantes sobre a mesma questão de direito.</p>

<p>A não observância desses precedentes pode gerar consequências práticas importantes, como a interposição de <strong>reclamação</strong> (Art. 988 do CPC), a invalidade da fundamentação da decisão (Art. 489, §1º, VI) e a <strong>dispensa de remessa necessária</strong> (Art. 496, §4º).</p>

<h3 class="h5 mt-4 mb-3" style="color: #3b5998;">📋 Os 5 Tipos de Precedentes Obrigatórios (Art. 927):</h3>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="block block-bordered" style="border-left: 4px solid #3b5998; margin-bottom: 0;">
            <div class="block-content block-content-full">
                <h4 class="h6 mb-2" style="color: #3b5998;">
                    <i class="fa fa-gavel text-primary mr-1"></i>
                    <strong>I - Controle Concentrado de Constitucionalidade</strong>
                </h4>
                <p class="mb-0 text-muted">
                    Decisões do <strong>STF</strong> em ADI, ADC, ADPF e ADO. Têm eficácia <em>erga omnes</em> e efeito vinculante para todo o Judiciário e Administração Pública.
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="block block-bordered" style="border-left: 4px solid #3b5998; margin-bottom: 0;">
            <div class="block-content block-content-full">
                <h4 class="h6 mb-2" style="color: #3b5998;">
                    <i class="fa fa-star text-warning mr-1"></i>
                    <strong>II - Súmulas Vinculantes</strong>
                </h4>
                <p class="mb-0 text-muted">
                    <a href="/sumulas/stf">Enunciados de súmula vinculante do STF</a> (Art. 103-A da CF/88). Vinculam <strong>todos</strong> os órgãos do Judiciário e da Administração Pública.
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="block block-bordered" style="border-left: 4px solid #3b5998; margin-bottom: 0;">
            <div class="block-content block-content-full">
                <h4 class="h6 mb-2" style="color: #3b5998;">
                    <i class="fa fa-repeat text-info mr-1"></i>
                    <strong>III - Recursos Repetitivos e IRDR</strong>
                </h4>
                <p class="mb-0 text-muted">
                    <a href="/teses/stf">Teses do STF</a> e <a href="/teses/stj">STJ</a> em recursos repetitivos, repercussão geral e IRDR. A tese fixada aplica-se a todos os casos idênticos.
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="block block-bordered" style="border-left: 4px solid #3b5998; margin-bottom: 0;">
            <div class="block-content block-content-full">
                <h4 class="h6 mb-2" style="color: #3b5998;">
                    <i class="fa fa-list-alt text-success mr-1"></i>
                    <strong>IV - Súmulas do STF e STJ</strong>
                </h4>
                <p class="mb-0 text-muted">
                    <a href="/sumulas/stf">Súmulas do STF</a> em matéria constitucional e <a href="/sumulas/stj">súmulas do STJ</a> em matéria infraconstitucional. Embora não vinculantes, devem ser observadas.
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-12 mb-3">
        <div class="block block-bordered" style="border-left: 4px solid #3b5998; margin-bottom: 0;">
            <div class="block-content block-content-full">
                <h4 class="h6 mb-2" style="color: #3b5998;">
                    <i class="fa fa-sitemap text-danger mr-1"></i>
                    <strong>V - Orientação do Plenário ou Órgão Especial</strong>
                </h4>
                <p class="mb-0 text-muted">
                    Juízes e desembargadores devem observar a orientação do plenário ou órgão especial do tribunal ao qual estão vinculados (hierarquia interna).
                </p>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3" role="alert">
    <h4 class="alert-heading" style="font-size: 1.1rem;">
        <i class="fa fa-info-circle mr-1"></i> Fundamentação Adequada (Art. 489, §1º)
    </h4>
    <p class="mb-0">
        Ao aplicar precedentes, o juiz deve <strong>identificar seus fundamentos determinantes</strong> (ratio decidendi) e <strong>demonstrar que o caso se ajusta</strong> a eles. Ao deixar de seguir precedente invocado pela parte, deve demonstrar a <strong>distinção</strong> (distinguishing) ou <strong>superação</strong> (overruling) do entendimento.
    </p>
</div>

<div class="text-center mt-4">
    <a href="/precedentes-vinculantes-cpc" class="btn btn-primary btn-lg">
        <i class="fa fa-book mr-2"></i> 
        Leia o Guia Completo sobre Precedentes Vinculantes
    </a>
    <p class="text-muted mt-2 mb-0">
        <small>Conheça as consequências práticas, técnicas de aplicação (distinguishing e overruling), modulação de efeitos e muito mais.</small>
    </p>
</div>',
                'published' => true,
            ]
        );
    }
}
