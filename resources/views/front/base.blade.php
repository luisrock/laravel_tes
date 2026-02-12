<!doctype html>
<html lang="pt-br">

<head>
    @if (App::environment() == 'production')
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <!-- Only for production -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-175097640-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', 'UA-175097640-1');
        </script>
    @endif
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title') - Teses & Súmulas</title>

    <meta name="description" content="{{ $description ?? config('tes_constants.options.meta_description') }}">
    <meta name="author" content="Mauro Lopes">
    <meta name="keywords" content="teses, súmulas, stf, stj, tst, tnu, jurisprudência, repercussão geral, repetitivos">

    <link href="{{ $canonical_url ?? Request::url() }}" rel="canonical">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('page-title') - Teses & Súmulas">
    <meta property="og:site_name" content="Teses & Súmulas">
    <meta property="og:description" content="{{ $description ?? config('tes_constants.options.meta_description') }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $canonical_url ?? Request::url() }}">
    <meta property="og:locale" content="pt_BR">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('page-title') - Teses & Súmulas">
    <meta name="twitter:description" content="{{ $description ?? config('tes_constants.options.meta_description') }}">
    <meta name="twitter:site" content="@tesesesumulas">

    <!-- Icons -->
    <link rel="apple-touch-icon" sizes="57x57" href='{{ url('assets/img/icons/apple-icon-57x57.png') }}'>
    <link rel="apple-touch-icon" sizes="60x60" href='{{ url('assets/img/icons/apple-icon-60x60.png') }}'>
    <link rel="apple-touch-icon" sizes="72x72" href='{{ url('assets/img/icons/apple-icon-72x72.png') }}'>
    <link rel="apple-touch-icon" sizes="76x76" href='{{ url('assets/img/icons/apple-icon-76x76.png') }}'>
    <link rel="apple-touch-icon" sizes="114x114" href='{{ url('assets/img/icons/apple-icon-114x114.png') }}'>
    <link rel="apple-touch-icon" sizes="120x120" href='{{ url('assets/img/icons/apple-icon-120x120.png') }}'>
    <link rel="apple-touch-icon" sizes="144x144" href='{{ url('assets/img/icons/apple-icon-144x144.png') }}'>
    <link rel="apple-touch-icon" sizes="152x152" href='{{ url('assets/img/icons/apple-icon-152x152.png') }}'>
    <link rel="apple-touch-icon" sizes="180x180" href='{{ url('assets/img/icons/apple-icon-180x180.png') }}'>
    <link rel="icon" type="image/png" sizes="192x192"
        href='{{ url('assets/img/icons/android-icon-192x192.png') }}'>
    <link rel="icon" type="image/png" sizes="32x32" href='{{ url('assets/img/icons/favicon-32x32.png') }}'>
    <link rel="icon" type="image/png" sizes="96x96" href='{{ url('assets/img/icons/favicon-96x96.png') }}'>
    <link rel="icon" type="image/png" sizes="16x16" href='{{ url('assets/img/icons/favicon-16x16.png') }}'>
    <link rel="manifest" href='{{ url('assets/img/icons/manifest.json') }}'>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content='{{ url('assets/img/icons/ms-icon-144x144.png') }}'>
    <meta name="theme-color" content="#ffffff">
    <!-- END Icons -->

    @php
        //mPdf does not access css by url, but by full path
        $basepath = empty($display_pdf) ? url('/') : public_path();

        $modernizedRouteNames = [
            'searchpage',
            'alltemaspage',
            'temapage',
            'indexsumulaspage',
            'stfallsumulaspage',
            'stjallsumulaspage',
            'tstallsumulaspage',
            'tnuallsumulaspage',
            'stfalltesespage',
            'stjalltesespage',
            'stfsumulapage',
            'stjsumulapage',
            'tstsumulapage',
            'tnusumulapage',
            'stftesepage',
            'stjtesepage',
            'newsletterspage',
            'newsletter.show',
            'newsletterobrigadopage',
        ];

        $isModernizedRoute = request()->routeIs($modernizedRouteNames);
    @endphp

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600,700%7COpen+Sans:300,400,400italic,600,700">
    <link rel="stylesheet" id="css-main" href='{{ $basepath . '/assets/css/tescustom.min.css' }}' type="text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"
        integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href='{{ $basepath . '/assets/css/tes.css?v=' . time() }}' type="text/css">

    @if (empty($display_pdf))
        @vite(['resources/js/tailwind-home.js'])
    @endif


    @yield('styles')

    {{-- Estilos globais do novo header/footer --}}
    @include('partials.header-footer-styles')

    @if ($display_pdf == 'display:none;')
        <style>
            body,
            .trib-texto-quantidade {
                background-color: #fff;
            }
        </style>
    @endif

    <!-- google adsense -->
    {{-- pausado em 10/10/2023 --}}
    {{-- retomado em 30/10/2023 --}}
    @if(config('app.env') === 'production')
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6476437932373204"
        crossorigin="anonymous"></script>
    @endif
    <!-- END google adsense -->

    <!-- Pixel Code for https://proofcourse.com/ -->
    {{-- Removido em 03/11/2025 - causava loading infinito --}}
    <!-- END Pixel Code -->

    <!-- Matomo -->
    @if(config('app.env') === 'production')
    <script>
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "//maurolopes.com.br/matomo/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '2']);
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    @endif
    <!-- End Matomo Code -->

    <!-- Pixel Code - https://proof.maurolopes.com.br/ -->
    @if(config('app.env') === 'production')
    <script defer src="https://proof.maurolopes.com.br/pixel/k8r0O33PgwZCwU1fFfKU9yHbhXEOAn2a"></script>
    @endif
    <!-- END Pixel Code -->

</head>

<body class="@if(request()->routeIs('user-panel.*', 'subscription.show', 'refund.*')) page-user-panel @endif">
    <!-- Page Container -->
    <div id="page-container" class="main-content-boxed">

        <!-- Main Container -->
        <main id="main-container">

            {{-- Header Global --}}
            @include('partials.header')

            <div class="page-content">
                @yield('content')
            </div>

            {{-- Footer Global --}}
            @include('partials.footer')

        </main>
        <!-- END Main Container -->

    </div>
    <!-- END Page Container -->

    <!-- Botão Voltar ao Topo -->
    <button id="back-to-top" class="btn btn-primary" title="Voltar ao topo" style="{{ $display_pdf }}">
        <i class="fa fa-arrow-up"></i>
    </button>
    <!-- END Botão Voltar ao Topo -->

    @unless($isModernizedRoute)
        <script src='{{ url('assets/js/tescustom.core.min.js') }}'></script>
        <script src='{{ url('assets/js/tescustom.app.min.js') }}'></script>
    @endunless
    <script src='{{ url('assets/js/tes.js') }}'></script>
    <script src='{{ url('assets/js/tes_tema_concept.js') }}'></script>
    @yield('adminjs')
    @yield('atualizacoesjs')
    @yield('scripts')
    
    <!-- Script Botão Voltar ao Topo -->
    <script>
    (function() {
        const backToTopBtn = document.getElementById('back-to-top');
        if (!backToTopBtn) return;
        
        // Mostrar/ocultar botão baseado no scroll
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.style.display = 'flex';
                    backToTopBtn.style.opacity = '0';
                    setTimeout(() => backToTopBtn.style.opacity = '1', 10);
                } else {
                    backToTopBtn.style.opacity = '0';
                    setTimeout(() => backToTopBtn.style.display = 'none', 300);
                }
            }, 100);
        });
        
        // Ação do clique - scroll suave para o topo
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    })();
    </script>
    <!-- END Script Botão Voltar ao Topo -->

</body>

</html>
