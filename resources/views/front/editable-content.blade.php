@extends('front.base')

@section('page-title', $content->title)

@section('content')

    <!-- Schema.org Structured Data para SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": "{{ $content->title }}",
      "description": "{{ $description }}",
      "author": {
        "@type": "Person",
        "name": "Mauro Lopes"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Teses & Súmulas",
        "logo": {
          "@type": "ImageObject",
          "url": "{{ url('/assets/img/icons/android-icon-192x192.png') }}"
        }
      },
      "datePublished": "{{ $content->created_at }}",
      "dateModified": "{{ $content->updated_at }}",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ url()->current() }}"
      },
      "inLanguage": "pt-BR"
    }
    </script>

    <!-- Page Content -->

    <!-- Hero -->
    <div class="bg-body-light" style="{{ $display_pdf }}">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill h3 my-2">
                    <a href="{{ url('/') }}">
                        Teses & Súmulas
                    </a>
                    <span class="text-muted">|</span> {{ $content->title }}
                </h1>
                <span>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR"
                        class="badge badge-primary">Extensão para o Chrome</a>
                </span>
            </div>
            <p>
                <a href="{{ route('searchpage') }}">Pesquisar teses e súmulas</a> | 
                <a href="{{ route('alltemaspage') }}">Pesquisas prontas</a>
            </p>
        </div>
    </div>
    <!-- END Hero -->

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="content content-full pt-2 pb-0">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <!-- Success Message -->
    @if (session('success'))
    <div class="content content-full pt-2 pb-0">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fa fa-check-circle mr-2"></i>
            <strong>{{ session('success') }}</strong>
        </div>
    </div>
    @endif
    <!-- END Success Message -->

    <div class="content" id="content-article">
        <div class="block">
            @auth
                @if(in_array(Auth::user()->email, config('tes_constants.admins')))
                    <div class="block-header block-header-default">
                        <h3 class="block-title"></h3>
                        <div class="block-options">
                            <a href="{{ route('content.edit', $content->slug) }}" class="btn btn-sm btn-primary" title="Editar conteúdo">
                                <i class="fa fa-pencil"></i> Editar
                            </a>
                        </div>
                    </div>
                @endif
            @endauth
            <div class="block-content block-content-full">
                <article class="content-article">
                    {!! $content->content !!}
                </article>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div class="block block-themed" style="{{ $display_pdf }}">
            <div class="block-header bg-primary">
                <h3 class="block-title text-white">🔍 Pesquise Precedentes Vinculantes</h3>
            </div>
            <div class="block-content">
                <p class="mb-3">Utilize nossa ferramenta de busca para encontrar súmulas, teses de repercussão geral e recursos repetitivos:</p>
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('stfallsumulaspage') }}" class="block block-link-shadow text-center">
                            <div class="block-content block-content-full">
                                <div class="font-size-sm font-w600 text-primary">
                                    Súmulas STF
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('stjallsumulaspage') }}" class="block block-link-shadow text-center">
                            <div class="block-content block-content-full">
                                <div class="font-size-sm font-w600 text-primary">
                                    Súmulas STJ
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('stfalltesespage') }}" class="block block-link-shadow text-center">
                            <div class="block-content block-content-full">
                                <div class="font-size-sm font-w600 text-primary">
                                    Teses STF
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('stjalltesespage') }}" class="block block-link-shadow text-center">
                            <div class="block-content block-content-full">
                                <div class="font-size-sm font-w600 text-primary">
                                    Teses STJ
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('searchpage') }}" class="btn btn-primary">
                        <i class="fa fa-search mr-1"></i> Buscar Agora
                    </a>
                </div>
            </div>
        </div>
        <!-- END Call to Action -->
        
    </div>
    <!-- END Page Content -->

@endsection

@section('styles')
<style>
/* Estilos para o artigo */
.content-article {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #333;
}

.content-article h2 {
    color: #3b5998;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-size: 1.75rem;
    font-weight: 600;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e3e6e8;
}

.content-article h3 {
    color: #5c80d1;
    margin-top: 1.5rem;
    margin-bottom: 0.8rem;
    font-size: 1.4rem;
    font-weight: 600;
}

.content-article h4 {
    color: #6d8cd5;
    margin-top: 1.2rem;
    margin-bottom: 0.6rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.content-article p {
    margin-bottom: 1rem;
    text-align: justify;
}

.content-article ul, .content-article ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.content-article li {
    margin-bottom: 0.5rem;
}

.content-article a {
    color: #3b5998;
    text-decoration: none;
    font-weight: 500;
}

.content-article a:hover {
    text-decoration: underline;
    color: #1e3a70;
}

.content-article strong {
    font-weight: 600;
    color: #2c3e50;
}

.content-article code {
    background-color: #f4f5f7;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: #e74c3c;
}

.content-article small {
    color: #6c757d;
    font-size: 0.875rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .content-article {
        font-size: 1rem;
    }
    
    .content-article h2 {
        font-size: 1.5rem;
    }
    
    .content-article h3 {
        font-size: 1.25rem;
    }
    
    .content-article p {
        text-align: left;
    }
}
</style>
@endsection

