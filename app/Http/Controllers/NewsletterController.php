<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use DOMDocument;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function show($slug)
    {
        $newsletter = Newsletter::where('slug', $slug)->firstOrFail();

        return view('front.newsletter', [
            'newsletter' => $newsletter,
            'newsletterContent' => $this->sanitizeNewsletterContent($newsletter->web_content ?? $newsletter->html_content ?? ''),
            'display_pdf' => false,
            'description' => Str::limit(strip_tags($newsletter->plain_text ?? $newsletter->html_content), 155),
        ]);
    }

    private function sanitizeNewsletterContent(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return $doc->saveHTML();
        }

        $clean = '';
        foreach ($body->childNodes as $child) {
            $clean .= $doc->saveHTML($child);
        }

        return $clean;
    }
}
