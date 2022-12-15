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

        <div class="spinner-border text-primary" id="spinning" role="status" style="display:none;">
            <!--                         <span class="sr-only">Buscando...</span> -->
        </div>
        <div id="loading-text" style="display:none;">
            Acessando a base do órgão...
        </div>

    </form>
    @auth
        @php $toStore = false; @endphp
        @if(in_array(Auth::user()->email, ['mauluis@gmail.com','trator70@gmail.com','ivanaredler@gmail.com']))
            @if(!empty($output['total_count']))
            @php $toStore = true; @endphp
            <div>
                <br><button class="btn btn-sm btn-primary" id="btn-store-search"> Salvar Pesquisa </button> 
                <input type="text" name="store-title">
            </div>
            <div>
                <div id="similar-searched"></div>
            </div>
            @endif
        @endif        
    @endauth
</div>
<!-- END Search -->

@yield('content_results')

<!-- END Page Content -->

@endsection


@if($toStore ?? '')
    
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/mark.min.js" integrity="sha512-5CYOlHXGh6QpOFA/TeTylKLWfB3ftPsde7AnmhuitiTX4K5SqCLBeKro6sPS8ilsz1Q4NRx3v8Ko2IBiszzdww==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
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

                //fill the store-title input with the input name "q" valu without the " char
                let rawTitle = $('input[name="q"]').val().replace(/"/g, '').trim();
                let title = titleCase(rawTitle);
                $('input[name="store-title"]').val(title);
                $.ajax({
                    url: "{{route('searchByKeywordSimilarity')}}",
                    type:"POST",
                    data: {
                        'keyword':title, 
                        '_token':'{{ csrf_token() }}'
                    },
                    success:function(response) {
                        if(response.hasOwnProperty('success')) {
                            console.log('success')
                        }
                        //insert items on the div similar-searched
                        let similar = '';
                        console.log(response.success)
                        response.success.forEach(function(item) {
                            //if item.percentage > 95, disable #btn-store-search and mark the item with a yellow color
                            if(item.percentage > 95) {
                                $('#btn-store-search').attr('disabled', true);
                                similar += '<span style="background-color:yellow;">' + item.label + " (" + item.percentage + "%)</span>, ";
                            } else {
                                similar += item.label + " (" + item.percentage + "%), ";
                            }
                        });
                        //remove the last comma on similar
                        similar = similar.slice(0, -2);
                        $('#similar-searched').html(similar);
                    },
                    error: function(response) {
                        $('#similar-searched').val(stringify(response));
                    },
                });

                //to config mark.js: https://markjs.io/configurator.html
                let context = document.querySelectorAll(".table-results");
                
                let instance = new Mark(context);
                instance.mark(rawTitle, {
                    //make mark.js ignore words with less than three characters
                    filter : function(textNode, foundTerm, totalCounter, counter) {
                        return foundTerm.length > 2;
                    },
                   
                });
                
                //get on the database table 'pesquisas' all the similar searches and console.log them 
                $('#btn-store-search').click(function() {
                    return;
                });
            })
        </script>
    
@endif        

