<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use App\Models\SiteSetting;
use App\Services\Sendy\SendyService;
use Illuminate\View\View;

class CampaignsPageController extends Controller
{
    public function index(SendyService $sendy): View
    {
        $campaigns = Newsletter::orderBy('sent_at', 'desc')->paginate(10);

        $user = auth()->user();
        $integrationEnabled = SiteSetting::getAsBool('newsletter_integration_enabled', false);
        $isAlreadySubscribed = false;

        if ($integrationEnabled && $user) {
            $isAlreadySubscribed = $sendy->syncUserSubscriptionState($user);
        }

        return view('front.newsletters', [
            'campaigns' => $campaigns,
            'display_pdf' => false,
            'integrationEnabled' => $integrationEnabled,
            'isAlreadySubscribed' => $isAlreadySubscribed,
        ]);
    }
}
