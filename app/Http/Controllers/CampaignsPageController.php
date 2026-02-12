<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;

class CampaignsPageController extends Controller
{
    public function index()
    {
        $campaigns = Newsletter::orderBy('sent_at', 'desc')->paginate(10);

        return view('front.newsletters', ['campaigns' => $campaigns, 'display_pdf' => false]);
    }
}
