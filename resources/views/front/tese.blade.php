@extends('front.base')

@section('page-title', $label)

@section('styles')
<style>
    /* Paywall Blur Effect - Aplica apenas aos textos (p, listas, spans) mantendo títulos H4 visíveis */
    .premium-content-blur p, 
    .premium-content-blur li, 
    .premium-content-blur span,
    .premium-content-blur div > text {
        color: transparent !important;
        text-shadow: 0 0 15px rgba(15, 23, 42, 0.95) !important;
        filter: blur(4px) !important;
        user-select: none !important;
        pointer-events: none !important;
        opacity: 0.5 !important;
    }
    
    /* Garante que o container absoluto do card tenha altura suficiente para engatilhar o sticky */
    .paywall-sticky-container {
        pointer-events: none;
        height: 100%; /* Ensure container takes full height so sticky can travel */
    }
    .paywall-sticky-card {
        pointer-events: auto;
        position: sticky !important;
        top: 2rem; /* Distância do topo da tela ao rolar (navbar) */
    }
</style>
@endsection

@section('content')

    <!-- Page Content -->

    <!-- Page Content -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
                 <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-100 tw-text-blue-800">Tese Vinculante</span>
                 <span class="tw-text-slate-500 tw-text-sm">{{ $tribunal }}</span>
            </div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">Tema {{ $tese->numero }}</h1>
            
            <div class="tw-flex tw-flex-wrap tw-gap-2 tw-items-center">
                 @if(isset($tese->isCancelada) && $tese->isCancelada)
                    <div class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-red-100 tw-text-red-800">
                        <i class="fa fa-ban"></i> CANCELADO
                    </div>
                @endif
                @if(isset($tese->situacao))
                     <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-lg tw-text-sm tw-font-medium tw-bg-slate-100 tw-text-slate-700 tw-border tw-border-slate-200">
                         Situação: {{ $tese->situacao }}
                     </span>
                @endif
            </div>

        </section>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4 tw-pb-2">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pb-10" id="content-results">

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-3 tw-gap-6">
            
            <!-- Main Content -->
            <div class="lg:tw-col-span-2 tw-space-y-6">
                <!-- Tese Box -->
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-flex tw-justify-between tw-items-center">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Tese Fixada</h3>
                        <button class="btn-copy-text tw-inline-flex tw-items-center tw-justify-center tw-px-3 tw-py-1.5 tw-border tw-border-slate-300 tw-shadow-sm tw-text-sm tw-font-medium tw-rounded-md tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors" data-clipboard-text="{{ $tese->to_be_copied }}">
                            <i class="fa fa-copy tw-mr-1.5"></i> <span class="btn-text">Copiar</span>
                        </button>
                    </div>
                    <div class="tw-p-6 md:tw-p-8">
                         <div class="tw-prose tw-prose-slate tw-max-w-none">
                            <p class="tw-text-xl tw-font-serif tw-text-slate-800 tw-leading-relaxed">
                                {{ $tese->tese_texto }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Questão Box -->
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-px-6 tw-py-4 tw-bg-slate-50 tw-border-b tw-border-slate-200">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Questão Submetida a Julgamento</h3>
                    </div>
                    <div class="tw-p-6">
                         <p class="tw-text-slate-700 tw-leading-relaxed">
                            {{ $tese->tema_texto }}
                        </p>
                    </div>
                </div>

                @if(isset($ai_sections) && $ai_sections->isNotEmpty())
                <!-- MOCKUP V1: Análise de Inteligência Artificial -->
                <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-relative tw-mt-6" id="ai-premium-box">
                    <div class="tw-px-6 tw-py-4 tw-rounded-t-xl tw-bg-slate-50 tw-border-b tw-border-slate-200">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0 tw-flex tw-items-center">
                            <i class="fa fa-robot tw-text-slate-500 tw-mr-2"></i> Decifrando a tese
                        </h3>
                    </div>

                    <div class="tw-p-6 md:tw-p-8 tw-space-y-8">
                        
                        @if($ai_sections->has('teaser'))
                        <!-- 1. Teaser (Sempre aberto) -->
                        <div>
                            <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-3 tw-flex tw-items-center">
                                <i class="fa fa-bolt tw-mr-2 tw-text-yellow-500"></i> O Ponto Central
                            </h4>
                            <div class="tw-prose tw-prose-slate tw-max-w-none tw-text-slate-700 tw-leading-relaxed tw-text-lg">
                                {!! Str::markdown($ai_sections->get('teaser')) !!}
                            </div>
                        </div>
                        @endif

                        @if($ai_sections->has('caso_fatico') || $ai_sections->has('contornos_juridicos') || $ai_sections->has('modulacao') || $ai_sections->has('tese_explicada'))
                        <!-- 2 ao 5: BLOQUEADOS PELO PAYWALL -->
                        <div class="tw-relative tw-mt-8">
                            
                            <!-- O conteúdo real fica no HTML, mas escondemos visualmente no paywall. -->
                            <!-- Removemos classes de blur globais para focar apenas nas tags P via CSS Customizado premium-content-blur -->
                            <div class="tw-space-y-8 tw-select-none {{ !$has_access ? 'premium-content-blur' : '' }}" {!! !$has_access ? 'aria-hidden="true"' : '' !!}>
                                
                                @if($ai_sections->has('caso_fatico'))
                                <!-- 2. Caso Fático -->
                                <div>
                                    <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-3 tw-flex tw-items-center tw-opacity-100">
                                        <i class="fa fa-book-open tw-mr-2 tw-text-slate-400"></i> Resumo do Caso Fático
                                    </h4>
                                    <div class="tw-prose tw-prose-slate tw-max-w-none tw-text-slate-500 tw-leading-relaxed">
                                        {!! Str::markdown($ai_sections->get('caso_fatico')) !!}
                                    </div>
                                </div>
                                @endif

                                @if($ai_sections->has('contornos_juridicos'))
                                <div>
                                    <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-3 tw-flex tw-items-center tw-opacity-100">
                                        <i class="fa fa-gavel tw-mr-2 tw-text-slate-400"></i> Contornos Jurídicos do Acórdão
                                    </h4>
                                    <div class="tw-prose tw-prose-slate tw-max-w-none tw-text-slate-500 tw-leading-relaxed">
                                        {!! Str::markdown($ai_sections->get('contornos_juridicos')) !!}
                                    </div>
                                </div>
                                @endif
                                
                                @if($ai_sections->has('modulacao'))
                                <div>
                                    <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-3 tw-flex tw-items-center tw-opacity-100">
                                        <i class="fa fa-calendar-check tw-mr-2 tw-text-slate-400"></i> Efeitos (Modulação)
                                    </h4>
                                    <div class="tw-prose tw-prose-slate tw-max-w-none tw-text-slate-500 tw-leading-relaxed">
                                        {!! Str::markdown($ai_sections->get('modulacao')) !!}
                                    </div>
                                </div>
                                @endif

                                @if($ai_sections->has('tese_explicada'))
                                <div>
                                    <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-3 tw-flex tw-items-center tw-opacity-100">
                                        <i class="fa fa-chalkboard-teacher tw-mr-2 tw-text-slate-400"></i> Tese Explicada
                                    </h4>
                                    <div class="tw-prose tw-prose-slate tw-max-w-none tw-text-slate-500 tw-leading-relaxed">
                                        {!! Str::markdown($ai_sections->get('tese_explicada')) !!}
                                    </div>
                                </div>
                                @endif

                            </div>

                            @if(!$has_access)
                            {{-- OVERLAY: Gradiente para fading visual --}}
                            <div class="tw-absolute tw-inset-0 tw-z-10 tw-bg-gradient-to-b tw-from-white/5 tw-via-white/70 tw-to-white tw-pointer-events-none tw-rounded-b-lg"></div>

                            {{-- CARD CTA: Registerwall ou Paywall --}}
                            <div class="tw-absolute tw-inset-0 tw-z-20 tw-flex tw-justify-center tw-items-start tw-pt-6 tw-px-6 paywall-sticky-container">
                                <div class="tw-bg-slate-900 tw-text-white tw-rounded-2xl tw-p-8 tw-shadow-2xl tw-max-w-md tw-w-full tw-text-center tw-relative tw-overflow-hidden paywall-sticky-card">
                                    <div class="tw-absolute -tw-top-12 -tw-right-12 tw-w-32 tw-h-32 tw-bg-brand-500 tw-rounded-full tw-opacity-20 tw-blur-2xl"></div>
                                    <div class="tw-absolute -tw-bottom-12 -tw-left-12 tw-w-32 tw-h-32 tw-bg-blue-500 tw-rounded-full tw-opacity-20 tw-blur-2xl"></div>
                                    
                                    @if($isRegisterwall)
                                        {{-- REGISTERWALL --}}
                                        <i class="fa fa-user-plus tw-text-3xl tw-text-brand-400 tw-mb-4"></i>
                                        <h3 class="tw-text-xl tw-font-bold tw-mb-2">Análise Jurídica Completa</h3>
                                        <p class="tw-text-slate-300 tw-text-sm tw-mb-6 tw-leading-relaxed">
                                            Crie sua conta <strong>gratuita</strong> para acessar o <strong>Resumo do Caso Fático</strong>, os <strong>Contornos Jurídicos do Acórdão</strong> e mais — e ainda navegue <strong>sem anúncios</strong>!
                                        </p>
                                        
                                        <div class="tw-space-y-3">
                                            <a href="/register" class="tw-block tw-w-full tw-bg-brand-600 hover:tw-bg-brand-500 tw-text-white tw-font-semibold tw-py-3 tw-px-4 tw-rounded-xl tw-transition-colors tw-pointer-events-auto">
                                                Criar Conta Grátis
                                            </a>
                                            <p class="tw-text-xs tw-text-slate-400 tw-pointer-events-auto">
                                                Já tem conta? <a href="/login?redirect=/tese/{{ strtolower($tribunal) }}/{{ $tese->numero }}" class="tw-text-brand-400 hover:tw-text-brand-300 tw-underline">Entre na sua conta</a>.
                                            </p>
                                        </div>
                                    @else
                                        {{-- PAYWALL --}}
                                        <i class="fa fa-lock tw-text-3xl tw-text-brand-400 tw-mb-4"></i>
                                        <h3 class="tw-text-xl tw-font-bold tw-mb-2">Análise Jurídica Exclusiva</h3>
                                        <p class="tw-text-slate-300 tw-text-sm tw-mb-6 tw-leading-relaxed">
                                            Acesse agora o <strong>Resumo do Caso Fático</strong> que deu origem à tese, os <strong>Contornos Jurídicos do Acórdão</strong> e mais.
                                        </p>
                                        
                                        <div class="tw-space-y-3">
                                            <a href="/assinar" class="tw-block tw-w-full tw-bg-brand-600 hover:tw-bg-brand-500 tw-text-white tw-font-semibold tw-py-3 tw-px-4 tw-rounded-xl tw-transition-colors tw-pointer-events-auto">
                                                Assine o T&S
                                            </a>
                                            <p class="tw-text-xs tw-text-slate-400 tw-pointer-events-auto">
                                                Já possui assinatura? <a href="/login?redirect=/tese/{{ strtolower($tribunal) }}/{{ $tese->numero }}" class="tw-text-brand-400 hover:tw-text-brand-300 tw-underline">Entre na sua conta</a>.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            {{-- Fim do Card CTA --}}
                            @endif

                        </div>
                        @endif

                        <!-- Disclaimer Exigido -->
                        <div class="tw-pt-6 tw-mt-8 tw-border-t tw-border-slate-200">
                            <div class="tw-bg-amber-50 tw-border-l-4 tw-border-amber-400 tw-p-4 tw-rounded-r-lg">
                                <div class="tw-flex">
                                    <div class="tw-flex-shrink-0">
                                        <i class="fa fa-info-circle tw-text-amber-400"></i>
                                    </div>
                                    <div class="tw-ml-3">
                                        <p class="tw-text-sm tw-text-amber-700 tw-m-0">
                                            <strong>ATENÇÃO:</strong> os comentários da seção "Decifrando a tese" não são oficiais e não substituem a análise dos acórdãos dos precedentes.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Downloads Originais (Acórdãos PDF vinculados) Exibidos no fim do box da IA -->
                        @if($has_access && isset($acordaos_pdfs) && $acordaos_pdfs->isNotEmpty())
                        <div class="tw-pt-8 tw-mt-6">
                            <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4 tw-flex tw-items-center">
                                <i class="fa fa-file-pdf tw-mr-2 tw-text-red-500"></i> Acórdãos Originais para Download
                            </h4>
                            <div class="tw-flex tw-flex-col tw-gap-3">
                                @foreach($acordaos_pdfs as $pdf)
                                <a href="{{ $pdf->presigned_url ?? '#' }}" target="_blank" class="tw-inline-flex tw-items-center tw-justify-between tw-w-full tw-max-w-md tw-px-4 tw-py-3 tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg hover:tw-bg-slate-50 hover:tw-border-slate-300 tw-transition-colors tw-group">
                                    <div class="tw-flex tw-items-center">
                                        <div class="tw-w-8 tw-h-8 tw-rounded-md tw-bg-red-50 tw-text-red-600 tw-flex tw-items-center tw-justify-center tw-mr-3 group-hover:tw-bg-red-100 tw-transition-colors">
                                            <i class="fa fa-download tw-text-sm"></i>
                                        </div>
                                        <div class="tw-flex tw-flex-col">
                                            <span class="tw-text-sm tw-font-medium tw-text-slate-700 group-hover:tw-text-slate-900">
                                                {{ $pdf->numero_acordao ?: 'Baixar Acórdão' }}
                                            </span>
                                            <span class="tw-text-xs tw-text-slate-500">
                                                Acórdão {{ $pdf->tipo ?: 'Original' }}
                                            </span>
                                        </div>
                                    </div>
                                    <i class="fa fa-chevron-right tw-text-slate-300 tw-text-xs group-hover:tw-text-slate-400 tw-transition-colors"></i>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif
                <!-- FIM MOCKUP V1 -->

            </div>

            <!-- Sidebar Info -->
            <div class="tw-space-y-6">
                 <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">
                    <div class="tw-p-5">
                        <h4 class="tw-text-sm tw-font-bold tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-4">Informações do Julgamento</h4>
                        
                        <dl class="tw-space-y-4">
                            @if(isset($tese->relator))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Relator</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->relator }}</dd>
                            </div>
                            @endif

                            @if(isset($tese->acordao))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Acórdão (Leading Case)</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->acordao }}</dd>
                            </div>
                            @endif

                            @if(isset($tese->tempo))
                            <div>
                                <dt class="tw-text-xs tw-text-slate-500 tw-mb-1">Data</dt>
                                <dd class="tw-text-sm tw-font-medium tw-text-slate-800">{{ $tese->tempo }}</dd>
                            </div>
                            @endif
                        </dl>

                        <div class="tw-mt-6 tw-pt-6 tw-border-t tw-border-slate-100 tw-space-y-3">
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($tese->to_be_copied . ' ' . Request::url()) }}" 
                               target="_blank"
                               class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-bg-green-600 tw-text-white hover:tw-bg-green-700 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                                <i class="fab fa-whatsapp tw-mr-2"></i> Compartilhar no Zap
                            </a>
                            
                            @if(!empty($tese->link))
                            <a href="{{ $tese->link }}" target="_blank" class="tw-flex tw-items-center tw-justify-center tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-text-slate-700 hover:tw-bg-slate-50 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition">
                                Ver origem <i class="fa fa-external-link-alt tw-ml-2 tw-text-xs"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
