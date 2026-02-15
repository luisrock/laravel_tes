@props(['keyword' => '', 'tribunal' => '', 'lista_tribunais' => []])

<div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 hover:tw-shadow-md tw-transition-shadow tw-duration-300">
    <form method="GET" action="{{ route('searchpage') }}" id="trib-form" class="tw-p-6 md:tw-p-8 tw-space-y-6">
        
        <div class="tw-space-y-2 tw-text-center md:tw-text-left">
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-900 tw-tracking-tight">Pesquisa de Teses e Súmulas</h1>
            <p class="tw-text-slate-600 tw-text-base md:tw-text-lg">Consulta rápida, objetiva e responsiva para jurisprudência dos principais tribunais.</p>
        </div>

        @if (session('success'))
        <div class="tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-text-emerald-800 tw-rounded-lg tw-p-4 tw-flex tw-items-start tw-gap-3" role="alert">
            <i class="fa fa-check-circle tw-mt-1"></i>
            <div class="tw-flex-1">
                <strong>{{ session('success') }}</strong>
            </div>
            <button type="button" class="tw-text-emerald-600 hover:tw-text-emerald-800" onclick="this.closest('[role=alert]').remove()" aria-label="Fechar">
                <i class="fa fa-times"></i>
            </button>
        </div>
        @endif

        @if ($errors->any())
        <div class="tw-bg-red-50 tw-border tw-border-red-200 tw-text-red-800 tw-rounded-lg tw-p-4" role="alert">
            <ul class="tw-list-disc tw-list-inside tw-space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="tw-flex tw-gap-3">
            <div class="tw-group tw-relative tw-flex tw-items-center tw-w-full tw-bg-white tw-border tw-border-slate-300 tw-rounded-lg tw-shadow-sm focus-within:tw-ring-2 focus-within:tw-ring-brand-500 focus-within:tw-border-brand-500 tw-transition-all">
                <div class="tw-pl-3 tw-flex tw-items-center tw-pointer-events-none tw-text-slate-400">
                    <i class="fa fa-search tw-text-lg"></i>
                </div>
                <input type="text" 
                    class="tw-block tw-w-full tw-pl-3 tw-pr-4 tw-py-3 tw-bg-transparent tw-border-none focus:tw-ring-0 tw-text-slate-900 tw-placeholder-slate-400 focus:tw-outline-none" 
                    name="q" 
                    value="{{ $keyword ?? '' }}" 
                    placeholder="Buscar tema, tese ou súmula..."
                    required>
            </div>
            <button type="submit" class="tw-hidden md:tw-inline-flex tw-items-center tw-justify-center tw-ml-3 tw-px-6 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-lg tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-transition-colors tw-shadow-sm">
                Pesquisar
            </button>
        </div>

        <div>
            <label class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-3">Selecione o Tribunal:</label>
            <div id="radios-tribunais" class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-4 lg:tw-grid-cols-8 tw-gap-3">
                @foreach ($lista_tribunais as $t => $arr)
                <label class="home-pilot-radio tw-cursor-pointer tw-relative">
                    <input class="tw-peer tw-sr-only" type="radio" name="tribunal" value="{{ $t }}"
                        @if ( !empty($tribunal) && strtolower($tribunal) === strtolower($t) ) checked @endif>
                    <div class="tw-flex tw-items-center tw-justify-center tw-w-full tw-py-2 tw-px-3 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-full tw-text-slate-600 tw-font-medium tw-transition-all peer-checked:tw-bg-brand-100 peer-checked:tw-border-brand-600 peer-checked:tw-text-brand-800 hover:tw-bg-slate-100 peer-focus:tw-ring-2 peer-focus:tw-ring-brand-500 peer-focus:tw-ring-offset-1">
                        {{ $t }}
                    </div>
                </label>
                @endforeach
            </div>
            <p class="tw-mt-3 tw-text-sm tw-text-slate-500 tw-text-center md:tw-text-left" id="selected-tribunal-status">
                Tribunal selecionado: <strong id="selected-tribunal-label" class="tw-text-brand-700">nenhum</strong>
            </p>
        </div>

        <div class="md:tw-hidden">
            <button type="submit" class="tw-w-full tw-flex tw-items-center tw-justify-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-lg tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-brand-500 tw-shadow-sm">
                Pesquisar
            </button>
        </div>

    </form>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="tw-hidden tw-absolute tw-inset-0 tw-bg-white/90 tw-z-10 tw-flex tw-flex-col tw-items-center tw-justify-center tw-rounded-xl tw-backdrop-blur-sm">
        <div class="tw-w-12 tw-h-12 tw-border-4 tw-border-slate-200 tw-border-t-brand-600 tw-rounded-full tw-animate-spin tw-mb-4"></div>
        <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800">Buscando jurisprudência...</h3>
        <p class="tw-text-slate-500 tw-text-sm">Consultando base de dados dos tribunais</p>
    </div>
</div>

<script>
(function() {
    function initSearchForm() {
        const form = document.getElementById('trib-form');
        const loadingOverlay = document.getElementById('loading-overlay');
        const tribunalRadios = document.querySelectorAll('input[name="tribunal"]');
        const selectedTribunalLabel = document.getElementById('selected-tribunal-label');

        function updateSelectedTribunal() {
            const checked = document.querySelector('input[name="tribunal"]:checked');
            if (checked && selectedTribunalLabel) {
                selectedTribunalLabel.textContent = checked.value;
            } else if (selectedTribunalLabel) {
                 selectedTribunalLabel.textContent = 'nenhum';
            }
        }

        tribunalRadios.forEach(radio => {
            radio.addEventListener('change', updateSelectedTribunal);
        });

        // Initialize label on load
        updateSelectedTribunal();

        if (form && loadingOverlay) {
            form.addEventListener('submit', function(e) {
                const keyword = form.querySelector('input[name="q"]').value.trim();
                const tribunal = form.querySelector('input[name="tribunal"]:checked');
                
                if (keyword.length >= 3 && tribunal) {
                    loadingOverlay.classList.remove('tw-hidden');
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSearchForm);
    } else {
        initSearchForm();
    }
})();
</script>
