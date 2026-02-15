@extends('layouts.app')

@section('page-title', 'Planos de Assinatura')

@section('content')
<!-- Hero -->
<div class="tw-bg-slate-50 tw-border-b tw-border-slate-200">
    <div class="tw-max-w-7xl tw-mx-auto tw-py-16 tw-px-4 sm:tw-px-6 lg:tw-px-8">
        <div class="tw-text-center">
            <h1 class="tw-text-base tw-font-semibold tw-text-brand-600 tw-tracking-wide tw-uppercase">Assinatura Premium</h1>
            <p class="tw-mt-1 tw-text-4xl tw-font-extrabold tw-text-slate-900 sm:tw-text-5xl sm:tw-tracking-tight lg:tw-text-6xl">
                Escolha seu plano
            </p>
            <p class="tw-max-w-xl tw-mt-5 tw-mx-auto tw-text-xl tw-text-slate-500">
                Navegue sem anúncios e acesse conteúdo exclusivo com os planos Teses & Súmulas.
            </p>
        </div>
    </div>
</div>

<div class="tw-py-12 tw-bg-white">
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
        
        @if(session('error'))
            <div class="tw-max-w-3xl tw-mx-auto tw-mb-8 tw-bg-red-50 tw-border-l-4 tw-border-red-400 tw-p-4">
                <div class="tw-flex">
                    <div class="tw-flex-shrink-0">
                        <svg class="tw-h-5 tw-w-5 tw-text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="tw-ml-3">
                        <p class="tw-text-sm tw-text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="tw-max-w-3xl tw-mx-auto tw-mb-8 tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-4">
                <div class="tw-flex">
                    <div class="tw-flex-shrink-0">
                        <svg class="tw-h-5 tw-w-5 tw-text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="tw-ml-3">
                        <p class="tw-text-sm tw-text-blue-700">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @guest
            <div class="tw-max-w-3xl tw-mx-auto tw-mb-10 tw-bg-slate-50 tw-rounded-lg tw-p-4 tw-border tw-border-slate-200 tw-text-center">
                <p class="tw-text-slate-600">
                    Para assinar, você precisa ter uma conta. 
                    <a href="{{ route('login') }}" class="tw-text-brand-600 tw-font-semibold hover:tw-underline">Faça login ou cadastre-se</a>.
                </p>
            </div>
        @endguest

        @auth
            @if(auth()->user()->isSubscriber())
                <div class="tw-max-w-3xl tw-mx-auto tw-mb-10 tw-bg-blue-50 tw-rounded-lg tw-p-4 tw-border tw-border-blue-200 tw-text-center">
                    <p class="tw-text-blue-800">
                        Você já é assinante! 
                        <a href="{{ route('subscription.show') }}" class="tw-text-blue-900 tw-font-bold hover:tw-underline">Gerencie sua assinatura</a>
                    </p>
                </div>
            @endif
        @endauth

        <div class="tw-grid tw-max-w-lg tw-gap-5 tw-mx-auto lg:tw-grid-cols-2 lg:tw-max-w-none">
            @foreach($plans as $tier => $plan)
            <div class="tw-flex tw-flex-col tw-rounded-lg tw-shadow-lg tw-overflow-hidden tw-bg-white tw-border {{ $tier === 'premium' ? 'tw-border-brand-500 tw-ring-2 tw-ring-brand-500 tw-ring-opacity-50' : 'tw-border-slate-200' }}">
                @if($tier === 'premium')
                    <div class="tw-bg-brand-500 tw-py-1 tw-text-center">
                        <p class="tw-text-xs tw-font-bold tw-text-white tw-uppercase tw-tracking-wide">Recomendado</p>
                    </div>
                @endif
                
                <div class="tw-px-6 tw-py-8 tw-bg-white sm:tw-p-10 sm:tw-pb-6">
                    <div>
                        <h3 class="tw-text-2xl tw-font-semibold tw-text-slate-900 tw-text-center">
                            {{ $plan['name'] }}
                        </h3>
                        <p class="tw-mt-4 tw-text-sm tw-text-slate-500 tw-text-center">
                            {{ $plan['description'] ?? 'Acesse todos os benefícios do plano ' . $plan['name'] }}
                        </p>
                    </div>
                </div>

                <div class="tw-flex-1 tw-flex tw-flex-col tw-justify-between tw-px-6 tw-pt-6 tw-pb-8 tw-bg-slate-50 tw-space-y-6 sm:tw-p-10 sm:tw-pt-6">
                    <ul class="tw-space-y-4">
                        <li class="tw-flex tw-items-start">
                            <div class="tw-flex-shrink-0">
                                <svg class="tw-h-6 tw-w-6 tw-text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="tw-ml-3 tw-text-base tw-text-slate-700">Navegação sem anúncios</p>
                        </li>
                        <li class="tw-flex tw-items-start">
                            <div class="tw-flex-shrink-0">
                                <svg class="tw-h-6 tw-w-6 tw-text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="tw-ml-3 tw-text-base tw-text-slate-700">Acesso a conteúdo exclusivo</p>
                        </li>
                        @if($tier === 'premium')
                        <li class="tw-flex tw-items-start">
                            <div class="tw-flex-shrink-0">
                                <svg class="tw-h-6 tw-w-6 tw-text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="tw-ml-3 tw-text-base tw-text-slate-700">Ferramentas de IA (em breve)</p>
                        </li>
                        <li class="tw-flex tw-items-start">
                            <div class="tw-flex-shrink-0">
                                <svg class="tw-h-6 tw-w-6 tw-text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="tw-ml-3 tw-text-base tw-text-slate-700">Suporte prioritário</p>
                        </li>
                        @endif
                    </ul>

                    <div class="tw-rounded-md tw-shadow">
                        <form action="{{ route('subscription.checkout') }}" method="POST" 
                              x-data="{ 
                                  selectedPrice: '{{ isset($plan['prices']['yearly']) ? $plan['prices']['yearly']['id'] : (isset($plan['prices']['monthly']) ? $plan['prices']['monthly']['id'] : '') }}' 
                              }">
                            @csrf
                            
                            <div class="tw-justify-center tw-flex tw-mb-6 tw-space-x-4">
                                @if(isset($plan['prices']['monthly']))
                                <label class="tw-cursor-pointer tw-relative">
                                    <input type="radio" name="priceId" value="{{ $plan['prices']['monthly']['id'] }}" class="tw-sr-only" x-model="selectedPrice">
                                    <div class="tw-px-4 tw-py-3 tw-rounded-lg tw-border tw-transition-all tw-duration-200" 
                                         :class="selectedPrice == '{{ $plan['prices']['monthly']['id'] }}' 
                                            ? 'tw-bg-brand-50 tw-border-brand-600 tw-ring-2 tw-ring-brand-600 tw-text-brand-900 tw-shadow-md' 
                                            : 'tw-bg-white tw-border-slate-200 tw-text-slate-500 hover:tw-border-brand-300 hover:tw-bg-slate-50 hover:tw-text-slate-700'">
                                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-1">
                                            <div class="tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">Mensal</div>
                                            <div x-show="selectedPrice == '{{ $plan['prices']['monthly']['id'] }}'" class="tw-text-brand-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="tw-text-lg tw-font-bold">R$ {{ number_format($plan['prices']['monthly']['amount'], 2, ',', '.') }}</div>
                                    </div>
                                </label>
                                @endif

                                @if(isset($plan['prices']['yearly']))
                                <label class="tw-cursor-pointer tw-relative">
                                    <input type="radio" name="priceId" value="{{ $plan['prices']['yearly']['id'] }}" class="tw-sr-only" x-model="selectedPrice">
                                    <div class="tw-px-4 tw-py-3 tw-rounded-lg tw-border tw-transition-all tw-duration-200 tw-relative" 
                                         :class="selectedPrice == '{{ $plan['prices']['yearly']['id'] }}' 
                                            ? 'tw-bg-brand-50 tw-border-brand-600 tw-ring-2 tw-ring-brand-600 tw-text-brand-900 tw-shadow-md' 
                                            : 'tw-bg-white tw-border-slate-200 tw-text-slate-500 hover:tw-border-brand-300 hover:tw-bg-slate-50 hover:tw-text-slate-700'">
                                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-1">
                                            <div class="tw-font-bold tw-text-sm tw-uppercase tw-tracking-wide">Anual</div>
                                            <div x-show="selectedPrice == '{{ $plan['prices']['yearly']['id'] }}'" class="tw-text-brand-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="tw-text-lg tw-font-bold">R$ {{ number_format($plan['prices']['yearly']['amount'], 2, ',', '.') }}</div>
                                        
                                        @php
                                            $monthlyTotal = isset($plan['prices']['monthly']) ? $plan['prices']['monthly']['amount'] * 12 : 0;
                                            $savings = $monthlyTotal - $plan['prices']['yearly']['amount'];
                                            $savingsPercent = $monthlyTotal > 0 ? round(($savings / $monthlyTotal) * 100) : 0;
                                        @endphp
                                        @if($savingsPercent > 0)
                                            <span class="tw-absolute tw--top-3 tw--right-2 tw-bg-green-100 tw-text-green-800 tw-text-xs tw-font-extrabold tw-px-2.5 tw-py-1 tw-rounded-full tw-shadow-sm tw-border tw-border-green-200">
                                                ECO {{ $savingsPercent }}%
                                            </span>
                                        @endif
                                    </div>
                                </label>
                                @endif
                            </div>

                            @auth
                                @if(!auth()->user()->isSubscriber())
                                <button type="submit" class="tw-w-full tw-flex tw-items-center tw-justify-center tw-px-5 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 tw-shadow-sm hover:tw-shadow-md tw-transition-all tw-duration-200 tw-transform hover:tw--translate-y-0.5">
                                    Assinar {{ $plan['name'] }}
                                </button>
                                @else
                                <button type="button" disabled class="tw-w-full tw-flex tw-items-center tw-justify-center tw-px-5 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-md tw-text-slate-400 tw-bg-slate-100 tw-cursor-not-allowed">
                                    Já assinado
                                </button>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="tw-w-full tw-flex tw-items-center tw-justify-center tw-px-5 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-md tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700 tw-shadow-sm hover:tw-shadow-md tw-transition-all tw-duration-200 tw-transform hover:tw--translate-y-0.5">
                                    Faça login para assinar
                                </a>
                            @endauth
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="tw-mt-10 tw-text-center tw-text-sm tw-text-slate-500">
            <p>
                Pagamento seguro via Stripe. Cancele quando quiser.<br>
                Dúvidas? <a href="mailto:contato@tesesesumulas.com.br" class="tw-text-brand-600 hover:tw-underline">contato@tesesesumulas.com.br</a>
            </p>
        </div>
    </div>
</div>
@endsection
