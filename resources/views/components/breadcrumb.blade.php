{{-- 
    Componente de Breadcrumb com Schema.org
    
    Uso:
    <x-breadcrumb :items="[
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Index', 'url' => url('/index')],
        ['name' => 'Súmulas STF', 'url' => null] // último item sem URL
    ]" />
--}}

@props(['items'])

@if(isset($items) && count($items) > 1)
<!-- Breadcrumb -->
<style>
.breadcrumb-item + .breadcrumb-item::before {
    display: none !important;
    content: none !important;
}
</style>
<nav aria-label="breadcrumb" class="d-print-none">
    <ol class="breadcrumb breadcrumb-alt mb-0">
        @foreach($items as $index => $item)
            @if($index < count($items) - 1)
                <li class="breadcrumb-item">
                    @if($index === 0)
                        <a href="{{ $item['url'] ?? '#' }}" class="link-fx">{{ $item['name'] }}</a>
                    @else
                        <span style="color: #6c757d; margin: 0 0.5rem;">&gt;</span>
                        <a href="{{ $item['url'] ?? '#' }}" class="link-fx">{{ $item['name'] }}</a>
                    @endif
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">
                    <span style="color: #6c757d; margin: 0 0.5rem;">&gt;</span>
                    {{ $item['name'] }}
                </li>
            @endif
        @endforeach
    </ol>
</nav>
<!-- END Breadcrumb -->

<!-- Schema.org Structured Data -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    @foreach($items as $index => $item)
    {
      "@type": "ListItem",
      "position": {{ $index + 1 }},
      "name": "{{ $item['name'] }}",
      "item": "{{ $item['url'] ?? url()->current() }}"
    }{{ $index < count($items) - 1 ? ',' : '' }}
    @endforeach
  ]
}
</script>
<!-- END Schema.org -->

<style>
/* Breadcrumb responsivo e integrado ao tema */
.breadcrumb-alt {
    background-color: transparent;
    padding: 0.75rem 0;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.breadcrumb-alt .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    font-size: 1.1rem;
    padding: 0 0.5rem;
}

.breadcrumb-alt .breadcrumb-item a {
    color: #3b5998;
    text-decoration: none;
}

.breadcrumb-alt .breadcrumb-item a:hover {
    color: #1e3a70;
    text-decoration: underline;
}

.breadcrumb-alt .breadcrumb-item.active {
    color: #6c757d;
}

/* Responsividade para mobile */
@media (max-width: 576px) {
    .breadcrumb-alt {
        font-size: 0.75rem;
        padding: 0.5rem 0;
    }
    
    .breadcrumb-alt .breadcrumb-item + .breadcrumb-item::before {
        padding: 0 0.25rem;
    }
    
    /* Ocultar itens intermediários em telas pequenas se houver muitos */
    .breadcrumb-alt .breadcrumb-item:not(:first-child):not(:last-child):not(:nth-last-child(2)) {
        display: none;
    }
    
    /* Mostrar "..." para indicar itens ocultos */
    .breadcrumb-alt .breadcrumb-item:nth-child(2)::after {
        content: "...";
        margin-left: 0.5rem;
        color: #6c757d;
    }
}

/* Esconder na impressão */
@media print {
    .d-print-none {
        display: none !important;
    }
}
</style>
@endif

