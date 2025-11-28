@extends('front.base')

@section('page-title', $newsletter->subject)

@section('content')

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
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

    <!-- Hero -->
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">
                    <a href="{{ url('/') }}">Teses & Súmulas</a>
                    <span class="text-muted"> | </span> Newsletter
                </h1>

            </div>
        </div>
    </div>
    <!-- END Hero -->

    <div class="content">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb breadcrumb-alt bg-transparent px-0">
                <li class="breadcrumb-item">
                    <a class="link-fx" href="{{ route('newsletterspage') }}">Newsletters</a>
                </li>
                <li class="breadcrumb-item" aria-current="page">
                    @php
                        preg_match('/#(\d+)/', $newsletter->subject, $matches);
                        $editionNumber = $matches[1] ?? '';
                    @endphp
                    Edição {{ $editionNumber }}
                </li>
            </ol>
        </nav>
        <div class="block block-rounded">
            <div class="block-header block-header-default" style="display: flex; justify-content: flex-end;">
                <div class="block-options">
                    <span class="text-muted font-size-sm">
                        {{ $newsletter->sent_at ? $newsletter->sent_at->format('d/m/Y') : '' }}
                    </span>
                </div>
            </div>
            <div class="block-content" style="font-family: 'Open Sans', sans-serif; line-height: 1.6; font-size: 16px;">
                
                <h3 class="text-primary mb-4" style="font-weight: 700;">{{ $newsletter->subject }}</h3>
                <!-- Conteúdo da Newsletter -->
                <div class="newsletter-content mb-5">
                    {!! $newsletter->web_content !!}
                </div>
                
                <hr>
                
                <!-- CTA -->
                <div class="alert alert-info text-center my-4">
                    <h4 class="alert-heading">Gostou deste conteúdo?</h4>
                    <p>Receba nossas atualizações jurídicas diretamente no seu email. É grátis!</p>
                    <a href="https://newsletter.maurolopes.com.br/subscription?f=guJ3cS2Vm7AxAFSQ24hY1x2LOVOrbH44BBFmy4NXDEULQPmQ9VecJ538XJVLM9JbWogaBgUkTuwWL1y8WaWG1w" target="_blank" class="btn btn-primary">
                        <i class="fa fa-envelope mr-1"></i> Inscrever-se na Newsletter
                    </a>
                </div>
            </div>
        </div>
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
    
    /* Fix breadcrumb separator */
    .breadcrumb-item + .breadcrumb-item::before {
        content: ">" !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }
</style>
@endsection
