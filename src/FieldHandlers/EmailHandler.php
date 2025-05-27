<?php

namespace VasilGerginski\FilamentTextExtractor\FieldHandlers;

class EmailHandler extends AbstractFieldHandler
{
    public function shouldExtract(string $fieldName, mixed $value): bool
    {
        return false; // Don't extract emails
    }

    public function extractText(string $fieldName, mixed $value): array
    {
        return [];
    }
}