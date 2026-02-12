<?php

namespace App\Console\Commands;

use App\Models\Newsletter;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PDO;
use PDOException;

class ImportNewsletters extends Command
{
    protected $signature = 'newsletters:import';

    protected $description = 'Import newsletters from Sendy RSS feed and scrape content';

    public function handle()
    {
        $this->info('Connecting to Sendy database...');

        // Sendy DB Configuration (from env or hardcoded as per user preference in this context)
        // Ideally should be in config/database.php, but for now we use the known credentials
        $sourceDb = [
            'host' => 'localhost',
            'user' => 'forge_sendyuser',
            'pass' => '69fh9hwww7evgggutx6u',
            'name' => 'forge_sendy',
        ];

        try {
            $pdo = new PDO("mysql:host={$sourceDb['host']};dbname={$sourceDb['name']};charset=utf8mb4", $sourceDb['user'], $sourceDb['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->error('Failed to connect to Sendy DB: '.$e->getMessage());

            return 1;
        }

        // Fetch campaigns that are sent, belong to app 1, and from "Newsletter T&S"
        // Order by ID DESC to get latest first
        $sql = "SELECT id, title, html_text, plain_text, sent 
                FROM campaigns 
                WHERE sent != '' 
                AND app = 1 
                AND from_name = 'Newsletter T&S' 
                ORDER BY id DESC";

        $stmt = $pdo->query($sql);

        $count = 0;
        $skipped = 0;

        while ($campaign = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sendyId = $campaign['id'];
            $title = $campaign['title'];

            $this->info("Processing: $title (ID: $sendyId)");

            if (Newsletter::where('sendy_id', $sendyId)->exists()) {
                $this->info(' - Already exists. Skipping.');
                $skipped++;

                continue;
            }

            // Create slug with smart truncation
            $tempTitle = $title;
            while (true) {
                $slug = Str::slug($tempTitle);
                if (strlen($slug) <= 200) {
                    break;
                }

                $lastSlash = strrpos($tempTitle, '/');
                if ($lastSlash === false) {
                    $slug = substr($slug, 0, 200);
                    break;
                }

                $tempTitle = substr($tempTitle, 0, $lastSlash);
                $tempTitle = trim($tempTitle);
            }

            // Ensure unique slug
            $slugCount = 1;
            $originalSlug = $slug;
            while (Newsletter::where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$slugCount++;
            }

            $htmlContent = $campaign['html_text'];
            $plainText = $campaign['plain_text'];
            if (empty($plainText)) {
                $plainText = strip_tags($htmlContent);
            }
            $plainText = html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $sentAt = $campaign['sent'];
            if (is_numeric($sentAt)) {
                $sentAt = Carbon::createFromTimestamp($sentAt);
            } else {
                $sentAt = Carbon::parse($sentAt);
            }

            try {
                Newsletter::create([
                    'sendy_id' => $sendyId,
                    'subject' => $title, // Full title, no truncation (DB is TEXT)
                    'slug' => $slug,
                    'html_content' => $htmlContent, // Raw content, cleaning happens in Accessor
                    'plain_text' => $plainText,
                    'sent_at' => $sentAt,
                ]);
                $this->info(' - Imported successfully.');
                $count++;
            } catch (Exception $e) {
                $this->error(" - Database Error for '$title': ".$e->getMessage());
            }
        }

        $this->info("Import completed. $count imported, $skipped skipped.");

        return 0;
    }
}
