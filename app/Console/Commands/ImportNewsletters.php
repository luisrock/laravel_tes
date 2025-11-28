<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Newsletter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportNewsletters extends Command
{
    protected $signature = 'newsletters:import';
    protected $description = 'Import newsletters from Sendy RSS feed and scrape content';

    public function handle()
    {
        $feedUrl = 'https://newsletter.maurolopes.com.br/campaigns-rss?a=L9xHzUpwhmUIoYCP8NXF7DiR6YlwhU&i=1';
        $this->info("Fetching RSS feed from $feedUrl...");

        // Suppress libxml errors
        libxml_use_internal_errors(true);
        
        try {
            $content = file_get_contents($feedUrl);
            if (!$content) {
                $this->error("Failed to fetch RSS feed content.");
                return 1;
            }
            
            // Fix unescaped ampersands which break simplexml
            $content = str_replace('&', '&amp;', $content);
            
            $rss = simplexml_load_string($content);
            if (!$rss) {
                $this->error("Failed to parse RSS feed.");
                foreach(libxml_get_errors() as $error) {
                    $this->error($error->message);
                }
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            return 1;
        }

        $count = 0;
        foreach ($rss->channel->item as $item) {
            $link = (string) $item->link;
            $title = (string) $item->title;
            $pubDate = (string) $item->pubDate;
            $sendyId = md5($link); // Use link hash as ID since we don't have real ID

            $this->info("Processing: $title");

            if (Newsletter::where('sendy_id', $sendyId)->exists()) {
                $this->info(" - Already exists. Skipping.");
                continue;
            }

            // Scrape content
            $htmlContent = $this->scrapeContent($link);
            if (!$htmlContent) {
                $this->error(" - Failed to scrape content from $link");
                continue;
            }

            // Create slug
            $slug = Str::slug($title);
            // Ensure unique slug
            $slugCount = 1;
            $originalSlug = $slug;
            while (Newsletter::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $slugCount++;
            }

            try {
                Newsletter::create([
                    'sendy_id' => $sendyId,
                    'subject' => Str::limit($title, 250),
                    'slug' => $slug,
                    'html_content' => mb_convert_encoding($htmlContent, 'UTF-8', 'UTF-8'),
                    'plain_text' => html_entity_decode(mb_convert_encoding(strip_tags($htmlContent), 'UTF-8', 'UTF-8')),
                    'sent_at' => Carbon::parse($pubDate),
                ]);
                $this->info(" - Imported successfully.");
                $count++;
            } catch (\Exception $e) {
                $this->error(" - Database Error for '$title': " . $e->getMessage());
            }
        }

        $this->info("Import completed. $count newsletters imported.");
        return 0;
    }

    private function scrapeContent($url)
    {
        try {
            $html = file_get_contents($url);
            if (!$html) return null;

            // Extract body content only
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
                // Clean up some common email clutter if needed
                $content = $matches[1];
                
                // Fix relative links if any (though email usually has absolute)
                
                return $content;
            }
            
            return $html; // Fallback to full HTML
        } catch (\Exception $e) {
            return null;
        }
    }
}
