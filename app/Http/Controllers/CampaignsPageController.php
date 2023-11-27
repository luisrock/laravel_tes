<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleXMLElement;
use DateTime;
use DateTimeZone;

class CampaignsPageController extends Controller
{
    public function index()
    {
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        $feedUrl = 'https://newsletter.maurolopes.com.br/campaigns-rss?a=L9xHzUpwhmUIoYCP8NXF7DiR6YlwhU&i=1';

        try {
            $feedContent = file_get_contents($feedUrl);
            if ($feedContent === false) {
                throw new \Exception('Unable to retrieve RSS feed content.');
            }

            // Replace unescaped ampersands with their HTML entity equivalent
            $feedContent = str_replace('&', '&amp;', $feedContent);

            $rss = simplexml_load_string($feedContent, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
            if ($rss === false) {
                throw new \Exception('Failed to parse RSS feed.');
            }


            $campaigns = [];
            foreach ($rss->channel->item as $item) {
                // Parse the pubDate using DateTime and set the timezone to UTC
                $date = new DateTime((string) $item->pubDate, new DateTimeZone('UTC'));
                // Convert the timezone to UTC-3
                $date->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                // Format the date in Portuguese (Brazil)
                $formattedDate = strftime('%A, %d de %B de %Y, %H:%M', $date->getTimestamp());


                $campaigns[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'description' => (string) $item->description,
                    'pubDate' => $formattedDate,
                ];
            }

            return view('front.newsletters', ['campaigns' => $campaigns, 'display_pdf' => false]);
        } catch (\Exception $e) {
            // Log the error message
            \Log::error($e->getMessage());

            // Return a custom view with the error message or abort with an error message
            abort(500, $e->getMessage());
        }
    }
}