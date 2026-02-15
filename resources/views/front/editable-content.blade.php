@extends('layouts.app')

@section('page-title', $content->title)

@section('content')

    <!-- Schema.org Structured Data para SEO -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
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

    <!-- Breadcrumb -->
    @if(isset($breadcrumb))
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4">
        <x-breadcrumb :items="$breadcrumb" />
    </div>
    @endif
    <!-- END Breadcrumb -->

    <!-- Success Message -->
    @if (session('success'))
    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-pt-4">
        <div class="tw-bg-brand-50 tw-border tw-border-brand-200 tw-text-brand-800 tw-px-4 tw-py-3 tw-rounded tw-relative" role="alert">
            <span class="tw-block sm:tw-inline">
                <i class="fa fa-check-circle tw-mr-2"></i>
                <strong>{{ session('success') }}</strong>
            </span>
            <span class="tw-absolute tw-top-0 tw-bottom-0 tw-right-0 tw-px-4 tw-py-3" onclick="this.parentElement.style.display='none';">
                <svg class="tw-fill-current tw-h-6 tw-w-6 tw-text-brand-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
            </span>
        </div>
    </div>
    @endif
    <!-- END Success Message -->

    <div class="tw-max-w-5xl tw-mx-auto tw-px-4 tw-py-6">
        <div class="tw-bg-white tw-shadow-sm tw-rounded-xl tw-overflow-hidden tw-border tw-border-slate-200">
            @auth
                @if(in_array(Auth::user()->email, config('tes_constants.admins')))
                    <div class="tw-bg-slate-50 tw-border-b tw-border-slate-200 tw-px-6 md:tw-px-8 tw-py-6 tw-flex tw-justify-between tw-items-center">
                        <h1 class="tw-text-3xl tw-font-bold tw-text-slate-800 tw-tracking-tight">{{ $content->title }}</h1>
                        <a href="{{ route('content.edit', $content->slug) }}" class="tw-bg-brand-600 hover:tw-bg-brand-700 tw-text-white tw-font-bold tw-py-2 tw-px-4 tw-rounded-lg tw-text-sm tw-transition tw-shadow-sm" title="Editar conteúdo">
                            <i class="fa fa-pencil tw-mr-1"></i> Editar
                        </a>
                    </div>
                @else
                    <div class="tw-px-6 md:tw-px-8 tw-pt-8 tw-pb-4">
                         <h1 class="tw-text-3xl md:tw-text-4xl tw-font-bold tw-text-slate-800 tw-tracking-tight tw-mb-2">{{ $content->title }}</h1>
                         <div class="tw-w-20 tw-h-1 tw-bg-brand-600 tw-rounded"></div>
                    </div>
                @endif
            @else
                <div class="tw-px-6 md:tw-px-8 tw-pt-8 tw-pb-4">
                     <h1 class="tw-text-3xl md:tw-text-4xl tw-font-bold tw-text-slate-800 tw-tracking-tight tw-mb-2">{{ $content->title }}</h1>
                     <div class="tw-w-20 tw-h-1 tw-bg-brand-600 tw-rounded"></div>
                </div>
            @endauth
            
            <div class="tw-p-6 md:tw-p-10">
                <article class="tw-prose tw-prose-lg tw-prose-slate tw-max-w-none hover:tw-prose-a:tw-text-brand-600">
                    {!! $content->content !!}
                </article>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div class="tw-mt-10 tw-bg-brand-700 tw-rounded-xl tw-shadow-lg tw-overflow-hidden">
            <div class="tw-p-8 md:tw-p-10 tw-text-center">
                <h3 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-white tw-mb-4">🔍 Pesquise Precedentes Vinculantes</h3>
                <p class="tw-text-brand-100 tw-text-lg tw-mb-8 tw-max-w-3xl tw-mx-auto tw-leading-relaxed">
                    Utilize nossa ferramenta de busca para encontrar súmulas, teses de repercussão geral e recursos repetitivos dos tribunais superiores.
                </p>
                
                <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-4 tw-gap-4 tw-mb-10 tw-max-w-4xl tw-mx-auto">
                    <a href="{{ route('stfallsumulaspage') }}" class="tw-bg-white/10 hover:tw-bg-white/20 tw-backdrop-blur-sm tw-transition tw-rounded-xl tw-p-5 tw-text-white tw-text-center tw-group tw-no-underline tw-border tw-border-white/10">
                        <span class="tw-block tw-font-bold tw-uppercase tw-text-sm tw-tracking-wide group-hover:tw-scale-105 tw-transition-transform">Súmulas STF</span>
                    </a>
                    <a href="{{ route('stjallsumulaspage') }}" class="tw-bg-white/10 hover:tw-bg-white/20 tw-backdrop-blur-sm tw-transition tw-rounded-xl tw-p-5 tw-text-white tw-text-center tw-group tw-no-underline tw-border tw-border-white/10">
                         <span class="tw-block tw-font-bold tw-uppercase tw-text-sm tw-tracking-wide group-hover:tw-scale-105 tw-transition-transform">Súmulas STJ</span>
                    </a>
                    <a href="{{ route('stfalltesespage') }}" class="tw-bg-white/10 hover:tw-bg-white/20 tw-backdrop-blur-sm tw-transition tw-rounded-xl tw-p-5 tw-text-white tw-text-center tw-group tw-no-underline tw-border tw-border-white/10">
                         <span class="tw-block tw-font-bold tw-uppercase tw-text-sm tw-tracking-wide group-hover:tw-scale-105 tw-transition-transform">Teses STF</span>
                    </a>
                     <a href="{{ route('stjalltesespage') }}" class="tw-bg-white/10 hover:tw-bg-white/20 tw-backdrop-blur-sm tw-transition tw-rounded-xl tw-p-5 tw-text-white tw-text-center tw-group tw-no-underline tw-border tw-border-white/10">
                         <span class="tw-block tw-font-bold tw-uppercase tw-text-sm tw-tracking-wide group-hover:tw-scale-105 tw-transition-transform">Teses STJ</span>
                    </a>
                </div>
                
                <a href="{{ route('searchpage') }}" class="tw-inline-flex tw-items-center tw-justify-center tw-px-8 tw-py-4 tw-border tw-border-transparent tw-text-lg tw-font-semibold tw-rounded-lg tw-text-brand-900 tw-bg-white hover:tw-bg-brand-50 tw-transition tw-shadow-lg hover:tw-shadow-xl tw-no-underline tw-transform hover:tw--translate-y-0.5">
                    <i class="fa fa-search tw-mr-2.5"></i> Buscar Agora
                </a>
            </div>
        </div>
        <!-- END Call to Action -->
        
    </div>
    <!-- END Page Content -->

@endsection

@section('styles')
<style>
    /* Custom styles for content that might come from Summernote/WYSIWYG to ensure it looks good in Tailwind */
    .tw-prose h2 {
        color: #1e293b; /* slate-800 */
        font-weight: 700;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        font-size: 1.75em;
        line-height: 1.3;
    }
    .tw-prose h3 {
        color: #3f5a78; /* brand-700 */
        font-weight: 600;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-size: 1.5em;
    }
    .tw-prose h4 {
        color: #475569; /* slate-600 */
        font-weight: 600;
        margin-top: 1.5rem;
        font-size: 1.25em;
    }
    .tw-prose blockquote {
        border-left-color: #3f5a78; /* brand-700 */
        background-color: #f8fafc;
        padding: 1.25rem;
        font-style: italic;
        border-left-width: 4px;
        margin-top: 2rem;
        margin-bottom: 2rem;
        border-radius: 0 0.5rem 0.5rem 0;
    }
    .tw-prose a {
        color: #4b6c90; /* brand-600 */
        text-decoration: none;
        font-weight: 500;
        border-bottom: 1px solid transparent;
        transition: all 0.2s;
    }
    .tw-prose a:hover {
        border-bottom-color: currentColor;
        color: #3f5a78; /* brand-700 */
    }
    .tw-prose table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 2rem;
        margin-bottom: 2rem;
        font-size: 0.95em;
    }
    .tw-prose th, .tw-prose td {
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        text-align: left;
    }
    .tw-prose th {
        background-color: #f8fafc;
        font-weight: 600;
        color: #1e293b;
    }
    .tw-prose tr:nth-child(even) {
        background-color: #fbfcff;
    }
    .tw-prose ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin-top: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .tw-prose ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
        margin-top: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .tw-prose li {
        margin-bottom: 0.5rem;
    }
    .tw-prose p {
        margin-bottom: 1.5rem;
        line-height: 1.8;
    }
    .tw-prose strong {
        color: #334155;
        font-weight: 600;
    }
</style>
@endsection
