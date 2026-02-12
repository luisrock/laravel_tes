@extends('front.base')

@section('page-title', 'Newsletters do T&S')

@section('content')

    <!-- Page Content -->

    <div class="home-pilot-shell tw-pt-4">
        <section class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-2">
            <h1 class="home-pilot-title tw-m-0">Newsletters do T&S</h1>
            <p class="home-pilot-subtitle tw-m-0">
                Faça uma <a href="{{ route('searchpage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">pesquisa</a> ou veja as
                <a href="{{ route('alltemaspage') }}" class="tw-text-brand-700 hover:tw-text-brand-800">pesquisas prontas</a>.
            </p>
        </section>
    </div>

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

    <div class="home-pilot-shell tw-pt-2" id="content-results">

        <!-- Results -->

        <div class="home-pilot-card tw-p-5 md:tw-p-6">
            <div>


                <div class="tab-pane fade fade-up active show" role="tabpanel">

                    <div class="home-results-count tw-flex tw-justify-between tw-items-center tw-gap-2">
                        <code>Arquivo de Newsletters</code>
                        <span class="tw-text-sm tw-text-slate-600">Inscreva-se <a class="tw-text-brand-700 hover:tw-text-brand-800"
                                href="https://newsletter.maurolopes.com.br/subscription?f=guJ3cS2Vm7AxAFSQ24hY1x2LOVOrbH44BBFmy4NXDEULQPmQ9VecJ538XJVLM9JbWogaBgUkTuwWL1y8WaWG1w">aqui</a></span>
                    </div>

                    <table class="home-results-table table-results">

                        <tbody>
                            @foreach ($campaigns as $campaign)
                                <tr>
                                    <td>
                                        <h4 class="tw-text-lg tw-font-semibold tw-mt-0 tw-mb-2">
                                            <a href="{{ route('newsletter.show', $campaign->slug) }}">{{ $campaign->subject }}</a>
                                        </h4>

                                        <p class="tw-text-slate-500">
                                            {{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($campaign->plain_text ?? $campaign->html_content)), 200) }}
                                        </p>

                                        <span class="tw-text-slate-500 tw-text-sm tw-flex tw-justify-end">
                                            Envio: {{ $campaign->sent_at ? $campaign->sent_at->format('d/m/Y') : '' }}
                                        </span>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                    
                    <div class="tw-mt-4">
                        {{ $campaigns->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- END Results -->

    </div>


@endsection
