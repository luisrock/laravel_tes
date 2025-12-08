@extends('front.base')

@section('page-title', 'Newsletters do T&S')

@section('content')

    <!-- Page Content -->

    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">
                    <a href="{{ url('/') }}">
                        Teses & Súmulas
                    </a>
                    <span class="text-muted"> | </span> Newsletters do T&S
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="badge badge-primary">Extensão para o Chrome</a>
                </span>
            </div>
            <p>
                Faça uma <a href="{{ route('searchpage') }}">pesquisa</a> ou veja as <a
                    href="{{ route('alltemaspage') }}">pesquisas prontas</a>.
            </p>
        </div>
    </div>
    <!-- END Hero -->

    {{-- <div class="bg-body-light">
        <div class="content content-full">

            <h1>Campaign Archive</h1>
            <ul>
                @foreach ($campaigns as $campaign)
                    <li>
                        <a href="{{ $campaign['link'] }}">{{ $campaign['title'] }}</a>
                        <div>{!! $campaign['description'] !!}</div>
                        <p>Date: {{ $campaign['pubDate'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div> --}}

    <div class="content" id="content-results">

        <!-- Results -->

        <div class="block-content tab-content overflow-hidden">
            <div class="block-content tab-content overflow-hidden">


                <div class="tab-pane fade fade-up active show" role="tabpanel">

                    <div class="font-size-h4 font-w600 p-2 mb-4 border-left border-4x border-primary bg-body-light"
                        style="display:flex; justify-content:space-between">
                        <code>Arquivo de Newsletters</code>
                        <span>Inscreva-se <a
                                href="https://newsletter.maurolopes.com.br/subscription?f=guJ3cS2Vm7AxAFSQ24hY1x2LOVOrbH44BBFmy4NXDEULQPmQ9VecJ538XJVLM9JbWogaBgUkTuwWL1y8WaWG1w">aqui</a></span>
                    </div>

                    <table class="table table-striped table-vcenter table-results">

                        <tbody>
                            @foreach ($campaigns as $campaign)
                                <tr>
                                    <td>
                                        <h4 class="h5 mt-3 mb-2">
                                            <a href="{{ route('newsletter.show', $campaign->slug) }}">{{ $campaign->subject }}</a>
                                        </h4>

                                        <p class="d-sm-block text-muted">
                                            {{ \Illuminate\Support\Str::limit($campaign->preview_text, 200) }}
                                        </p>

                                        <span class="text-muted"
                                            style="display: flex;justify-content: flex-end;font-size: 0.8em;">
                                            Envio: {{ $campaign->sent_at ? $campaign->sent_at->format('d/m/Y') : '' }}
                                        </span>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                    
                    <div class="mt-4">
                        {{ $campaigns->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- END Results -->

    </div>


@endsection
