<?php

namespace VasilGerginski\FilamentTextExtractor\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Translatable implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value) {
            return $value;
        }

        // Generate translation key
        $modelName = class_basename($model);
        $modelKey = Str::snake($modelName);
        $translationKey = "{$modelKey}_{$model->id}_{$key}";
        
        // Get the lang file name
        $fileName = Str::snake(Str::plural($modelName));
        
        // Try to get translation
        $translated = __("{$fileName}.{$translationKey}");
        
        // If translation exists (not the same as the key), return it
        if ($translated !== "{$fileName}.{$translationKey}") {
            return $translated;
        }
        
        // Otherwise, check if we have a translated value in the database
        $extractedText = \VasilGerginski\FilamentTextExtractor\Models\ExtractedText::where([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'field_name' => $key,
            'locale' => app()->getLocale(),
        ])->first();
        
        if ($extractedText && $extractedText->translated_value) {
            return $extractedText->translated_value;
        }
        
        // Fall back to original value
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // When setting, we just store the original value
        // The translation happens when retrieving
        return $value;
    }
}