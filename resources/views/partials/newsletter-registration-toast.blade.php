@php
    $toast = session()->pull('newsletter.registration_toast');
@endphp

@if (in_array($toast, ['subscribed', 'invite'], true))
    <div id="newsletter-registration-toast"
         class="tw-fixed tw-bottom-4 tw-right-4 tw-z-50 tw-max-w-sm tw-rounded-lg tw-shadow-lg tw-p-4 {{ $toast === 'subscribed' ? 'tw-bg-emerald-600 tw-text-white' : 'tw-bg-slate-800 tw-text-white' }}"
         role="status">
        <div class="tw-flex tw-items-start tw-gap-2">
            <p class="tw-text-sm">
                @if ($toast === 'subscribed')
                    Você foi inscrito na nossa newsletter semanal. Para sair, vá em
                    <a href="{{ route('user-panel.profile') }}" class="tw-underline tw-font-medium">Perfil</a>.
                @else
                    Inscreva-se também para receber a newsletter de atualização semanal acessando
                    <a href="{{ route('user-panel.profile') }}" class="tw-underline tw-font-medium">Minha Conta &gt; Perfil</a>.
                @endif
            </p>
            <button type="button"
                    class="tw-shrink-0 tw-text-white/80 hover:tw-text-white"
                    aria-label="Fechar"
                    onclick="document.getElementById('newsletter-registration-toast')?.remove()">&times;</button>
        </div>
    </div>
    <script>
        setTimeout(function () {
            document.getElementById('newsletter-registration-toast')?.remove();
        }, 8000);
    </script>
@endif
