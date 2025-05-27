<?php

namespace VasilGerginski\FilamentTextExtractor\FieldHandlers;

abstract class AbstractFieldHandler
{
    abstract public function shouldExtract(string $fieldName, mixed $value): bool;
    abstract public function extractText(string $fieldName, mixed $value): array;
}