@php
    $integrationOn = \App\Models\SiteSetting::getAsBool('newsletter_integration_enabled', false);
    $popupOn = \App\Models\SiteSetting::getAsBool('newsletter_popup_enabled', false);
@endphp

@guest
    @if ($integrationOn && $popupOn)
        @include('partials.newsletter-popup-content')
    @endif
@endguest
