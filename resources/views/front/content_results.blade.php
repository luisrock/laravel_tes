@extends('front.search')

@section('content_results')

<!-- content -->
<!-- Page Content -->
<div class="content" id="content-results">
    <!-- Results -->
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-block nav-tabs-items" data-toggle="tabs" role="tablist" style="{{ $display_pdf }}">
            <li class="nav-item" id="nav-sumulas">
                <a class="nav-link active" href="#busca-sumulas-trib">Súmulas</a>
            </li>
            @hasSection('teses_total_text')
            <li class="nav-item" id="nav-teses">
                <a class="nav-link" href="#busca-teses-trib">Teses</a>
            </li>
            @endif
        </ul>


        <div class="block-content tab-content overflow-hidden">

<!-- PDF Button -->
        @if (   (
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
                    target="_blank" 
                    rel="nofollow">
                <img src="assets/img/pdf.png" 
                        alt="image_pdf" title="Gerar PDF">
                </a>
            </div>
        
        @endif
<!-- PDF Button END-->

            <!-- Súmulas -->
            <div class="tab-pane fade fade-up show active" id="busca-sumulas-trib" role="tabpanel">
                <div
                    class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">

                    @yield('sumulas_total_text')

                </div>
                <table class="table table-striped table-vcenter">
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
            </div>
            <!-- END Súmulas -->
            <!--mpdf  <pagebreak>  mpdf-->
            <!-- Teses -->
            <div class="tab-pane fade fade-up" id="busca-teses-trib" role="tabpanel">
                <div
                    class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light trib-texto-quantidade">

                    @yield('teses_total_text')

                </div>
                <table class="table table-striped table-vcenter">
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
            </div>
            <!-- END Súmulas -->

        </div>
    </div>
    <!-- END Results -->

</div>

@endsection