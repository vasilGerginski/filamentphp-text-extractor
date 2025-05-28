<?php

namespace VasilGerginski\FilamentTextExtractor\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JsonTranslatable implements CastsAttributes
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
        // Decode JSON first
        $decoded = json_decode($value, true);
        
        if (!is_array($decoded)) {
            return $decoded;
        }
        
        // Recursively translate text values in JSON structure
        return $this->translateJsonValues($decoded, $model);
    }

    /**
     * Recursively translate text values in JSON structure
     */
    protected function translateJsonValues(array $data, Model $model): array
    {
        $translated = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursively handle nested arrays
                $translated[$key] = $this->translateJsonValues($value, $model);
            } elseif (is_string($value) && !empty($value) && $this->shouldTranslate($key, $value)) {
                // Translate string values for UI component fields
                $translated[$key] = $this->translateText($value, $model);
            } else {
                // Keep other values as-is
                $translated[$key] = $value;
            }
        }
        
        return $translated;
    }

    /**
     * Check if a field should be translated based on key name
     */
    protected function shouldTranslate(string $key, string $value): bool
    {
        // Only translate fields that are typically used in UI components
        $translatableKeys = [
            'title', 'subtitle', 'heading', 'subheading',
            'text', 'content', 'description', 'label',
            'button_text', 'link_text', 'caption',
            'message', 'placeholder', 'alt_text'
        ];
        
        $key = strtolower($key);
        
        // Check if the key matches translatable patterns
        foreach ($translatableKeys as $translatableKey) {
            if (str_contains($key, $translatableKey)) {
                return true;
            }
        }
        
        // Also check if the value looks like translatable text (not URL, email, etc.)
        return $this->looksLikeTranslatableText($value);
    }

    /**
     * Check if a value looks like translatable text
     */
    protected function looksLikeTranslatableText(string $value): bool
    {
        // Skip if it looks like URL, email, or technical data
        if (filter_var($value, FILTER_VALIDATE_URL) || 
            filter_var($value, FILTER_VALIDATE_EMAIL) ||
            preg_match('/^[a-z_]+$/', $value) || // snake_case identifiers
            preg_match('/^\d+$/', $value) || // numeric IDs
            strlen($value) < 3) { // too short
            return false;
        }
        
        return true;
    }

    /**
     * Translate a text value
     */
    protected function translateText(string $value, Model $model): string
    {
        // Get the model name for the translation file (e.g., "blog_post")
        $modelName = class_basename($model);
        $fileName = Str::snake($modelName);
        
        // Try to get translation using the simple key approach: blog_post.{text_value}
        $translated = __("{$fileName}.{$value}");
        
        // If translation found (not the key itself), return it
        if ($translated !== "{$fileName}.{$value}") {
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
        // Store as JSON
        return json_encode($value);
    }
}