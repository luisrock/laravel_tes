@extends('front.base')

@section('page-title', $newsletter->subject)

@section('content')

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "{{ $newsletter->subject }}",
      "datePublished": "{{ $newsletter->sent_at ? $newsletter->sent_at->toIso8601String() : $newsletter->created_at->toIso8601String() }}",
      "author": {
        "@type": "Person",
        "name": "Mauro Lopes"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Teses & Súmulas",
        "logo": {
          "@type": "ImageObject",
          "url": "{{ url('assets/img/logo.png') }}"
        }
      }
    }
    </script>

    <div class="home-pilot-shell tw-pt-4">
        <section class="home-pilot-card tw-p-5 md:tw-p-6 tw-space-y-2">
            <h1 class="home-pilot-title tw-m-0">Newsletter</h1>
        </section>
    </div>

    <div class="home-pilot-shell tw-pt-2">
        <nav aria-label="breadcrumb" class="tw-mb-3">
            <ol class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-sm tw-text-slate-600 tw-p-0 tw-m-0 tw-list-none">
                <li>
                    <a class="tw-text-brand-700 hover:tw-text-brand-800" href="{{ route('newsletterspage') }}">Newsletters</a>
                </li>
                <li class="tw-text-slate-400">&gt;</li>
                <li aria-current="page">
                    @php
                        preg_match('/#(\d+)/', $newsletter->subject, $matches);
                        $editionNumber = $matches[1] ?? '';
                    @endphp
                    Edição {{ $editionNumber }}
                </li>
            </ol>
        </nav>
        <article class="home-pilot-card tw-p-5 md:tw-p-6">
            <div class="tw-flex tw-justify-end tw-mb-4">
                <span class="tw-text-slate-500 tw-text-sm">
                        {{ $newsletter->sent_at ? $newsletter->sent_at->format('d/m/Y') : '' }}
                </span>
            </div>
            <div class="tw-leading-relaxed tw-text-[16px]">
                
                <h3 class="tw-text-brand-700 tw-font-bold tw-text-2xl tw-mb-4">{{ $newsletter->subject }}</h3>
                <!-- Conteúdo da Newsletter -->
                <div class="newsletter-content tw-mb-5">
                    {!! $newsletterContent !!}
                </div>
                
                <hr class="tw-border-slate-200 tw-my-6">
                
                <!-- CTA -->
                <div class="tw-bg-sky-50 tw-border tw-border-sky-200 tw-rounded-xl tw-p-5 tw-text-center tw-my-4">
                    <h4 class="tw-text-lg tw-font-semibold tw-mb-2">Gostou deste conteúdo?</h4>
                    <p class="tw-text-slate-700 tw-mb-3">Receba nossas atualizações jurídicas diretamente no seu email. É grátis!</p>
                    <a href="https://newsletter.maurolopes.com.br/subscription?f=guJ3cS2Vm7AxAFSQ24hY1x2LOVOrbH44BBFmy4NXDEULQPmQ9VecJ538XJVLM9JbWogaBgUkTuwWL1y8WaWG1w" target="_blank" class="home-pilot-btn newsletter-cta-btn tw-inline-flex tw-items-center tw-gap-2">
                        <i class="fa fa-envelope"></i> Inscrever-se na Newsletter
                    </a>
                </div>
            </div>
        </article>
    </div>

@endsection

@section('styles')
<style>
    /* Limpeza de estilos de email antigos */
    .newsletter-content table {
        max-width: 100% !important;
        width: 100% !important;
    }
    .newsletter-content img {
        max-width: 100%;
        height: auto;
    }
    .newsletter-content::after {
        content: "";
        display: block;
        clear: both;
    }

    .newsletter-cta-btn,
    .newsletter-cta-btn:hover,
    .newsletter-cta-btn:focus,
    .newsletter-cta-btn:visited {
        color: #fff !important;
        text-decoration: none;
    }
    
</style>
@endsection
