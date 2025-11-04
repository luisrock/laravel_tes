@extends('front.base')

@section('page-title', 'Pesquisa')

@section('content')
<!-- Hero -->
<div class="bg-body-light" style="{{ $display_pdf }}">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-sm-fill h3 my-2">
                <a href="{{ url('/') }}">
                Teses & Súmulas
                </a>
            </h1>
            <span>
                <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                    class="badge badge-primary">Extensão para o Chrome</a>
            </span>
        </div>
        <p>
            Pesquisa Simplificada de Súmulas e Teses de Repetitivos e de Repercussão Geral
            feita na base de dados de tribunais superiores
            e outros órgãos relevantes, com geração opcional de PDF contendo os resultados.
            Prepare seu estudo|aula|decisão|petição|parecer etc.
        </p>
    </div>
</div>
<!-- END Hero -->


<!--mpdf  <h2>Teses e Súmulas</h2> mpdf-->


<!-- Search -->
<div class="content" style="{{ $display_pdf }}">
    <form method="GET" id="trib-form">

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="input-group">
            <input type="text" class="form-control" name="q" value="{{ $keyword ?? '' }}" placeholder="Buscar..."
                required>
            <!-- <input type="hidden" name="keylabel"> -->
            <div class="input-group-append">
                <span class="input-group-text" style="cursor:pointer;"
                    onclick="document.getElementById('trib-form').submit();">
                    <i class="fa fa-fw fa-search"></i>
                </span>
            </div>
        </div>

        <div id="radios-tribunais">

            @foreach ($lista_tribunais as $t => $arr)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tribunal" id="{{ strtolower($t) }}" value="{{ $t }}"
                    @if ( !empty($tribunal) && strtolower($tribunal) === strtolower($t) ) checked @endif
                    <?php //echo ($tribunal === $t) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="{{ strtolower($t) }}">
                    {{ $t }}
                </label>
            </div>
            @endforeach

        </div>


        <div class="input-group">
            <input class="btn btn-sm btn-primary" id="btn-send-trib-form" type="submit" value="pesquisar">
        </div>

    </form>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Carregando...</span>
        </div>
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
            <div id="admin-store">
                <div>
                    <input type="text" name="store-label" style="width: 300;"><br> 
                    <button class="btn btn-sm btn-secondary" id="btn-similar-search"> Similares </button>
                    <select name="typeToCompare">
                        <option value="label" selected>by label</option>
                        <option value="keyword">by keyword</option>
                    </select>
                    <input type="number" name="similarity-percentage" value="80" min="0" max="100" style="width: 50px;">%
                </div>
                <div class="similar-block" style="display:none;">
                    <div id="similar-searched"></div>
                    <button class="btn btn-sm btn-primary" id="btn-store-search" disabled> Salvar Pesquisa </button>
                    
                </div>
            </div>
            @endif
        @endif        
    @endauth
</div>
<!-- END Search -->

<!-- Temas Mais Consultados -->
@if(isset($popular_themes) && $popular_themes->count() > 0)
<div class="content">
    <div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title">🔥 Temas Mais Consultados</h3>
            <div class="block-options">
                <span class="text-muted">Explore os temas mais populares</span>
            </div>
        </div>
        <div class="block-content">
            <div class="row">
                @foreach($popular_themes as $theme)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <a href="{{ url('/tema/' . $theme->slug) }}" class="block block-link-shadow text-center" style="text-decoration: none;">
                        <div class="block-content block-content-full">
                            <div class="font-size-sm font-w600 text-primary">
                                {{ $theme->label ?? $theme->keyword }}
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
<!-- END Temas Mais Consultados -->

@yield('content_results')

<!-- END Page Content -->

@endsection


<!-- Script Loading State -->
<script>
(function() {
    const form = document.getElementById('trib-form');
    const loadingOverlay = document.getElementById('loading-overlay');
    
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
                                similar += '<div class="row">';
                                similar += '<div class="col-4">' + itemToShow + '</div>';
                                similar += '<div class="col-4">' + itemSecondary + '</div>';
                                similar += '<div class="col-4">' + item.percentage + '%</div>';
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
                    $('#admin-store').empty().html('<div class="alert alert-info" role="alert">Salvando...(seja paciente)</div>');
                    
                    
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
                                        $('#admin-store').empty().append('<div class="alert alert-success" role="alert">Pesquisa salva com sucesso!</div>');
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

