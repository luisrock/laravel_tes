{{-- Footer Principal - Partial DRY --}}
{{-- Uso: @include('partials.footer') --}}

<footer class="site-footer tw-bg-slate-900 tw-text-slate-300 tw-mt-10">
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-12">
        <div class="tw-grid tw-grid-cols-1 xl:tw-grid-cols-12 tw-gap-10">
            <section class="xl:tw-col-span-5 tw-space-y-3">
                <a href="{{ url('/') }}" class="tw-inline-flex tw-items-center tw-gap-3 tw-text-white hover:tw-text-slate-100">
                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-md tw-bg-brand-700 tw-text-white tw-text-xs tw-font-semibold">T&S</span>
                    <span class="tw-font-semibold tw-text-base">Teses & Súmulas</span>
                </a>
                <p class="tw-text-sm tw-leading-relaxed tw-text-slate-200 tw-max-w-md tw-m-0">
                    Plataforma de consulta jurídica com foco em súmulas e teses de repetitivos e repercussão geral, com navegação objetiva e leitura clara.
                </p>
                <a href="{{ url('/index') }}" class="tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-medium tw-text-sky-300 hover:tw-text-sky-200 tw-underline tw-underline-offset-4 tw-decoration-sky-400/70 hover:tw-decoration-sky-300">
                    Índice completo de súmulas e teses
                    <i class="fa fa-arrow-right tw-text-xs" aria-hidden="true"></i>
                </a>
            </section>

            <section class="xl:tw-col-span-7 tw-grid tw-grid-cols-1 sm:tw-grid-cols-3 tw-gap-8">
                <div>
                    <h4 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-100">Navegação</h4>
                    <div class="tw-mt-3 tw-space-y-2.5">
                        <a href="{{ route('searchpage') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Pesquisar</a>
                        <a href="{{ route('alltemaspage') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Temas</a>
                        <a href="{{ route('newsletterspage') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Atualizações</a>
                        <a href="{{ route('contact.index') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Contato</a>
                    </div>
                </div>

                <div>
                    <h4 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-100">Recursos</h4>
                    <div class="tw-mt-3 tw-space-y-2.5">
                        <a href="https://chrome.google.com/webstore/detail/teses-e-s%C3%BAmulas/biigfejcdpcpibfmffgmmndpjhnlcjfb?hl=pt-BR" target="_blank" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Extensão Chrome</a>
                    </div>
                </div>

                <div>
                    <h4 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-100">Conta</h4>
                    <div class="tw-mt-3 tw-space-y-2.5">
                        @auth
                            @if(in_array(auth()->user()->email, config('tes_constants.admins')))
                                <a href="{{ route('admin') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white"><b>Admin Dashboard</b></a>
                            @endif
                            <a href="{{ route('subscription.show') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Minha assinatura</a>
                            <a href="{{ route('subscription.portal') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Gerenciar pagamento</a>
                            <a href="{{ route('user-panel.dashboard') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Minha conta</a>
                        @else
                            <a href="{{ route('login') }}" class="tw-block tw-text-sm tw-text-slate-100 hover:tw-text-white">Entrar</a>
                        @endauth
                    </div>
                </div>
            </section>
        </div>

        <div class="tw-mt-10 tw-pt-5 tw-border-t tw-border-slate-700 tw-flex tw-flex-col sm:tw-flex-row tw-items-start sm:tw-items-center tw-justify-between tw-gap-2 tw-text-xs tw-text-slate-400">
            <p class="tw-m-0">© <span id="footer-year"></span> Teses & Súmulas. Todos os direitos reservados.</p>
            <p class="tw-m-0">Criado por <a href="https://maurolopes.com.br" target="_blank" class="tw-text-sky-300 hover:tw-text-sky-200 tw-underline tw-underline-offset-4 tw-decoration-sky-400/70">Mauro Lopes</a>.</p>
        </div>
    </div>
</footer>

<script>
document.getElementById('footer-year').textContent = new Date().getFullYear();
</script>
