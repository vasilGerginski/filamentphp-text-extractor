<?php

namespace VasilGerginski\FilamentTextExtractor\FieldHandlers;

use Illuminate\Support\Str;

class LongTextHandler extends AbstractFieldHandler
{
    public function shouldExtract(string $fieldName, mixed $value): bool
    {
        return is_string($value) && 
               strlen($value) > config('filament-text-extractor.long_text.threshold', 500);
    }

    public function extractText(string $fieldName, mixed $value): array
    {
        $config = config('filament-text-extractor.long_text');
        $extracted = [];

        // Extract sentences if enabled
        if ($config['extract_sentences']) {
            $sentences = $this->extractSentences($value);
            foreach ($sentences as $sentence) {
                $sentence = trim($sentence);
                if (strlen($sentence) >= $config['min_sentence_length']) {
                    $extracted[$sentence] = $sentence;
                }
            }
        }

        // Extract paragraphs if enabled
        if ($config['extract_paragraphs']) {
            $paragraphs = $this->extractParagraphs($value);
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if (strlen($paragraph) > $config['chunk_size']) {
                    // Split long paragraphs into chunks
                    $chunks = $this->splitIntoChunks($paragraph, $config['chunk_size']);
                    foreach ($chunks as $chunk) {
                        $extracted[$chunk] = $chunk;
                    }
                } else {
                    $extracted[$paragraph] = $paragraph;
                }
            }
        }

        // Extract headings if enabled
        if ($config['extract_headings']) {
            $headings = $this->extractHeadings($value);
            foreach ($headings as $heading) {
                $extracted[$heading] = $heading;
            }
        }

        return $extracted;
    }

    protected function extractSentences(string $text): array
    {
        // Remove code blocks if configured
        if (config('filament-text-extractor.long_text.skip_code_blocks')) {
            $text = preg_replace('/```.*?```/s', '', $text);
            $text = preg_replace('/`[^`]+`/', '', $text);
        }

        // Split by sentence endings, considering common abbreviations
        $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text);
        
        return array_filter($sentences, function($sentence) {
            return !empty(trim($sentence)) && 
                   !preg_match('/^\s*[-*+]\s/', $sentence) && // Skip list items
                   !preg_match('/^\s*\d+\.\s/', $sentence);   // Skip numbered lists
        });
    }

    protected function extractParagraphs(string $text): array
    {
        // Split by double line breaks (paragraph separation)
        $paragraphs = preg_split('/\n\s*\n/', $text);
        
        return array_filter($paragraphs, function($paragraph) {
            $paragraph = trim($paragraph);
            return !empty($paragraph) && 
                   strlen($paragraph) > 20 && // Minimum paragraph length
                   !preg_match('/^#+\s/', $paragraph) && // Skip markdown headings
                   !preg_match('/^<h[1-6]/', $paragraph); // Skip HTML headings
        });
    }

    protected function extractHeadings(string $text): array
    {
        $headings = [];

        // Extract Markdown headings
        preg_match_all('/^#+\s+(.+)$/m', $text, $markdownHeadings);
        if (!empty($markdownHeadings[1])) {
            $headings = array_merge($headings, $markdownHeadings[1]);
        }

        // Extract HTML headings
        preg_match_all('/<h[1-6][^>]*>([^<]+)<\/h[1-6]>/i', $text, $htmlHeadings);
        if (!empty($htmlHeadings[1])) {
            $headings = array_merge($headings, $htmlHeadings[1]);
        }

        return array_map('trim', $headings);
    }

    protected function splitIntoChunks(string $text, int $chunkSize): array
    {
        $chunks = [];
        $sentences = $this->extractSentences($text);
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if (strlen($currentChunk . ' ' . $sentence) <= $chunkSize) {
                $currentChunk .= ($currentChunk ? ' ' : '') . $sentence;
            } else {
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                }
                $currentChunk = $sentence;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }
}