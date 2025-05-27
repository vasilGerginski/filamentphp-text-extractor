<?php

namespace VasilGerginski\FilamentTextExtractor\FieldHandlers;

use Illuminate\Support\Str;

class SlugHandler extends AbstractFieldHandler
{
    public function shouldExtract(string $fieldName, mixed $value): bool
    {
        return is_string($value) && 
               (Str::contains($fieldName, 'slug') || 
                preg_match('/^[a-z0-9-]+$/', $value));
    }

    public function extractText(string $fieldName, mixed $value): array
    {
        // Convert slug back to readable text
        $text = str_replace(['-', '_'], ' ', $value);
        $text = Str::title($text);
        
        return [$text => $text];
    }
}