@extends('front.base')

@section('page-title', 'Pesquisa')

@section('content')

<!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


<!-- Search -->
<div class="tw-home-pilot">
<div class="home-pilot-shell" style="{{ $display_pdf }}">
    <form method="GET" id="trib-form" class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-5">

        <div class="tw-space-y-2">
            <h1 class="home-pilot-title">Pesquisa de Teses e Súmulas</h1>
            <p class="home-pilot-subtitle">Consulta rápida, objetiva e responsiva para jurisprudência dos principais tribunais.</p>
        </div>

        @if (session('success'))
        <div class="home-pilot-alert home-pilot-alert-success" role="alert">
            <button type="button" class="home-pilot-alert-close" onclick="this.closest('[role=alert]').remove()" aria-label="Fechar">
                &times;
            </button>
            <i class="fa fa-check-circle tw-mr-2"></i>
            <strong>{{ session('success') }}</strong>
        </div>
        @endif

        @if ($errors->any())
        <div class="home-pilot-alert home-pilot-alert-danger" role="alert">
            <ul class="tw-mb-0 tw-pl-5 tw-space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="tw-flex tw-gap-2 tw-items-stretch">
            <input type="text" class="home-pilot-input" name="q" value="{{ $keyword ?? '' }}" placeholder="Buscar tema, tese ou súmula..."
                required>
            <!-- <input type="hidden" name="keylabel"> -->
            <div class="tw-hidden md:tw-flex">
                <span class="tw-rounded-lg tw-border tw-border-slate-300 tw-bg-slate-50 tw-px-4 tw-text-slate-600 tw-inline-flex tw-items-center" style="cursor:pointer;"
                    onclick="document.getElementById('trib-form').submit();">
                    <i class="fa fa-fw fa-search"></i>
                </span>
            </div>
        </div>

        <div id="radios-tribunais" class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-4 lg:tw-grid-cols-8 tw-gap-2">

            @foreach ($lista_tribunais as $t => $arr)
            <label class="home-pilot-chip home-pilot-radio" for="{{ strtolower($t) }}">
                <input class="tw-sr-only" type="radio" name="tribunal" id="{{ strtolower($t) }}" value="{{ $t }}"
                    @if ( !empty($tribunal) && strtolower($tribunal) === strtolower($t) ) checked @endif
                    <?php //echo ($tribunal === $t) ? 'checked' : ''; ?>>
                <span class="tw-w-full tw-text-center">{{ $t }}</span>
            </label>
            @endforeach

        </div>

        <p class="home-pilot-selection-status" id="selected-tribunal-status">
            Tribunal selecionado: <strong id="selected-tribunal-label">nenhum</strong>
        </p>


        <div class="tw-mt-2">
            <input class="home-pilot-btn tw-w-full md:tw-w-auto" id="btn-send-trib-form" type="submit" value="Pesquisar">
        </div>

    </form>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="home-pilot-spinner" role="status" aria-label="Carregando"></div>
        <div class="loading-text">
            Buscando jurisprudência...
        </div>
        <div class="loading-subtext">
            Consultando base de dados dos tribunais
        </div>
    </div>
    <!-- END Loading Overlay -->
    @auth
        @php $toStore = false; @endphp
        @if(in_array(Auth::user()->email, ['mauluis@gmail.com','trator70@gmail.com','ivanaredler@gmail.com']))
            @if(!empty($output['total_count']))
            @php $toStore = true; @endphp
            <div id="admin-store" class="home-pilot-card tw-mt-4 tw-p-4 tw-space-y-3">
                <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                    <input type="text" name="store-label" class="home-pilot-input sm:tw-max-w-xs">
                    <button class="home-pilot-btn tw-bg-slate-700 hover:tw-bg-slate-800 tw-py-2 tw-px-3 tw-text-sm" id="btn-similar-search">Similares</button>
                    <select name="typeToCompare" class="home-pilot-select">
                        <option value="label" selected>by label</option>
                        <option value="keyword">by keyword</option>
                    </select>
                    <div class="tw-inline-flex tw-items-center tw-gap-1">
                        <input type="number" name="similarity-percentage" value="80" min="0" max="100" class="home-pilot-input tw-w-16 tw-px-2 tw-py-2">%
                    </div>
                </div>
                <div class="similar-block tw-space-y-3" style="display:none;">
                    <div id="similar-searched" class="tw-space-y-2"></div>
                    <button class="home-pilot-btn tw-py-2 tw-px-3 tw-text-sm" id="btn-store-search" disabled>Salvar Pesquisa</button>
                    
                </div>
            </div>
            @endif
        @endif        
    @endauth
</div>
<!-- END Search -->

<!-- Precedentes Vinculantes CPC -->
@if(!empty($precedentes_home ?? null))
<div class="home-pilot-shell tw-pt-0" style="{{ $display_pdf }}">
    <section class="home-pilot-card tw-overflow-hidden">
        <header class="tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-flex tw-items-center tw-justify-between tw-gap-3 tw-flex-wrap tw-px-5 tw-py-4 md:tw-px-6">
            <h2 class="tw-text-slate-900 tw-font-semibold tw-text-lg">{{ optional($precedentes_home)->title ?? '' }}</h2>
            <div>
                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-emerald-100 tw-text-emerald-800 tw-text-xs tw-font-semibold tw-px-3 tw-py-1">Guia Completo</span>
                @if($admin)
                    <a href="{{ route('content.edit', 'precedentes-home') }}" class="home-pilot-btn tw-py-2 tw-px-3 tw-text-sm tw-ml-2" title="Editar conteúdo">
                        <i class="fa fa-pencil"></i> Editar
                    </a>
                @endif
            </div>
        </header>
        <div class="tw-p-5 md:tw-p-6">
            {!! optional($precedentes_home)->content ?? '' !!}
        </div>
    </section>
</div>
@endif
<!-- END Precedentes Vinculantes CPC -->

<!-- Temas Mais Consultados -->
@if(isset($popular_themes) && $popular_themes->count() > 0)
<div class="home-pilot-shell tw-pt-0">
    <section class="home-pilot-card tw-overflow-hidden">
        <header class="tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-px-5 tw-py-4 md:tw-px-6">
            <h3 class="tw-text-slate-900 tw-font-semibold tw-text-lg">Temas Mais Consultados</h3>
        </header>
        <div class="tw-p-5 md:tw-p-6">
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-3">
                @foreach($popular_themes as $theme)
                <div>
                    <a href="{{ url('/tema/' . $theme->slug) }}" class="tw-block tw-border tw-border-slate-200 tw-rounded-lg hover:tw-border-brand-300 hover:tw-bg-brand-50 tw-transition tw-p-4" style="text-decoration: none;">
                        <div class="tw-text-sm tw-font-semibold tw-text-brand-800">
                                {{ $theme->label ?? $theme->keyword }}
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endif
<!-- END Temas Mais Consultados -->

<!-- Quizzes Jurídicos -->
@if(isset($featured_quizzes) && $featured_quizzes->count() > 0)
<div class="home-pilot-shell tw-pt-0">
    <section class="home-pilot-card tw-overflow-hidden">
        <header class="tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-flex tw-items-center tw-justify-between tw-gap-3 tw-flex-wrap tw-px-5 tw-py-4 md:tw-px-6">
            <h3 class="tw-text-slate-900 tw-font-semibold tw-text-lg"><i class="fa fa-graduation-cap tw-text-brand-700"></i> Teste seus Conhecimentos</h3>
            <div>
                <a href="{{ route('quizzes.index') }}" class="home-pilot-btn tw-py-2 tw-px-3 tw-text-sm">
                    Ver Todos os Quizzes <i class="fa fa-arrow-right ml-1"></i>
                </a>
            </div>
        </header>
        <div class="tw-p-5 md:tw-p-6">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-3">
                @foreach($featured_quizzes as $quiz)
                <div>
                    <a href="{{ route('quiz.show', $quiz->slug) }}" class="tw-block tw-h-full tw-border tw-border-slate-200 tw-rounded-lg hover:tw-border-brand-300 tw-transition tw-p-4 tw-border-l-4 tw-border-l-brand-500" style="text-decoration: none;">
                        <div>
                            <div class="tw-font-semibold tw-text-brand-700 tw-mb-2">
                                {{ $quiz->title }}
                            </div>
                            <div class="tw-text-sm tw-text-slate-600 tw-space-y-1">
                                <span class="tw-block"><i class="fa fa-question-circle"></i> {{ $quiz->questions_count }} questões</span>
                                @if($quiz->tribunal)
                                <span class="tw-block"><i class="fa fa-building"></i> {{ $quiz->tribunal }}</span>
                                @endif
                                <span>
                                    @if($quiz->difficulty == 'easy')
                                        <span class="tw-text-emerald-700"><i class="fa fa-signal"></i> Fácil</span>
                                    @elseif($quiz->difficulty == 'hard')
                                        <span class="tw-text-red-700"><i class="fa fa-signal"></i> Difícil</span>
                                    @else
                                        <span class="tw-text-amber-700"><i class="fa fa-signal"></i> Médio</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endif
<!-- END Quizzes Jurídicos -->

@yield('content_results')

<!-- END Page Content -->

</div>
@endsection


<!-- Script Loading State -->
<script>
(function() {
    function initSearchHomeScripts() {
    const form = document.getElementById('trib-form');
    const loadingOverlay = document.getElementById('loading-overlay');
    const tribunalRadios = document.querySelectorAll('#radios-tribunais input[type="radio"]');

    function syncTribunalSelection() {
        let selectedTribunal = 'nenhum';

        tribunalRadios.forEach(function(radio) {
            const label = radio.closest('.home-pilot-radio');
            if (!label) {
                return;
            }

            if (radio.checked) {
                label.classList.add('is-selected');
                selectedTribunal = (label.textContent || '').trim();
            } else {
                label.classList.remove('is-selected');
            }
        });

        const selectedTribunalLabel = document.getElementById('selected-tribunal-label');
        if (selectedTribunalLabel) {
            selectedTribunalLabel.textContent = selectedTribunal;
        }
    }

    if (tribunalRadios.length > 0) {
        tribunalRadios.forEach(function(radio) {
            radio.addEventListener('change', syncTribunalSelection);
        });

        syncTribunalSelection();
    }
    
    if (form && loadingOverlay) {
        form.addEventListener('submit', function(e) {
            // Validar se há keyword e tribunal selecionado
            const keyword = form.querySelector('input[name="q"]').value.trim();
            const tribunal = form.querySelector('input[name="tribunal"]:checked');
            
            if (keyword.length >= 3 && tribunal) {
                // Mostrar loading
                loadingOverlay.classList.add('active');
                document.body.classList.add('loading');
            }
        });
    }
    
    // Função global para testar loading manualmente (pode usar no console)
    window.testLoading = function(seconds = 3) {
        console.log('🔄 Mostrando loading por ' + seconds + ' segundos...');
        loadingOverlay.classList.add('active');
        document.body.classList.add('loading');
        
        setTimeout(function() {
            loadingOverlay.classList.remove('active');
            document.body.classList.remove('loading');
            console.log('✅ Loading ocultado!');
        }, seconds * 1000);
    };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSearchHomeScripts);
    } else {
        initSearchHomeScripts();
    }
})();
</script>
<!-- END Script Loading State -->

@if($toStore ?? '')
    
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/mark.min.js" integrity="sha512-5CYOlHXGh6QpOFA/TeTylKLWfB3ftPsde7AnmhuitiTX4K5SqCLBeKro6sPS8ilsz1Q4NRx3v8Ko2IBiszzdww==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            function markit(term, sel) {
                //to config mark.js: https://markjs.io/configurator.html
                let context = document.querySelectorAll(sel);
                let instance = new Mark(context);
                instance.mark(term, {
                    //make mark.js ignore words with less than three characters
                    filter : function(textNode, foundTerm, totalCounter, counter) {
                        return foundTerm.length > 2;
                    },
                });
            }
                

            $( document ).ready(function() {

                //Primeira letra maiúsula, salvo se a palavra tiver menos de 3 caracteres
                function titleCase(str, limit = 3) {
                    var splitStr = str.toLowerCase().split(' ');
                    for (var i = 0; i < splitStr.length; i++) {
                        if(splitStr[i].length < limit) {
                            continue;
                        }
                        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
                    }
                    // Directly return the joined string
                    return splitStr.join(' '); 
                } 

                //fill the store-label input with the input name "q" valu without the " char
                let keywordSearched = $('input[name="q"]').val().trim();
                let rawTitle = keywordSearched.replace(/"/g, '');
                let title = titleCase(rawTitle);
                $('input[name="store-label"]').val(title);

                //click on the btn-similar-search
                $('#btn-similar-search').click(function() {

                    //show the similar-block
                    $('.similar-block').show();
                    $('#similar-searched').empty();
                    //enable the btn-store-search
                    $('#btn-store-search').attr('disabled', false);

                    let label = $('input[name="store-label"]').val();
                    //get select[name="typeToCompare"] option selected
                    let typeToCompare = $('select[name="typeToCompare"]').find(":selected").val();

                    //call the route searchByKeywordSimilarity
                    $.ajax({
                        url: "{{route('searchByKeywordSimilarity')}}",
                        type:"POST",
                        data: {
                            'keywordSearched': keywordSearched, 
                            'label': label,
                            'percentage':$('input[name="similarity-percentage"]').val(),
                            'typeToCompare': typeToCompare,
                            '_token':'{{ csrf_token() }}'
                        },
                        success:function(response) {
                            if(response.hasOwnProperty('success')) {
                                console.log('success')
                            }
                            //insert items on the div similar-searched
                            let similar = '';
                            console.log(response.success)

                            //if response.success is not empty, order by percentage
                            if(response.success.length > 0) {
                                response.success.sort(function(a, b) {
                                    return b.percentage - a.percentage;
                                });
                            }

                            response.success.forEach(function(item) {
                                if(typeToCompare == 'keyword') {
                                    itemToShow = item.keyword;
                                    itemSecondary = item.label;
                                } else {
                                    itemToShow = item.label;
                                    itemSecondary = item.keyword;
                                }

                                //TODO: alternate keyword and label on the parenthesis

                                //if item.percentage > 95, disable #btn-store-search and mark the item with a yellow color
                                //create three columns to fill the similar-searched div
                                similar += '<div class="tw-grid tw-grid-cols-3 tw-gap-2 tw-text-sm tw-p-2 tw-rounded tw-bg-slate-50 tw-border tw-border-slate-200">';
                                similar += '<div class="tw-text-slate-700">' + itemToShow + '</div>';
                                similar += '<div class="tw-text-slate-600">' + itemSecondary + '</div>';
                                similar += '<div class="tw-text-slate-900 tw-font-semibold">' + item.percentage + '%</div>';
                                similar += '</div>';




                                

                                // if(item.percentage > 95) {
                                //     $('#btn-store-search').attr('disabled', true);
                                //     similar += '<span style="background-color:yellow;">' + itemToShow + " - " + item.percentage + "%</span> ";
                                // } else {
                                //     similar += itemToShow + " - " + item.percentage + "% ";
                                // }
                            });
                            //remove the last comma on similar
                            similar = similar.slice(0, -2);
                            

                            $('#similar-searched').html(similar);

                            if(typeToCompare == 'keyword') {
                                termToMark = keywordSearched;
                            } else {
                                termToMark = label;
                            }
                            markit(termToMark, '#similar-searched');
                        },
                        error: function(response) {
                            $('#similar-searched').val(stringify(response));
                        },
                    });
  
                });

                //get on the database table 'pesquisas' all the similar searches and console.log them 
                $('#btn-store-search').on('click', function() {

                    let label = $('input[name="store-label"]').val();

                    //disable the btn-store-search
                    $('#btn-store-search').attr('disabled', true);
                    $(this).text('Salvando...');
                    $('#admin-store').empty().html('<div class="home-pilot-alert home-pilot-alert-info" role="alert">Salvando...(seja paciente)</div>');
                    
                    
                    //make a get request to the route getidbykeyword with the keywordSearched
                    $.ajax({
                        url: "{{route('getidbykeyword')}}",
                        type:"GET",
                        data: {
                            'keyword': keywordSearched, 
                            '_token':'{{ csrf_token() }}'
                        },
                        success:function(response) {
                            //make a post to save, now that we have the ID
                            $.ajax({
                                url: "{{route('adminstore')}}",
                                type:"POST",
                                data: {
                                    'id' : response.success,
                                    'label' : label,
                                    'create' : 1,
                                    'check' : 0,
                                    '_token' : '{{ csrf_token() }}'
                                },
                                success:function(response){
                                    if(response.hasOwnProperty('success') && response['success'] == 1) {
                                        console.log('created successfully');
                                        $('#admin-store').empty().append('<div class="home-pilot-alert home-pilot-alert-success" role="alert">Pesquisa salva com sucesso!</div>');
                                    }
                                //console.log(response);
                                },
                            }); 
                        },
                        error: function(response) {
                            console.log('ERROR ON REQUEST TO GET THE ID: ' + response);
                        },
                    });


            


                    
                    return;
                });

                markit(rawTitle, ".table-results");
            })
        </script>
    
@endif        

