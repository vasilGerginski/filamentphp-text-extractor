<?php

namespace VasilGerginski\FilamentTextExtractor\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use VasilGerginski\FilamentTextExtractor\Models\ExtractedText;

class LangFileGenerator
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('filament-text-extractor', []);
    }

    /**
     * Generate language files for the given model type and locale
     */
    public function generateForModel(string $modelType, ?string $locale = null): void
    {
        if (!$this->shouldGenerate()) {
            return;
        }

        $locales = $locale ? [$locale] : $this->getConfiguredLocales();

        foreach ($locales as $loc) {
            $this->generateLangFile($modelType, $loc);
        }
    }

    /**
     * Generate a language file for a specific model and locale
     */
    protected function generateLangFile(string $modelType, string $locale): void
    {
        $texts = ExtractedText::where('model_type', $modelType)
            ->where('locale', $locale)
            ->get();

        if ($texts->isEmpty()) {
            return;
        }

        $fileName = $this->getFileName($modelType);
        $filePath = lang_path("{$locale}/{$fileName}");

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($filePath));

        $content = $this->generateFileContent($texts);
        
        File::put($filePath, $content);
    }

    /**
     * Generate the content for the language file
     */
    protected function generateFileContent($texts): string
    {
        $translations = [];

        foreach ($texts as $text) {
            $key = $this->generateKey($text);
            $value = $text->translated_value ?? $text->original_value;
            
            // Set nested array values using dot notation
            data_set($translations, $key, $value);
        }

        return $this->formatPhpArray($translations);
    }

    /**
     * Generate a translation key for the given text
     */
    protected function generateKey(ExtractedText $text): string
    {
        $modelName = class_basename($text->model_type);
        $modelKey = Str::snake($modelName);
        
        // Create a key like: post_1_title or post_1_content
        return "{$modelKey}_{$text->model_id}_{$text->field_name}";
    }

    /**
     * Get the filename for the given model type
     */
    protected function getFileName(string $modelType): string
    {
        $pattern = $this->config['lang_file_pattern'] ?? '{model_name}.php';
        $modelName = class_basename($modelType);
        $fileName = Str::snake(Str::plural($modelName));
        
        return str_replace('{model_name}', $fileName, $pattern);
    }

    /**
     * Check if language file generation is enabled
     */
    protected function shouldGenerate(): bool
    {
        return $this->config['auto_generate_lang_files'] ?? false;
    }

    /**
     * Get configured locales
     */
    protected function getConfiguredLocales(): array
    {
        return $this->config['locales'] ?? ['en'];
    }

    /**
     * Format array as PHP file content
     */
    protected function formatPhpArray(array $array): string
    {
        $export = var_export($array, true);
        
        // Improve formatting
        $export = preg_replace('/^([ ]*)(.*)/m', '$1$1$2', $export);
        $export = preg_replace('/array\s*\(/', '[', $export);
        $export = preg_replace('/\)$/', ']', $export);
        $export = preg_replace('/\)(\s*,)/', ']$1', $export);
        $export = preg_replace('/=>\s*\n\s*\[/', '=> [', $export);
        
        return "<?php\n\nreturn {$export};\n";
    }

    /**
     * Update or create translations for a single extracted text
     */
    public function updateTranslation(ExtractedText $text): void
    {
        if (!$this->shouldGenerate()) {
            return;
        }

        $this->generateLangFile($text->model_type, $text->locale);
    }

    /**
     * Remove translations when an extracted text is deleted
     */
    public function removeTranslation(ExtractedText $text): void
    {
        if (!$this->shouldGenerate()) {
            return;
        }

        $fileName = $this->getFileName($text->model_type);
        $filePath = lang_path("{$text->locale}/{$fileName}");

        if (!File::exists($filePath)) {
            return;
        }

        $translations = include $filePath;
        $key = $this->generateKey($text);
        
        data_forget($translations, $key);
        
        if (empty($translations)) {
            File::delete($filePath);
        } else {
            File::put($filePath, $this->formatPhpArray($translations));
        }
    }
}