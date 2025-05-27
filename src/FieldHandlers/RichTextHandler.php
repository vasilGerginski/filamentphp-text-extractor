<?php

namespace VasilGerginski\FilamentTextExtractor\FieldHandlers;

use DOMDocument;
use DOMXPath;

class RichTextHandler extends AbstractFieldHandler
{
    public function shouldExtract(string $fieldName, mixed $value): bool
    {
        return is_string($value) && 
               (strip_tags($value) !== $value || // Contains HTML
                preg_match('/\*\*.*?\*\*|\*.*?\*|__.*?__|_.*?_/', $value)); // Contains Markdown
    }

    public function extractText(string $fieldName, mixed $value): array
    {
        $config = config('filament-text-extractor.rich_text');
        $extracted = [];

        // Handle HTML content
        if (strip_tags($value) !== $value) {
            $extracted = array_merge($extracted, $this->extractFromHtml($value, $config));
        }

        // Handle Markdown content
        if (preg_match('/[*_#`\[\]]/', $value)) {
            $extracted = array_merge($extracted, $this->extractFromMarkdown($value, $config));
        }

        return $extracted;
    }

    protected function extractFromHtml(string $html, array $config): array
    {
        $extracted = [];
        
        // Create DOM document
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Extract headings separately if configured
        if ($config['extract_headings_separately']) {
            $headings = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
            foreach ($headings as $heading) {
                $text = trim($heading->textContent);
                if (!empty($text)) {
                    $extracted[$text] = $text;
                }
            }
        }

        // Extract alt text from images
        if ($config['extract_alt_text']) {
            $images = $xpath->query('//img[@alt]');
            foreach ($images as $img) {
                $altText = trim($img->getAttribute('alt'));
                if (!empty($altText)) {
                    $extracted[$altText] = $altText;
                }
            }
        }

        // Extract link text
        if ($config['extract_link_text']) {
            $links = $xpath->query('//a');
            foreach ($links as $link) {
                $linkText = trim($link->textContent);
                if (!empty($linkText) && !filter_var($linkText, FILTER_VALIDATE_URL)) {
                    $extracted[$linkText] = $linkText;
                }
            }
        }

        // Extract paragraph text
        $paragraphs = $xpath->query('//p | //div');
        foreach ($paragraphs as $paragraph) {
            $text = trim($paragraph->textContent);
            if (!empty($text) && strlen($text) > 10) {
                // Split long paragraphs into sentences
                $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text);
                foreach ($sentences as $sentence) {
                    $sentence = trim($sentence);
                    if (strlen($sentence) > 10) {
                        $extracted[$sentence] = $sentence;
                    }
                }
            }
        }

        // Extract text from lists
        $listItems = $xpath->query('//li');
        foreach ($listItems as $item) {
            $text = trim($item->textContent);
            if (!empty($text)) {
                $extracted[$text] = $text;
            }
        }

        return $extracted;
    }

    protected function extractFromMarkdown(string $markdown, array $config): array
    {
        $extracted = [];

        // Extract headings
        if ($config['extract_headings_separately']) {
            preg_match_all('/^#+\s+(.+)$/m', $markdown, $headings);
            foreach ($headings[1] as $heading) {
                $heading = trim($heading);
                if (!empty($heading)) {
                    $extracted[$heading] = $heading;
                }
            }
        }

        // Extract link text
        if ($config['extract_link_text']) {
            preg_match_all('/\[([^\]]+)\]\([^)]+\)/', $markdown, $links);
            foreach ($links[1] as $linkText) {
                $linkText = trim($linkText);
                if (!empty($linkText)) {
                    $extracted[$linkText] = $linkText;
                }
            }
        }

        // Extract image alt text
        if ($config['extract_alt_text']) {
            preg_match_all('/!\[([^\]]*)\]\([^)]+\)/', $markdown, $images);
            foreach ($images[1] as $altText) {
                $altText = trim($altText);
                if (!empty($altText)) {
                    $extracted[$altText] = $altText;
                }
            }
        }

        // Extract list items
        preg_match_all('/^\s*[-*+]\s+(.+)$/m', $markdown, $listItems);
        foreach ($listItems[1] as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $extracted[$item] = $item;
            }
        }

        // Extract regular paragraphs (non-special content)
        $lines = explode("\n", $markdown);
        $currentParagraph = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines, headings, list items, code blocks
            if (empty($line) || 
                preg_match('/^#+\s/', $line) || 
                preg_match('/^\s*[-*+]\s/', $line) ||
                preg_match('/^```/', $line) ||
                preg_match('/^\s*>/', $line)) {
                
                if (!empty($currentParagraph)) {
                    $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $currentParagraph);
                    foreach ($sentences as $sentence) {
                        $sentence = trim($sentence);
                        if (strlen($sentence) > 10) {
                            $extracted[$sentence] = $sentence;
                        }
                    }
                    $currentParagraph = '';
                }
                continue;
            }
            
            $currentParagraph .= ($currentParagraph ? ' ' : '') . $line;
        }
        
        // Handle remaining paragraph
        if (!empty($currentParagraph)) {
            $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $currentParagraph);
            foreach ($sentences as $sentence) {
                $sentence = trim($sentence);
                if (strlen($sentence) > 10) {
                    $extracted[$sentence] = $sentence;
                }
            }
        }

        return $extracted;
    }
}