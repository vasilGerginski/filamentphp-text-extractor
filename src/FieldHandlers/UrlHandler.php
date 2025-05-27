<?php

namespace VasilGerginski\FilamentTextExtractor\FieldHandlers;

class UrlHandler extends AbstractFieldHandler
{
    public function shouldExtract(string $fieldName, mixed $value): bool
    {
        return false; // Don't extract URLs
    }

    public function extractText(string $fieldName, mixed $value): array
    {
        return [];
    }
}