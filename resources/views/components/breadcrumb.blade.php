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
<nav aria-label="breadcrumb" class="tw-text-sm tw-text-slate-600 print:tw-hidden">
    <ol class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-m-0 tw-p-0 tw-list-none">
        @foreach($items as $index => $item)
            @if($index > 0)
                <li class="tw-text-slate-400" aria-hidden="true">&gt;</li>
            @endif
            @if($index < count($items) - 1)
                <li>
                    <a href="{{ $item['url'] ?? '#' }}" class="tw-text-brand-700 hover:tw-text-brand-800 hover:tw-underline">
                        {{ $item['name'] }}
                    </a>
                </li>
            @else
                <li aria-current="page" class="tw-text-slate-700 tw-font-medium">
                    {{ $item['name'] }}
                </li>
            @endif
        @endforeach
    </ol>
</nav>

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
@endif

