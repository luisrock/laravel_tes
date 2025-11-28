<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Newsletter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportLegacyNewsletters extends Command
{
    protected $signature = 'newsletters:import-legacy {file}';
    protected $description = 'Import legacy newsletters from JSON file';

    public function handle()
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        // Handle phpMyAdmin JSON export structure
        // It's an array of objects. We need to find the one with type="table" and name="campaigns"
        $campaignsData = [];
        if (is_array($data)) {
            foreach ($data as $block) {
                if (isset($block['type']) && $block['type'] === 'table' && $block['name'] === 'campaigns') {
                    $campaignsData = $block['data'];
                    break;
                }
            }
        }
        
        // Fallback if structure is simple
        if (empty($campaignsData) && isset($data['campaigns'])) {
            $campaignsData = $data['campaigns'];
        } elseif (empty($campaignsData) && is_array($data) && isset($data[0]['id'])) {
             $campaignsData = $data;
        }

        $data = $campaignsData;

        if (empty($data)) {
            $this->error("Could not find campaigns data in JSON file.");
            return 1;
        }

        $targetIds = [1, 3, 4, 5, 6, 8, 9];
        $count = 0;

        foreach ($data as $item) {
            // Check if ID is in target list
            // Note: JSON keys might be strings, so we cast to int for comparison
            if (!in_array((int)$item['id'], $targetIds)) {
                continue;
            }

            $this->info("Processing ID {$item['id']}: {$item['title']}");

            // Check if already exists by sendy_id
            $newsletter = Newsletter::where('sendy_id', $item['id'])->first();

            // Create slug if new
            if (!$newsletter) {
                $slug = Str::slug($item['title']);
                $slugCount = 1;
                $originalSlug = $slug;
                while (Newsletter::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $slugCount++;
                }
            } else {
                $slug = $newsletter->slug;
            }

            // Map fields
            $sentAt = isset($item['sent']) && !empty($item['sent']) ? Carbon::createFromTimestamp($item['sent']) : Carbon::now();
            
            // Fix plain text: use JSON value if present, otherwise strip tags from HTML
            $plainText = !empty($item['plain_text']) ? $item['plain_text'] : strip_tags($item['html_text']);
            $plainText = html_entity_decode($plainText);

            $data = [
                'sendy_id' => $item['id'],
                'subject' => $item['title'],
                'slug' => $slug,
                'html_content' => $item['html_text'],
                'plain_text' => $plainText,
                'sent_at' => $sentAt,
            ];

            try {
                if ($newsletter) {
                    $newsletter->update($data);
                    $this->info(" - Updated successfully.");
                } else {
                    Newsletter::create($data);
                    $this->info(" - Imported successfully.");
                }
                $count++;
            } catch (\Exception $e) {
                $this->error(" - Error: " . $e->getMessage());
            }
        }

        $this->info("Done. Imported $count newsletters.");
        return 0;
    }
}
