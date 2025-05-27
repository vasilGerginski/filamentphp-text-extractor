<?php

namespace VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource;
use VasilGerginski\FilamentTextExtractor\Models\ExtractedText;

class EditExtractedText extends EditRecord
{
    protected static string $resource = ExtractedTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load all translations for this text
        $translations = ExtractedText::where('model_type', $data['model_type'])
            ->where('model_id', $data['model_id'])
            ->where('text_key', $data['text_key'])
            ->whereIn('locale', ['bg', 'es', 'fr', 'de'])
            ->get()
            ->keyBy('locale');

        $data['translations'] = [];
        
        foreach (['bg', 'es', 'fr', 'de'] as $locale) {
            if ($translations->has($locale)) {
                $translation = $translations->get($locale);
                $data['translations'][$locale] = [
                    'locale' => $locale,
                    'text_value' => $translation->text_value,
                    'is_translated' => $translation->is_translated,
                ];
            } else {
                $data['translations'][$locale] = [
                    'locale' => $locale,
                    'text_value' => '',
                    'is_translated' => false,
                ];
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Save translations separately
        if (isset($data['translations'])) {
            foreach ($data['translations'] as $locale => $translation) {
                if (!empty($translation['text_value'])) {
                    ExtractedText::updateOrCreate([
                        'model_type' => $data['model_type'],
                        'model_id' => $data['model_id'],
                        'text_key' => $data['text_key'],
                        'locale' => $locale,
                    ], [
                        'text_value' => $translation['text_value'],
                        'field_name' => $data['field_name'],
                        'is_translated' => $translation['is_translated'] ?? false,
                        'last_extracted_at' => now(),
                    ]);
                }
            }
            
            unset($data['translations']);
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Translations saved')
            ->body('All translations have been saved successfully.');
    }
}