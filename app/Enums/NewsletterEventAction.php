<?php

namespace App\Enums;

enum NewsletterEventAction: string
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case AlreadySubscribed = 'already_subscribed';
    case Failed = 'failed';
    case Impression = 'impression';
    case Dismissed = 'dismissed';
}
