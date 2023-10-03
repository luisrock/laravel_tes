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

    <link href="{{ $canonical_url ?? Request::url() }}" rel="canonical">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="Teses e Súmulas">
    <meta property="og:site_name" content="tesesesumulas">
    <meta property="og:description" content="{{ $description ?? config('tes_constants.options.meta_description') }}">
    <meta property="og:type" content="website">
    <!--         <meta property="og:url" content="">
        <meta property="og:image" content=""> -->

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
    @endphp

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600,700%7COpen+Sans:300,400,400italic,600,700">
    <link rel="stylesheet" id="css-main" href='{{ $basepath . '/assets/css/tescustom.min.css' }}' type="text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"
        integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href='{{ $basepath . '/assets/css/tes.css?v=' . time() }}' type="text/css">


    @yield('styles')

    @if ($display_pdf == 'display:none;')
        <style>
            body,
            .trib-texto-quantidade {
                background-color: #fff;
            }
        </style>
    @endif

    <!-- google adsense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6476437932373204"
        crossorigin="anonymous"></script>
    <!-- END google adsense -->

    <!-- Pixel Code for https://proofcourse.com/ -->
    <script defer src="https://proofcourse.com/pixel/g5ptl75ocv5t93oo54rbvw4r8uxspxz3"></script>
    <!-- END Pixel Code -->

    <!-- Matomo -->
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
    <!-- End Matomo Code -->

</head>

<body>
    <!-- Page Container -->
    <div id="page-container" class="main-content-boxed">

        <!-- Main Container -->
        <main id="main-container">

            @yield('content')

        </main>
        <!-- END Main Container -->

        <!-- Footer -->
        <footer id="page-footer" class="bg-body-light" style="{{ $display_pdf }}">
            <div class="content py-3">
                <div class="row font-size-sm">
                    <div class="col-sm-6 order-sm-2 py-1 text-center text-sm-right">
                        @if (Route::currentRouteName() == 'searchpage')
                            <a class="font-w600" href="{{ route('alltemaspage') }}">Pesquisas prontas</a>
                        @elseif(Route::currentRouteName() == 'alltemaspage')
                            <a class="font-w600" href="{{ route('searchpage') }}">Pesquisar</a>
                        @else
                            <a class="font-w600" href="{{ route('alltemaspage') }}">Pesquisas prontas</a> | <a
                                class="font-w600" href="{{ route('searchpage') }}">Pesquisar</a>
                        @endif
                        @auth
                            @if (in_array(Auth::user()->email, config('tes_constants.admins')))
                                | <a href="{{ route('admin') }}">Admin</a>
                            @endif
                        @endauth
                    </div>
                    <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-left">
                        <a class="font-w600" href="/index">Todas as Súmulas e Teses do STF e do STJ</a>

                        {{-- Fontes:

                        <a class="font-w600" href="https://jurisprudencia.stf.jus.br/pages/search"
                            target="_blank">STF</a>
                        -
                        <a class="font-w600" href="https://jurisprudencia.tst.jus.br/" target="_blank">TST</a>
                        -
                        <a class="font-w600" href="https://scon.stj.jus.br/SCON/" target="_blank">STJ</a>
                        -
                        <a class="font-w600" href="https://www2.cjf.jus.br/jurisprudencia/tnu/"
                            target="_blank">TNU</a>
                        -
                        <a class="font-w600" href="https://pesquisa.apps.tcu.gov.br/#/pesquisa/jurisprudencia"
                            target="_blank">TCU</a>
                        -
                        <a class="font-w600" href="http://idg.carf.fazenda.gov.br/jurisprudencia/sumulas-carf"
                            target="_blank">CARF</a>
                        -
                        <a class="font-w600"
                            href="https://www.cnj.jus.br/corregedoria-nacional-de-justica/redescobrindo-os-juizados-especiais/enunciados-fonaje/"
                            target="_blank">FONAJE</a>
                        -
                        <a class="font-w600" href="https://www.cjf.jus.br/enunciados/" target="_blank">CEJ</a> --}}
                    </div>
                </div>
                <hr>
                <div class="row font-size-sm">
                    <div class="col-sm-6 order-sm-2 py-1 text-center text-sm-right">
                        &copy; <span data-toggle="year-copy"></span>. Todos os direitos reservados.
                    </div>
                    <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-left">
                        Criado por <a class="font-w600" href="https://maurolopes.com.br" target="_blank">Mauro
                            Lopes</a>.
                    </div>
                </div>

            </div>
        </footer>
        <!-- END Footer -->


    </div>
    <!-- END Page Container -->

    <script src='{{ url('assets/js/tescustom.core.min.js') }}'></script>
    <script src='{{ url('assets/js/tescustom.app.min.js') }}'></script>
    <script src='{{ url('assets/js/tes.js') }}'></script>
    <script src='{{ url('assets/js/tes_tema_concept.js') }}'></script>
    @yield('adminjs')

</body>

</html>
