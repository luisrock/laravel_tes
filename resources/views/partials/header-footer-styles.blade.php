{{-- Estilos do Header e Footer - Partial DRY --}}
{{-- Uso: @include('partials.header-footer-styles') --}}

<style>
/* Layout para manter footer no bottom */
html, body {
    height: 100%;
}
#page-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
#main-container {
    flex: 1 0 auto;
    display: flex;
    flex-direction: column;
}
.page-content {
    flex: 1 0 auto;
}

.site-footer {
    margin-top: auto;
}

/* Header/footer em largura total mesmo com container boxed legado */
.site-header,
.site-footer {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
}

</style>
