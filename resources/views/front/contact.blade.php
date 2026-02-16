@extends('front.base')

@section('page-title', $label)

@section('content')
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-6 md:tw-pt-8 tw-pb-10">
        <section class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-space-y-3 tw-border tw-border-slate-200">
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-800 tw-m-0">{{ $label }}</h1>
            <p class="tw-text-slate-600 tw-text-lg tw-leading-relaxed tw-m-0">
                Envie sua dúvida, sugestão ou relato de problema. Responderemos no e-mail informado.
            </p>
        </section>

        <section class="tw-mt-6 tw-bg-white tw-shadow-sm tw-rounded-xl tw-p-6 md:tw-p-8 tw-border tw-border-slate-200">
            @if (session('success'))
                <div class="tw-mb-6 tw-rounded-lg tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-p-4 tw-text-emerald-800" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="tw-mb-6 tw-rounded-lg tw-border tw-border-rose-200 tw-bg-rose-50 tw-p-4 tw-text-rose-800" role="alert">
                    <p class="tw-font-semibold tw-mb-2">Não foi possível enviar a mensagem.</p>
                    <ul class="tw-list-disc tw-pl-5 tw-space-y-1 tw-m-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="tw-space-y-5">
                @csrf
                @honeypot

                <div>
                    <label for="name" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1.5">Nome</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', auth()->user()?->name) }}"
                        required
                        maxlength="120"
                        class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-px-3 tw-py-2.5 tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500"
                    >
                </div>

                <div>
                    <label for="email" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1.5">E-mail</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $prefilledEmail) }}"
                        required
                        maxlength="255"
                        @auth readonly @endauth
                        class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-px-3 tw-py-2.5 tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500 @auth tw-bg-slate-100 tw-cursor-not-allowed @endauth"
                    >
                    @auth
                        <p class="tw-text-xs tw-text-slate-500 tw-mt-1.5">Este e-mail é o do seu cadastro e não pode ser alterado.</p>
                    @endauth
                </div>

                <div>
                    <label for="subject" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1.5">Assunto</label>
                    <input
                        id="subject"
                        name="subject"
                        type="text"
                        value="{{ old('subject') }}"
                        required
                        maxlength="180"
                        class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-px-3 tw-py-2.5 tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500"
                    >
                </div>

                <div>
                    <label for="message" class="tw-block tw-text-sm tw-font-medium tw-text-slate-700 tw-mb-1.5">Mensagem</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="8"
                        required
                        maxlength="5000"
                        class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-px-3 tw-py-2.5 tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-border-brand-500"
                    >{{ old('message') }}</textarea>
                </div>

                <div class="tw-flex tw-items-center tw-justify-end">
                    <button
                        type="submit"
                        class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-lg tw-bg-brand-600 tw-px-5 tw-py-2.5 tw-text-white tw-font-medium hover:tw-bg-brand-700 tw-transition-colors"
                    >
                        <i class="fa fa-paper-plane"></i>
                        Enviar mensagem
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
