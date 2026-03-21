{{-- Header Principal - Partial DRY --}}
{{-- Uso: @include('partials.header') --}}

@if(config('teses.test_toolbar_enabled') && auth()->check() && auth()->user()->email === config('teses.test_toolbar_email'))
<div class="tw-bg-slate-800 tw-text-white tw-text-xs tw-py-1.5 tw-px-4">
    <div class="tw-max-w-7xl tw-mx-auto tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2">
        <span class="tw-font-semibold tw-text-amber-300"><i class="fa fa-flask tw-mr-1"></i> Teste ({{ auth()->user()->roles->pluck('name')->implode(', ') }})</span>

        <div class="tw-flex tw-items-center tw-gap-3 tw-flex-wrap">
            @php $viewCount = \App\Models\ContentView::where('user_id', auth()->id())->where('viewed_at', '>=', now()->subHours(24))->count(); @endphp
            <span class="tw-text-slate-300">Views 24h: <strong class="tw-text-white">{{ $viewCount }}</strong></span>

            <form action="{{ route('test-toolbar.reset-views') }}" method="POST" class="tw-inline">
                @csrf
                <button type="submit" class="tw-px-2 tw-py-0.5 tw-rounded tw-bg-red-600 hover:tw-bg-red-500 tw-text-white tw-font-medium tw-transition-colors">Zerar Views</button>
            </form>

            <span class="tw-h-4 tw-w-px tw-bg-slate-500"></span>

            @foreach(['registered', 'subscriber', 'premium'] as $roleName)
                <form action="{{ route('test-toolbar.switch-role') }}" method="POST" class="tw-inline">
                    @csrf
                    <input type="hidden" name="role" value="{{ $roleName }}">
                    <button type="submit" class="tw-px-2 tw-py-0.5 tw-rounded tw-font-medium tw-transition-colors {{ auth()->user()->hasRole($roleName) ? 'tw-bg-amber-500 tw-text-slate-900' : 'tw-bg-slate-600 hover:tw-bg-slate-500 tw-text-slate-200' }}">{{ ucfirst($roleName) }}</button>
                </form>
            @endforeach
        </div>

        @if(session('test-toolbar-message'))
            <span class="tw-text-green-300 tw-font-medium">{{ session('test-toolbar-message') }}</span>
        @endif
    </div>
</div>
@endif

<header class="site-header tw-bg-white tw-border-b tw-border-slate-200">
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-h-16 tw-flex tw-items-center tw-justify-between">
        <a href="{{ url('/') }}" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-slate-900 hover:tw-text-brand-700 tw-transition">
            <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-md tw-bg-brand-700 tw-text-white tw-text-xs tw-font-semibold">T&S</span>
            <span class="tw-text-base sm:tw-text-lg tw-font-semibold">Teses & Súmulas</span>
        </a>

        <button id="site-nav-toggle" type="button" class="md:tw-hidden tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-rounded-md tw-border tw-border-slate-300 tw-text-slate-700 hover:tw-bg-slate-100" aria-expanded="false" aria-controls="site-nav-menu" aria-label="Abrir menu">
            <i class="fa fa-bars"></i>
        </button>

        <nav class="tw-hidden md:tw-flex tw-items-center tw-gap-1" id="site-nav-desktop">
            <a href="{{ route('searchpage') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Pesquisar</a>
            <a href="{{ route('alltemaspage') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Prontas</a>
            <a href="/index" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Índice</a>
            <a href="{{ route('newsletterspage') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Newsletters</a>

            <span class="tw-h-6 tw-w-px tw-bg-slate-300 tw-mx-2"></span>

            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Admin</a>
                @endif

                @if(config('subscription.enabled'))
                    @if(auth()->user()->isSubscriber())
                        <a href="{{ route('subscription.show') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Minha Assinatura</a>
                    @else
                        <a href="{{ route('subscription.plans') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-bg-brand-700 tw-text-white hover:tw-bg-brand-800 tw-transition">Assinar</a>
                    @endif
                @endif

                <a href="{{ route('user-panel.dashboard') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Minha Conta</a>
                <form action="{{ route('logout') }}" method="POST" class="tw-inline">
                    @csrf
                    <button type="submit" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Sair</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-brand-700 hover:tw-bg-slate-100 tw-transition">Entrar</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-bg-brand-700 tw-text-white hover:tw-bg-brand-800 tw-transition">Criar Conta</a>
                @endif
            @endauth
        </nav>
    </div>

    <nav id="site-nav-menu" class="md:tw-hidden tw-hidden tw-border-t tw-border-slate-200 tw-bg-white tw-p-3 tw-space-y-1">
        <a href="{{ route('searchpage') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Pesquisar</a>
        <a href="{{ route('alltemaspage') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Prontas</a>
        <a href="/index" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Índice</a>
        <a href="{{ route('newsletterspage') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Newsletters</a>

        <div class="tw-my-2 tw-h-px tw-bg-slate-200"></div>

        @auth
            @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('admin') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Admin</a>
            @endif

            @if(config('subscription.enabled'))
                @if(auth()->user()->isSubscriber())
                    <a href="{{ route('subscription.show') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Minha Assinatura</a>
                @else
                    <a href="{{ route('subscription.plans') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-bg-brand-700 tw-text-white">Assinar</a>
                @endif
            @endif

            <a href="{{ route('user-panel.dashboard') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Minha Conta</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="tw-w-full tw-text-left tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Sair</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-brand-700 hover:tw-bg-slate-100">Entrar</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-bg-brand-700 tw-text-white hover:tw-bg-brand-800 tw-mt-2">Criar Conta</a>
            @endif
        @endauth
    </nav>
</header>

<script>
(function () {
    const toggle = document.getElementById('site-nav-toggle');
    const menu = document.getElementById('site-nav-menu');

    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener('click', function () {
        const isOpen = !menu.classList.contains('tw-hidden');
        menu.classList.toggle('tw-hidden');
        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });
})();
</script>
