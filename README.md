# Filament Text Extractor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vasilgerginski/filamentphp-text-extractor.svg?style=flat-square)](https://packagist.org/packages/vasilgerginski/filamentphp-text-extractor)
[![Total Downloads](https://img.shields.io/packagist/dt/vasilgerginski/filamentphp-text-extractor.svg?style=flat-square)](https://packagist.org/packages/vasilgerginski/filamentphp-text-extractor)
[![License](https://img.shields.io/packagist/l/vasilgerginski/filamentphp-text-extractor.svg?style=flat-square)](https://packagist.org/packages/vasilgerginski/filamentphp-text-extractor)

A powerful Laravel package that seamlessly integrates with Filament Admin Panel to extract and manage translatable text from your Eloquent models. Perfect for multi-language applications, content management systems, and any project requiring dynamic text extraction and translation management.

## 🚀 Features

- **🔍 Automatic Text Extraction**: Extract translatable fields from any Eloquent model with a simple trait
- **🎨 Filament Integration**: Beautiful admin interface built with Filament for managing extracted texts
- **📝 Rich Text Support**: Extracts text from HTML, Markdown, and rich text editor content
- **🏗️ Filament Builder Support**: Automatically extracts text from Filament Builder JSON blocks
- **🌍 Multi-locale Support**: Built-in support for multiple languages and locales
- **📊 Extraction Dashboard**: Analytics and overview of all extracted texts
- **⚡ Queue Support**: Handle bulk text extraction asynchronously for better performance
- **🔧 Flexible Field Handlers**: Specialized handlers for different field types (email, URL, rich text, etc.)
- **🧪 Well Tested**: Comprehensive test suite included
- **📦 Easy Installation**: Simple setup with minimal configuration

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Filament 3.0 or higher

## 🔧 Installation

Install the package via Composer:

```bash
composer require vasilgerginski/filamentphp-text-extractor
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-text-extractor-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="filament-text-extractor-config"
```

## 🎯 Quick Start

### 1. Add the Trait to Your Model

Add the `ExtractsTranslatableText` trait to any model you want to extract text from:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use VasilGerginski\FilamentTextExtractor\Traits\ExtractsTranslatableText;

class BlogPost extends Model
{
    use ExtractsTranslatableText;
    
    // Define which fields should be extracted
    protected array $translatableFields = [
        'title',
        'content',
        'excerpt',
        'meta_description',
    ];
}
```

### 2. Extract Text

You can extract text from a single model instance:

```php
$blogPost = BlogPost::find(1);
$blogPost->extractTranslatableText();
```

Or extract from all instances of a model:

```php
use VasilGerginski\FilamentTextExtractor\Jobs\ExtractTextJob;

// Extract synchronously
ExtractTextJob::dispatchSync(BlogPost::class);

// Or dispatch to queue
ExtractTextJob::dispatch(BlogPost::class);
```

### 3. Use the Artisan Command

Extract text for any model using the provided Artisan command:

```bash
# Extract from all blog posts
php artisan text-extractor:extract "App\Models\BlogPost"

# Extract with specific options
php artisan text-extractor:extract "App\Models\BlogPost" --queue
```

## 📄 Advanced Features

### Filament Builder Block Extraction

The package automatically detects and extracts text from Filament Builder JSON blocks. When you have a Builder field with blocks like:

```php
Builder::make('content')
    ->blocks([
        Builder\Block::make('heading')
            ->schema([
                TextInput::make('content')
                    ->label('Heading text')
            ]),
        Builder\Block::make('paragraph')
            ->schema([
                Textarea::make('content')
                    ->label('Paragraph text')
            ]),
        Builder\Block::make('image')
            ->schema([
                FileUpload::make('image'),
                TextInput::make('alt_text')
            ]),
    ])
```

The extractor will automatically:
- Parse the JSON structure
- Extract text from all text-based fields within blocks
- Maintain context with keys like `model.field.block_type.field_name`
- Skip non-text fields like images (but extract alt text!)

### Rich Text Extraction

For rich text fields (TinyMCE, Trix, etc.), the package:
- Strips HTML tags to extract clean text
- Preserves meaningful content structure
- Handles nested HTML elements
- Extracts alt text from images
- Processes both plain HTML and encoded content

Example:
```php
// Original rich text content
$content = '<h1>Welcome</h1><p>This is <strong>important</strong> text.</p>';

// Extracted as
'Welcome This is important text.'
```

## 🎨 Filament Admin Interface

The package automatically registers a Filament resource for managing extracted texts. After installation, you'll find a new "Extracted Texts" section in your Filament admin panel with:

- **List View**: Browse all extracted texts with search and filters
- **Edit View**: Update extracted text content and metadata
- **Create View**: Manually add new text entries
- **Dashboard**: Overview of extraction statistics and recent activity

### Dashboard Features

The extraction dashboard provides:
- Total number of extracted texts
- Breakdown by model type
- Language distribution
- Recent extractions
- Quick actions for bulk operations

## ⚙️ Configuration

The configuration file (`config/filament-text-extractor.php`) allows you to customize:

```php
return [
    // Default locale for extracted texts
    'default_locale' => 'en',
    
    // Available locales
    'locales' => ['en', 'es', 'fr', 'de'],
    
    // Auto-generate Laravel translation files
    'auto_generate_lang_files' => true,
    
    // Translation file naming pattern
    'lang_file_pattern' => '{model_name}.php', // e.g., blog_posts.php
    
    // Queue name for extraction jobs
    'queue' => 'default',
    
    // Fields to exclude from extraction
    'excluded_fields' => ['id', 'password', 'remember_token'],
    
    // Custom field handlers
    'field_handlers' => [
        'email' => \VasilGerginski\FilamentTextExtractor\FieldHandlers\EmailHandler::class,
        'url' => \VasilGerginski\FilamentTextExtractor\FieldHandlers\UrlHandler::class,
        // Add your custom handlers here
    ],
];
```

## 🔄 Field Handlers

The package includes specialized handlers for different field types:

- **EmailHandler**: Validates and formats email fields
- **UrlHandler**: Handles URL validation and formatting
- **RichTextHandler**: Processes HTML/rich text content, strips tags and extracts clean text
- **SlugHandler**: Manages URL slugs
- **LongTextHandler**: Optimized for large text fields
- **Built-in JSON Support**: Automatically detects and extracts text from Filament Builder blocks and other JSON structures

### Creating Custom Field Handlers

You can create custom field handlers by extending the `AbstractFieldHandler`:

```php
<?php

namespace App\FieldHandlers;

use VasilGerginski\FilamentTextExtractor\FieldHandlers\AbstractFieldHandler;

class PhoneNumberHandler extends AbstractFieldHandler
{
    public function handle(mixed $value): ?string
    {
        // Your custom logic here
        return $this->formatPhoneNumber($value);
    }
    
    public function shouldExtract(mixed $value): bool
    {
        return !empty($value) && $this->isValidPhoneNumber($value);
    }
}
```

## 🌐 Auto-Generated Translation Files

The package can automatically generate Laravel translation files in your `lang` directory, enabling seamless localization:

### Automatic Translation File Generation

When texts are extracted, the package automatically creates/updates translation files:

```php
// After extraction, files are created in:
// lang/en/blog_posts.php
// lang/es/blog_posts.php
// etc.

// Example generated file content:
return [
    'post_1_title' => 'Welcome to our Blog',
    'post_1_content' => 'This is the first blog post content...',
    'post_1_excerpt' => 'A brief introduction to our blog',
    // ... more extracted texts
];
```

### Using Translations in Your Models

With the generated translation files, you can easily access localized content:

```php
// In your Blade views
{{ __('blog_posts.post_1_title') }}

// Or create a model accessor
class BlogPost extends Model
{
    use ExtractsTranslatableText;
    
    public function getLocalizedTitleAttribute()
    {
        return __("blog_posts.post_{$this->id}_title");
    }
    
    public function getLocalizedContentAttribute()
    {
        return __("blog_posts.post_{$this->id}_content");
    }
}

// Usage
$post = BlogPost::find(1);
echo $post->localized_title; // Automatically uses current locale
```

### Automatic Model Casting

You can even create a custom cast for automatic translation:

```php
use VasilGerginski\FilamentTextExtractor\Casts\Translatable;

class BlogPost extends Model
{
    protected $casts = [
        'title' => Translatable::class,
        'content' => Translatable::class,
        'excerpt' => Translatable::class,
    ];
}

// Now these fields automatically return translated values
$post = BlogPost::find(1);
echo $post->title; // Returns translated title based on current locale
```

### Translation Workflow

1. **Extract**: Run extraction to populate the database and generate translation files
2. **Translate**: Edit the generated files in `lang/[locale]/` or use the Filament admin
3. **Deploy**: Your application automatically uses the translated values

```bash
# Extract and generate translation files
php artisan text-extractor:extract "App\Models\BlogPost" --generate-lang-files

# Files created:
# - lang/en/blog_posts.php
# - lang/es/blog_posts.php (if Spanish is configured)
# - lang/fr/blog_posts.php (if French is configured)
```

## 🧪 Testing

The package includes a comprehensive test suite. Run the tests with:

```bash
composer test
```

For code coverage:

```bash
composer test-coverage
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 🐛 Bug Reports

If you discover any issues, please create an issue on GitHub:
https://github.com/vasilGerginski/filamentphp-text-extractor/issues

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🔒 Security

If you discover any security-related issues, please email vasil.gerginsky@gmail.com instead of using the issue tracker.

## 👏 Credits

- [Vasil Gerginski](https://github.com/vasilGerginski)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🌟 Show Your Support

If you find this package helpful, please consider giving it a star on GitHub! ⭐

---

Built with ❤️ by [Vasil Gerginski](https://github.com/vasilGerginski)