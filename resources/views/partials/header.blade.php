{{-- Header Principal - Partial DRY --}}
{{-- Uso: @include('partials.header') --}}

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
            <a href="{{ route('alltemaspage') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Temas</a>
            <a href="{{ route('newsletterspage') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Atualizações</a>

            <span class="tw-h-6 tw-w-px tw-bg-slate-300 tw-mx-2"></span>

            @auth
                @if(in_array(auth()->user()->email, config('tes_constants.admins')))
                    <a href="{{ route('admin') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Admin</a>
                @endif
            @endauth

            @auth
                @if(auth()->user()->isSubscriber())
                    <a href="{{ route('subscription.show') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Minha Assinatura</a>
                @else
                    <a href="{{ route('subscription.plans') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-bg-brand-700 tw-text-white hover:tw-bg-brand-800 tw-transition">Assinar</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="tw-inline">
                    @csrf
                    <button type="submit" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100 hover:tw-text-brand-700 tw-transition">Sair</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-brand-700 hover:tw-bg-slate-100 tw-transition">Entrar</a>
            @endauth
        </nav>
    </div>

    <nav id="site-nav-menu" class="md:tw-hidden tw-hidden tw-border-t tw-border-slate-200 tw-bg-white tw-p-3 tw-space-y-1">
        <a href="{{ route('searchpage') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Pesquisar</a>
        <a href="{{ route('alltemaspage') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Temas</a>
        <a href="{{ route('newsletterspage') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Atualizações</a>

        <div class="tw-my-2 tw-h-px tw-bg-slate-200"></div>

        @auth
            @if(in_array(auth()->user()->email, config('tes_constants.admins')))
                <a href="{{ route('admin') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Admin</a>
            @endif
        @endauth

        @auth
            @if(auth()->user()->isSubscriber())
                <a href="{{ route('subscription.show') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Minha Assinatura</a>
            @else
                <a href="{{ route('subscription.plans') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-bg-brand-700 tw-text-white">Assinar</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="tw-w-full tw-text-left tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-slate-700 hover:tw-bg-slate-100">Sair</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="tw-block tw-px-3 tw-py-2 tw-rounded-md tw-text-sm tw-font-medium tw-text-brand-700 hover:tw-bg-slate-100">Entrar</a>
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
