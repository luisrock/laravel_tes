@extends('layouts.admin')

@section('title', 'Upload de Acórdãos')

@section('content')
<div class="tw-max-w-5xl tw-mx-auto" x-data="{ 
    uploadModalOpen: false,
    teseId: null,
    tribunal: 'STF',
    numeroAcordao: '',
    
    openUploadModal(id, trib, acordao) {
        this.teseId = id;
        this.tribunal = trib;
        this.numeroAcordao = (trib === 'STF' && acordao) ? acordao : '';
        this.uploadModalOpen = true;
        // Reset form fields if needed or handle via x-model binding
    }
}">
    
    <!-- Header -->
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4 tw-mb-8">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Análise do Precedente</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Upload de acórdãos (PDFs) das teses STF/STJ para análise com IA</p>
        </div>
        <div>
            <a href="{{ route('admin') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-font-semibold tw-text-xs tw-text-gray-700 tw-uppercase tw-tracking-widest tw-shadow-sm hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-transition tw-ease-in-out tw-duration-150">
                <i class="fas fa-arrow-left tw-mr-2"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-p-6 tw-mb-8">
        <form method="GET" action="{{ route('admin.acordaos.index') }}" id="filterForm" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-12 tw-gap-4 tw-items-end">
            
            <!-- Tribunal -->
            <div class="lg:tw-col-span-2">
                <label for="tribunal" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Tribunal</label>
                <select name="tribunal" id="tribunal" class="tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 tw-sm:text-sm">
                    <option value="STF" {{ $tribunal === 'STF' ? 'selected' : '' }}>STF</option>
                    <option value="STJ" {{ $tribunal === 'STJ' ? 'selected' : '' }}>STJ</option>
                </select>
            </div>

            <!-- Per Page -->
            <div class="lg:tw-col-span-2">
                <label for="per_page" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Por página</label>
                <select name="per_page" id="per_page" onchange="this.form.submit()" class="tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 tw-sm:text-sm">
                    @foreach([10, 20, 50, 100, 200, 500, 1000] as $option)
                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Order -->
            <div class="lg:tw-col-span-2">
                <label for="order" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Ordenar</label>
                <select name="order" id="order" onchange="this.form.submit()" class="tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 tw-sm:text-sm">
                    <option value="desc" {{ $order === 'desc' ? 'selected' : '' }}>Mais recentes</option>
                    <option value="asc" {{ $order === 'asc' ? 'selected' : '' }}>Mais antigos</option>
                </select>
            </div>

            <!-- Search -->
            <div class="lg:tw-col-span-4">
                <label for="search" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Buscar tema</label>
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                        <i class="fas fa-search tw-text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="tw-block tw-w-full tw-pl-10 tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 tw-sm:text-sm" placeholder="Digite o número ou tema...">
                </div>
            </div>

            <!-- Actions -->
            <div class="lg:tw-col-span-2 tw-flex tw-items-end tw-gap-2">
                <button type="submit" class="tw-flex-1 tw-inline-flex tw-justify-center tw-items-center tw-px-4 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-md tw-shadow-sm tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500">
                    Buscar
                </button>
                <a href="{{ route('admin.acordaos.index', ['tribunal' => $tribunal]) }}" class="tw-inline-flex tw-items-center tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-shadow-sm tw-text-sm tw-leading-4 tw-font-medium tw-rounded-md tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500" title="Limpar filtros">
                    <i class="fas fa-times"></i>
                </a>
            </div>

            <!-- Checkboxes -->
            <div class="lg:tw-col-span-12 tw-flex tw-flex-col sm:tw-flex-row tw-gap-4 tw-mt-2">
                <div class="tw-flex tw-items-center">
                    <input type="checkbox" name="only_with_tese_checkbox" id="only_with_tese" 
                           class="tw-h-4 tw-w-4 tw-text-brand-600 tw-focus:ring-brand-500 tw-border-gray-300 tw-rounded"
                           {{ $onlyWithTese ? 'checked' : '' }}
                           onchange="document.getElementById('only_with_tese_hidden').value = this.checked ? '1' : '0'; this.form.submit();">
                    <label for="only_with_tese" class="tw-ml-2 tw-block tw-text-sm tw-text-gray-900">
                        Apenas temas com tese divulgada
                    </label>
                    <input type="hidden" name="only_with_tese" id="only_with_tese_hidden" value="{{ $onlyWithTese ? '1' : '0' }}">
                </div>
                
                <div class="tw-flex tw-items-center">
                    <input type="checkbox" name="only_without" id="only_without" value="1" 
                           class="tw-h-4 tw-w-4 tw-text-brand-600 tw-focus:ring-brand-500 tw-border-gray-300 tw-rounded"
                           {{ request('only_without') ? 'checked' : '' }}
                           onchange="this.form.submit()">
                    <label for="only_without" class="tw-ml-2 tw-block tw-text-sm tw-text-gray-900">
                        Apenas temas sem acórdãos
                    </label>
                </div>
            </div>
        </form>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="tw-mb-6 tw-bg-green-50 tw-border-l-4 tw-border-green-400 tw-p-4 tw-rounded-r-md tw-flex tw-justify-between tw-items-start" role="alert">
            <div class="tw-flex">
                <div class="tw-flex-shrink-0">
                    <i class="fas fa-check-circle tw-text-green-400"></i>
                </div>
                <div class="tw-ml-3">
                    <p class="tw-text-sm tw-text-green-700">{{ session('success') }}</p>
                </div>
            </div>
            <button type="button" class="tw-ml-auto tw-pl-3 tw-text-green-700" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="tw-mb-6 tw-bg-red-50 tw-border-l-4 tw-border-red-400 tw-p-4 tw-rounded-r-md tw-flex tw-justify-between tw-items-start" role="alert">
            <div class="tw-flex">
                <div class="tw-flex-shrink-0">
                    <i class="fas fa-exclamation-circle tw-text-red-400"></i>
                </div>
                <div class="tw-ml-3">
                    <p class="tw-text-sm tw-text-red-700">{{ session('error') }}</p>
                </div>
            </div>
            <button type="button" class="tw-ml-auto tw-pl-3 tw-text-red-700" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Content List -->
    @if($teses->count() > 0)
        <div class="tw-space-y-6">
            @foreach($teses as $tese)
                @php
                    $hasTese = !empty($tese->tese_texto);
                    $hasAcordaos = $tese->acordaos_count > 0;
                    
                    // Border Colors mapping
                    $borderColorClass = 'tw-border-gray-400'; // Default: No tese, no acordaos
                    if (!$hasTese && $hasAcordaos) $borderColorClass = 'tw-border-amber-500';
                    elseif ($hasTese && !$hasAcordaos) $borderColorClass = 'tw-border-brand-500'; // Blue-ish brand color
                    elseif ($hasTese && $hasAcordaos) $borderColorClass = 'tw-border-emerald-500';
                @endphp

                <div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border-l-4 {{ $borderColorClass }} tw-border-y tw-border-r tw-border-gray-200 hover:tw-shadow-md tw-transition-shadow">
                    <div class="tw-p-6">
                        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start tw-gap-4 tw-mb-4">
                            <div class="tw-flex-grow">
                                <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-flex tw-items-center tw-flex-wrap tw-gap-2">
                                    Tema {{ $tese->numero }} - {{ $tribunal }}
                                    @if($tese->acordaos_count == 0)
                                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-amber-100 tw-text-amber-800">
                                            ⚠️ Sem acórdãos
                                        </span>
                                    @endif
                                </h3>
                                <p class="tw-mt-1 tw-text-sm tw-text-gray-500 tw-line-clamp-3">
                                    {{ $tese->tema ?? 'Sem tema definido' }}
                                </p>
                            </div>
                            <div class="tw-flex-shrink-0">
                                <button type="button" 
                                        @click="openUploadModal({{ $tese->tese_id }}, '{{ $tribunal }}', '{{ $tese->acordao ?? '' }}')"
                                        class="tw-inline-flex tw-items-center tw-px-3 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-leading-4 tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500">
                                    <i class="fas fa-plus tw-mr-2"></i> Adicionar
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Acórdãos -->
                        @if($tese->acordaos && $tese->acordaos->count() > 0)
                            <div class="tw-mt-4 tw-bg-gray-50 tw-rounded-md tw-border tw-border-gray-200">
                                <ul class="tw-divide-y tw-divide-gray-200">
                                    @foreach($tese->acordaos as $acordao)
                                        <li class="tw-px-4 tw-py-3 tw-flex tw-items-center tw-justify-between tw-gap-4">
                                            <div class="tw-flex-grow tw-min-w-0">
                                                <div class="tw-flex tw-items-center tw-gap-2">
                                                    <i class="fas fa-file-pdf tw-text-red-500"></i>
                                                    <span class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">
                                                        {{ $acordao->filename_original }}
                                                    </span>
                                                </div>
                                                <div class="tw-mt-1 tw-flex tw-flex-wrap tw-gap-x-4 tw-gap-y-1 tw-text-xs tw-text-gray-500">
                                                    <span><span class="tw-font-medium">Tipo:</span> {{ $acordao->tipo }}</span>
                                                    <span><span class="tw-font-medium">Nº:</span> {{ $acordao->numero_acordao }}</span>
                                                    <span><span class="tw-font-medium">Ver:</span> {{ $acordao->version }}</span>
                                                    <span>{{ number_format($acordao->file_size / 1024, 2) }} KB</span>
                                                </div>
                                            </div>
                                            <div class="tw-flex-shrink-0">
                                                <form action="{{ route('admin.acordaos.destroy', $acordao) }}" 
                                                      method="POST" 
                                                      class="tw-inline"
                                                      onsubmit="return confirm('Tem certeza que deseja remover este acórdão?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="tw-text-gray-400 hover:tw-text-red-600 tw-transition-colors">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="tw-mt-4 tw-flex tw-items-center tw-justify-center tw-p-4 tw-text-sm tw-text-gray-500 tw-bg-gray-50 tw-rounded-md tw-border tw-border-dashed tw-border-gray-300">
                                <i class="fas fa-info-circle tw-mr-2"></i> Nenhum acórdão vinculado
                            </div>
                        @endif

                        <!-- Footer Actions -->
                        @if(!empty($tese->link))
                            <div class="tw-mt-4 tw-flex tw-justify-end">
                                <a href="{{ $tese->link }}" target="_blank" class="tw-inline-flex tw-items-center tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">
                                    Ver site oficial <i class="fas fa-external-link-alt tw-ml-1"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="tw-mt-8">
            {{ $teses->withQueryString()->links() }}
        </div>
    @else
        <div class="tw-rounded-md tw-bg-blue-50 tw-p-4 tw-flex">
            <div class="tw-flex-shrink-0">
                <i class="fas fa-info-circle tw-text-blue-400"></i>
            </div>
            <div class="tw-ml-3">
                <h3 class="tw-text-sm tw-font-medium tw-text-blue-800">Nenhuma tese encontrada</h3>
                <div class="tw-mt-2 tw-text-sm tw-text-blue-700">
                    <p>Tente ajustar os filtros de busca ou selecionar outro tribunal.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Upload Modal (Alpine.js) -->
    <div x-show="uploadModalOpen" 
         x-transition:enter="tw-ease-out tw-duration-300"
         x-transition:enter-start="tw-opacity-0"
         x-transition:enter-end="tw-opacity-100"
         x-transition:leave="tw-ease-in tw-duration-200"
         x-transition:leave-start="tw-opacity-100"
         x-transition:leave-end="tw-opacity-0"
         class="tw-fixed tw-inset-0 tw-z-50 tw-overflow-y-auto" 
         style="display: none;"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div class="tw-flex tw-items-end tw-justify-center tw-min-h-screen tw-pt-4 tw-px-4 tw-pb-20 tw-text-center sm:tw-block sm:tw-p-0">
            <div x-show="uploadModalOpen" 
                 x-transition:enter="tw-ease-out tw-duration-300"
                 x-transition:enter-start="tw-opacity-0"
                 x-transition:enter-end="tw-opacity-100" 
                 x-transition:leave="tw-ease-in tw-duration-200"
                 x-transition:leave-start="tw-opacity-100" 
                 x-transition:leave-end="tw-opacity-0"
                 class="tw-fixed tw-inset-0 tw-bg-gray-500 tw-bg-opacity-75 tw-transition-opacity" 
                 @click="uploadModalOpen = false"
                 aria-hidden="true"></div>

            <span class="tw-hidden sm:tw-inline-block sm:tw-align-middle sm:tw-h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="uploadModalOpen" 
                 x-transition:enter="tw-ease-out tw-duration-300"
                 x-transition:enter-start="tw-opacity-0 tw-translate-y-4 sm:tw-translate-y-0 sm:tw-scale-95"
                 x-transition:enter-end="tw-opacity-100 tw-translate-y-0 sm:tw-scale-100"
                 x-transition:leave="tw-ease-in tw-duration-200"
                 x-transition:leave-start="tw-opacity-100 tw-translate-y-0 sm:tw-scale-100"
                 x-transition:leave-end="tw-opacity-0 tw-translate-y-4 sm:tw-translate-y-0 sm:tw-scale-95"
                 class="tw-inline-block tw-align-bottom tw-bg-white tw-rounded-lg tw-text-left tw-overflow-hidden tw-shadow-xl tw-transform tw-transition-all sm:tw-my-8 sm:tw-align-middle sm:tw-max-w-lg sm:tw-w-full">
                
                <form action="{{ route('admin.acordaos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="tw-bg-white tw-px-4 tw-pt-5 tw-pb-4 sm:tw-p-6 sm:tw-pb-4">
                        <div class="sm:tw-flex sm:tw-items-start">
                            <div class="tw-mx-auto tw-flex-shrink-0 tw-flex tw-items-center tw-justify-center tw-h-12 tw-w-12 tw-rounded-full tw-bg-brand-100 sm:tw-mx-0 sm:tw-h-10 sm:tw-w-10">
                                <i class="fas fa-cloud-upload-alt tw-text-brand-600"></i>
                            </div>
                            <div class="tw-mt-3 tw-text-center sm:tw-mt-0 sm:tw-ml-4 sm:tw-text-left tw-w-full">
                                <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900" id="modal-title">
                                    Adicionar Acórdão
                                </h3>
                                <div class="tw-mt-4 tw-space-y-4">
                                    <input type="hidden" name="tese_id" x-model="teseId">
                                    <input type="hidden" name="tribunal" x-model="tribunal">
                                    @if($onlyWithTese)
                                        <input type="hidden" name="only_with_tese" value="1">
                                    @endif

                                    <!-- Nº Acórdão -->
                                    <div>
                                        <label for="numero_acordao" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Nº do Acórdão</label>
                                        <input type="text" name="numero_acordao" id="numero_acordao" x-model="numeroAcordao" required
                                               class="tw-mt-1 tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-block tw-w-full tw-shadow-sm tw-sm:text-sm tw-border-gray-300 tw-rounded-md"
                                               placeholder="Ex: ARE 1553607">
                                    </div>

                                    <!-- Tipo -->
                                    <div>
                                        <label for="tipo" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Tipo</label>
                                        <select id="tipo" name="tipo" required
                                                class="tw-mt-1 tw-block tw-w-full tw-py-2 tw-px-3 tw-border tw-border-gray-300 tw-bg-white tw-rounded-md tw-shadow-sm tw-focus:outline-none tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-sm:text-sm">
                                            <option value="Principal">Principal</option>
                                            <option value="Embargos de Declaração">Embargos de Declaração</option>
                                            <option value="Modulação de Efeitos">Modulação de Efeitos</option>
                                            <option value="Recurso Extraordinário">Recurso Extraordinário</option>
                                            <option value="Recurso Especial">Recurso Especial</option>
                                            <option value="Outros">Outros</option>
                                        </select>
                                    </div>

                                    <!-- Label -->
                                    <div>
                                        <label for="label" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Label (Opcional)</label>
                                        <input type="text" name="label" id="label"
                                               class="tw-mt-1 tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-block tw-w-full tw-shadow-sm tw-sm:text-sm tw-border-gray-300 tw-rounded-md"
                                               placeholder="Descrição livre...">
                                    </div>

                                    <!-- File -->
                                    <div>
                                        <label for="file" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">Arquivo PDF</label>
                                        <input type="file" name="file" id="file" accept=".pdf" required
                                               class="tw-mt-1 tw-block tw-w-full tw-text-sm tw-text-gray-500
                                                      file:tw-mr-4 file:tw-py-2 file:tw-px-4
                                                      file:tw-rounded-md file:tw-border-0
                                                      file:tw-text-sm file:tw-font-semibold
                                                      file:tw-bg-brand-50 file:tw-text-brand-700
                                                      hover:file:tw-bg-brand-100">
                                        <p class="tw-mt-1 tw-text-xs tw-text-gray-500">Máx. 10MB. Apenas PDF.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tw-bg-gray-50 tw-px-4 tw-py-3 sm:tw-px-6 sm:tw-flex sm:tw-flex-row-reverse">
                        <button type="submit" class="tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-shadow-sm tw-px-4 tw-py-2 tw-bg-brand-600 tw-text-base tw-font-medium tw-text-white hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 sm:tw-ml-3 sm:tw-w-auto sm:tw-text-sm">
                            Enviar
                        </button>
                        <button type="button" @click="uploadModalOpen = false" class="tw-mt-3 tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-gray-300 tw-shadow-sm tw-px-4 tw-py-2 tw-bg-white tw-text-base tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 sm:tw-mt-0 sm:tw-ml-3 sm:tw-w-auto sm:tw-text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
