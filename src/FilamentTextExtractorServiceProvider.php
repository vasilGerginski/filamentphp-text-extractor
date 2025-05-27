<?php

namespace VasilGerginski\FilamentTextExtractor;

use Illuminate\Support\ServiceProvider;
use VasilGerginski\FilamentTextExtractor\Commands\ExtractTranslatableTextCommand;
use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;

class FilamentTextExtractorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-text-extractor.php', 'filament-text-extractor'
        );

        $this->app->singleton(TextExtractionService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-text-extractor');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExtractTranslatableTextCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/filament-text-extractor.php' => config_path('filament-text-extractor.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        // Register Filament resources
        \Filament\Support\Facades\FilamentAsset::register([
            // Register any assets if needed
        ], 'local/filament-text-extractor');
    }
}