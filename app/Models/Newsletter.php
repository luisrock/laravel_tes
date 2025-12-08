<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'sendy_id',
        'subject',
        'slug',
        'html_content',
        'plain_text',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the HTML content without the footer (starting from "Nota:").
     *
     * @return string
     */
    public function getWebContentAttribute()
    {
        $content = $this->html_content;
        
        // Regex to match "Nota:" (possibly inside tags) and everything after it
        // We look for "Nota" optionally surrounded by tags, followed by a colon
        // The 's' modifier allows dot to match newlines
        // We use [^>]* instead of .*? to prevent greedy matching across tags
        $pattern = '/(<p[^>]*>)?\s*(<strong[^>]*>)?\s*Nota\s*(<\/strong>)?\s*:.*$/is';
        
        $cleanContent = preg_replace($pattern, '', $content);

        return $cleanContent ?: $content; // Return original if regex fails or returns empty (though empty might be correct if Nota is at start)
    }
}
