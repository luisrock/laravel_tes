@extends('front.base')

@section('page-title', 'Resultados da Pesquisa')

@section('content')

<div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-py-8 md:tw-py-12 tw-space-y-8">

    <!-- Search Section -->
    @include('partials.search-form', ['keyword' => $keyword ?? '', 'lista_tribunais' => $lista_tribunais])
    <!-- END Search Section -->

    @php
        // Build list of visible tribunals (excluding hidden ones like TCU)
        $visibleTribunals = [];
        foreach ($output as $key => $data) {
            if (is_array($data) && !in_array(strtoupper($key), $tribunaisExcluidos)) {
                $visibleTribunals[$key] = $data;
            }
        }
    @endphp

    @if (count($visibleTribunals) > 0)
    <div x-data="{
            activeTab: '{{ $hasAnyResults ? $firstActiveTab : '' }}',
            activeSubTab: {},
            init() {
                // Initialize sub-tabs: sumulas first, then teses
                @foreach ($visibleTribunals as $key => $data)
                    @php
                        $hasSumulas = ($data['sumula']['total'] ?? 0) > 0;
                        $hasTeses = ($data['tese']['total'] ?? 0) > 0;
                        $defaultSub = $hasSumulas ? 'sumulas' : ($hasTeses ? 'teses' : 'sumulas');
                    @endphp
                    this.activeSubTab['{{ $key }}'] = '{{ $defaultSub }}';
                @endforeach
            }
        }"
        class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-border tw-border-slate-200 tw-overflow-hidden">

        <!-- Tribunal Tab Headers -->
        <div class="tw-flex tw-overflow-x-auto tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-scrollbar-hide">
            @foreach ($visibleTribunals as $key => $data)
                @php
                    $count = $data['total_count'] ?? 0;
                    $isDisabled = $count === 0;
                @endphp
                <button
                    @if(!$isDisabled) @click="activeTab = '{{ $key }}'" @endif
                    :class="{
                        'tw-border-brand-500 tw-text-brand-600 tw-bg-white': activeTab === '{{ $key }}',
                        'tw-border-transparent tw-text-slate-500 hover:tw-text-slate-700 hover:tw-border-slate-300': activeTab !== '{{ $key }}' && !{{ $isDisabled ? 'true' : 'false' }},
                        'tw-opacity-25 tw-cursor-not-allowed': {{ $isDisabled ? 'true' : 'false' }}
                    }"
                    class="tw-whitespace-nowrap tw-py-4 tw-px-5 md:tw-px-6 tw-border-b-2 tw-font-medium tw-text-sm tw-transition-colors focus:tw-outline-none"
                    {{ $isDisabled ? 'disabled' : '' }}
                    title="{{ $isDisabled ? 'Sem resultados no ' . strtoupper($key) : strtoupper($key) . ': ' . $count . ' resultado(s)' }}">
                    {{ strtoupper($key) }}
                    @if ($count > 0)
                        <span class="tw-ml-1 tw-text-xs tw-font-normal tw-text-slate-400">({{ $count }})</span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Tab Contents -->
        <div class="tw-p-6 md:tw-p-8">

            {{-- Global no results --}}
            @if (!$hasAnyResults)
                <div class="tw-text-center tw-py-16">
                    <i class="fa fa-search tw-text-5xl tw-text-slate-300 tw-mb-4"></i>
                    <h3 class="tw-text-xl tw-font-semibold tw-text-slate-700 tw-mb-2">Nenhum resultado encontrado</h3>
                    <p class="tw-text-slate-500">Sua busca por
                        <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1.5 tw-py-0.5 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
                        não retornou resultados em nenhum tribunal.
                    </p>
                </div>
            @endif

            @if ($hasAnyResults)
            @foreach ($visibleTribunals as $key => $data)
                @php
                    $tribunalCount = $data['total_count'] ?? 0;
                    $sumTotal = $data['sumula']['total'] ?? 0;
                    $teseTotal = $data['tese']['total'] ?? 0;
                    $hasTeses = !in_array(strtoupper($key), $sem_tese);
                    $tribunalName = $lista_tribunais[strtoupper($key)]['name'] ?? strtoupper($key);
                @endphp

                <div x-show="activeTab === '{{ $key }}'" x-cloak>

                    {{-- No results for this tribunal --}}
                    @if ($tribunalCount === 0)
                        <div class="tw-text-center tw-py-12 tw-text-slate-500">
                            <i class="fa fa-search tw-text-4xl tw-text-slate-300 tw-mb-4"></i>
                            <p class="tw-text-lg tw-font-medium tw-text-slate-600">Nenhum resultado encontrado</p>
                            <p class="tw-mt-1">no {{ strtoupper($key) }} para
                                <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
                            </p>
                        </div>
                    @else
                        {{-- Sub-tabs: Súmulas / Teses --}}
                        @if ($hasTeses)
                        <div class="tw-flex tw-gap-2 tw-mb-6">
                            <button
                                @click="activeSubTab['{{ $key }}'] = 'sumulas'"
                                :class="activeSubTab['{{ $key }}'] === 'sumulas'
                                    ? 'tw-bg-brand-600 tw-text-white tw-shadow-sm'
                                    : 'tw-bg-slate-100 tw-text-slate-600 hover:tw-bg-slate-200'"
                                class="tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-colors focus:tw-outline-none">
                                Súmulas
                                <span class="tw-ml-1 tw-opacity-75">({{ $sumTotal }})</span>
                            </button>
                            <button
                                @click="activeSubTab['{{ $key }}'] = 'teses'"
                                :class="activeSubTab['{{ $key }}'] === 'teses'
                                    ? 'tw-bg-brand-600 tw-text-white tw-shadow-sm'
                                    : 'tw-bg-slate-100 tw-text-slate-600 hover:tw-bg-slate-200'"
                                class="tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-colors focus:tw-outline-none">
                                Teses
                                <span class="tw-ml-1 tw-opacity-75">({{ $teseTotal }})</span>
                            </button>
                        </div>
                        @endif

                        {{-- Súmulas Pane --}}
                        <div x-show="activeSubTab['{{ $key }}'] === 'sumulas'" x-cloak>
                            @if ($sumTotal > 0)
                                <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-text-slate-700 tw-mb-6">
                                    <span class="tw-font-bold tw-text-brand-700">{{ $sumTotal }}</span>
                                    <span class="tw-text-slate-600">{{ $sumTotal == 1 ? 'enunciado encontrado' : 'enunciados encontrados' }}</span>
                                    <span class="tw-text-slate-600">no {{ strtoupper($key) }} para</span>
                                    <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
                                </div>

                                <div class="tw-overflow-x-auto">
                                    <table class="tw-w-full tw-border-separate tw-border-spacing-y-4">
                                        <tbody class="tw-space-y-4">
                                            @includeIf('front.results.inners.' . $key . '_sum', ['output' => $data])
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="tw-text-center tw-py-8 tw-text-slate-500">
                                    <p>Nenhuma súmula encontrada no {{ strtoupper($key) }} para
                                        <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Teses Pane --}}
                        @if ($hasTeses)
                        <div x-show="activeSubTab['{{ $key }}'] === 'teses'" x-cloak>
                            @if ($teseTotal > 0)
                                <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-text-slate-700 tw-mb-6">
                                    <span class="tw-font-bold tw-text-brand-700">{{ $teseTotal }}</span>
                                    <span class="tw-text-slate-600">{{ $teseTotal == 1 ? 'tese encontrada' : 'teses encontradas' }}</span>
                                    <span class="tw-text-slate-600">no {{ strtoupper($key) }} para</span>
                                    <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
                                </div>

                                <div class="tw-overflow-x-auto">
                                    <table class="tw-w-full tw-border-separate tw-border-spacing-y-4">
                                        <tbody class="tw-space-y-4">
                                            @includeIf('front.results.inners.' . $key . '_rep', ['output' => $data])
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="tw-text-center tw-py-8 tw-text-slate-500">
                                    <p>Nenhuma tese encontrada no {{ strtoupper($key) }} para
                                        <mark class="tw-bg-brand-100 tw-text-brand-800 tw-px-1 tw-rounded tw-font-semibold">{{ $keyword }}</mark>
                                    </p>
                                </div>
                            @endif
                        </div>
                        @endif

                    @endif
                </div>
            @endforeach
            @endif

        </div>
    </div>
    @endif

    {{-- Admin Store Section --}}
    @if ($admin && $hasAnyResults)
    <div id="admin-store" class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-slate-200 tw-p-6">
        <div class="tw-flex tw-items-center tw-gap-3 tw-mb-4">
            <span class="tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-amber-100 tw-text-amber-800">Admin</span>
            <h3 class="tw-text-lg tw-font-semibold tw-text-slate-800 tw-m-0">Ferramentas</h3>
        </div>
        <p class="tw-text-sm tw-text-slate-600 tw-mb-4">
            Keyword: <code class="tw-font-bold tw-text-slate-700">{{ $keyword }}</code>
        </p>
        <div class="tw-flex tw-gap-3">
            <a href="{{ route('temapage', ['tema' => \Illuminate\Support\Str::slug($keyword)]) }}"
                class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-slate-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 tw-transition-colors"
                target="_blank">
                <i class="fa fa-external-link tw-mr-2"></i> Ver tema
            </a>
        </div>
    </div>
    @endif

</div>

<style>
    /* Hide scrollbar on tab headers */
    .tw-scrollbar-hide::-webkit-scrollbar { display: none; }
    .tw-scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    /* Alpine x-cloak */
    [x-cloak] { display: none !important; }
</style>

@endsection
