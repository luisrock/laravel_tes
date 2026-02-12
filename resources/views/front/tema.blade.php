@extends('front.base')

@section('page-title', $label)

@yield('stj')

@section('content')

    @php
        $admin = false;
    @endphp
    @auth
        @php
            $admin = in_array(Auth::user()->email, [
                'mauluis@gmail.com',
                'trator70@gmail.com',
                'ivanaredler@gmail.com',
            ]);
        @endphp
    @endauth


    <!-- Page Content -->

    <div class="home-pilot-shell tw-pt-4" style="{{ $display_pdf }}">
        <section class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-2">
            <h1 class="home-pilot-title tw-m-0">{{ $label }}</h1>
            <p class="home-pilot-subtitle tw-m-0">
                Faça <a href="{{ route('searchpage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">outra pesquisa</a> ou veja as
                <a href="{{ route('alltemaspage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">pesquisas prontas</a>.
                @if ($admin)
                    <br><a href="{{ route('admin') }}" class="tw-text-brand-700 hover:tw-text-brand-800">Admin</a>
                @endif
            </p>
        </section>
    </div>
    <!-- END Hero -->

    <div class="home-pilot-shell tw-pt-2" id="content-results">


        @if ($concept)
            @if ($concept_validated_at || $admin)
                <!-- conceito -->
                <div class="home-pilot-card tw-p-5 md:tw-p-6 tw-mb-4">
                    <table class="home-results-table table-results">
                        <tbody>
                            <tr>
                                <td>
                                    <h4 class="tw-text-base tw-font-semibold tw-mt-0 tw-mb-2">
                                        <a id="open-concept" href="#" class="tw-text-brand-700 hover:tw-text-brand-800">Resumo</a>
                                    </h4>
                                    <p class="tw-text-slate-600" id="conceito">{{ $concept }}</p>
                                    <!-- <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">Gerado por GPT-4</span> -->
                                    <!-- if admin, insert 3 buttons: validate, edit or remove  -->
                                    @if ($admin)
                                        <div id = "content-actions-buttons"
                                            class="tw-flex tw-justify-end tw-gap-2">
                                            @if (!$concept_validated_at)
                                                <button type="button" class="home-pilot-btn tw-bg-emerald-700 hover:tw-bg-emerald-800 tw-py-2 tw-px-3 tw-text-sm" id="#concept-validate"
                                                    data-concept-id="{{ $id }}">
                                                    <i class="fa fa-check"></i> Validar
                                                </button>
                                            @endif
                                            <button type="button" class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-lg tw-border tw-border-amber-300 tw-bg-amber-50 tw-text-amber-800 hover:tw-bg-amber-100 tw-py-2 tw-px-3 tw-text-sm tw-font-medium" id="#concept-edit"
                                                data-concept-id="{{ $id }}">
                                                <i class="fa fa-edit"></i> Editar
                                            </button>
                                            <button type="button" class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-lg tw-border tw-border-red-300 tw-bg-red-50 tw-text-red-700 hover:tw-bg-red-100 tw-py-2 tw-px-3 tw-text-sm tw-font-medium" id="#concept-remove"
                                                data-concept-id="{{ $id }}">
                                                <i class="fa fa-trash"></i> Remover
                                            </button>
                                        </div>

                                        <!-- modais -->
                                        <!-- Edit concept modal -->
                                        <div class="modal fade" id="editConceptModal" tabindex="-1" role="dialog"
                                            aria-labelledby="editConceptModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editConceptModalLabel">Editar Conceito
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Fechar">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <textarea id="editConceptTextarea" class="form-control" rows="5"></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <button type="button" class="btn btn-primary"
                                                            id="saveEditConcept">Salvar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Remove concept modal -->
                                        <div class="modal fade" id="removeConceptModal" tabindex="-1" role="dialog"
                                            aria-labelledby="removeConceptModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="removeConceptModalLabel">Remover
                                                            Conceito</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Fechar">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Tem certeza de que deseja remover este conceito?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancelar</button>
                                                        <button type="button" class="btn btn-danger"
                                                            id="confirmRemoveConcept">Remover</button>
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
            @if ($admin)
                <!-- criar um textarea com um botao para buscar conceito via GPT-3  -->
                <div class="home-pilot-card tw-p-5 md:tw-p-6 tw-mb-4">
                    <table class="home-results-table table-results">
                        <tbody>
                            <tr>
                                <td>
                                    <h4 class="tw-text-base tw-font-semibold tw-mt-0 tw-mb-3">
                                        <a href="#" class="tw-text-brand-700 hover:tw-text-brand-800">Gerador de Conceito</a>
                                    </h4>
                                    <input type="radio" id="gpt-4" name="gpt-model" value="gpt-4" checked>
                                    <label for="gpt-4">GPT-4</label>
                                    <input type="radio" id="gpt-4-turbo" name="gpt-model" value="gpt-4-turbo">
                                    <label for="gpt-4-turbo">GPT-4 Turbo</label>
                                    <input type="radio" id="gpt-4o" name="gpt-model" value="gpt-4o" checked>
                                    <label for="gpt-4o">GPT-4o</label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <code>System Prompt</code>
                                    <textarea id="concept-system-prompt" class="home-pilot-input tw-mt-1" rows="3">Você é um jurista brasileiro, professor de direito e conhecedor da jurisprudência formada até 2021. Você nunca inventa e quando não sabe, diz apenas 'Desculpe'.</textarea>
                                </td>
                                <td>
                                    <code>User Prompt</code>
                                    <textarea id="concept-user-prompt" class="home-pilot-input tw-mt-1" rows="3">Apresente um conceito objetivo e didático sobre o seguinte tema: {{ $label }}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="tw-text-slate-600" id="conceito-gerado"></p>
                                    <!-- <span class="text-muted" style="display: flex;justify-content: flex-end;font-size: 0.8em;">Gerado por GPT-4</span> -->
                                    <!-- if admin, insert 3 buttons: validate, edit or remove  -->
                                    <div id = "content-actions-buttons"
                                        class="tw-flex tw-justify-end tw-gap-2">
                                        <button type="button" class="home-pilot-btn tw-bg-emerald-700 hover:tw-bg-emerald-800 tw-py-2 tw-px-3 tw-text-sm" id="concept-create"
                                            data-concept-id="{{ $id }}"
                                            data-concept-label="{{ $label }}"
                                            data-concept-save-route="{{ route('save-concept') }}"
                                            data-concept-generate-route="{{ route('generate-concept') }}">
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
        <div class="home-pilot-card tw-overflow-hidden">
            <div class="home-results-tabs" role="tablist" style="{{ $display_pdf }}">

                @foreach ($output as $out => $put)
                    @php $isActiveTab = $loop->first; @endphp
                    <button type="button" class="home-results-tab {{ $isActiveTab ? 'is-active' : '' }}" data-tab-target="#tema-{{ $out }}" id="nav-{{ $out }}" aria-selected="{{ $isActiveTab ? 'true' : 'false' }}">{{ strtoupper($out) }}</button>
                @endforeach

            </div>

            <div class="home-results-content">


                @foreach ($output as $out => $put)
                    @php $isActivePane = $loop->first; @endphp
                    <section class="home-results-pane {{ $isActivePane ? 'is-active' : '' }}" id="tema-{{ $out }}" role="tabpanel" {{ $isActivePane ? '' : 'hidden' }}>
                        <div class="home-results-count trib-texto-quantidade">
                            <code>{{ $label }}</code> - {{ strtoupper($out) }}
                            (resultados: <code>{{ $output[$out]['total_count'] }}</code>)
                        </div>
                        <table class="home-results-table table-results">
                            <tbody>
                                @includeif('front.results.inners.' . strtolower($out) . '_sum', [
                                    'output' => $output[$out],
                                ])
                                @includeif('front.results.inners.' . strtolower($out) . '_rep', [
                                    'output' => $output[$out],
                                ])
                            </tbody>
                        </table>
                    </section>
                @endforeach

            </div>
        </div>
        <!-- END Results -->

        <!-- Temas Relacionados -->
        @if(isset($related_themes) && $related_themes->count() > 0)
        <div class="home-pilot-card tw-p-5 md:tw-p-6 tw-mt-4" style="{{ $display_pdf }}">
            <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-mb-3">Temas Relacionados</h3>
            <div>
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-3">
                    @foreach($related_themes as $theme)
                    <div>
                        <a href="{{ url('/tema/' . $theme->slug) }}" class="tw-block tw-border tw-border-slate-200 tw-rounded-lg hover:tw-border-brand-300 hover:tw-bg-brand-50 tw-transition tw-p-4" style="text-decoration: none;">
                            <div>
                                <div class="tw-text-sm tw-font-semibold tw-text-brand-800">
                                    {{ $theme->label ?? $theme->keyword }}
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <!-- END Temas Relacionados -->

    </div>

    <script>
    (function () {
        const tabs = document.querySelectorAll('.home-results-tab');
        const panes = document.querySelectorAll('.home-results-pane');

        if (!tabs.length || !panes.length) {
            return;
        }

        function activateTab(targetSelector) {
            tabs.forEach(function (tab) {
                const isActive = tab.dataset.tabTarget === targetSelector;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panes.forEach(function (pane) {
                const isActive = `#${pane.id}` === targetSelector;
                pane.classList.toggle('is-active', isActive);
                pane.hidden = !isActive;
            });
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activateTab(tab.dataset.tabTarget);
            });
        });
    })();
    </script>


@endsection
