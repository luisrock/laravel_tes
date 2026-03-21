@extends('layouts.user-panel')

@section('panel-title', 'Histórico de Visualizações')

@section('panel-content')
<div class="tw-bg-white tw-shadow-sm tw-rounded-lg tw-border tw-border-slate-200">
    <div class="tw-px-6 tw-py-4 tw-border-b tw-border-slate-100 tw-bg-slate-50/50">
        <h3 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-uppercase tw-tracking-wide">Teses visualizadas</h3>
    </div>

    @if($views->isEmpty())
        <div class="tw-p-8 tw-text-center">
            <svg class="tw-mx-auto tw-h-12 tw-w-12 tw-text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="tw-mt-3 tw-text-sm tw-text-slate-500">Você ainda não visualizou nenhuma análise de IA.</p>
            <a href="{{ route('searchpage') }}" class="tw-mt-4 tw-inline-flex tw-items-center tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">
                Buscar teses
            </a>
        </div>
    @else
        <div class="tw-divide-y tw-divide-slate-100">
            @foreach($views as $view)
                <div class="tw-px-6 tw-py-4 hover:tw-bg-slate-50 tw-transition-colors">
                    <div class="tw-flex tw-items-start tw-justify-between tw-gap-4">
                        <div class="tw-min-w-0 tw-flex-1">
                            <div class="tw-flex tw-items-center tw-gap-2 tw-mb-1">
                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-bold tw-bg-brand-100 tw-text-brand-800">
                                    {{ $view->tribunal_label }}
                                </span>
                                @if(isset($view->tese_numero))
                                    <span class="tw-text-xs tw-text-slate-500">Tese nº {{ $view->tese_numero }}</span>
                                @endif
                            </div>
                            @if(isset($view->tese_url))
                                <a href="{{ $view->tese_url }}" class="tw-text-sm tw-font-medium tw-text-slate-800 hover:tw-text-brand-600 tw-line-clamp-2 text-decoration-none">
                                    {{ $view->tema_texto ?: 'Tese sem tema cadastrado' }}
                                </a>
                            @else
                                <p class="tw-text-sm tw-text-slate-600 tw-line-clamp-2">
                                    {{ $view->tema_texto ?? 'Conteúdo não disponível' }}
                                </p>
                            @endif
                        </div>
                        <span class="tw-text-xs tw-text-slate-400 tw-whitespace-nowrap tw-shrink-0">
                            {{ $view->viewed_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @if($views->hasPages())
            <div class="tw-px-6 tw-py-4 tw-border-t tw-border-slate-100 tw-bg-slate-50/50">
                {{ $views->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
