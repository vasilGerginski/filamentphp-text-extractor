<?php

return [
    'enabled' => env('TEXT_EXTRACTION_ENABLED', true),
    'default_locale' => env('APP_LOCALE', 'en'),
    'auto_extract_on_save' => env('AUTO_EXTRACT_ON_SAVE', true),
    'queue_extraction' => env('QUEUE_TEXT_EXTRACTION', false),
    'queue_name' => env('TEXT_EXTRACTION_QUEUE', 'default'),
    
    // Language file generation
    'auto_generate_lang_files' => env('AUTO_GENERATE_LANG_FILES', true),
    'lang_file_pattern' => '{model_name}.php', // e.g., blog_posts.php
    'locales' => ['en', 'es', 'fr', 'de'], // Available locales
    
    // Special field handlers
    'field_handlers' => [
        'slug' => \VasilGerginski\FilamentTextExtractor\FieldHandlers\SlugHandler::class,
        'email' => \VasilGerginski\FilamentTextExtractor\FieldHandlers\EmailHandler::class,
        'url' => \VasilGerginski\FilamentTextExtractor\FieldHandlers\UrlHandler::class,
        'long_text' => \VasilGerginski\FilamentTextExtractor\FieldHandlers\LongTextHandler::class,
        'rich_text' => \VasilGerginski\FilamentTextExtractor\FieldHandlers\RichTextHandler::class,
    ],
    
    // Text filters
    'min_text_length' => 2,
    'max_text_length' => 500,
    'skip_system_keys' => ['id', 'type', 'component', 'statePath', 'class', 'style', 'key'],
    'text_like_keys' => ['title', 'content', 'text', 'label', 'description', 'message', 'heading', 'caption', 'alt'],
    
    // Long text handling
    'long_text' => [
        'threshold' => 500, // Characters above which text is considered "long"
        'chunk_size' => 200, // Size of text chunks for paragraph-based extraction
        'extract_sentences' => true, // Extract individual sentences
        'extract_paragraphs' => true, // Extract paragraphs as units
        'extract_headings' => true, // Extract headings from HTML/Markdown
        'min_sentence_length' => 10,
        'skip_code_blocks' => true,
        'preserve_html_structure' => true,
    ],
    
    // Rich text processing
    'rich_text' => [
        'strip_html_tags' => ['script', 'style', 'meta', 'link'],
        'preserve_formatting_tags' => ['strong', 'em', 'b', 'i', 'u'],
        'extract_alt_text' => true,
        'extract_link_text' => true,
        'extract_headings_separately' => true,
    ],
];