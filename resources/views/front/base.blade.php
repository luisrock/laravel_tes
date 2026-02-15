<!doctype html>
<html lang="pt-br" class="tw-h-full tw-antialiased tw-scroll-smooth">
<head>
    @if (App::environment() == 'production')
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-175097640-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', 'UA-175097640-1');
        </script>
    @endif
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    <link rel="icon" type="image/png" sizes="192x192" href='{{ url('assets/img/icons/android-icon-192x192.png') }}'>
    <link rel="icon" type="image/png" sizes="32x32" href='{{ url('assets/img/icons/favicon-32x32.png') }}'>
    <link rel="icon" type="image/png" sizes="96x96" href='{{ url('assets/img/icons/favicon-96x96.png') }}'>
    <link rel="icon" type="image/png" sizes="16x16" href='{{ url('assets/img/icons/favicon-16x16.png') }}'>
    <link rel="manifest" href='{{ url('assets/img/icons/manifest.json') }}'>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content='{{ url('assets/img/icons/ms-icon-144x144.png') }}'>
    <meta name="theme-color" content="#ffffff">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

    <!-- Styles -->
    @vite(['resources/js/tailwind-home.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

    @yield('styles')

    <!-- Google Adsense -->
    @if(config('app.env') === 'production')
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6476437932373204" crossorigin="anonymous"></script>
    @endif

    <!-- Matomo -->
    @if(config('app.env') === 'production')
    <script>
        var _paq = window._paq = window._paq || [];
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "//maurolopes.com.br/matomo/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '2']);
            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.async = true; g.src = u + 'matomo.js'; s.parentNode.insertBefore(g, s);
        })();
    </script>
    @endif

    <!-- Pixel Code -->
    @if(config('app.env') === 'production')
    <script defer src="https://proof.maurolopes.com.br/pixel/k8r0O33PgwZCwU1fFfKU9yHbhXEOAn2a"></script>
    @endif
</head>

<body class="tw-bg-slate-50 tw-text-slate-900 tw-flex tw-flex-col tw-min-h-screen">
    
    @include('partials.header')

    <main class="tw-flex-grow">
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- Back to Top Button -->
    <button id="back-to-top" class="tw-fixed tw-bottom-6 tw-right-6 tw-z-50 tw-hidden tw-opacity-0 tw-transition-all tw-duration-300 tw-p-3 tw-rounded-full tw-bg-brand-600 tw-text-white tw-shadow-lg hover:tw-bg-brand-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500 focus:tw-ring-opacity-50" title="Voltar ao topo">
        <i class="fa fa-arrow-up"></i>
    </button>

    <script>
    (function() {
        // Back to Top Logic
        const backToTopBtn = document.getElementById('back-to-top');
        if (backToTopBtn) {
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.classList.remove('tw-hidden');
                        // Small delay to allow display block to apply before opacity transition
                        setTimeout(() => backToTopBtn.classList.remove('tw-opacity-0'), 10);
                    } else {
                        backToTopBtn.classList.add('tw-opacity-0');
                        setTimeout(() => backToTopBtn.classList.add('tw-hidden'), 300);
                    }
                }, 100);
            });
            
            backToTopBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    })();
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('scripts')
</body>
</html>
