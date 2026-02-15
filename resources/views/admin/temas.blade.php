@extends('layouts.admin')

@section('title', 'Temas & Pesquisas')

@section('content')
<div class="tw-max-w-5xl tw-mx-auto">
    <!-- Header -->
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4 tw-mb-8">
        <div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-900">Temas & Pesquisas</h1>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Gerencie os temas de pesquisa do site</p>
        </div>
        <div>
            <a href="{{ route('admin') }}" class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md tw-font-semibold tw-text-xs tw-text-gray-700 tw-uppercase tw-tracking-widest tw-shadow-sm hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-offset-2 tw-disabled:opacity-25 tw-transition tw-ease-in-out tw-duration-150">
                <i class="fas fa-arrow-left tw-mr-2"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>

    <div class="tw-bg-white tw-overflow-hidden tw-shadow-sm tw-rounded-lg tw-border tw-border-gray-200">
        <!-- Card Header -->
        <div class="tw-px-6 tw-py-4 tw-bg-gray-50 tw-border-b tw-border-gray-200 tw-flex tw-justify-between tw-items-center">
            <h2 class="tw-text-lg tw-font-medium tw-text-gray-900">Temas</h2>
            <button id="toggle-created" class="tw-text-sm tw-text-brand-600 hover:tw-text-brand-800 focus:tw-outline-none hover:tw-underline">
                hide created
            </button>
        </div>

        <div class="tw-p-6">
            <!-- Controls Grid -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-6 tw-gap-4 tw-mb-6">
                
                <!-- Busca -->
                <div class="tw-col-span-1 md:tw-col-span-2 xl:tw-col-span-2">
                    <label for="search-input" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Buscar</label>
                    <div class="tw-relative tw-rounded-md tw-shadow-sm">
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                            <i class="fas fa-search tw-text-gray-400"></i>
                        </div>
                        <input type="text" id="search-input" class="tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-block tw-w-full tw-pl-10 tw-sm:text-sm tw-border-gray-300 tw-rounded-md" placeholder="Digite keyword ou label...">
                    </div>
                </div>

                <!-- Filtro por Status -->
                <div>
                    <label for="filter-status" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Status</label>
                    <select id="filter-status" class="tw-mt-1 tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 tw-focus:outline-none tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-sm:text-sm tw-rounded-md">
                        <option value="all">Todos</option>
                        <option value="not_created" selected>Não Criados</option>
                        <option value="created">Criados</option>
                        <option value="checked">Verificados</option>
                        <option value="pending">Pendentes</option>
                    </select>
                </div>

                <!-- Ordenar por -->
                <div>
                    <label for="order-by" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Ordenar por</label>
                    <select id="order-by" class="tw-mt-1 tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 tw-focus:outline-none tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-sm:text-sm tw-rounded-md">
                        <option value="keyword">Alfabética</option>
                        <option value="results" selected>Resultados</option>
                        <option value="created_at">Data</option>
                    </select>
                </div>

                <!-- Direção -->
                <div>
                    <label for="order-direction" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Direção</label>
                    <select id="order-direction" class="tw-mt-1 tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 tw-focus:outline-none tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-sm:text-sm tw-rounded-md">
                        <option value="asc">Crescente</option>
                        <option value="desc" selected>Decrescente</option>
                    </select>
                </div>

                <!-- Por página -->
                <div>
                    <label for="per-page" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-1">Por página</label>
                    <select id="per-page" class="tw-mt-1 tw-block tw-w-full tw-pl-3 tw-pr-10 tw-py-2 tw-text-base tw-border-gray-300 tw-focus:outline-none tw-focus:ring-brand-500 tw-focus:border-brand-500 tw-sm:text-sm tw-rounded-md">
                        <option value="30">30</option>
                        <option value="60" selected>60</option>
                        <option value="120">120</option>
                        <option value="500">500</option>
                    </select>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-4 tw-mb-6 tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                <div class="tw-flex tw-flex-col">
                    <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Total</span>
                    <span class="tw-text-lg tw-font-bold tw-text-gray-900" id="stat-total">{{ $stats['total'] }}</span>
                </div>
                <div class="tw-flex tw-flex-col">
                    <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Criados</span>
                    <span class="tw-text-lg tw-font-bold tw-text-green-600" id="stat-created">{{ $stats['created'] }}</span>
                </div>
                <div class="tw-flex tw-flex-col">
                    <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Verificados</span>
                    <span class="tw-text-lg tw-font-bold tw-text-blue-600" id="stat-checked">{{ $stats['checked'] }}</span>
                </div>
                <div class="tw-flex tw-flex-col">
                    <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Pendentes</span>
                    <span class="tw-text-lg tw-font-bold tw-text-amber-600" id="stat-pending">{{ $stats['pending'] }}</span>
                </div>
                <div class="tw-flex tw-flex-col">
                    <span class="tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">Exibindo</span>
                    <span class="tw-text-lg tw-font-bold tw-text-gray-900" id="stat-showing">0</span>
                </div>
            </div>

            <!-- Batch Actions -->
            <div id="batch-actions" class="tw-hidden tw-mb-4 tw-bg-brand-50 tw-border tw-border-brand-200 tw-rounded-lg tw-p-4 tw-flex tw-items-center tw-justify-between tw-transition-all tw-duration-300">
                <div class="tw-flex tw-items-center tw-gap-4">
                    <span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-brand-100 tw-text-brand-800">
                        <span id="selected-count" class="tw-mr-1 tw-font-bold">0</span> selecionados
                    </span>
                    <button id="deselect-all" class="tw-text-sm tw-text-gray-600 hover:tw-text-gray-900 tw-underline">
                        Desmarcar todos
                    </button>
                </div>
                <div class="tw-flex tw-gap-2">
                    <button id="delete-selected" class="tw-inline-flex tw-items-center tw-px-3 tw-py-2 tw-border tw-border-transparent tw-text-sm tw-leading-4 tw-font-medium tw-rounded-md tw-text-white tw-bg-red-600 hover:tw-bg-red-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-red-500">
                        <i class="fas fa-trash tw-mr-2"></i> Deletar
                    </button>
                    <!-- Future: Add 'Create Selected' button here -->
                </div>
            </div>

            <!-- Content Container -->
            <div id="temas-container" class="tw-min-h-[200px]">
                <div class="tw-flex tw-justify-center tw-items-center tw-p-12">
                    <i class="fas fa-spinner fa-spin tw-text-brand-600 tw-text-3xl tw-mr-3"></i>
                    <span class="tw-text-gray-600 tw-text-lg">Carregando temas...</span>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination-container" class="tw-mt-4 tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-center tw-gap-4" style="display: none;">
                <div id="pagination-info" class="tw-text-sm tw-text-gray-700"></div>
                <div id="pagination-controls"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styles for dynamic content */
    .hide-created {
        /* Controlado via JS, mas garantindo transição suave */
        transition: opacity 0.3s ease;
    }
</style>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<script>
    // Configuração de Rotas para o JS externo
    window.adminRoutes = {
        store: "{{ route('adminstore') }}",
        delete: "{{ route('admindel') }}",
        getTemas: "{{ route('admin.getTemas') }}"
    };
</script>

<!-- Note: admin-tailwind.js is loaded via Vite in layout, or we can explicitely push it here if needed. 
     Since it is in the main vite build now, checking if we need to load it manually or if it's auto-injected.
     Ideally, we should rely on the valid layout including the built assets. 
     If the layout uses @vite(['resources/js/admin-tailwind.js']), it works.
     Let's assume the layout handles generic scripts, but since we added it to input array, we should reference it here 
     OR include it in the layout. Better to include here cleanly via Vite directive for specific page assets. -->
     
@vite(['resources/js/admin-tailwind.js'])
@endpush
