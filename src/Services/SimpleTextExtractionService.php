<?php

namespace VasilGerginski\FilamentTextExtractor\Services;

use Illuminate\Database\Eloquent\Model;
use VasilGerginski\FilamentTextExtractor\Models\ExtractedText;

class TextExtractionService
{
    public function extractFromModel(Model $model): void
    {
        // Extract simple text fields
        if (method_exists($model, 'getTranslatableFields')) {
            foreach ($model->getTranslatableFields() as $field) {
                $value = $model->getAttribute($field);
                if (!empty($value) && is_string($value)) {
                    $this->saveExtractedText($model, $field, $value, 'text');
                }
            }
        }

        // Extract JSON array fields
        if (method_exists($model, 'getTranslatableJsonFields')) {
            foreach ($model->getTranslatableJsonFields() as $field) {
                $value = $model->getAttribute($field);
                
                if (is_array($value)) {
                    // Handle simple arrays (like tags)
                    if ($this->isSimpleArray($value)) {
                        foreach ($value as $index => $item) {
                            if (!empty($item) && is_string($item)) {
                                $this->saveExtractedText($model, $field, $item, 'json', ['index' => $index]);
                            }
                        }
                    } 
                    // Handle builder content
                    elseif ($this->isBuilderContent($value)) {
                        $this->extractBuilderContent($model, $field, $value);
                    }
                }
            }
        }

        // Extract rich text fields
        if (method_exists($model, 'getRichTextFields')) {
            foreach ($model->getRichTextFields() as $field) {
                $value = $model->getAttribute($field);
                if (!empty($value) && is_string($value)) {
                    $this->saveExtractedText($model, $field, $value, 'richtext');
                }
            }
        }

        // Extract special fields
        if (method_exists($model, 'getSpecialFields')) {
            foreach ($model->getSpecialFields() as $type => $field) {
                $value = $model->getAttribute($field);
                if (!empty($value) && is_string($value)) {
                    $this->saveExtractedText($model, $field, $value, $type);
                }
            }
        }
    }

    protected function isSimpleArray(array $array): bool
    {
        foreach ($array as $item) {
            if (!is_scalar($item)) {
                return false;
            }
        }
        return true;
    }

    protected function isBuilderContent(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        
        $firstItem = reset($array);
        return is_array($firstItem) && isset($firstItem['type']) && isset($firstItem['data']);
    }

    protected function extractBuilderContent(Model $model, string $field, array $content): void
    {
        foreach ($content as $index => $block) {
            if (!isset($block['type']) || !isset($block['data'])) {
                continue;
            }

            $blockType = $block['type'];
            $data = $block['data'];

            // Extract text from different block types
            switch ($blockType) {
                case 'heading':
                case 'paragraph':
                    if (isset($data['content']) && !empty($data['content'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['content'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index]
                        );
                    }
                    break;

                case 'quote':
                    if (isset($data['content']) && !empty($data['content'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['content'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index]
                        );
                    }
                    if (isset($data['author']) && !empty($data['author'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['author'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index, 'field' => 'author']
                        );
                    }
                    break;

                case 'callout':
                    if (isset($data['title']) && !empty($data['title'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['title'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index, 'field' => 'title']
                        );
                    }
                    if (isset($data['content']) && !empty($data['content'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['content'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index]
                        );
                    }
                    break;

                case 'image':
                    if (isset($data['alt']) && !empty($data['alt'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['alt'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index, 'field' => 'alt']
                        );
                    }
                    if (isset($data['caption']) && !empty($data['caption'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['caption'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index, 'field' => 'caption']
                        );
                    }
                    break;

                case 'section':
                    // Handle nested sections
                    if (isset($data['title']) && !empty($data['title'])) {
                        $this->saveExtractedText(
                            $model, 
                            $field, 
                            $data['title'], 
                            'builder',
                            ['block_type' => $blockType, 'block_index' => $index]
                        );
                    }
                    if (isset($data['blocks']) && is_array($data['blocks'])) {
                        foreach ($data['blocks'] as $nestedBlock) {
                            if (isset($nestedBlock['data']['content']) && !empty($nestedBlock['data']['content'])) {
                                $this->saveExtractedText(
                                    $model, 
                                    $field, 
                                    $nestedBlock['data']['content'], 
                                    'builder',
                                    ['block_type' => $blockType, 'block_index' => $index]
                                );
                            }
                        }
                    }
                    break;
            }
        }
    }

    protected function saveExtractedText(Model $model, string $fieldName, string $text, string $fieldType, array $context = null): void
    {
        ExtractedText::updateOrCreate([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'field_name' => $fieldName,
            'original_text' => $text,
            'field_type' => $fieldType,
        ], [
            'context' => $context ? json_encode($context) : null,
            'last_extracted_at' => now(),
        ]);
    }
}