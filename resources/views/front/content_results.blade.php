@extends('front.search')

@section('content_results')

<!-- content -->
<!-- Page Content -->
<div class="home-pilot-shell tw-pt-0" id="content-results">
    <!-- Results -->
    <div class="home-pilot-card tw-overflow-hidden">
        <div class="home-results-tabs" role="tablist" style="{{ $display_pdf }}">
            <button type="button" class="home-results-tab is-active" data-tab-target="#busca-sumulas-trib" id="nav-sumulas" aria-selected="true">Súmulas</button>
            @hasSection('teses_total_text')
            <button type="button" class="home-results-tab" data-tab-target="#busca-teses-trib" id="nav-teses" aria-selected="false">Teses</button>
            @endif
        </div>


        <div class="home-results-content">

<!-- PDF Button (apenas para admins - desabilitado temporariamente por bug no layout) -->
        @php
            $isAdmin = auth()->check() && in_array(auth()->user()->email, config('tes_constants.admins', []));
        @endphp
        @if ($isAdmin && (
                    !empty($output['sumula']['total']) 
                    || 
                    !empty($output['tese']['total'])
                )
                &&
                (
                    empty($_GET['print']) 
                    ||
                    'pdf' != $_GET['print']
                )   )

            <div id="pdf-button" style="{{ $display_pdf }}">
                <a href="{{ url()->full() }}&print=pdf"
                    class="home-pilot-btn tw-py-2 tw-px-3 tw-text-sm"
                    target="_blank" 
                    rel="nofollow">
                    <img src="assets/img/pdf.png" 
                        alt="image_pdf" title="Gerar PDF" class="tw-w-4 tw-h-4 tw-mr-1 tw-inline-block"> Gerar PDF
                </a>
            </div>
        
        @endif
<!-- PDF Button END-->

            <!-- Súmulas -->
            <section class="home-results-pane is-active" id="busca-sumulas-trib" role="tabpanel">
                <div class="home-results-count trib-texto-quantidade">

                    @yield('sumulas_total_text')

                </div>
                <table class="home-results-table table-results">
                    <tbody>

                        @yield('sumulas_inner_table')

                    </tbody>
                </table>
                <!--                                 <nav aria-label="Projects Search Navigation">
                                <ul class="pagination pagination-sm">
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)" tabindex="-1" aria-label="Antious">
                                            Ant
                                        </a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="javascript:void(0)">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">3</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">4</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)" aria-label="Prox">
                                            Próx
                                        </a>
                                    </li>
                                </ul>
                            </nav> -->
            </section>
            <!-- END Súmulas -->
            <!--mpdf  <pagebreak>  mpdf-->
            <!-- Teses -->
            <section class="home-results-pane" id="busca-teses-trib" role="tabpanel" hidden>
                <div class="home-results-count trib-texto-quantidade">

                    @yield('teses_total_text')

                </div>
                <table class="home-results-table table-results">
                    <tbody>

                        @yield('teses_inner_table')

                    </tbody>
                </table>
                <!--                                 <nav aria-label="Projects Search Navigation">
                                <ul class="pagination pagination-sm">
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)" tabindex="-1" aria-label="Antious">
                                            Ant
                                        </a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="javascript:void(0)">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">3</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)">4</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="javascript:void(0)" aria-label="Prox">
                                            Próx
                                        </a>
                                    </li>
                                </ul>
                            </nav> -->
            </section>
            <!-- END Súmulas -->

        </div>
    </div>
    <!-- END Results -->

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