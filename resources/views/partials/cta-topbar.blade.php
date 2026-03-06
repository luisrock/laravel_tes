@guest
<div x-data="{ 
        show: false,
        init() {
            const dismissedAt = localStorage.getItem('tes_topbar_dismissed');
            if (!dismissedAt || Date.now() > parseInt(dismissedAt)) {
                this.show = true;
            }
        },
        dismiss() {
            this.show = false;
            // Oculta por 24 horas (24 * 60 * 60 * 1000 milissegundos)
            localStorage.setItem('tes_topbar_dismissed', Date.now() + 86400000);
        }
    }" 
    x-show="show" 
    x-transition 
    x-cloak 
    class="tw-bg-brick-700 tw-text-white tw-py-3 tw-px-4 tw-relative tw-z-50 tw-shadow-lg tw-border-b tw-border-brick-800">
    <div class="tw-max-w-7xl tw-mx-auto tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-4">
        <div class="tw-flex tw-items-center tw-gap-3">
            <i class="fa fa-star tw-text-yellow-300 tw-text-lg"></i>
            <span class="tw-text-base sm:tw-text-lg tw-font-semibold tw-tracking-wide">
                Melhore sua experiência de pesquisa. Leia sem interrupções e sem anúncios, registrando-se gratuitamente.
            </span>
        </div>
        
        <div class="tw-flex tw-items-center tw-gap-4 tw-w-full sm:tw-w-auto tw-justify-between sm:tw-justify-end">
            <a href="{{ route('register') }}" class="tw-bg-white tw-text-brick-700 hover:tw-bg-brick-50 tw-px-5 tw-py-2 tw-rounded-md tw-text-sm sm:tw-text-base tw-font-bold tw-transition-colors tw-whitespace-nowrap tw-shadow-sm">
                Criar Conta Grátis
            </a>
            
            <button @click="dismiss()" type="button" class="tw-text-brick-100 hover:tw-text-white tw-transition-colors" aria-label="Fechar">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endguest
