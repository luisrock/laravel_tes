{{-- Header Principal - Partial DRY --}}
{{-- Uso: @include('partials.header') --}}

<header class="site-header">
    <div class="header-accent"></div>
    <div class="header-inner">
        <a href="{{ url('/') }}" class="site-logo">
            <span class="logo-icon">T&S</span>
            Teses & Súmulas
        </a>
        
        <button class="mobile-toggle" onclick="document.querySelector('.site-nav').classList.toggle('active')">
            ☰
        </button>
        
        <nav class="site-nav">
            <a href="{{ route('searchpage') }}">Pesquisar</a>
            <a href="{{ route('alltemaspage') }}">Temas</a>
            <a href="{{ route('newsletterspage') }}">Atualizações</a>
            <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR" 
               target="_blank" class="chrome-badge">
                🔗 Extensão Chrome
            </a>
            @auth
                <span class="nav-divider"></span>
                @if(auth()->user()->isSubscriber())
                    <a href="{{ route('subscription.show') }}">Minha Assinatura</a>
                @else
                    <a href="{{ route('subscription.plans') }}" class="btn-subscribe">Assinar</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <a href="#" onclick="this.parentNode.submit(); return false;">Sair</a>
                </form>
            @else
                <span class="nav-divider"></span>
                <a href="{{ route('login') }}" class="btn-login">Entrar</a>
                {{-- Botão Assinar temporariamente escondido até lançamento --}}
                {{-- <a href="{{ route('subscription.plans') }}" class="btn-subscribe">Assinar</a> --}}
            @endauth
        </nav>
    </div>
</header>
