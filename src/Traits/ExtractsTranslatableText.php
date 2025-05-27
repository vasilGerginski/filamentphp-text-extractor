<?php

namespace VasilGerginski\FilamentTextExtractor\Traits;

use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;
use VasilGerginski\FilamentTextExtractor\Jobs\ExtractTextJob;

trait ExtractsTranslatableText
{
    protected static function bootExtractsTranslatableText()
    {
        static::saved(function ($model) {
            if (!config('filament-text-extractor.enabled') || 
                !config('filament-text-extractor.auto_extract_on_save')) {
                return;
            }

            if (config('filament-text-extractor.queue_extraction')) {
                dispatch(new ExtractTextJob($model))
                    ->onQueue(config('filament-text-extractor.queue_name'));
            } else {
                app(TextExtractionService::class)->extractFromModel($model);
            }
        });
    }

    public function getTranslatableFields(): array
    {
        return $this->translatableFields ?? [];
    }

    public function getTranslatableJsonFields(): array
    {
        return $this->translatableJsonFields ?? [];
    }

    public function getSpecialFields(): array
    {
        return $this->specialFields ?? [];
    }

    public function getLongTextFields(): array
    {
        return $this->longTextFields ?? [];
    }

    public function getRichTextFields(): array
    {
        return $this->richTextFields ?? [];
    }

    public function shouldExtractText(): bool
    {
        return $this->extractTranslatableText ?? true;
    }
}