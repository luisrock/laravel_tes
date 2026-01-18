{{-- Footer Principal - Partial DRY --}}
{{-- Uso: @include('partials.footer') --}}

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-main">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="footer-logo">
                    <span class="logo-icon">T&S</span>
                    Teses & Súmulas
                </a>
                <p class="footer-tagline">
                    Pesquisa Simplificada de Súmulas e Teses de Repetitivos e Repercussão Geral
                </p>
            </div>
            
            <div class="footer-links">
                <div class="footer-col">
                    <h4>Navegação</h4>
                    <a href="{{ route('searchpage') }}">Pesquisar</a>
                    <a href="{{ route('alltemaspage') }}">Temas</a>
                    <a href="{{ route('newsletterspage') }}">Atualizações</a>
                </div>
                
                <div class="footer-col">
                    <h4>Recursos</h4>
                    <a href="{{ url('/index') }}">Índice de Súmulas e Teses</a>
                    <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR" target="_blank">Extensão Chrome</a>
                </div>
                
                <div class="footer-col">
                    <h4>Conta</h4>
                    @auth
                        <a href="{{ route('subscription.show') }}">Minha Assinatura</a>
                        <a href="{{ route('subscription.portal') }}">Gerenciar Pagamento</a>
                    @else
                        <a href="{{ route('login') }}">Entrar</a>
                        {{-- Link Assinar temporariamente escondido até lançamento --}}
                        {{-- <a href="{{ route('subscription.plans') }}">Assinar</a> --}}
                    @endauth
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-copyright">
                © <span id="footer-year"></span> Teses & Súmulas. Todos os direitos reservados.
            </div>
            <div class="footer-credits">
                Criado por <a href="https://maurolopes.com.br" target="_blank">Mauro Lopes</a>
            </div>
        </div>
    </div>
</footer>

<script>
document.getElementById('footer-year').textContent = new Date().getFullYear();
</script>
