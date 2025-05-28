<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use VasilGerginski\FilamentTextExtractor\Traits\ExtractsTranslatableText;
use VasilGerginski\FilamentTextExtractor\Casts\Translatable;
use VasilGerginski\FilamentTextExtractor\Casts\JsonTranslatable;

class BlogPost extends Model
{
    use ExtractsTranslatableText;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'meta_description', 
        'rich_content', 'author_email', 'tags'
    ];

    protected $casts = [
        'tags' => JsonTranslatable::class,
        'content' => JsonTranslatable::class,
        'title' => Translatable::class,
        'excerpt' => Translatable::class,
        'meta_description' => Translatable::class,
    ];

    // Short text fields (titles, labels, etc.)
    protected array $translatableFields = [
        'title',
        'excerpt', 
        'meta_description',
    ];

    // JSON fields with builder data
    protected array $translatableJsonFields = [
        'tags',
        'content',  // Builder content blocks
    ];

    // Long text content (blog posts, articles)
    protected array $longTextFields = [
        // Remove 'content' from here as it's a JSON field
    ];

    // Rich text content (HTML, formatted text)
    protected array $richTextFields = [
        'rich_content',  // HTML content from rich editors
    ];

    // Special fields with custom handlers
    protected array $specialFields = [
        'slug' => 'slug',
        'email' => 'author_email',
    ];
}