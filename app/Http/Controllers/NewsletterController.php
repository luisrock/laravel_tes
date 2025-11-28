<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function show($slug)
    {
        $newsletter = Newsletter::where('slug', $slug)->firstOrFail();

        return view('front.newsletter', [
            'newsletter' => $newsletter,
            'display_pdf' => false,
            'description' => \Illuminate\Support\Str::limit(strip_tags($newsletter->plain_text ?? $newsletter->html_content), 155)
        ]);
    }
}
