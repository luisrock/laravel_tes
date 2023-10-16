<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsletterPageController extends Controller
{

    public function index(Request $request)
    {
        $display_pdf = '';
        return view('front.newsletter_obrigado', compact('display_pdf'));
    } //end public function
}