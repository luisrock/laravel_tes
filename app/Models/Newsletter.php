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

    /**
     * Get clean preview text for listing pages.
     * Removes CSS, JavaScript, and HTML tags to provide a clean text preview.
     *
     * @return string
     */
    public function getPreviewTextAttribute(): string
    {
        $content = $this->plain_text ?? $this->html_content;
        
        // Remove <style> tags and their content
        $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);
        
        // Remove <script> tags and their content
        $content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);
        
        // Remove CSS @keyframes and similar rules that might appear as text
        $content = preg_replace('/@keyframes[^{]+\{[^}]+\}[^}]*\}/is', '', $content);
        $content = preg_replace('/@[a-z-]+[^{]*\{[^}]*\}/is', '', $content);
        
        // Remove any remaining inline CSS patterns
        $content = preg_replace('/\{[^}]*:[^}]*\}/s', '', $content);
        
        // Strip remaining HTML tags
        $content = strip_tags($content);
        
        // Decode HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Clean up whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        return $content;
    }
}
