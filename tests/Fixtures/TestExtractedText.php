<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class ExtractedText extends Model
{
    protected $table = 'extracted_texts';

    protected $fillable = [
        'model_type',
        'model_id',
        'field_name',
        'field_type',
        'original_text',
        'translated_text',
        'context',
        'is_translated',
        'last_extracted_at',
    ];

    protected $casts = [
        'is_translated' => 'boolean',
        'last_extracted_at' => 'datetime',
        'context' => 'json',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}