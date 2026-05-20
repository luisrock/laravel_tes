<?php

namespace App\Enums;

enum SendyStatus: string
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Unconfirmed = 'unconfirmed';
    case Bounced = 'bounced';
    case SoftBounced = 'soft_bounced';
    case Complained = 'complained';
    case NotFound = 'not_found';
}
