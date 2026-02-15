@extends('layouts.app')

@section('page-title', 'Newsletter Obrigado')

@section('content')
    <div class="tw-container tw-mx-auto tw-px-4 tw-pt-12 tw-pb-8" style="{{ $display_pdf ?? '' }}">
        <div class="tw-max-w-2xl tw-mx-auto tw-bg-white tw-shadow-md tw-rounded-lg tw-overflow-hidden tw-border tw-border-slate-200">
            <div class="tw-bg-indigo-600 tw-h-2"></div>
            <div class="tw-p-6 md:tw-p-8 tw-text-center">
                <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-16 tw-h-16 tw-rounded-full tw-bg-green-100 tw-mb-6">
                    <i class="fa fa-check tw-text-2xl tw-text-green-600"></i>
                </div>
                
                <h2 class="tw-text-3xl tw-font-bold tw-text-slate-800 tw-mb-4">Obrigado!</h2>
                <div class="tw-prose tw-prose-lg tw-text-slate-600 tw-leading-relaxed tw-font-medium tw-mx-auto">
                    <p>
                        Você receberá um email semanal contendo as atualizações em teses e súmulas dos principais tribunais, com
                        curadoria feita pelo Juiz federal Mauro Lopes, criador do Teses e Súmulas.
                    </p>
                </div>
                
                <div class="tw-mt-8">
                    <a href="{{ url('/') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-px-5 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-md tw-text-indigo-700 tw-bg-indigo-100 hover:tw-bg-indigo-200 tw-transition">
                        Voltar para o Início
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
