<?php

namespace VasilGerginski\FilamentTextExtractor\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use VasilGerginski\FilamentTextExtractor\Models\ExtractedText;
use VasilGerginski\FilamentTextExtractor\Traits\ExtractsTranslatableText;
use VasilGerginski\FilamentTextExtractor\Services\LangFileGenerator;

class TextExtractionService
{
    protected array $extractedTexts = [];
    protected string $defaultLocale;
    protected array $fieldHandlers = [];
    protected LangFileGenerator $langFileGenerator;

    public function __construct()
    {
        $this->defaultLocale = config('filament-text-extractor.default_locale', 'en');
        $this->loadFieldHandlers();
        $this->langFileGenerator = new LangFileGenerator();
    }

    protected function loadFieldHandlers(): void
    {
        $handlers = config('filament-text-extractor.field_handlers', []);
        
        foreach ($handlers as $type => $handlerClass) {
            if (class_exists($handlerClass)) {
                $this->fieldHandlers[$type] = new $handlerClass;
            }
        }
    }

    public function extractFromModel(Model $model): void
    {
        if (!method_exists($model, 'shouldExtractText') || !$model->shouldExtractText()) {
            return;
        }

        $this->extractedTexts = [];

        // Extract from regular fields
        $this->extractFromFields($model);

        // Extract from JSON fields
        $this->extractFromJsonFields($model);

        // Extract from special fields
        $this->extractFromSpecialFields($model);

        // Extract from long text fields
        $this->extractFromLongTextFields($model);

        // Extract from rich text fields
        $this->extractFromRichTextFields($model);

        // Save to database and language files
        $this->saveExtractedTexts($model);
    }

    protected function extractFromFields(Model $model): void
    {
        $fields = method_exists($model, 'getTranslatableFields') 
            ? $model->getTranslatableFields() 
            : [];

        foreach ($fields as $field) {
            $value = $model->getAttribute($field);
            if (is_string($value) && !empty($value)) {
                $this->addText($value, $value, $field);
            }
        }
    }

    protected function extractFromJsonFields(Model $model): void
    {
        $jsonFields = method_exists($model, 'getTranslatableJsonFields') 
            ? $model->getTranslatableJsonFields() 
            : [];

        foreach ($jsonFields as $field) {
            $value = $model->getAttribute($field);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->extractFromArray($decoded, $field);
                }
            } elseif (is_array($value)) {
                $this->extractFromArray($value, $field);
            }
        }
    }

    protected function extractFromArray(array $data, string $parentField = null): void
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && !empty($value)) {
                if ($this->isTranslatableText($key, $value)) {
                    $this->addText($value, $value, $parentField);
                }
            } elseif (is_array($value)) {
                $this->extractFromArray($value, $parentField);
            }
        }
    }

    protected function extractFromSpecialFields(Model $model): void
    {
        $specialFields = method_exists($model, 'getSpecialFields') 
            ? $model->getSpecialFields() 
            : [];

        foreach ($specialFields as $fieldType => $fieldNames) {
            if (!isset($this->fieldHandlers[$fieldType])) {
                continue;
            }

            $handler = $this->fieldHandlers[$fieldType];
            
            foreach ((array) $fieldNames as $fieldName) {
                $value = $model->getAttribute($fieldName);
                
                if ($handler->shouldExtract($fieldName, $value)) {
                    $texts = $handler->extractText($fieldName, $value);
                    foreach ($texts as $key => $text) {
                        $this->addText($key, $text, $fieldName);
                    }
                }
            }
        }
    }

    protected function extractFromLongTextFields(Model $model): void
    {
        $longTextFields = method_exists($model, 'getLongTextFields') 
            ? $model->getLongTextFields() 
            : [];

        $handler = $this->fieldHandlers['long_text'] ?? null;
        if (!$handler) {
            return;
        }

        foreach ($longTextFields as $fieldName) {
            $value = $model->getAttribute($fieldName);
            
            if ($handler->shouldExtract($fieldName, $value)) {
                $texts = $handler->extractText($fieldName, $value);
                foreach ($texts as $key => $text) {
                    $this->addText($key, $text, $fieldName);
                }
            }
        }
    }

    protected function extractFromRichTextFields(Model $model): void
    {
        $richTextFields = method_exists($model, 'getRichTextFields') 
            ? $model->getRichTextFields() 
            : [];

        $handler = $this->fieldHandlers['rich_text'] ?? null;
        if (!$handler) {
            return;
        }

        foreach ($richTextFields as $fieldName) {
            $value = $model->getAttribute($fieldName);
            
            if ($handler->shouldExtract($fieldName, $value)) {
                $texts = $handler->extractText($fieldName, $value);
                foreach ($texts as $key => $text) {
                    $this->addText($key, $text, $fieldName);
                }
            }
        }
    }

    protected function isTranslatableText(string $key, string $value): bool
    {
        $config = config('filament-text-extractor');
        
        // Skip system fields
        if (in_array($key, $config['skip_system_keys'])) {
            return false;
        }

        // Skip URLs, emails, and other non-translatable content
        if (filter_var($value, FILTER_VALIDATE_URL) || 
            filter_var($value, FILTER_VALIDATE_EMAIL) ||
            preg_match('/^[a-z_-]+$/', $value) ||
            is_numeric($value)) {
            return false;
        }

        // Check length constraints
        $length = strlen($value);
        if ($length < $config['min_text_length'] || $length > $config['max_text_length']) {
            return false;
        }

        // Look for text-like fields
        foreach ($config['text_like_keys'] as $textKey) {
            if (Str::contains(strtolower($key), $textKey)) {
                return true;
            }
        }

        // If it contains spaces and letters, likely translatable
        return preg_match('/[a-zA-Z].*\s.*[a-zA-Z]/', $value);
    }

    protected function addText(string $key, string $value, string $fieldName = null): void
    {
        $key = trim($key);
        $value = trim($value);
        
        if (!empty($key) && !isset($this->extractedTexts[$key])) {
            $this->extractedTexts[$key] = [
                'value' => $value,
                'field' => $fieldName,
            ];
        }
    }

    protected function saveExtractedTexts(Model $model): void
    {
        if (empty($this->extractedTexts)) {
            return;
        }

        foreach ($this->extractedTexts as $key => $data) {
            ExtractedText::updateOrCreate([
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'text_key' => $key,
                'locale' => $this->defaultLocale,
            ], [
                'text_value' => $data['value'], // Kept for backward compatibility
                'original_value' => $data['value'],
                'field_name' => $data['field'],
                'last_extracted_at' => now(),
            ]);
        }

        // Generate language files using the new generator
        $this->langFileGenerator->generateForModel(get_class($model), $this->defaultLocale);
    }

    protected function saveToLanguageFile(Model $model): void
    {
        if (empty($this->extractedTexts)) {
            return;
        }

        $modelName = class_basename($model);
        $langPath = resource_path("lang/{$this->defaultLocale}");
        $filename = Str::snake($modelName) . '.php';
        $filepath = $langPath . '/' . $filename;

        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }

        $existing = [];
        if (File::exists($filepath)) {
            $existing = include $filepath;
            if (!is_array($existing)) {
                $existing = [];
            }
        }

        $newTexts = array_map(fn($data) => $data['value'], $this->extractedTexts);
        $merged = array_merge($existing, $newTexts);
        ksort($merged);

        $content = "<?php\n\nreturn [\n";
        foreach ($merged as $key => $value) {
            $escapedKey = str_replace("'", "\\'", $key);
            $escapedValue = str_replace("'", "\\'", $value);
            $content .= "    '{$escapedKey}' => '{$escapedValue}',\n";
        }
        $content .= "];\n";

        File::put($filepath, $content);
    }

    public function exportToLangFiles($records): void
    {
        $groupedTexts = $records->groupBy(['locale', 'model_type']);
        
        foreach ($groupedTexts as $locale => $modelGroups) {
            foreach ($modelGroups as $modelType => $texts) {
                $modelName = class_basename($modelType);
                $langPath = resource_path("lang/{$locale}");
                $filename = Str::snake($modelName) . '.php';
                $filepath = $langPath . '/' . $filename;

                if (!File::exists($langPath)) {
                    File::makeDirectory($langPath, 0755, true);
                }

                $translations = [];
                foreach ($texts as $text) {
                    $translations[$text->text_key] = $text->text_value;
                }

                ksort($translations);

                $content = "<?php\n\nreturn [\n";
                foreach ($translations as $key => $value) {
                    $escapedKey = str_replace("'", "\\'", $key);
                    $escapedValue = str_replace("'", "\\'", $value);
                    $content .= "    '{$escapedKey}' => '{$escapedValue}',\n";
                }
                $content .= "];\n";

                File::put($filepath, $content);
            }
        }
    }

    public function extractAllModels(): void
    {
        $models = $this->getModelsWithTrait();
        
        foreach ($models as $modelClass) {
            $records = $modelClass::all();
            
            foreach ($records as $record) {
                $this->extractFromModel($record);
            }
        }
    }

    protected function getModelsWithTrait(): array
    {
        $models = [];
        $path = app_path('Models');
        
        if (!File::exists($path)) {
            return $models;
        }

        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $class = 'App\\Models\\' . $file->getFilenameWithoutExtension();
            
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $traits = $reflection->getTraitNames();
                
                if (in_array(ExtractsTranslatableText::class, $traits)) {
                    $models[] = $class;
                }
            }
        }
        
        return $models;
    }
}