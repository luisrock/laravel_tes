<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Teses & Súmulas') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    {{-- Estilos globais do novo header/footer --}}
    @include('partials.header-footer-styles')
    
    <style>
        /* Remove padding negativo que conflita com layout app */
        .site-header, .site-footer {
            width: 100%;
            left: 0;
            right: 0;
            margin-left: 0;
            margin-right: 0;
        }
        .site-footer {
            margin-top: 0;
        }
        #app {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main.py-4 {
            flex: 1;
        }
    </style>
    
    @yield('admin-styles')
</head>

<body>
    <div id="app">
        {{-- Header Global --}}
        @include('partials.header')

        <main class="py-4">
            @yield('content')
        </main>
        
        {{-- Footer Global --}}
        @include('partials.footer')
    </div>
    @yield('adminjs')
    @yield('admin-scripts')
    @yield('atualizacoesjs')
</body>

</html>
