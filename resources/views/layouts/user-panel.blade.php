<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="tw-h-full tw-antialiased tw-scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page-title', 'Minha Conta') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/js/tailwind-home.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @yield('panel-styles')
</head>
<body class="tw-font-sans tw-bg-slate-50 tw-text-slate-900 tw-flex tw-flex-col tw-min-h-screen">
    
    @include('partials.header')

    <main class="tw-flex-grow tw-py-12">
        <div class="tw-mx-auto tw-max-w-7xl tw-px-4 sm:tw-px-6 lg:tw-px-8">
            <div class="tw-mx-auto tw-max-w-4xl">
                <div class="tw-mb-8 tw-flex tw-justify-between tw-items-center">
                    <h1 class="tw-text-2xl tw-font-bold tw-text-slate-900">@yield('panel-title', 'Minha Conta')</h1>
                    <a href="{{ route('searchpage') }}" class="tw-inline-flex tw-items-center tw-text-sm tw-font-medium tw-text-brand-600 hover:tw-text-brand-800 hover:tw-underline">
                        <svg class="tw-mr-2 tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Voltar ao site
                    </a>
                </div>

                @yield('panel-content')
            </div>
        </div>
    </main>
    
    @include('partials.footer')
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('panel-scripts')
</body>
</html>
