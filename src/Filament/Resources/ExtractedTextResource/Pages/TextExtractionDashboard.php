<?php

namespace VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource\Pages;

use Filament\Resources\Pages\Page;
use VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource;
use VasilGerginski\FilamentTextExtractor\Models\ExtractedText;

class TextExtractionDashboard extends Page
{
    protected static string $resource = ExtractedTextResource::class;
    
    protected static string $view = 'filament-text-extractor::dashboard';

    protected function getViewData(): array
    {
        return [
            'totalTexts' => ExtractedText::count(),
            'translatedCount' => ExtractedText::where('is_translated', true)->count(),
            'pendingCount' => ExtractedText::where('is_translated', false)->count(),
            'modelsCount' => ExtractedText::distinct('model_type')->count(),
        ];
    }
}