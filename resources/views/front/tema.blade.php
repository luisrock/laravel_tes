@extends('front.base')

@section('page-title', $label)

@yield('stj')

@section('content')

@auth
@php
    $admin = in_array(Auth::user()->email, ['mauluis@gmail.com','trator70@gmail.com','ivanaredler@gmail.com'])
@endphp
@endauth


<!-- Page Content -->

<!-- Hero -->
<div class="bg-body-light" style="{{ $display_pdf }}">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-sm-fill h3 my-2">
                <a href="{{ url('/') }}">
                Teses & Súmulas
                </a>
                <span class="text-muted">sobre</span> {{ $label }}
            </h1>
            <span>
                <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                    class="badge badge-primary">Extensão para o Chrome</a>
            </span>
        </div>
        <p>
            Faça <a href="{{ route('searchpage') }}">outra pesquisa</a> ou veja as <a href="{{ route('alltemaspage') }}">pesquisas prontas</a>.
            @if($admin)
            <br><a href="{{ route('admin') }}">Admin</a>
            @endif
        </p>
    </div>
</div>
<!-- END Hero -->

<div class="content" id="content-results">
    

    @if($concept)
        @if($concept_validated_at || $admin )    
    <!-- conceito -->
    <div class="block block-conceito">
        <table class="table table-striped table-vcenter table-results">
            <tbody>                                                
                <tr>
                    <td>
                        <h4 class="h5 mt-3 mb-2">
                            <a id="open-concept" href="#">Resumo</a>
                        </h4>
                        <p class="d-sm-block text-muted" id="conceito">{{$concept}}</p>
                        <!-- <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">Gerado por GPT-4</span> -->
                        <!-- if admin, insert 3 buttons: validate, edit or remove  -->
                        @if($admin)
                        <div id = "content-actions-buttons" style="display: flex;justify-content: flex-end; column-gap: 10px;">
                            @if(!$concept_validated_at)
                            <button type="button" class="btn btn-sm btn-success" id="#concept-validate" data-concept-id="{{$id}}">
                                <i class="fa fa-check"></i> Validar
                            </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-warning" id="#concept-edit" data-concept-id="{{$id}}">
                                <i class="fa fa-edit"></i> Editar
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="#concept-remove" data-concept-id="{{$id}}">
                                <i class="fa fa-trash"></i> Remover
                            </button>
                        </div>

<!-- modais -->
<!-- Edit concept modal -->
<div class="modal fade" id="editConceptModal" tabindex="-1" role="dialog" aria-labelledby="editConceptModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editConceptModalLabel">Editar Conceito</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <textarea id="editConceptTextarea" class="form-control" rows="5"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="saveEditConcept">Salvar</button>
      </div>
    </div>
  </div>
</div>

<!-- Remove concept modal -->
<div class="modal fade" id="removeConceptModal" tabindex="-1" role="dialog" aria-labelledby="removeConceptModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="removeConceptModalLabel">Remover Conceito</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Tem certeza de que deseja remover este conceito?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmRemoveConcept">Remover</button>
      </div>
    </div>
  </div>
</div>
<!-- modais -->
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
        @endif
    
    @else
    <!-- sem conceito -->
      @if($admin)
    <!-- criar um textarea com um botao para buscar conceito via GPT-3  -->
    <div class="block block-conceito">
        <table class="table table-striped table-vcenter table-results">
            <tbody>   
                <tr>
                  <td>
                    <h4 class="h5 mt-3 mb-2">
                        <a href="#">Gerador de Conceito</a>
                    </h4>
                    <input type="radio" id="gpt-3.5-turbo" name="gpt-model" value="gpt-3.5-turbo" >
                    <label for="gpt-3.5-turbo">GPT-3.5 Turbo</label>
                    <input type="radio" id="gpt-4" name="gpt-model" value="gpt-4" checked>
                    <label for="gpt-4">GPT-4</label>
                  </td>
                </tr>
                <tr>
                    <td>
                      <code>System Prompt</code>
                        <textarea id="concept-system-prompt" class="form-control" rows="3">Você é um jurista brasileiro, professor de direito e conhecedor da jurisprudência formada até 2021. Você nunca inventa e quando não sabe, diz apenas 'Desculpe'.</textarea>
                    </td>
                    <td>
                      <code>User Prompt</code>
                        <textarea id="concept-user-prompt" class="form-control" rows="3">Apresente um conceito objetivo e didático sobre o seguinte tema: {{$label}}</textarea>
                    </td>
                </tr>                                             
                <tr>
                    <td>
                        <p class="d-sm-block text-muted" id="conceito-gerado"></p>
                        <!-- <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">Gerado por GPT-4</span> -->
                        <!-- if admin, insert 3 buttons: validate, edit or remove  -->
                        <div id = "content-actions-buttons" style="display: flex;justify-content: flex-end; column-gap: 10px;">
                            <button type="button" class="btn btn-sm btn-success" id="concept-create" data-concept-id="{{$id}}" data-concept-label="{{$label}}" data-concept-save-route="{{ route('save-concept') }}" data-concept-generate-route="{{ route('generate-concept') }}">
                                <span id="original-content"> 
                                  <i class="fa fa-check"></i> Gerar
                                </span>
                                <span id="loading" style="display:none;">
                                  <i class="fa fa-spinner fa-spin"></i> Aguarde...
                                </span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
      @endif
    @endif


    <!-- Results -->
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-block nav-tabs-tribunais" data-toggle="tabs" role="tablist" style="{{ $display_pdf }}">

            @foreach($output as $out => $put)
            @php 
            $class_link = ($loop->first) ? "nav-link active" : "nav-link"; 
            @endphp 
            <li class="nav-item nav-item-tribunal" id="nav-{{$out}}">
                <a class="{{$class_link}}" href="#tema-{{$out}}">{{ strtoupper($out) }}</a>
            </li>
            @endforeach

        </ul>
        
        <div class="block-content tab-content overflow-hidden">


            @foreach($output as $out => $put)
            @php
            $class_pane = ($loop->first) ? "tab-pane fade fade-up active show" : "tab-pane fade fade-up"; 
            @endphp
            <div class="{{$class_pane}}" id="tema-{{$out}}" role="tabpanel">
                <div class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">
                    <code>{{ $label }}</code> - {{ strtoupper($out) }} 
                    (resultados: <code>{{($output[$out]['total_count']) }}</code>)
                </div>
                <table class="table table-striped table-vcenter table-results">
                    <tbody>
                        @includeif('front.results.inners.' . strtolower($out) . '_sum', ['output' => $output[$out]])
                        @includeif('front.results.inners.' . strtolower($out) . '_rep', ['output' => $output[$out]])
                    </tbody>
                </table>
            </div>
            @endforeach
                
        </div>
    </div>
    <!-- END Results -->

</div>


@endsection