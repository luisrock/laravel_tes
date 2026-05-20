<?php

namespace App\Enums;

enum NewsletterEventSource: string
{
    case Registration = 'registration';
    case GoogleOauth = 'google_oauth';
    case PanelToggle = 'panel_toggle';
    case NewslettersForm = 'newsletters_form';
    case Popup = 'popup';
    case Sync = 'sync';
}
