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
        if (empty($value)) {
            return $value;
        }

        // Get the model name for the translation file (e.g., "blog_post")
        $modelName = class_basename($model);
        $fileName = Str::snake($modelName);
        
        // Use the actual text value as the translation key
        $translationKey = $value;
        
        // Try to get translation using the simple key approach: blog_post.{text_value}
        $translated = __("{$fileName}.{$translationKey}");
        
        // If translation found (not the key itself), return it
        if ($translated !== "{$fileName}.{$translationKey}") {
            return $translated;
        }
        
        // If no translation found, try to get from extracted_texts table
        try {
            $extractedText = \VasilGerginski\FilamentTextExtractor\Models\ExtractedText::where([
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'text_value' => $value,
                'locale' => app()->getLocale(),
            ])->first();
            
            if ($extractedText && !empty($extractedText->translated_value)) {
                return $extractedText->translated_value;
            }
        } catch (\Exception $e) {
            // If database query fails, just return original value
        }
        
        // Return original value as fallback
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