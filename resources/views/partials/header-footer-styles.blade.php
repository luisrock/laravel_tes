{{-- Estilos do Header e Footer - Partial DRY --}}
{{-- Uso: @include('partials.header-footer-styles') --}}

<style>
/* ==========================================================================
   HEADER STYLES
   ========================================================================== */

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

/* Esconde o footer antigo do base.blade.php */
#page-footer {
    display: none !important;
}

/* Esconde o Hero antigo (h1 redundante) */
.bg-body-light:has(.flex-sm-fill.h3) {
    display: none !important;
}

/* Badge da extensão Chrome no header */
.chrome-badge {
    background: #198754;
    color: #fff !important;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none !important;
    margin-left: 8px;
    transition: background 0.15s ease;
}
.chrome-badge:hover {
    background: #146c43 !important;
    color: #fff !important;
}

/* Força largura total mesmo dentro de container boxed */
.site-header {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.header-accent {
    height: 3px;
    background: linear-gradient(90deg, #0d6efd, #6610f2);
}
.header-inner {
    width: 100%;
    padding: 12px 50px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.site-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a2e;
    text-decoration: none;
}
.site-logo:hover {
    text-decoration: none;
    color: #1a1a2e;
}
.logo-icon {
    width: 32px;
    height: 32px;
    background: #0d6efd;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: bold;
    font-size: 0.9rem;
}
.site-nav {
    display: flex;
    align-items: center;
    gap: 8px;
}
.site-nav a {
    color: #495057;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.15s ease;
}
.site-nav a:hover {
    color: #0d6efd;
    background: #f8f9fa;
}
.nav-divider {
    width: 1px;
    height: 24px;
    background: #dee2e6;
    margin: 0 8px;
}
.site-nav .btn-login {
    color: #0d6efd;
}
.site-nav .btn-subscribe {
    background: #0d6efd;
    color: #fff !important;
}
.site-nav .btn-subscribe:hover {
    background: #0b5ed7;
    color: #fff !important;
}

/* Mobile Menu */
.mobile-toggle {
    display: none;
    background: none;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 1.25rem;
    cursor: pointer;
    color: #333;
    padding: 6px 10px;
}
@media (max-width: 768px) {
    .mobile-toggle { display: block; }
    .site-nav {
        display: none;
        position: fixed;
        top: 58px;
        left: 0;
        right: 0;
        background: #fff;
        flex-direction: column;
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        z-index: 1000;
    }
    .site-nav.active { display: flex; }
    .site-nav a { padding: 12px 15px; width: 100%; }
    .nav-divider { display: none; }
}

/* ==========================================================================
   FOOTER STYLES
   ========================================================================== */

.site-footer {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    background: #1a1a2e;
    color: #b8b8c7;
    margin-top: 40px;
}
.footer-inner {
    padding: 40px 50px 20px;
}
.footer-main {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 40px;
    margin-bottom: 30px;
}
.footer-brand {
    max-width: 300px;
}
.footer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    margin-bottom: 12px;
}
.footer-logo:hover {
    text-decoration: none;
    color: #fff;
}
.footer-logo .logo-icon {
    width: 28px;
    height: 28px;
    font-size: 0.8rem;
}
.footer-tagline {
    font-size: 0.85rem;
    line-height: 1.5;
    margin: 0;
}
.footer-links {
    display: flex;
    gap: 60px;
    flex-wrap: wrap;
}
.footer-col h4 {
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0 0 15px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.footer-col a {
    display: block;
    color: #b8b8c7;
    text-decoration: none;
    font-size: 0.9rem;
    padding: 5px 0;
    transition: color 0.15s ease;
}
.footer-col a:hover {
    color: #fff;
}
.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    font-size: 0.85rem;
}
.footer-credits a {
    color: #0d6efd;
    text-decoration: none;
}
.footer-credits a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .footer-main {
        flex-direction: column;
        gap: 30px;
    }
    .footer-links {
        gap: 30px;
    }
    .footer-bottom {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}

</style>
