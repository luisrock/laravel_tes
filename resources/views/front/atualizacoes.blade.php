@extends('front.base')

@section('page-title', 'Atualizações')

@section('content')

    <!-- Hero -->
    <div class="tw-bg-slate-50 tw-border-b tw-border-slate-200" style="{{ $display_pdf }}">
        <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-py-8 md:tw-py-12">
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-gap-4 tw-mb-6">
                <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-900 tw-m-0">
                    <a href="{{ url('/') }}" class="hover:tw-text-brand-700 tw-transition-colors">
                        Teses & Súmulas
                    </a>
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-rounded-full tw-text-sm tw-font-medium tw-bg-brand-100 tw-text-brand-800 hover:tw-bg-brand-200 tw-transition-colors">
                        Extensão para o Chrome
                    </a>
                </span>
            </div>
            <p class="tw-text-slate-600 tw-text-lg tw-leading-relaxed tw-mb-8">
                Veja aqui as últimas atualizações de teses e súmulas trazidas para o site.
            </p>
            
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-start sm:tw-items-end tw-gap-4">
                <div>
                    <h2 class="tw-text-xl tw-font-semibold tw-text-slate-800 tw-mb-2">
                        Últimas Atualizações do Site
                    </h2>
                    <select id="dateFilter" class="tw-form-select tw-rounded-lg tw-border-slate-300 tw-text-slate-700 focus:tw-border-brand-500 focus:tw-ring-brand-500 tw-w-full sm:tw-w-auto" multiple>
                        <option value="all">Todas as datas</option>
                        <!-- As datas serão populadas aqui via jQuery -->
                    </select>
                </div>
            </div>

        </div>
    </div>
    <!-- END Hero -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-py-8">
        <div class="tw-space-y-8">
            <div id="tema-stf">
                @foreach ($tribunais as $tribunal => $log_types)
                    @php
                        $color = config('tes_constants.lista_tribunais.' . strtoupper($tribunal) . '.color');
                        // Mapeamento de cores legadas para classes Tailwind aproximadas para manter identidade visual
                        $bgClass = 'tw-bg-slate-100';
                        $borderClass = 'tw-border-slate-200';
                        $textClass = 'tw-text-slate-700';
                        
                        // Ajuste fino pode ser feito aqui se necessário, mas usando estilos inline para preservar cores específicas dos tribunais
                        // caso o config retorne hexadecimais específicos.
                    @endphp
                    
                    <div class="tribunal-header tw-bg-slate-50 tw-border-l-4 tw-p-4 tw-rounded-r-lg tw-mb-6 tw-flex tw-items-center tw-shadow-sm trib-texto-quantidade"
                        style="border-color: {{ $color }} !important;">
                        <span class="tw-text-lg tw-font-bold tw-text-slate-800">
                            {{ config('tes_constants.lista_tribunais.' . strtoupper($tribunal) . '.name') }} - {{ strtoupper($tribunal) }}
                        </span>
                    </div>

                    @if (!empty($log_types['news']))
                        <div class="tw-mb-8 tw-overflow-hidden tw-rounded-xl tw-border tw-border-slate-200 tw-shadow-sm table-results" style="border-top: 4px solid {{ $color }};">
                            <div class="header-row tw-bg-slate-50 tw-p-4 tw-border-b tw-border-slate-200">
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0" style="color: {{ $color }};">
                                    Novidades ({{ strtoupper($tribunal) }})
                                </h4>
                            </div>
                            
                            <div class="tw-divide-y tw-divide-slate-200">
                                @foreach ($log_types['news'] as $log)
                                    <div class="tw-p-4 md:tw-p-6 hover:tw-bg-slate-50 tw-transition-colors">
                                        @if ($log->tipo == 'súmula')
                                            <h4 class="tw-text-base md:tw-text-lg tw-font-semibold tw-mb-2">
                                                <a href="{{ $log->link }}" class="hover:tw-underline" style="color: {{ $color }}">
                                                    {{ $log->original->titulo }} - {{ strtoupper($tribunal) }}
                                                </a>
                                            </h4>
                                        @elseif($log->tipo == 'tese')
                                            <h4 class="tw-text-base md:tw-text-lg tw-font-semibold tw-mb-2">
                                                <a href="{{ $log->link }}" class="hover:tw-underline" style="color: {{ $color }}">
                                                    Tema {{ $log->original->numero }} - {{ strtoupper($tribunal) }}
                                                </a>
                                            </h4>
                                        @endif
                                        
                                        <p class="tw-text-slate-700 tw-leading-relaxed tw-mb-3 tw-font-medium">
                                            {{ $log->original->texto }}
                                        </p>
                                        
                                        <div class="tw-flex tw-justify-between tw-items-center tw-text-sm tw-text-slate-500">
                                            <span class="tw-shrink-0" data-date="{{ date('d/m/Y', strtotime($log->created_at)) }}">
                                                <i class="fa fa-calendar-alt tw-mr-1"></i>
                                                {{ date('d/m/Y', strtotime($log->created_at)) }}
                                            </span>
                                            <span class="tw-truncate tw-ml-4">
                                                Acesse&nbsp; <a href="{{ $log->link }}" class="tw-font-medium hover:tw-underline" style="color: {{ $color }}">aqui</a>.
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (!empty($log_types['updates']))
                        <div class="tw-mb-8 tw-overflow-hidden tw-rounded-xl tw-border tw-border-slate-200 tw-shadow-sm table-results" style="border-top: 4px solid {{ $color }};">
                            <div class="header-row tw-bg-slate-50 tw-p-4 tw-border-b tw-border-slate-200">
                                <h4 class="tw-text-lg tw-font-semibold tw-m-0" style="color: {{ $color }};">
                                    Alterações ({{ strtoupper($tribunal) }})
                                </h4>
                            </div>

                            <div class="tw-divide-y tw-divide-slate-200">
                                @foreach ($log_types['updates'] as $log)
                                    <div class="tw-p-4 md:tw-p-6 hover:tw-bg-slate-50 tw-transition-colors">
                                        <h4 class="tw-text-base md:tw-text-lg tw-font-semibold tw-mb-2">
                                            <a href="{{ $log->link }}" class="hover:tw-underline" style="color: {{ $color }}">
                                                @if ($log->tipo == 'súmula')
                                                    Súmula {{ $log->numero }}
                                                @elseif($log->tipo == 'tese')
                                                    Tema {{ $log->numero }}
                                                @endif
                                                - {{ strtoupper($tribunal) }}
                                            </a>
                                        </h4>
                                        
                                        <div class="tw-text-sm tw-text-slate-500 tw-mb-2">
                                            Objeto da Atualização: <span class="tw-font-medium tw-text-slate-700">{{ $log->col_altered }}</span>
                                        </div>
                                        
                                        <p class="tw-text-slate-700 tw-leading-relaxed tw-mb-3 tw-font-medium">
                                            {{ $log->new_value }}
                                        </p>
                                        
                                        <div class="tw-flex tw-justify-between tw-items-center tw-text-sm tw-text-slate-500">
                                            <span class="tw-shrink-0" data-date="{{ date('d/m/Y', strtotime($log->updated_at)) }}">
                                                <i class="fa fa-calendar-alt tw-mr-1"></i>
                                                {{ date('d/m/Y', strtotime($log->updated_at)) }}
                                            </span>
                                            <span class="tw-truncate tw-ml-4">
                                                Acesse&nbsp; <a href="{{ $log->link }}" class="tw-font-medium hover:tw-underline" style="color: {{ $color }}">aqui</a>.
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if(!empty($log_types['news']) || !empty($log_types['updates']))
                        <hr class="tw-border-slate-200 tw-my-8">
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- END Page Content -->

@endsection

@section('atualizacoesjs')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            let dates = [];

            // Coletar todas as datas da página
            $('span[data-date]').each(function() {
                let currentDate = $(this).data('date');
                if (!dates.includes(currentDate)) {
                    dates.push(currentDate);
                }
            });

            dates.sort(function(a, b) {
                let partsA = a.split('/');
                let partsB = b.split('/');
                return new Date(partsB[2], partsB[1] - 1, partsB[0]) - new Date(partsA[2], partsA[1] - 1,
                    partsA[0]);
            });

            // Populando o dropdown com as datas
            dates.forEach(function(date) {
                $('#dateFilter').append('<option value="' + date + '">' + date + '</option>');
            });

            // Evento para filtrar as linhas baseado nas datas selecionadas
            $('#dateFilter').on('change', function() {
                let selectedDates = $(this).val();

                if (selectedDates.includes("all")) {
                    // Mostrar tudo
                    $('.table-results > div.tw-divide-y > div').show();
                    $('.table-results').show();
                    $('.tribunal-header').show();
                    $('hr.tw-border-slate-200').show();
                } else {
                    // Esconder todos os itens individuais primeiro
                    $('.table-results > div.tw-divide-y > div').hide();

                    // Mostrar apenas os itens que correspondem à data
                    $('.table-results > div.tw-divide-y > div').each(function() {
                        let rowDate = $(this).find('span[data-date]').data('date');
                        if (selectedDates.includes(rowDate)) {
                            $(this).show();
                        }
                    });

                    // Gerenciar visibilidade dos headers de tribunal e containers de resultados
                    $('.table-results').each(function() {
                        let $container = $(this);
                        let $tribunalHeader = $container.prevAll('.tribunal-header').first();
                        
                        // Verifica se há itens visíveis dentro deste container
                        if ($container.find('div.tw-divide-y > div:visible').length > 0) {
                            $container.show();
                            $tribunalHeader.show();
                        } else {
                            $container.hide();
                            // Só esconde o header do tribunal se TODOS os containers associados a ele estiverem vazios/escondidos
                            // (Simplificação: assume estrutura linear par Header -> Container)
                            // Na estrutura atual, cada loop cria Header -> (News Container) -> (Updates Container).
                            // O Header deve sumir se ambos estiverem vazios.
                            
                            // Verificar se o container irmão (updates ou news) também está vazio
                            // Essa lógica pode ser complexa via DOM traversal simples.
                            // Alternativa: Re-verificar headers baseados na visibilidade dos seus containers próximos.
                        }
                    });
                    
                    // Varredura final para esconder headers órfãos e HRs
                    $('.tribunal-header').each(function() {
                        let $header = $(this);
                        // Procura containers de resultado seguintes até o próximo header ou fim
                        let $nextContainers = $header.nextUntil('.tribunal-header', '.table-results');
                        
                        // Se nenhum container visível seguir este header, esconde o header
                        let hasVisibleContent = false;
                        $nextContainers.each(function() {
                            if ($(this).is(':visible')) hasVisibleContent = true;
                        });
                        
                        if (hasVisibleContent) {
                            $header.show();
                        } else {
                            $header.hide();
                        }
                    });
                    
                    // Esconder HRs se não houver conteúdo visível adjacente
                     $('hr.tw-border-slate-200').each(function() {
                        let $hr = $(this);
                        let $prevContent = $hr.prevUntil('.tribunal-header').filter(':visible');
                         if ($prevContent.length === 0) {
                             $hr.hide();
                         } else {
                             $hr.show();
                         }
                     });
                }
            });
        });
    </script>
@endsection
