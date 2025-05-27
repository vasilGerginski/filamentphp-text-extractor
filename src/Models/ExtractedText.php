<?php

namespace VasilGerginski\FilamentTextExtractor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtractedText extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'field_name',
        'text_key',
        'text_value',
        'original_value',
        'translated_value',
        'locale',
        'is_translated',
        'last_extracted_at',
    ];

    protected $casts = [
        'is_translated' => 'boolean',
        'last_extracted_at' => 'datetime',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}