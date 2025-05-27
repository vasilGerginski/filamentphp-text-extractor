<?php

namespace VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource;
use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;

class ListExtractedTexts extends ListRecords
{
    protected static string $resource = ExtractedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('extract_all')
                ->label('Extract All Texts')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    app(TextExtractionService::class)->extractAllModels();
                    
                    Notification::make()
                        ->title('Success')
                        ->body('Text extraction completed!')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),
                
            Actions\Action::make('dashboard')
                ->label('Dashboard')
                ->icon('heroicon-o-chart-bar')
                ->url(fn () => static::getResource()::getUrl('dashboard')),
                
            Actions\CreateAction::make(),
        ];
    }
}