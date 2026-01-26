@extends('layouts.app')

@section('admin-styles')
<link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
<style>
    .tese-card {
        border-left: 4px solid #5c80d1;
        margin-bottom: 1.5rem;
        transition: all 0.2s ease;
    }
    .tese-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    /* Sem tese e sem acórdão: cinza */
    .tese-card.no-tese.no-acordaos {
        border-left-color: #9ca3af;
    }
    /* Sem tese e com acórdão: laranja */
    .tese-card.no-tese.has-acordaos {
        border-left-color: #f59e0b;
    }
    /* Com tese e sem acórdão: azul */
    .tese-card.has-tese.no-acordaos {
        border-left-color: #5c80d1;
    }
    /* Com tese e com acórdão: verde */
    .tese-card.has-tese.has-acordaos {
        border-left-color: #10b981;
    }
    .acordao-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }
    .acordao-item:last-child {
        margin-bottom: 0;
    }
    .acordao-info {
        flex: 1;
    }
    .acordao-name {
        font-weight: 500;
        color: #1f2937;
    }
    .acordao-meta {
        font-size: 0.875rem;
        color: #6b7280;
    }
    .filter-form {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .warning-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background: #fef3c7;
        color: #92400e;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Análise do Precedente - Upload de Acórdãos</h2>
                    <p class="text-muted mb-0">Faça upload de acórdãos (PDFs) das teses STF/STJ para análise com IA</p>
                </div>
                <div>
                    <a href="{{ route('admin') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-form">
                <form method="GET" action="{{ route('admin.acordaos.index') }}" class="row g-3" id="filterForm">
                    <div class="col-md-3">
                        <label for="tribunal" class="form-label">Tribunal</label>
                        <select name="tribunal" id="tribunal" class="form-control">
                            <option value="STF" {{ $tribunal === 'STF' ? 'selected' : '' }}>STF</option>
                            <option value="STJ" {{ $tribunal === 'STJ' ? 'selected' : '' }}>STJ</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Buscar tema</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               value="{{ request('search') }}" placeholder="Digite o número ou tema...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <a href="{{ route('admin.acordaos.index', ['tribunal' => $tribunal]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="only_with_tese_checkbox" id="only_with_tese" 
                                   {{ $onlyWithTese ? 'checked' : '' }}
                                   onchange="
                                       var hidden = document.getElementById('only_with_tese_hidden');
                                       hidden.value = this.checked ? '1' : '0';
                                       this.form.submit();
                                   ">
                            <label class="form-check-label" for="only_with_tese">
                                Apenas temas com tese divulgada
                            </label>
                        </div>
                        <!-- Campo hidden que sempre envia o valor -->
                        <input type="hidden" name="only_with_tese" id="only_with_tese_hidden" value="{{ $onlyWithTese ? '1' : '0' }}">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="only_without" id="only_without" 
                                   value="1" {{ request('only_without') ? 'checked' : '' }}>
                            <label class="form-check-label" for="only_without">
                                Apenas temas sem acórdãos
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mensagens -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Lista de Teses -->
            @if($teses->count() > 0)
                @foreach($teses as $tese)
                    @php
                        $hasTese = !empty($tese->tese_texto);
                        $hasAcordaos = $tese->acordaos_count > 0;
                        $cardClasses = 'tese-card ';
                        $cardClasses .= $hasTese ? 'has-tese ' : 'no-tese ';
                        $cardClasses .= $hasAcordaos ? 'has-acordaos' : 'no-acordaos';
                    @endphp
                    <div class="card {{ $cardClasses }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">
                                        Tema {{ $tese->numero }} - {{ $tribunal }}
                                        @if($tese->acordaos_count == 0)
                                            <span class="warning-badge ml-2">⚠️ Sem acórdãos</span>
                                        @endif
                                    </h5>
                                    <p class="card-text text-muted mb-0">
                                        {{ \Illuminate\Support\Str::limit($tese->tema ?? 'Sem tema', 200) }}
                                    </p>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#uploadModal{{ $tese->tese_id }}"
                                        onclick="setUploadData({{ $tese->tese_id }}, '{{ $tribunal }}', '{{ $tese->acordao ?? '' }}')">
                                    <i class="fas fa-plus"></i> Adicionar Acórdão
                                </button>
                            </div>

                            @if($tese->acordaos && $tese->acordaos->count() > 0)
                                <div class="mt-3">
                                    <h6 class="mb-2">Acórdãos vinculados:</h6>
                                    @foreach($tese->acordaos as $acordao)
                                        <div class="acordao-item">
                                            <div class="acordao-info">
                                                <div class="acordao-name">
                                                    <i class="fas fa-file-pdf text-danger"></i> 
                                                    {{ $acordao->filename_original }}
                                                </div>
                                                <div class="acordao-meta">
                                                    Tipo: {{ $acordao->tipo }} | 
                                                    Nº: {{ $acordao->numero_acordao }} | 
                                                    Versão: {{ $acordao->version }} | 
                                                    {{ number_format($acordao->file_size / 1024, 2) }} KB
                                                </div>
                                            </div>
                                            <form action="{{ route('admin.acordaos.destroy', $acordao) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja remover este acórdão?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <!-- Rodapé do card: "Nenhum acórdão vinculado" ou link "Ver Original" -->
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                @if($tese->acordaos && $tese->acordaos->count() > 0)
                                    <div></div>
                                @else
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle"></i> Nenhum acórdão vinculado
                                    </div>
                                @endif
                                @if($tribunal === 'STF' && !empty($tese->link))
                                    <a href="{{ $tese->link }}" target="_blank" class="text-primary" title="Ver no site oficial do STF" style="text-decoration: none;">
                                        <i class="fa fa-external-link"></i> Ver Original
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Upload -->
                    <div class="modal fade" id="uploadModal{{ $tese->tese_id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Adicionar Acórdão - Tema {{ $tese->numero }}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('admin.acordaos.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="tese_id" id="tese_id_{{ $tese->tese_id }}" value="{{ $tese->tese_id }}">
                                        <input type="hidden" name="tribunal" id="tribunal_{{ $tese->tese_id }}" value="{{ $tribunal }}">
                                    @if($onlyWithTese)
                                        <input type="hidden" name="only_with_tese" value="1">
                                    @endif

                                        <div class="form-group">
                                            <label for="numero_acordao_{{ $tese->tese_id }}">Nº do Acórdão</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="numero_acordao_{{ $tese->tese_id }}" 
                                                   name="numero_acordao" 
                                                   value="{{ $tribunal === 'STF' && !empty($tese->acordao) ? $tese->acordao : '' }}"
                                                   placeholder="Ex: ARE 1553607, RE 559937" 
                                                   required>
                                            <small class="form-text text-muted">Número do acórdão (ex: ARE 1553607, RE 559937, ADI 1234)</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="tipo_{{ $tese->tese_id }}">Tipo</label>
                                            <select class="form-control" id="tipo_{{ $tese->tese_id }}" name="tipo" required>
                                                <option value="Principal" selected>Principal</option>
                                                <option value="Embargos de Declaração">Embargos de Declaração</option>
                                                <option value="Modulação de Efeitos">Modulação de Efeitos</option>
                                                <option value="Recurso Extraordinário">Recurso Extraordinário</option>
                                                <option value="Recurso Especial">Recurso Especial</option>
                                                <option value="Outros">Outros</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="label_{{ $tese->tese_id }}">Label (opcional)</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="label_{{ $tese->tese_id }}" 
                                                   name="label" 
                                                   placeholder="Descrição livre do acórdão">
                                        </div>

                                        <div class="form-group">
                                            <label for="file_{{ $tese->tese_id }}">Arquivo PDF</label>
                                            <input type="file" 
                                                   class="form-control-file" 
                                                   id="file_{{ $tese->tese_id }}" 
                                                   name="file" 
                                                   accept=".pdf"
                                                   required>
                                            <small class="form-text text-muted">Tamanho máximo: 50MB. Apenas arquivos PDF.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Enviar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Paginação -->
                <div class="mt-4">
                    {{ $teses->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma tese encontrada com os filtros selecionados.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function setUploadData(teseId, tribunal, acordao) {
    // Preencher campos do modal com dados da tese
    document.getElementById('tese_id_' + teseId).value = teseId;
    document.getElementById('tribunal_' + teseId).value = tribunal;
    
    // Auto-preenchimento do número do acórdão para STF (se disponível)
    if (tribunal === 'STF' && acordao) {
        document.getElementById('numero_acordao_' + teseId).value = acordao;
    }
}
</script>
@endsection
