@if (app(\App\Services\Newsletter\NewsletterPopupVisibility::class)->shouldRender())
    @include('partials.newsletter-popup-content')
@endif
