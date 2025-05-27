<?php

namespace VasilGerginski\FilamentTextExtractor;

use Filament\Contracts\Plugin;
use Filament\Panel;
use VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource;

class FilamentTextExtractorPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-text-extractor';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ExtractedTextResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}