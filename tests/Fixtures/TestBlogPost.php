<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use VasilGerginski\FilamentTextExtractor\Traits\ExtractsTranslatableText;

class TestBlogPost extends Model
{
    use ExtractsTranslatableText;

    protected $table = 'blog_posts';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'meta_description', 
        'rich_content', 'author_email', 'tags'
    ];

    protected $casts = [
        'tags' => 'array',
        'content' => 'array',
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
        'content',
    ];

    // Long text content (blog posts, articles)
    protected array $longTextFields = [
        'content',  // Also used as JSON builder field
    ];

    // Rich text content (HTML, formatted text)
    protected array $richTextFields = [
        'rich_content',
    ];

    // Special fields with custom handlers
    protected array $specialFields = [
        'slug' => 'slug',
        'email' => 'author_email',
    ];
}